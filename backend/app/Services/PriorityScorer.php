<?php

namespace App\Services;

use App\Models\Item;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class PriorityScorer
{
    private float $dueWeight;

    private float $easeWeight;

    private float $urgencyMax;

    private int $horizonDays;

    private float $noDueUrgency;

    private int $defaultShortlistSize;

    public function __construct()
    {
        $this->dueWeight = (float) config('planit.priority.due_weight');
        $this->easeWeight = (float) config('planit.priority.ease_weight');
        $this->urgencyMax = (float) config('planit.priority.urgency_max');
        $this->horizonDays = max(1, (int) config('planit.priority.urgency_horizon_days'));
        $this->noDueUrgency = (float) config('planit.priority.urgency_no_due');
        $this->defaultShortlistSize = (int) config('planit.shortlist_size');
    }

    /**
     * Urgência saturada: hoje/vencido → teto; futuro decai linearmente até
     * zerar no fim do horizonte; sem prazo → valor fixo baixo.
     */
    public function urgency(?CarbonInterface $dueDate): float
    {
        if ($dueDate === null) {
            return $this->noDueUrgency;
        }

        $daysUntilDue = Carbon::today()->startOfDay()->diffInDays($dueDate->copy()->startOfDay());
        $linear = $this->urgencyMax * (1 - $daysUntilDue / $this->horizonDays);

        return max(0.0, min($this->urgencyMax, $linear));
    }

    public function ease(int $effort): float
    {
        return 6 - $effort;
    }

    public function score(Item $item): float
    {
        return $this->urgency($item->due_date) * $this->dueWeight
            + $this->ease($item->effort) * $this->easeWeight;
    }

    /**
     * Acionáveis ordenados por manual_priority ASC (nulos por último) e,
     * entre os manuais-empatados/automáticos, score DESC.
     */
    public function shortlist(?int $limit = null): Collection
    {
        return Item::actionable()
            ->get()
            ->each(fn (Item $item) => $item->score = $this->score($item))
            ->sort(function (Item $a, Item $b) {
                if ($a->manual_priority !== null || $b->manual_priority !== null) {
                    if ($a->manual_priority === null) {
                        return 1;
                    }
                    if ($b->manual_priority === null) {
                        return -1;
                    }

                    return $a->manual_priority <=> $b->manual_priority;
                }

                return $b->score <=> $a->score;
            })
            ->take($limit ?? $this->defaultShortlistSize)
            ->values();
    }
}
