<?php

use App\Http\Controllers\Api\Cr\CrSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'web'])->prefix('cr')->name('cr.')->group(function () {
    // Initialize a new session or load existing one
    Route::post('/init-session', [CrSessionController::class, 'initSession'])
        ->name('init-session');

    // Batch update student periods and scores
    Route::patch('/batch', [CrSessionController::class, 'batchUpdate'])
        ->name('batch');
});
