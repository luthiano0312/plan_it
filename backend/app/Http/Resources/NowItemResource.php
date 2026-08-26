<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Item $resource
 */
class NowItemResource extends JsonResource
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
            'parent_title' => $this->whenLoaded('parent', fn () => $this->resource->parent?->title),
            'due_date' => $this->resource->due_date?->format('Y-m-d'),
            'effort' => $this->resource->effort,
            'status' => $this->resource->status->value,
            'is_running' => (bool) ($this->resource->is_running ?? false),
            'score' => (float) ($this->resource->score ?? 0),
        ];
    }
}
