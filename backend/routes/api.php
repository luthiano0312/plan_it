<?php

use App\Http\Controllers\Api\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('/healthcheck', fn () => response()->json(['ok' => true]));

Route::apiResource('items', ItemController::class);
