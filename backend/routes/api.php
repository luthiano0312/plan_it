<?php

use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\NowController;
use App\Http\Controllers\Api\TimerController;
use Illuminate\Support\Facades\Route;

Route::get('/healthcheck', fn () => response()->json(['ok' => true]));

Route::get('/now', NowController::class);

Route::apiResource('items', ItemController::class);

Route::post('/items/{item}/timer/start', [TimerController::class, 'start']);
Route::post('/timer/stop', [TimerController::class, 'stop']);
Route::get('/timer/current', [TimerController::class, 'current']);

