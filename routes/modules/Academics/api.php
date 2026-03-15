<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Academics\SubjectApiController;
use App\Http\Controllers\Api\Cr\CrSessionController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('academics/subjects', SubjectApiController::class)->names('api.academics.subjects');
});

// Classroom Records API Routes
Route::middleware(['auth:sanctum', 'web'])->prefix('cr')->name('cr.')->group(function () {
    Route::post('/init-session', [CrSessionController::class, 'initSession'])->name('init-session');
    Route::patch('/batch', [CrSessionController::class, 'batchUpdate'])->name('batch');
});
