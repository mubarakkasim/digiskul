<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', [\App\Http\Controllers\HealthController::class, 'check']);

Route::get('/', function () {
    return response()->json([
        'name' => 'DIGISKUL API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});

