<?php

namespace App\Services;

use App\Enums\ItemStatus;
use App\Models\Item;

class ItemTransitionService
{
    /**
     * ÚNICO ponto de transição automática de status de Item.
     */

    public function markInProgressIfNeeded(Item $item): void
    {
        if ($item->status === ItemStatus::Pendente) {
            $item->forceFill(['status' => ItemStatus::EmAndamento])->save();
        }
    }

    public function complete(Item $item): Item
    {
        // primeira conclusão vence: concluir item JÁ concluído (sem reabertura
        // no meio) preserva o completed_at original
        if ($item->status !== ItemStatus::Concluido) {
            $item->forceFill([
                'status' => ItemStatus::Concluido,
                'completed_at' => now(),
            ])->save();
        }

        $this->propagateCompletion($item);

        return $item;
    }

    /**
     * Sobe pela cadeia de pais: conclui cada pai que ainda não está
     * concluído e cujos filhos diretos estão todos concluídos.
     */
    public function propagateCompletion(Item $child): void
    {
        $parent = $child->parent;

        while ($parent !== null) {
            $todosConcluidos = $parent->children()
                ->whereNot('status', ItemStatus::Concluido)
                ->doesntExist();

            if ($parent->status === ItemStatus::Concluido || ! $todosConcluidos) {
                break;
            }

            $parent->forceFill([
                'status' => ItemStatus::Concluido,
                'completed_at' => now(),
            ])->save();

            $parent = $parent->parent;
        }
    }
}
