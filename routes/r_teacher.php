<?php

use App\Http\Controllers\PresentationController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionTypeController;
use App\Http\Controllers\Teacher\TeacherNewTimeLineController;
use App\Http\Controllers\PeriodActivityController;

// Teacher routes
Route::get('teacher/question_types', [QuestionTypeController::class, 'index'])->name('question_types');
Route::middleware(['auth', 'role:teacher', 'role:admin'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/home', [TeacherController::class, 'home'])->name('home');
    Route::get('/classes', [TeacherController::class, 'classes'])->name('classes');
    Route::get('/attendance', [TeacherController::class, 'attendance'])->name('attendance');
    Route::get('/grades', [TeacherController::class, 'grades'])->name('grades');
    Route::post('/students', [TeacherController::class, 'students'])->name('students2');
    Route::post('/classes/students', [TeacherController::class, 'classes_students'])->name('classes_students');
    
    Route::get('/getTeacherClasses', [TeacherController::class, 'getTeacherClasses'])->name('getTeacherClasses');
    Route::get('/lesson_presentation', [TeacherController::class, 'lesson_presentation'])->name('lesson_presentation');

    // // Period Activities Routes
    // Route::get('/period-activities', [PeriodActivityController::class, 'index'])->name('period-activities.index');
    // Route::get('/period-activities/create', [PeriodActivityController::class, 'create'])->name('period-activities.create');
    // Route::post('/period-activities', [PeriodActivityController::class, 'store'])->name('period-activities.store');
    // Route::get('/period-activities/{periodActivity}/edit', [PeriodActivityController::class, 'edit'])->name('period-activities.edit');
    // Route::put('/period-activities/{periodActivity}', [PeriodActivityController::class, 'update'])->name('period-activities.update');
    // Route::delete('/period-activities/{periodActivity}', [PeriodActivityController::class, 'destroy'])->name('period-activities.destroy');
});

// Message routes have been moved to web.php




Route::middleware(['auth', 'role:teacher'])->prefix('api/teacher')->name('teacher.')->group(function () {
        Route::post('/students', [TeacherController::class, 'students'])->name('students');
        Route::post('/store_presentation', [ PresentationController::class, 'store_presentation'])
        ->name('presentation_store');


    Route::get('/teacher/schedule/{school_id}/{teacher_id}',
        [App\Http\Controllers\Teacher\ScheduleTeacherController::class, 'getTeacherScheduleData'])
        ->name('schedule.data');

        // Route::post('/teacher', [TeacherController::class, 'students'])->name('students');
});


// Student routes
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/home', [StudentController::class, 'home'])->name('home');
    Route::get('/schedule', [StudentController::class, 'schedule'])->name('schedule');
    Route::get('/grades', [StudentController::class, 'grades'])->name('grades');
    Route::get('/attendance', [StudentController::class, 'attendance'])->name('attendance');
});

// Admin routes (you likely already have these)
// Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
//     Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
//     // ... your existing admin routes
// });



// Teacher Timeline Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/timeline', [TeacherNewTimeLineController::class, 'index'])
            ->name('timeline.index');
                 Route::get('/timeline', [TeacherNewTimeLineController::class, 'index'])
            ->name('timeline');

        Route::post('/timeline/schedule', [TeacherNewTimeLineController::class, 'getTeacherSchedule'])
            ->name('timeline.schedule');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/teacher/dashboard/classrooms', [\App\Http\Controllers\Teacher\TeacherDashboardController::class, 'classrooms'])
        ->name('teacher.dashboard.classrooms');
});
