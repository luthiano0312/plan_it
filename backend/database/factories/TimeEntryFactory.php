<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\TimeEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'started_at' => fn () => now()->subMinutes(fake()->numberBetween(5, 60)),
            // alternando sessões fechadas e abertas
            'ended_at' => fake()->boolean() ? now() : null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => ['ended_at' => null]);
    }
}
