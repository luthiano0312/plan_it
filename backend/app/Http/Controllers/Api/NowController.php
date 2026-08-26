<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NowItemResource;
use App\Services\PriorityScorer;
use App\Services\TimerService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NowController extends Controller
{
    public function __construct(
        private readonly PriorityScorer $scorer,
        private readonly TimerService $timer,
    ) {
    }

    public function __invoke(): AnonymousResourceCollection
    {
        $aberta = $this->timer->current();

        $itens = $this->scorer->shortlist();
        $itens->load('parent'); // parent_title para a tela Agora
        $itens->each(function ($item) use ($aberta): void {
            $item->is_running = $aberta !== null && $aberta->item_id === $item->id;
        });

        return NowItemResource::collection($itens);
    }
}
