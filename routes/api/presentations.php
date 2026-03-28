<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PresentationController;
use App\Http\Controllers\API\PresentationCategoryController;

/*
|--------------------------------------------------------------------------
| API Routes - Presentations
|--------------------------------------------------------------------------
|
| These routes handle all presentation-related API endpoints for the
| offline-first presentation system with MySQL database backend.
|
*/

// Presentation Categories
Route::prefix('categories')->group(function () {
    Route::get('/', [PresentationCategoryController::class, 'index']);
    Route::post('/', [PresentationCategoryController::class, 'store']);
    Route::get('/stats', [PresentationCategoryController::class, 'stats']);
    Route::get('/{category}', [PresentationCategoryController::class, 'show']);
    Route::put('/{category}', [PresentationCategoryController::class, 'update']);
    Route::delete('/{category}', [PresentationCategoryController::class, 'destroy']);
});

// Presentations
Route::prefix('presentations')->group(function () {
    Route::get('/', [PresentationController::class, 'index']);
    Route::post('/', [PresentationController::class, 'store']);
    Route::get('/stats', [PresentationController::class, 'stats']);
    Route::get('/search', [PresentationController::class, 'search']);
    Route::post('/{presentation}/duplicate', [PresentationController::class, 'duplicate']);
    Route::get('/{presentation}', [PresentationController::class, 'show']);
    Route::put('/{presentation}', [PresentationController::class, 'update']);
    Route::delete('/{presentation}', [PresentationController::class, 'destroy']);
});

// Aliases for cleaner URLs
Route::get('/categories', [PresentationCategoryController::class, 'index']);
Route::get('/categories/stats', [PresentationCategoryController::class, 'stats']);
Route::get('/presentations', [PresentationController::class, 'index']);
Route::get('/presentations/stats', [PresentationController::class, 'stats']);
Route::get('/presentations/search', [PresentationController::class, 'search']);
