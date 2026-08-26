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
            // description, parent_id, due_date e manual_priority ficam null/default.
            // status usa o default 'pendente' da migration.
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
