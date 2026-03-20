<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DpMasterScheduleController;
use App\Http\Controllers\DpDailyPlannerController;
use App\Http\Controllers\DpFocusController;
use App\Http\Controllers\DpGamificationController;
use App\Http\Controllers\DpReportController;
use App\Http\Controllers\WeeklyPlanController;
use App\Http\Controllers\WeeklyPlanSessionController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Old Features Routes
|--------------------------------------------------------------------------
|
| These routes map to the legacy Vue components that have been archived
| into the resources/js/Pages/old_features directory. This ensures
| that any users with bookmarks or old dashboard links don't get 404s.
|
*/

// --- Daily Tasks (Legacy) ---
Route::middleware(['auth', 'verified'])->group(function () {
    // Master Schedule
    Route::get('/master-schedule', [DpMasterScheduleController::class, 'index'])->name('dp.master.index');
    Route::post('/master-schedule', [DpMasterScheduleController::class, 'store'])->name('dp.master.store');
    Route::put('/master-schedule/{dpTask}', [DpMasterScheduleController::class, 'update'])->name('dp.master.update');
    Route::delete('/master-schedule/{dpTask}', [DpMasterScheduleController::class, 'destroy'])->name('dp.master.destroy');

    // Daily Planner
    Route::get('/daily-planner', [DpDailyPlannerController::class, 'index'])->name('dp.daily.index');
    Route::put('/daily-planner/{dpDailyTask}', [DpDailyPlannerController::class, 'update'])->name('dp.daily.update');

    // Focus
    Route::get('/live-focus', [DpFocusController::class, 'index'])->name('dp.focus.index');
    Route::post('/live-focus', [DpFocusController::class, 'store'])->name('dp.focus.store');
    Route::put('/live-focus/{dpFocusLog}', [DpFocusController::class, 'update'])->name('dp.focus.update');
    Route::post('/live-focus/{dpFocusLog}/distraction', [DpFocusController::class, 'logDistraction'])->name('dp.focus.distraction');

    // Gamification
    Route::get('/gamification', [DpGamificationController::class, 'index'])->name('dp.gamification.index');
    Route::post('/gamification', [DpGamificationController::class, 'store'])->name('dp.gamification.store');

    // Reports
    Route::get('/reports', [DpReportController::class, 'index'])->name('dp.reports.index');
});

// --- Weekly Plans (Legacy) ---
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
        Route::get('headers', [WeeklyPlanController::class, 'index']);
        Route::post('headers', [WeeklyPlanController::class, 'store']);
        Route::get('headers/{weeklyPlan}', [WeeklyPlanController::class, 'show']);
        Route::put('headers/{weeklyPlan}', [WeeklyPlanController::class, 'update']);
        Route::delete('headers/{weeklyPlan}', [WeeklyPlanController::class, 'destroy']);
        Route::post('headers/generate-semester', [WeeklyPlanController::class, 'generateSemesterPlans']);
        
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

