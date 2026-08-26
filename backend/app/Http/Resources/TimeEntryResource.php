<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\TimeEntry $resource
 */
class TimeEntryResource extends JsonResource
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
            'item_id' => $this->resource->item_id,
            'started_at' => $this->resource->started_at->toISOString(),
            'ended_at' => $this->resource->ended_at?->toISOString(),
            'duration_seconds' => (int) round($this->resource->started_at->diffInSeconds($this->resource->ended_at ?? now())),
            'item' => $this->whenLoaded('item', fn () => ItemResource::make($this->resource->item)->resolve()),
        ];
    }
}
