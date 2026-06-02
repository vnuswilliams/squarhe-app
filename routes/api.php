<?php

use App\Http\Controllers\Api\SyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/device/register', [SyncController::class, 'register']);
    Route::post('/sync', [SyncController::class, 'sync']);
    Route::get('/sync/snapshot', [SyncController::class, 'snapshot']);
});
