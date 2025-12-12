<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeeklyPlanController;
use App\Http\Controllers\WeeklyPlanSessionController;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    // Vue.js Pages Routes
    Route::get('/weekly-plans', function () {
        return Inertia::render('WeeklyPlans/Index');
    })->name('weekly-plans.index');
    
    Route::get('/weekly-plans/{weeklyPlan}/edit', function ($weeklyPlanId) {
        return Inertia::render('WeeklyPlans/Edit', [
            'weeklyPlanId' => $weeklyPlanId
        ]);
    })->name('weekly-plans.edit');
    
    // Resource Routes (for API fallback)
    Route::resource('weekly-plans', WeeklyPlanController::class)->except(['index', 'edit']);
    Route::resource('weekly-plan-sessions', WeeklyPlanSessionController::class);
    
    // API Routes for Weekly Plans
    Route::prefix('api/weeklyplansystem')->group(function () {
        // Weekly Plan Headers
        Route::get('headers', [WeeklyPlanController::class, 'index']);
        Route::post('headers', [WeeklyPlanController::class, 'store']);
        Route::get('headers/{weeklyPlan}', [WeeklyPlanController::class, 'show']);
        Route::put('headers/{weeklyPlan}', [WeeklyPlanController::class, 'update']);
        Route::delete('headers/{weeklyPlan}', [WeeklyPlanController::class, 'destroy']);
        Route::post('headers/generate-semester', [WeeklyPlanController::class, 'generateSemesterPlans']);
        
        // Weekly Plan Sessions
        Route::get('sessions', [WeeklyPlanSessionController::class, 'index']);
        Route::post('sessions', [WeeklyPlanSessionController::class, 'store']);
        Route::get('sessions/{session}', [WeeklyPlanSessionController::class, 'show']);
        Route::put('sessions/{session}', [WeeklyPlanSessionController::class, 'update']);
        Route::delete('sessions/{session}', [WeeklyPlanSessionController::class, 'destroy']);
        Route::post('sessions/reorder', [WeeklyPlanSessionController::class, 'reorder']);
        Route::post('sessions/bulk-update', [WeeklyPlanSessionController::class, 'bulkUpdate']);
    });
    
    // Legacy API Routes (for backward compatibility)
    Route::prefix('api')->group(function () {
        Route::get('weekly-plans/by-academic-year/{academicYearId}', [WeeklyPlanController::class, 'getByAcademicYear']);
        Route::get('weekly-plans/by-semester/{academicYearId}/{semester}', [WeeklyPlanController::class, 'getBySemester']);
        Route::get('weekly-plans/by-week/{academicYearId}/{semester}/{weekNumber}', [WeeklyPlanController::class, 'getByWeek']);
        Route::get('weekly-plans/by-cst/{cstId}', [WeeklyPlanController::class, 'getByCst']);
    });
});
