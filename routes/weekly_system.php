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

// =====================================================================
// TEACHER ROUTES (outside the weekly-system group to have direct access)
// =====================================================================

// Teacher's Weekly Plans Editor (available at /teacher/my-weekly-plans)
Route::middleware(['auth', 'verified'])->get('/teacher/my-weekly-plans', function () {
    return Inertia::render('my_table_mnger/weekly_system/teacher/SimpleWeeklyPlans');
})->name('teacher.my-weekly-plans');

// Timetable Editor (Moved from inside weekly-system group)
Route::middleware(['auth', 'verified'])->get('/admin/schedules/dashboard', function () {
    return Inertia::render('my_table_mnger/weekly_system/admin/TimetableEditor');
})->name('admin.schedules.dashboard');

// =====================================================================
// WEEKLY SYSTEM ROUTES (admin and teacher routes under /weekly-system)
// =====================================================================

Route::middleware(['auth', 'verified'])->prefix('weekly-system')->name('weekly-system.')->group(function () {
    
    // =====================================================================
    // ADMIN ROUTES
    // =====================================================================
    
    // School Browser
    Route::get('/school-browser', [App\Http\Controllers\SchoolBrowserController::class, 'index'])
        ->name('school-browser');
    
    // Schedule Copies Management - DEPRECATED/REMOVED
    /*
    Route::get('/schedule-copies', function () {
        return Inertia::render('my_table_mnger/weekly_system/admin/ScheduleCopiesIndex');
    })->name('schedule-copies.index');
    */
    
    // Timetable Editor
    // Timetable Editor - MOVED to /admin/schedules/dashboard
    /*
    Route::get('/timetable-editor', function () {
        return Inertia::render('my_table_mnger/weekly_system/admin/TimetableEditor');
    })->name('timetable-editor');
    */
    
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

            // Copy teacher weekly plans (old immediate copy)
            Route::post('/teacher/copy-plans-classrooms', [WeeklySystemController::class, 'copyPlansClassrooms'])
                ->name('api.teacher-copy-plans');

            // New staged copy: preview then commit
            Route::post('/teacher/copy-plans-classrooms/preview', [WeeklySystemController::class, 'previewCopyPlansClassrooms'])
                ->name('api.teacher-copy-plans.preview');
            Route::post('/teacher/copy-plans-classrooms/commit', [WeeklySystemController::class, 'commitCopyPlansClassrooms'])
                ->name('api.teacher-copy-plans.commit');

            // Update schedule period_order (inline edit from table)
            Route::put('/schedules/{schedule}/period-order', [WeeklySystemController::class, 'updateSchedulePeriodOrder'])
                ->name('api.schedules.update-period-order');
        
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

        // Slot availability for conflict detection
        Route::get('/slot-availability', [\App\Http\Controllers\ScheduleController::class, 'getSlotAvailability'])
            ->name('api.slot-availability');

        // Get all teacher conflicts for the grid display
        Route::get('/teacher-conflicts', [\App\Http\Controllers\ScheduleController::class, 'getTeacherConflicts'])
            ->name('api.teacher-conflicts');

        // AI Import endpoints
        Route::post('/ai-import/validate', [\App\Http\Controllers\ScheduleController::class, 'validateAIImport'])
            ->name('api.ai-import.validate');
        Route::post('/ai-import/apply', [\App\Http\Controllers\ScheduleController::class, 'applyAIImport'])
            ->name('api.ai-import.apply');
        Route::post('/ai-import/update', [\App\Http\Controllers\ScheduleController::class, 'applyAIUpdate'])
            ->name('api.ai-import.update');

        // Random Fill endpoints
        Route::post('/random-fill/preview', [\App\Http\Controllers\ScheduleController::class, 'generateRandomFillPreview'])
            ->name('api.random-fill.preview');
        Route::post('/random-fill/apply', [\App\Http\Controllers\ScheduleController::class, 'applyRandomFill'])
            ->name('api.random-fill.apply');

        // Draft Management endpoints
        Route::post('/drafts/save', [\App\Http\Controllers\ScheduleController::class, 'saveDraft'])
            ->name('api.drafts.save');
        Route::post('/drafts/load', [\App\Http\Controllers\ScheduleController::class, 'loadDraft'])
            ->name('api.drafts.load');
        Route::get('/drafts', [\App\Http\Controllers\ScheduleController::class, 'getDrafts'])
            ->name('api.drafts.index');
        Route::post('/drafts/delete', [\App\Http\Controllers\ScheduleController::class, 'deleteDraft'])
            ->name('api.drafts.delete');
        Route::post('/drafts/compare', [\App\Http\Controllers\ScheduleController::class, 'compareDrafts'])
            ->name('api.drafts.compare');

        // Note: Schedule Sync endpoints removed as Schedule Copies are deprecated

        // CST Overview endpoint
        Route::post('/cst-overview', [\App\Http\Controllers\ClassroomSubjectTeacherController::class, 'getCSTOverview'])
            ->name('api.cst-overview');

        // CST Management endpoints
        Route::put('/cst/{id}/classes-per-week', [\App\Http\Controllers\ClassroomSubjectTeacherController::class, 'updateClassesPerWeek'])
            ->name('api.cst.update-classes-per-week');
        Route::post('/cst-bulk-update-classes-per-week', [\App\Http\Controllers\ClassroomSubjectTeacherController::class, 'bulkUpdateClassesPerWeek'])
            ->name('api.cst.bulk-update-classes-per-week');
        Route::post('/cst-sync-classes-per-week', [\App\Http\Controllers\ClassroomSubjectTeacherController::class, 'syncClassesPerWeek'])
            ->name('api.cst.sync-classes-per-week');
        Route::post('/cst/{id}/restore', [\App\Http\Controllers\ClassroomSubjectTeacherController::class, 'restore'])
            ->name('api.cst.restore');
        Route::delete('/cst/{id}', [\App\Http\Controllers\ClassroomSubjectTeacherController::class, 'softDelete'])
            ->name('api.cst.soft-delete');
        
        // Assignment (CST) CRUD endpoints for SchoolBrowser
        Route::put('/assignments/{assignment}', [\App\Http\Controllers\ClassroomSubjectTeacherController::class, 'update'])
            ->name('api.assignments.update');
        Route::delete('/assignments/{assignment}', [\App\Http\Controllers\ClassroomSubjectTeacherController::class, 'destroy'])
            ->name('api.assignments.destroy');
        Route::post('/assignments', [\App\Http\Controllers\ClassroomSubjectTeacherController::class, 'store'])
            ->name('api.assignments.store');

        // Sync analysis dashboard
        Route::get('/sync-analysis', [WeeklySystemController::class, 'getSyncAnalysis'])
            ->name('api.sync-analysis');

        // Batch create weekly plans
        Route::post('/weekly-plans/batch-create', [WeeklySystemController::class, 'batchCreate'])
            ->name('api.batch-create');
    });
});