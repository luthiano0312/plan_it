<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TimeEntryResource;
use App\Models\Item;
use App\Services\TimerService;
use Illuminate\Http\JsonResponse;

class TimerController extends Controller
{
    public function __construct(private readonly TimerService $timer)
    {
    }

    public function start(Item $item): JsonResponse
    {
        $entry = $this->timer->start($item);
        $entry->setRelation('item', $item);

        return response()->json(TimeEntryResource::make($entry)->resolve(), 201);
    }

    public function stop(): JsonResponse
    {
        $entry = $this->timer->stopCurrent();

        return response()->json([
            'data' => $entry !== null ? TimeEntryResource::make($entry)->resolve() : null,
        ]);
    }

    public function current(): JsonResponse
    {
        $entry = $this->timer->current();

        return response()->json([
            'data' => $entry !== null ? TimeEntryResource::make($entry)->resolve() : null,
        ]);
    }
}
