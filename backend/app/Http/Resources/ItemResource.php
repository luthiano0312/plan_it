<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Item $resource
 */
class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'parent_id' => $this->resource->parent_id,
            'parent_title' => $this->whenLoaded('parent', fn () => $this->resource->parent?->title),
            'due_date' => $this->resource->due_date?->format('Y-m-d'),
            'effort' => $this->resource->effort,
            'manual_priority' => $this->resource->manual_priority,
            'status' => $this->resource->status->value,
            'completed_at' => $this->resource->completed_at?->toISOString(),
            'is_leaf' => $this->contagemDeFilhos() === 0,
            'total_seconds' => (int) ($this->resource->total_seconds ?? 0),
            'children' => ItemResource::collection($this->whenLoaded('children')),
            'time_sessions' => $this->whenLoaded(
                'timeEntries',
                fn () => $this->resource->timeEntries->map(fn ($entry) => [
                    'id' => $entry->id,
                    'started_at' => $entry->started_at->toISOString(),
                    'ended_at' => $entry->ended_at?->toISOString(),
                    'duration_seconds' => (int) round($entry->started_at->diffInSeconds($entry->ended_at ?? now())),
                ]),
            ),
        ];
    }

    private function contagemDeFilhos(): int
    {
        if (isset($this->resource->children_count)) {
            return (int) $this->resource->children_count;
        }

        return $this->resource->relationLoaded('children')
            ? $this->resource->children->count()
            : $this->resource->children()->count();
    }
}
