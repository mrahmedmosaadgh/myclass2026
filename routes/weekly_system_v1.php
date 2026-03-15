<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeeklySystemV1\WeeklySystemController;

/*
|--------------------------------------------------------------------------
| Weekly System V1 Routes
|--------------------------------------------------------------------------
|
| This route file implements the Feature-First architecture for the
| Weekly System module (Version 1).
|
*/

Route::middleware(['auth', 'verified'])->prefix('weekly-system-v1')->name('weekly-system-v1.')->group(function () {
    
    // Dashboard - renders different views based on role
    Route::get('/', [WeeklySystemController::class, 'dashboard'])->name('dashboard');
    
    // Curriculum & Lessons - Single route, diverging response
    Route::get('/curriculum-lessons', [WeeklySystemController::class, 'curriculumLessonsIndex'])
        ->name('curriculum-lessons.index');
    
    // Weekly Plans Manager - Admin sees all, Teacher sees own
    Route::get('/weekly-plans-manager', [WeeklySystemController::class, 'weeklyPlansManager'])
        ->name('weekly-plans-manager');
    
    // My Weekly Plans (Teacher)
    Route::get('/my-weekly-plans', [WeeklySystemController::class, 'myWeeklyPlans'])
        ->name('my-weekly-plans');
    
    // Timetable Editor (placeholder - to be implemented)
    // Route::get('/timetable-editor', [WeeklySystemController::class, 'timetableEditor'])
    //     ->name('timetable-editor');
    
    // Schedule Copies (Admin only - placeholder)
    // Route::get('/schedule-copies', [WeeklySystemController::class, 'scheduleCopiesIndex'])
    //     ->name('schedule-copies.index');
    
    // API endpoints
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/curricula', [WeeklySystemController::class, 'getCurriculaApi'])
            ->name('curricula.index');
    });
});
