<?php

use Illuminate\Support\Facades\Route;

Route::get('/healthcheck', fn () => response()->json(['ok' => true]));
