<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\CourseManagement\LessonPlanTemplate;
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
    
    // Section Template Management
    Route::prefix('section-templates')->name('section-templates.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LessonSectionTemplateController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\LessonSectionTemplateController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\LessonSectionTemplateController::class, 'show'])->name('show');
        Route::put('/{id}', [\App\Http\Controllers\LessonSectionTemplateController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\LessonSectionTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/set-active', [\App\Http\Controllers\LessonSectionTemplateController::class, 'setActive'])->name('set-active');
    });

    // Template Manager Page
    Route::get('/section-template-manager', function () {
        return Inertia::render('my_table_mnger/lesson_presentation/SectionTemplateManager');
    })->name('section-template-manager');

    // ========================================
    // Teacher Views
    // ========================================
    
    // Teacher Dashboard - List all lessons by grade
    Route::get('/dashboard', function () {
        return Inertia::render('my_table_mnger/lesson_presentation/LessonList');
    })->name('index');

    // Lesson Editor
    Route::get('/edit', function () {
        $teacher = \App\Models\Teacher::first(); // TODO: Auth::user()->teacher
        $school = \App\Models\School::first();

        // Handle Subject Selection
        $subjectId = request()->input('subject_id');
        $subject = $subjectId 
            ? \App\Models\Subject::find($subjectId) 
            : \App\Models\Subject::first();

        // Handle Grade Selection (optional, but good to have context)
        $gradeId = request()->input('grade_id');
        
        // Get active template
        $activeTemplate = LessonPlanTemplate::where('is_active', true)->first();
        $sections = $activeTemplate && isset($activeTemplate->structure['sections']) 
            ? $activeTemplate->structure['sections'] 
            : [];
        
        // Fetch Grade Name
        $gradeName = null;
        if ($gradeId) {
            $grade = \App\Models\Grade::find($gradeId);
            $gradeName = $grade ? $grade->name : null;
        }

        return Inertia::render('my_table_mnger/lesson_presentation/lesson_presentation', [
            'defaultContext' => [
                'teacher_id' => $teacher ? $teacher->id : null,
                'school_id' => $school ? $school->id : null,
                'subject_id' => $subject ? $subject->id : null,
                'grade_id' => $gradeId,
                'subject_name' => $subject ? $subject->name : 'Unknown Subject',
                'grade_name' => $gradeName,
            ],
            'sections' => $sections,
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
        $student = \App\Models\Student::first(); // TODO: Replace with Auth::user()->student
        $presentation = \App\Models\free\LessonPresentation::findOrFail($id);
        
        return Inertia::render('my_table_mnger/lesson_presentation/StudentLessonView', [
            'presentationId' => $id,
            'studentId' => $student ? $student->id : 1,
            'sections' => $presentation->getSections(),
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
    
    // Bulk operations (must come before parameterized routes)
    Route::put('/{id}/slides/bulk', [LessonPresentationController::class, 'bulkUpdateSlides'])->name('slides.bulk-update');
    
    Route::put('/{id}/slides/{slideId}', [LessonPresentationController::class, 'updateSlide'])->name('slides.update');
    Route::delete('/{id}/slides/{slideId}', [LessonPresentationController::class, 'deleteSlide'])->name('slides.delete');
    
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

