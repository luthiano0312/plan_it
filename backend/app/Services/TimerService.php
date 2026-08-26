<?php

namespace App\Services;

use App\Models\Item;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\DB;

class TimerService
{
    public function __construct(private ItemTransitionService $transitions)
    {
    }

    /**
     * Sessão aberta é global: iniciar num item fecha a sessão alheia;
     * iniciar no mesmo item com sessão aberta é idempotente.
     */
    public function start(Item $item): TimeEntry
    {
        return DB::transaction(function () use ($item) {
            $aberta = TimeEntry::query()->whereNull('ended_at')->first();

            if ($aberta !== null && $aberta->item_id === $item->id) {
                return $aberta;
            }

            $aberta?->forceFill(['ended_at' => now()])->save();

            $entry = TimeEntry::query()->create([
                'item_id' => $item->id,
                'started_at' => now(),
            ]);

            $this->transitions->markInProgressIfNeeded($item);

            return $entry;
        });
    }

    public function stopCurrent(): ?TimeEntry
    {
        return DB::transaction(function () {
            $aberta = TimeEntry::query()->whereNull('ended_at')->first();

            $aberta?->forceFill(['ended_at' => now()])->save();

            return $aberta;
        });
    }

    public function current(): ?TimeEntry
    {
        return TimeEntry::query()->whereNull('ended_at')->with('item')->first();
    }

    /**
     * Segundos próprios + de TODOS os descendentes; sessão aberta conta até now().
     */
    public function totalSeconds(Item $item): int
    {
        $ids = $item->descendantIds()->push($item->id);

        $total = TimeEntry::query()
            ->whereIn('item_id', $ids)
            ->get()
            ->sum(fn (TimeEntry $e) => $e->started_at->diffInSeconds($e->ended_at ?? now()));

        return (int) round($total);
    }
}