use App\Http\Controllers\CourseManagement\CourseController;
use App\Http\Controllers\CourseManagement\CourseLevelController;
use App\Http\Controllers\CourseManagement\CourseSectionController;
use App\Http\Controllers\CourseManagement\CourseLessonController;



Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('import_students')->name('import_students.')->group(function () {
  
    // Import routes
    Route::get('import', [App\Http\Controllers\CourseManagement\StudentImportController::class, 'index'])->name('import.index');
    Route::get('import/template', [App\Http\Controllers\CourseManagement\StudentImportController::class, 'downloadTemplate'])->name('import.template');
    Route::post('import/validate', [App\Http\Controllers\CourseManagement\StudentImportController::class, 'validateFile'])->name('import.validate');
    Route::post('import/process', [App\Http\Controllers\CourseManagement\StudentImportController::class, 'import'])->name('import.process');
    

});



 


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('course-management')->name('course-management.')->group(function () {
    
    // Course routes
    Route::resource('courses', CourseController::class);
    
    // Nested Level routes
    Route::resource('courses.levels', CourseLevelController::class)->shallow();
    
    // Nested Section routes  
    Route::resource('levels.sections', CourseSectionController::class)->shallow();
    
    // Nested Lesson routes
    Route::resource('sections.lessons', CourseLessonController::class)->shallow();
    
    // Additional utility routes
    Route::post('courses/{course}/levels/reorder', [CourseLevelController::class, 'reorder'])->name('courses.levels.reorder');
    Route::post('levels/{level}/sections/reorder', [CourseSectionController::class, 'reorder'])->name('levels.sections.reorder');
    Route::post('sections/{section}/lessons/reorder', [CourseLessonController::class, 'reorder'])->name('sections.lessons.reorder');
    
    // Import routes
    Route::get('import', [App\Http\Controllers\CourseManagement\CourseImportController::class, 'index'])->name('import.index');
    Route::get('import/template', [App\Http\Controllers\CourseManagement\CourseImportController::class, 'downloadTemplate'])->name('import.template');
    Route::post('import/validate', [App\Http\Controllers\CourseManagement\CourseImportController::class, 'validateFile'])->name('import.validate');
    Route::post('import/process', [App\Http\Controllers\CourseManagement\CourseImportController::class, 'import'])->name('import.process');
    
    // Teacher Assignment routes
    Route::prefix('teachers')->name('teachers.')->group(function () {
        Route::get('/', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'index'])->name('index');
        Route::get('assign-by-course', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'assignByCourse'])->name('assign-by-course');
        Route::get('assign-by-teacher', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'assignByTeacher'])->name('assign-by-teacher');
        Route::post('assign-courses-to-teacher', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'assignCoursesToTeacher'])->name('assign-courses-to-teacher');
        Route::post('assign-teachers-to-course', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'assignTeachersToCourse'])->name('assign-teachers-to-course');
        Route::delete('assignments/{assignment}', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'removeAssignment'])->name('remove-assignment');
        Route::delete('remove-assignment-by-ids', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'removeAssignmentByIds'])->name('remove-assignment-by-ids');
        Route::patch('assignments/{assignment}/toggle', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'toggleAssignment'])->name('toggle-assignment');
        
        // Preview routes
        Route::get('preview-course', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'previewCourse'])->name('preview-course');
        
        // Teacher Dashboard
        Route::get('dashboard', function () {
            return inertia('CourseManagement/Teacher/TeacherDashboard');
        })->name('dashboard');
    });
    
    // API routes for course structure
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('courses/with-structure', [App\Http\Controllers\CourseManagement\CourseStructureController::class, 'index'])->name('courses.with-structure');
        Route::get('courses/{course}/structure', [App\Http\Controllers\CourseManagement\CourseStructureController::class, 'show'])->name('courses.structure');
        Route::get('teacher/courses', [App\Http\Controllers\CourseManagement\CourseStructureController::class, 'teacherCourses'])->name('teacher.courses');
    });
});
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
        return Inertia::render('old_features/lesson_presentation/SectionTemplateManager');
    })->name('section-template-manager');

    // ========================================
    // Teacher Views
    // ========================================
    
    // Teacher Dashboard - List all lessons by grade
    Route::get('/dashboard', function () {
        return Inertia::render('old_features/lesson_presentation/LessonList');
    })->name('index');

    // Manage Lesson Page
    Route::get('/manage/{id}', function ($id) {
        return Inertia::render('old_features/lesson_presentation/LessonManager', [
            'lessonId' => $id
        ]);
    })->name('manage');

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

        return Inertia::render('old_features/lesson_presentation/lesson_presentation', [
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
        
        return Inertia::render('old_features/lesson_presentation/StudentLessonView', [
            'presentationId' => $id,
            'studentId' => $student ? $student->id : 1,
            'sections' => $presentation->getSections(),
        ]);
    })->name('student.view');

    // Print View
    Route::get('/print/{id}', function ($id) {
        return Inertia::render('old_features/lesson_presentation/LessonPrintView', [
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

/*
|--------------------------------------------------------------------------
| Old Features: Vocab, Skills, Gamification (Batch 3)
|--------------------------------------------------------------------------
*/

// Vocabulary Flashcards Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/vocabulary-flashcards', [App\Http\Controllers\VocabularyFlashcardsController::class, 'index'])->name('vocabulary-flashcards');
    Route::get('/vocabulary-flashcards/practice', function () {
        return Inertia::render('VocabularyFlashcards/Index', ['mode' => 'practice']);
    })->name('vocabulary-flashcards.practice');
    Route::get('/vocabulary-flashcards/quiz', function () {
        return Inertia::render('VocabularyFlashcards/Index', ['mode' => 'quiz']);
    })->name('vocabulary-flashcards.quiz');
    Route::post('/vocabulary-flashcards', [App\Http\Controllers\VocabularyFlashcardsController::class, 'store'])->name('vocabulary-flashcards.store');
});

// Skill Practice Routes
Route::middleware(['auth', 'verified'])->prefix('skill-practice')->name('skill-practice.')->group(function () {
    // Skill Categories
    Route::get('/categories', [\App\Http\Controllers\SkillCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{id}', [\App\Http\Controllers\SkillCategoryController::class, 'show'])->name('categories.show');
    
    // Skills
    Route::get('/skills', [\App\Http\Controllers\SkillController::class, 'index'])->name('skills.index');
    Route::get('/skills/{skill}', [\App\Http\Controllers\SkillController::class, 'show'])->name('skills.show');
    
    // Skill Practice Sessions
    Route::post('/skills/{skill}/start', [\App\Http\Controllers\SkillPracticeController::class, 'start'])->name('skills.start');
    Route::post('/practice/next-question', [\App\Http\Controllers\SkillPracticeController::class, 'nextQuestion'])->name('practice.next-question');
    Route::post('/practice/submit-answer', [\App\Http\Controllers\SkillPracticeController::class, 'submitAnswer'])->name('practice.submit-answer');
    Route::post('/practice/end-session/{session}', [\App\Http\Controllers\SkillPracticeController::class, 'endSession'])->name('practice.end-session');
    
    // Skill Progress
    Route::get('/progress', [\App\Http\Controllers\SkillProgressController::class, 'index'])->name('progress.index');
    Route::get('/progress/{skill}', [\App\Http\Controllers\SkillProgressController::class, 'show'])->name('progress.show');
    Route::get('/awards', [\App\Http\Controllers\SkillProgressController::class, 'awards'])->name('awards');
});

// Admin Skill Management Routes
Route::middleware(['auth', 'verified', 'role:teacher|admin|super_admin'])->prefix('admin/skills')->name('admin.skills.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SkillCategoryController::class, 'adminIndex'])->name('index');
    Route::get('/categories', [\App\Http\Controllers\SkillCategoryController::class, 'adminIndex'])->name('categories.index');
    Route::get('/skills', [\App\Http\Controllers\SkillController::class, 'adminIndex'])->name('skills.index');
    Route::post('/skills', [\App\Http\Controllers\SkillController::class, 'store'])->name('skills.store');
    Route::put('/skills/{skill}', [\App\Http\Controllers\SkillController::class, 'update'])->name('skills.update');
    Route::delete('/skills/{skill}', [\App\Http\Controllers\SkillController::class, 'destroy'])->name('skills.destroy');
    
    // Skill Questions Management
    Route::get('/manage-questions', [\App\Http\Controllers\SkillQuestionLinkController::class, 'adminIndex'])->name('manage-questions');
    Route::post('/skills/{skill}/link-questions', [\App\Http\Controllers\SkillQuestionLinkController::class, 'linkQuestions'])->name('skills.link-questions');
    Route::delete('/skills/{skill}/unlink-question/{question}', [\App\Http\Controllers\SkillQuestionLinkController::class, 'unlinkQuestion'])->name('skills.unlink-question');
    Route::get('/skills/{skill}/linked-questions', [\App\Http\Controllers\SkillQuestionLinkController::class, 'getLinkedQuestions'])->name('skills.linked-questions');
});

// Gamification / Reward System Routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/reward_sys', function () {
        return Inertia::render('my_table_mnger/reward_sys/reward_sys');
    })->name('reward_sys');

    Route::get('/reward_sys/quiz', function () {
        return Inertia::render('my_table_mnger/reward_sys/reward_sys');
    })->name('reward_sys.quiz');

    // Admin Behavior Management
    Route::get('/admin/behaviors', function () {
        return Inertia::render('my_table_mnger/reward_sys/admin/BehaviorManagement');
    })->name('admin.behaviors');

    // Reward system drawing tool
    Route::get('/reward-system/drawing', function () {
        return Inertia::render('my_table_mnger/reward_sys/drawing/DrawingMain');
    })->name('reward.system.drawing');
});

