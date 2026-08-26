<?php

namespace App\Http\Controllers\Api;

use App\Enums\ItemStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\TimeEntry;
use App\Services\ItemTransitionService;
use App\Services\TimerService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function __construct(
        private readonly ItemTransitionService $transitions,
        private readonly TimerService $timer,
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $items = Item::query()
            ->with('parent')
            ->withCount('children')
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('project'), fn ($query, $project) => $query->where('parent_id', $project))
            ->get();

        $items->each(fn (Item $item) => $item->total_seconds = $this->timer->totalSeconds($item));

        return ItemResource::collection($items);
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate($this->regras());

        return response()->json((new ItemResource(Item::create($dados)))->resolve(), 201);
    }

    public function show(Item $item): array
    {
        $item->load('parent');
        $item->load(['children' => fn ($q) => $q->withCount('children')]);

        // sessões do item + de todos os descendentes (mesmo escopo do total_seconds)
        $idsSubarvore = $item->descendantIds()->push($item->id);
        $item->setRelation(
            'timeEntries',
            TimeEntry::query()->whereIn('item_id', $idsSubarvore)->orderByDesc('started_at')->get(),
        );

        $item->children_count = $item->children->count();
        $item->total_seconds = $this->timer->totalSeconds($item);

        $item->children->each(function (Item $filho): void {
            $filho->total_seconds = $this->timer->totalSeconds($filho);
        });

        return ItemResource::make($item)->resolve();
    }

    public function update(Request $request, Item $item): array
    {
        $dados = $request->validate([
            ...$this->regras($item),
            'status' => ['nullable', 'string', Rule::in(array_column(ItemStatus::cases(), 'value'))],
        ]);

        $novoStatus = $dados['status'] ?? null;

        if ($novoStatus === ItemStatus::Concluido->value && $item->status !== ItemStatus::Concluido) {
            // mudança para concluido passa pelo ponto único de transição
            unset($dados['status']);
            $item->update($dados);
            $this->transitions->complete($item);
        } else {
            // saindo de concluido: decisão 6 — reabrir zera completed_at
            if ($item->status === ItemStatus::Concluido && $novoStatus !== null && $novoStatus !== ItemStatus::Concluido->value) {
                $dados['completed_at'] = null;
            }
            $item->update($dados);
        }

        return ItemResource::make($item->refresh())->resolve();
    }

    public function destroy(Item $item): Response
    {
        $item->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function regras(?Item $item = null): array
    {
        // no update o title é validado só quando vier no payload (PATCH parcial)
        $title = $item === null
            ? ['required', 'string', 'max:255']
            : ['sometimes', 'required', 'string', 'max:255'];

        return [
            'title' => $title,
            'description' => ['nullable', 'string'],
            'parent_id' => [
                'nullable',
                'exists:items,id',
                function (string $atributo, mixed $valor, Closure $falha) use ($item): void {
                    if ($valor === null || $item === null) {
                        return;
                    }
                    $ancestral = Item::find($valor);
                    while ($ancestral !== null) {
                        if ($ancestral->id === $item->id) {
                            $falha('O item não pode ter a si mesmo como ancestral.');
                            return;
                        }
                        $ancestral = $ancestral->parent;
                    }
                },
            ],
            'due_date' => ['nullable', 'date'],
            'effort' => ['integer', 'between:1,5'],
            'manual_priority' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
