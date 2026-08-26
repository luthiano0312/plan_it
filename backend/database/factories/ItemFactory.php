<?php

namespace Database\Factories;

use App\Enums\ItemStatus;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'effort' => fake()->numberBetween(1, 5),
            // explícito para instâncias recém-criadas terem o atributo em
            // memória (o default da migration só existe no banco)
            'status' => ItemStatus::Pendente,
            // description, parent_id, due_date e manual_priority ficam null/default.
        ];
    }

    public function concluded(): static
    {
        return $this->state(fn () => [
            'status' => ItemStatus::Concluido,
            'completed_at' => now(),
        ]);
    }

    public function forParent(Item $parent): static
    {
        return $this->state(fn () => [
            'parent_id' => $parent->id,
        ]);
    }
}
