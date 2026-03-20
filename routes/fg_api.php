<?php

use App\Http\Controllers\Fg\FgAiController;
use App\Http\Controllers\Fg\FgDomainController;
use App\Http\Controllers\Fg\FgNoteController;
use App\Http\Controllers\Fg\FgSessionController;
use App\Http\Controllers\Fg\FgSubTaskController;
use App\Http\Controllers\Fg\FgTaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Focus Grid API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with api/fg and use auth:sanctum middleware.
| Included from routes/api.php
|
*/

Route::middleware('auth:sanctum')->prefix('fg')->group(function () {
    
    // Domains
    Route::apiResource('domains', FgDomainController::class);
    
    // Tasks
    Route::post('tasks/sync', [FgTaskController::class, 'sync']);
    Route::apiResource('tasks', FgTaskController::class);
    
    // Sub-Tasks
    Route::apiResource('subtasks', FgSubTaskController::class);
    
    // Notes
    Route::post('notes/sync', [FgNoteController::class, 'sync']);
    Route::apiResource('notes', FgNoteController::class);
    
    // Sessions
    Route::apiResource('sessions', FgSessionController::class)->except(['destroy']);
    
    // AI
    Route::post('ai/vent', [FgAiController::class, 'vent']);
    
    // Global batch sync
    Route::get('sync', [\App\Http\Controllers\Fg\FgSyncController::class, 'pull']);
    Route::post('sync', [\App\Http\Controllers\Fg\FgSyncController::class, 'push']);
});
