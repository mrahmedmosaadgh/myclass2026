<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeeklySystemController;
use App\Http\Controllers\SchoolBrowserController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Weekly System Routes
|--------------------------------------------------------------------------
|
| Routes for the Weekly Schedule Management System
| - Admin: Schedule Copies, Timetable Editor, Weekly Plans Management
| - Teacher: My Schedule, My Weekly Plans
|
*/

Route::middleware(['auth', 'verified'])->prefix('weekly-system')->name('weekly-system.')->group(function () {
    
    // =====================================================================
    // ADMIN ROUTES
    // =====================================================================
    
    // School Browser
    Route::get('/school-browser', [App\Http\Controllers\SchoolBrowserController::class, 'index'])
        ->name('school-browser');
    
    // Schedule Copies Management
    Route::get('/schedule-copies', function () {
        return Inertia::render('my_table_mnger/weekly_system/admin/ScheduleCopiesIndex');
    })->name('schedule-copies.index');
    
    // Timetable Editor
    Route::get('/timetable-editor', function () {
        return Inertia::render('my_table_mnger/weekly_system/admin/TimetableEditor');
    })->name('timetable-editor');
    
    // Weekly Plans Manager (Admin Dashboard)
    Route::get('/weekly-plans-manager', function () {
        return Inertia::render('my_table_mnger/weekly_system/admin/WeeklyPlansManager');
    })->name('weekly-plans-manager');
    
    // =====================================================================
    // TEACHER ROUTES
    // =====================================================================
    
    // Teacher's Personal Schedule
    Route::get('/my-schedule', function () {
        return Inertia::render('my_table_mnger/weekly_system/teacher/MySchedule');
    })->name('my-schedule');
    
    // Teacher's Weekly Plans Editor
    Route::get('/my-weekly-plans', function () {
        return Inertia::render('my_table_mnger/weekly_system/teacher/MyWeeklyPlans');
    })->name('my-weekly-plans');
    
    // =====================================================================
    // API ROUTES (for weekly system pages)
    // =====================================================================
    
    Route::prefix('api')->group(function () {
        // Get school data for school browser
        Route::get('/school-data', [App\Http\Controllers\SchoolBrowserController::class, 'getSchoolData'])
            ->name('api.school-data');
        
        // Get teacher completion stats for a week
        Route::get('/weekly-plans/teacher-stats', [WeeklySystemController::class, 'getTeacherStats'])
            ->name('api.teacher-stats');
        
        // Generate weekly plans for a week
        Route::post('/weekly-plans/generate', [WeeklySystemController::class, 'generateWeeklyPlans'])
            ->name('api.generate');
        
        // Get teacher's own schedule
        Route::get('/teacher/my-schedule', [WeeklySystemController::class, 'getMySchedule'])
            ->name('api.my-schedule');
        
        // Get teacher's weekly plans for a specific week
        Route::get('/teacher/my-weekly-plans', [WeeklySystemController::class, 'getMyWeeklyPlans'])
            ->name('api.my-weekly-plans');
        
        // Get weekly plans (admin can filter by teacher_id)
        Route::get('/weekly-plans', [WeeklySystemController::class, 'getWeeklyPlans'])
            ->name('api.weekly-plans');
        
        // Update a weekly plan (CW/HW/Notes)
        Route::put('/weekly-plans/{weeklyPlan}', [WeeklySystemController::class, 'updateWeeklyPlan'])
            ->name('api.update');

        // Sync a weekly plan
        Route::post('/weekly-plans/{weeklyPlan}/sync', [WeeklySystemController::class, 'syncHelper'])
            ->name('api.sync');
            
        // Sync entire week
        Route::post('/weekly-plans/sync-week', [WeeklySystemController::class, 'syncWeek'])
            ->name('api.sync-week');
    });
});
