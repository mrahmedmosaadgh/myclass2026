<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Courses\bm\BMAssessmentController;
use App\Http\Controllers\Courses\bm\BMCurriculumController;
use App\Http\Middleware\BMAssessmentGuard;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->prefix('bm')
    ->name('bm.')
    ->group(function () {

        // Student Assessment
        Route::get('/assessment', [BMAssessmentController::class, 'index'])->name('assessment.index');
        Route::post('/assessment/start', [BMAssessmentController::class, 'start'])->name('assessment.start');
        Route::post('/assessment/submit', [BMAssessmentController::class, 'submit'])->name('assessment.submit')->middleware(BMAssessmentGuard::class);
        Route::get('/assessment/results/{id}', [BMAssessmentController::class, 'results'])->name('assessment.results');
        Route::get('/assessment/history', [BMAssessmentController::class, 'history'])->name('assessment.history');

        Route::get('/learning-path', function() {
            return \Inertia\Inertia::render('Courses/bm/Student/LearningPath');
        })->name('learning_path');

        Route::get('/practice', function() {
            return \Inertia\Inertia::render('Courses/bm/Student/PracticeModule');
        })->name('practice');

        // Curriculum / Lessons
        Route::get('/lesson/{module}/{lesson}', [BMCurriculumController::class, 'show'])->name('lesson.show');
        Route::post('/lesson/progress', [BMCurriculumController::class, 'saveProgress'])->name('lesson.progress');

        // Teacher Portal
        Route::prefix('teacher')->name('teacher.')->group(function () {
            Route::get('/dashboard', function() { return \Inertia\Inertia::render('Courses/bm/Teacher/Dashboard'); })->name('dashboard');
            Route::get('/class-scores', function() { return \Inertia\Inertia::render('Courses/bm/Teacher/ClassScores'); })->name('class_scores');
            Route::get('/student-detail/{studentId}', function() { return \Inertia\Inertia::render('Courses/bm/Teacher/StudentDetail'); })->name('student_detail');
            Route::get('/gap-analysis', function() { return \Inertia\Inertia::render('Courses/bm/Teacher/GapAnalysis'); })->name('gap_analysis');
        });

        // Parent Portal
        Route::prefix('parent')->name('parent.')->group(function () {
            Route::get('/dashboard', function() { return \Inertia\Inertia::render('Courses/bm/Parent/Dashboard'); })->name('dashboard');
            Route::get('/recommendations', function() { return \Inertia\Inertia::render('Courses/bm/Parent/Recommendations'); })->name('recommendations');
        });
    });