/*
|--------------------------------------------------------------------------
| Old Features: Chat & Communication (Batch 4)
|--------------------------------------------------------------------------
*/

// Chat Routes
Route::middleware(['auth'])->group(function () {
    // Conversations
    Route::get('/conversations', [App\Http\Controllers\ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/create', [App\Http\Controllers\ConversationController::class, 'create'])->name('conversations.create');
    Route::post('/conversations', [App\Http\Controllers\ConversationController::class, 'store'])->name('conversations.store');
    Route::get('/conversations/{conversation}', [App\Http\Controllers\ConversationController::class, 'show'])->name('conversations.show');

    // Messages
    Route::post('/conversations/{conversation}/messages', [App\Http\Controllers\ChatMessageController::class, 'store'])->name('messages.store');
    Route::post('/conversations/{conversation}/typing', [App\Http\Controllers\ChatMessageController::class, 'typing'])->name('messages.typing');
    Route::post('/conversations/{conversation}/mark-seen', [App\Http\Controllers\ChatMessageController::class, 'markAsSeen'])->name('messages.mark-seen');
});

// User Messages Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/user-messages', [App\Http\Controllers\UserMessageController::class, 'index'])->name('user-messages.index');
    Route::post('/user-messages', [App\Http\Controllers\UserMessageController::class, 'store'])->name('user-messages.store');
    Route::post('/user-messages/{user_message}/read', [App\Http\Controllers\UserMessageController::class, 'markAsRead'])->name('user-messages.mark-as-read');
    Route::get('/user-messages/users', [App\Http\Controllers\UserMessageController::class, 'getUsers'])->name('user-messages.users');
});

