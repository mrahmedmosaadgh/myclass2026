<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\LessonPresentationController;
use App\Http\Controllers\LessonProgressController;

/*
|--------------------------------------------------------------------------
| Lesson Presentation Routes
|--------------------------------------------------------------------------
|
| All routes related to lesson presentations, student views, teacher
| progress dashboards, and course progression system.
|
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('lesson-presentation')->name('lesson-presentation.')->group(function () {

    // ========================================
    // Teacher Views
    // ========================================

    // Teacher Dashboard - List all lessons by grade
    Route::get('/dashboard', function () {
        return Inertia::render('my_table_mnger/lesson_presentation/LessonList');
    })->name('index');

    // Lesson Editor
    Route::get('/edit', function () {
        $teacher = auth()->user()->teacher;
        if (!$teacher) {
            abort(403, 'Teacher access required');
        }

        $school = \App\Models\School::first();
        $subject = \App\Models\Subject::first();

        return Inertia::render('my_table_mnger/lesson_presentation/lesson_presentation', [
            'defaultContext' => [
                'teacher_id' => $teacher->id,
                'school_id' => $school ? $school->id : null,
                'subject_id' => $subject ? $subject->id : null,
            ],
            'sections' => \App\Models\free\LessonPresentation::SECTIONS,
        ]);
    })->name('edit');

    // Teacher Progress Dashboard - View student progress for a lesson
    Route::get('/teacher/progress/{lessonId}', [LessonPresentationController::class, 'teacherProgressDashboard'])
        ->name('teacher.progress');

    // ========================================
    // Student Views
    // ========================================

    // Student Lesson List - View all available lessons
    Route::get('/student/lessons', [LessonPresentationController::class, 'studentLessonList'])
        ->name('student.lessons');

    // Student Lesson View - View a specific lesson
    Route::get('/student/{id}', function ($id) {
        $student = auth()->user()->student;
        if (!$student) {
            abort(403, 'Student access required');
        }

        return Inertia::render('my_table_mnger/lesson_presentation/StudentLessonView', [
            'presentationId' => $id,
            'studentId' => $student->id,
            'sections' => \App\Models\free\LessonPresentation::SECTIONS,
        ]);
    })->name('student.view');

    // Print View
    Route::get('/print/{id}', function ($id) {
        return Inertia::render('my_table_mnger/lesson_presentation/LessonPrintView', [
            'presentationId' => $id
        ]);
    })->name('print');

    // ========================================
    // Lesson CRUD API
    // ========================================

    Route::get('/list', [LessonPresentationController::class, 'index'])->name('list');
    Route::get('/teacher/grades', [LessonPresentationController::class, 'getTeacherGrades'])->name('teacher.grades');
    Route::post('/', [LessonPresentationController::class, 'store'])->name('store');
    Route::get('/{id}', [LessonPresentationController::class, 'show'])->name('show');
    Route::put('/{id}', [LessonPresentationController::class, 'update'])->name('update');
    Route::delete('/{id}', [LessonPresentationController::class, 'destroy'])->name('destroy');

    // ========================================
    // Slide Management
    // ========================================

    Route::post('/{id}/slides', [LessonPresentationController::class, 'addSlide'])->name('slides.add');
    Route::put('/{id}/slides/{slideId}', [LessonPresentationController::class, 'updateSlide'])->name('slides.update');
    Route::delete('/{id}/slides/{slideId}', [LessonPresentationController::class, 'deleteSlide'])->name('slides.delete');

    // Apply a template to a lesson presentation (create slides per template)
    Route::post('/{id}/apply-template', [LessonPresentationController::class, 'applyTemplate'])->name('apply-template');

    // ========================================
    // Students API
    // ========================================

    // Get students by grade (for "Open to All Students" feature)
    Route::get('/students/by-grade/{gradeId}', function ($gradeId) {
        $students = \App\Models\Student::where('grade_id', $gradeId)
            ->select('id', 'name', 'grade_id')
            ->get();
        return response()->json($students);
    })->name('students.by-grade');

    // ========================================
    // Utilities
    // ========================================

    // Image Proxy (for CORS issues)
    Route::post('/proxy-image', [LessonPresentationController::class, 'proxyImage'])->name('proxy-image');

    // ========================================
    // Course Progression System
    // ========================================

    Route::prefix('progress')->name('progress.')->group(function () {

        // Get Progress Data
        Route::get('/student/{studentId}', [LessonProgressController::class, 'getStudentProgress'])
            ->name('student');
        Route::get('/lesson/{lessonId}/students', [LessonProgressController::class, 'getLessonProgress'])
            ->name('lesson');
        Route::get('/{progressId}/submission', [LessonProgressController::class, 'getPracticeSubmission'])
            ->name('submission');

        // Teacher Actions
        Route::post('/open', [LessonProgressController::class, 'openLesson'])
            ->name('open');
        Route::post('/lock', [LessonProgressController::class, 'lockLesson'])
            ->name('lock');
        Route::post('/force-pass', [LessonProgressController::class, 'forcePass'])
            ->name('force-pass');
        Route::post('/grant-attempt', [LessonProgressController::class, 'grantAttempt'])
            ->name('grant-attempt');
        Route::post('/reset', [LessonProgressController::class, 'resetProgress'])
            ->name('reset');
        Route::put('/{id}/practice-grade', [LessonProgressController::class, 'gradePractice'])
            ->name('grade-practice');

        // Student Actions
        Route::put('/{id}/learn-complete', [LessonProgressController::class, 'completeLearn'])
            ->name('complete-learn');
        Route::post('/{id}/practice-submit', [LessonProgressController::class, 'submitPractice'])
            ->name('submit-practice');
        Route::post('/{id}/quiz-attempt', [LessonProgressController::class, 'recordQuizAttempt'])
            ->name('quiz-attempt');
    });
});
