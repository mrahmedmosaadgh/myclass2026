<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Academics\SubjectApiController;
use App\Http\Controllers\Api\Cr\CrSessionController;
use App\Http\Controllers\Api\Cr\CrCategoryMappingsController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('academics/subjects', SubjectApiController::class)->names('api.academics.subjects');
});

// Classroom Records API Routes
Route::middleware(['auth:sanctum', 'web'])->prefix('cr')->name('cr.')->group(function () {
    Route::post('/init-session', [CrSessionController::class, 'initSession'])->name('init-session');
    Route::patch('/batch', [CrSessionController::class, 'batchUpdate'])->name('batch');

    // Category mappings (scoring categories)
    Route::get('/category-mappings', [CrCategoryMappingsController::class, 'index']);
    Route::post('/category-mappings', [CrCategoryMappingsController::class, 'store']);
    Route::patch('/category-mappings/{mapping}', [CrCategoryMappingsController::class, 'update']);
    Route::delete('/category-mappings/{mapping}', [CrCategoryMappingsController::class, 'destroy']);
});