// Private Chat Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/private-chat', [App\Http\Controllers\PrivateChatController::class, 'index'])->name('private-chat.index');
    Route::get('/private-chat/{userId}', [App\Http\Controllers\PrivateChatController::class, 'chat'])->name('private-chat.chat');
    Route::post('/private-chat/{conversationId}/send', [App\Http\Controllers\PrivateChatController::class, 'sendMessage'])->name('private-chat.send-message');
    Route::get('/private-chat/{conversationId}/messages', [App\Http\Controllers\PrivateChatController::class, 'getMessages'])->name('private-chat.get-messages');
});

/*
|--------------------------------------------------------------------------
| Old Features: User Profiles / HR Management (Batch 5)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:admin|super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/teachers', [App\Http\Controllers\TeacherManagementController::class, 'index']);
    Route::resource('teacher', App\Http\Controllers\TeacherController::class);
    Route::resource('student-parent', App\Http\Controllers\StudentParentController::class);
    
    Route::get('students/download-template-with-classroom', [App\Http\Controllers\StudentController::class, 'downloadTemplateWithClassroom'])
        ->name('students.download-template-with-classroom');
    Route::get('students/download-template', [App\Http\Controllers\StudentController::class, 'downloadTemplate'])
        ->name('students.download-template');
    Route::get('students/filtered', [App\Http\Controllers\StudentController::class, 'getFiltered'])->name('students.filtered');
    Route::resource('students', App\Http\Controllers\StudentController::class);

    // Teacher Import/Export Routes
    Route::get('teacher/export', [App\Http\Controllers\TeacherController::class, 'export'])->name('teacher.export');
    Route::post('teacher/import', [App\Http\Controllers\TeacherController::class, 'import'])->name('teacher.import');
    Route::post('teacher/validate-import', [App\Http\Controllers\TeacherController::class, 'validateImport'])->name('teacher.validate-import');
    Route::post('teacher/undo-import/{importId}', [App\Http\Controllers\TeacherController::class, 'undoImport'])->name('teacher.undo-import');

    // Student Parent Import/Export Routes
    Route::get('student-parent/export', [App\Http\Controllers\StudentParentController::class, 'export'])->name('student-parent.export');
    Route::post('student-parent/validate-import', [App\Http\Controllers\StudentParentController::class, 'validateImport'])->name('student-parent.validate-import');
    Route::post('student-parent/import', [App\Http\Controllers\StudentParentController::class, 'import'])->name('student-parent.import');
    Route::post('student-parent/undo-import/{importId}', [App\Http\Controllers\StudentParentController::class, 'undoImport'])->name('student-parent.undo-import');

    // Student Routes
    Route::post('students/validate-import', [App\Http\Controllers\StudentController::class, 'validateImport'])->name('students.validate-import');
    Route::post('students/get-school-students/{school_id}', [App\Http\Controllers\StudentController::class, 'get_school_students'])->name('students.get-school-students');
    Route::post('students/import-with-classroom', [App\Http\Controllers\StudentController::class, 'importWithClassroom'])->name('students.import-with-classroom');
    Route::post('students/validate-import-batch', [App\Http\Controllers\StudentController::class, 'validateImportBatch'])->name('students.validate-import-batch');
    Route::post('students/import', [App\Http\Controllers\StudentController::class, 'import'])->name('students.import');
    Route::post('students/undo-import/{importId}', [App\Http\Controllers\StudentController::class, 'undoImport'])->name('students.undo-import');

    // Student Promotion Routes
    Route::post('students/promote', [App\Http\Controllers\StudentController::class, 'promoteStudents'])->name('students.promote');
    Route::post('students/promotion-preview', [App\Http\Controllers\StudentController::class, 'getPromotionPreview'])->name('students.promotion-preview');
    Route::get('students/{student}/classroom-history', [App\Http\Controllers\StudentController::class, 'getClassroomHistory'])->name('students.classroom-history');
});

/*
|--------------------------------------------------------------------------
| Old Features: Admin & System Tools (Batch 6)
|--------------------------------------------------------------------------
*/

// Chatbot - Admin Routes
Route::middleware(['role:admin', 'web'])->prefix('admin/chatbot')->name('admin.chatbot.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\ChatbotAdminController::class, 'index'])->name('admin.chatbot.index');
    Route::get('/{conversation}', [App\Http\Controllers\Admin\ChatbotAdminController::class, 'show'])->name('admin.chatbot.show');
    Route::post('/{conversation}/reply', [App\Http\Controllers\Admin\ChatbotAdminController::class, 'reply'])->name('admin.chatbot.reply');
    Route::patch('/{conversation}/status', [App\Http\Controllers\Admin\ChatbotAdminController::class, 'updateStatus'])->name('admin.chatbot.update');
});

// School branding settings (admin only)
Route::middleware(['role:admin', 'web'])->prefix('admin/school-branding')->name('admin.school-branding.')->group(function () {
    Route::get('/', [App\Http\Controllers\SchoolBrandingController::class, 'index'])->name('admin.school-branding.index');
    Route::put('/{school}', [App\Http\Controllers\SchoolBrandingController::class, 'update'])->name('admin.school-branding.update');
});
