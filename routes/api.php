<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Presentations API Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/api/presentations.php';
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\ContextController;
use App\Http\Controllers\CourseManagement\LessonPlanTemplateController;
use App\Http\Controllers\BehaviorController;
use App\Http\Controllers\StudentBehaviorController;
use App\Http\Controllers\StudentBehaviorsMainController;
use App\Http\Controllers\BehaviorIncidentController;
use App\Http\Controllers\ClassroomRecordController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionTypeController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\QuizSessionController;
use App\Http\Controllers\Api\MyProjectTaskController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\Api\ClassroomLayoutController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\ScheduleCopyController;
use App\Http\Controllers\ClassroomSubjectTeacherController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\Api\NavigationController;
use App\Http\Controllers\Api\Cr\CrSessionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public Menu API for Schedule App V4
Route::get('/schedule-app-v4/menu', function (Request $request) {
    return response()->json([
        'success' => true,
        'menu' => [
            [
                'name' => 'Schedule App V4',
                'icon' => 'calendar',
                'url' => '/my-fly-schedule-app/v4',
                'active' => true
            ],
            [
                'name' => 'Data Manager',
                'icon' => 'database',
                'url' => '/my-fly-schedule-app/v4#data-manager'
            ],
            [
                'name' => 'Settings',
                'icon' => 'cog',
                'url' => '/my-fly-schedule-app/v4#settings'
            ]
        ]
    ]);
})->withoutMiddleware(['auth:sanctum', 'web']);

// Menu API (authenticated)
Route::middleware(['auth:sanctum', 'web'])->get('/menu', [NavigationController::class, 'index']);

Route::middleware(['auth:sanctum','web'])->get('/schools', [App\Http\Controllers\SchoolController::class, 'apiIndex']);
Route::middleware(['auth:sanctum','web'])->get('/schools/{school}/subjects', [App\Http\Controllers\SchoolController::class, 'getSubjects']);

// Context API routes
Route::middleware(['auth:sanctum','web'])->get('/context', [ContextController::class, 'getCurrentContext']);
Route::middleware(['auth:sanctum','web'])->post('/context/update-all', [ContextController::class, 'updateAllSchoolsContext']);
Route::middleware(['auth:sanctum','web'])->put('/context/school/{school}', [ContextController::class, 'setActiveSchool']);
Route::middleware(['auth:sanctum','web'])->patch('/context/school/{school}', [ContextController::class, 'updateContext']);

// Debug endpoint to test authentication
Route::middleware(['auth:sanctum','web'])->get('/auth-test', function (Request $request) {
    return response()->json([
        'authenticated' => true,
        'user_id' => auth()->id(),
        'user_school_id' => auth()->user()->school_id ?? null,
        'user_name' => auth()->user()->name ?? null,
    ]);
});

// Academic Years API
Route::middleware(['auth:sanctum','web'])->get('/academic-years', [App\Http\Controllers\AcademicYearController::class, 'apiIndex']);

// Classroom Subject Teachers API
Route::middleware(['auth:sanctum','web'])->get('/classroom-subject-teachers/my-assignments', [App\Http\Controllers\ClassroomSubjectTeacherController::class, 'myAssignments']);

// Teacher Dashboard API
Route::middleware(['auth:sanctum','web'])
    ->get('/teacher/dashboard/classrooms', [\App\Http\Controllers\Teacher\TeacherDashboardController::class, 'classrooms']);

// We'll handle routes directly in the web route instead of using an API endpoint

// Course Management API Routes
Route::prefix('course-management')->middleware(['auth:sanctum'])->group(function () {
    
    // Lesson Plan Templates
    Route::apiResource('lesson-plan-templates', LessonPlanTemplateController::class);
    
});

// Project Tasks (No Middleware specified in route)
Route::prefix('myproject_tasks')->group(function () {
    Route::apiResource('/', App\Http\Controllers\Api\MyProjectTaskController::class)->parameters(['' => 'task']);

    // Hierarchical operations
    Route::post('{parent}/subtasks', [App\Http\Controllers\Api\MyProjectTaskController::class, 'createSubtask']);
    Route::get('{task}/subtasks', [App\Http\Controllers\Api\MyProjectTaskController::class, 'getSubtasks']);
    Route::patch('{task}/move', [App\Http\Controllers\Api\MyProjectTaskController::class, 'moveTask']);
    Route::post('reorder', [App\Http\Controllers\Api\MyProjectTaskController::class, 'reorderTasks']);
});
Route::middleware(['auth:sanctum','web'])->get('/project-tasks', [App\Http\Controllers\ProjectTaskController::class, 'index']);
Route::middleware(['auth:sanctum','web'])->get('/project-task/{projectTask}', [App\Http\Controllers\ProjectTaskController::class, 'show']);
// Public routes (if needed)
Route::prefix('course-management')->group(function () {
    Route::get('lesson-plan-templates/public', [LessonPlanTemplateController::class, 'index']);
});
 
Route::middleware(['auth:sanctum','web'])->get('/schools/{school}/subjects', [App\Http\Controllers\SchoolController::class, 'getSubjects']);
 
Route::middleware(['auth:sanctum','web'])->get('/subjects/{subject}/curricula', [App\Http\Controllers\SubjectController::class, 'getCurricula']);
// Route::middleware(['auth:sanctum','web'])->post('/worksheets', [App\Http\Controllers\WorksheetController::class, 'store']);


Route::apiResource('behaviors', BehaviorController::class);
Route::apiResource('student-behaviors', StudentBehaviorController::class);

Route::prefix('behavior')->group(function () {
    Route::post('/cancel-action/{action}', [StudentBehaviorController::class, 'cancelPointAction']);
});

// Student presentation (no auth required - share token only)
Route::get('/v8-presentations/shared/{shareToken}', [App\Http\Controllers\Api\PresentationController::class, 'loadSharedPresentation']);
Route::post('/v8-presentations/shared/{shareToken}/attempt', [App\Http\Controllers\Api\PresentationController::class, 'submitStudentAttempt']);

// Main Authenticated Web API Group
Route::middleware(['auth:sanctum', 'web'])->group(function () {

    // Auth Test
    Route::get('/auth-test', function (Request $request) {
        return response()->json([
            'authenticated' => true,
            'user_id' => auth()->id(),
            'user_school_id' => auth()->user()->school_id ?? null,
            'user_name' => auth()->user()->name ?? null,
        ]);
    });

    // Academic & School
    Route::get('/academic-years', [App\Http\Controllers\AcademicYearController::class, 'apiIndex']);
    Route::get('/schools/{school}/subjects', [App\Http\Controllers\SchoolController::class, 'getSubjects']);
    Route::get('/subjects/{subject}/curricula', [App\Http\Controllers\SubjectController::class, 'getCurricula']);

    // Teacher & Classroom
    Route::get('/classroom-subject-teachers/my-assignments', [App\Http\Controllers\ClassroomSubjectTeacherController::class, 'myAssignments']);
    Route::get('/teacher/dashboard/classrooms', [\App\Http\Controllers\Teacher\TeacherDashboardController::class, 'classrooms']);

    // Projects
    Route::get('/project-tasks', [App\Http\Controllers\ProjectTaskController::class, 'index']);
    Route::get('/project-task/{projectTask}', [App\Http\Controllers\ProjectTaskController::class, 'show']);

    // Behavior System
    Route::get('/behaviors', [BehaviorController::class, 'index']); // 🧠 Behavior master list
    Route::post('/student-behaviors', [StudentBehaviorController::class, 'store']); // 🎯 Record student behavior
    Route::post('/student-behaviors/quick-create', [StudentBehaviorController::class, 'quickCreate']); // 🎯 Quick create
    Route::get('/student-behaviors/{student}', [StudentBehaviorController::class, 'studentSummary']); // 📊 Show student behavior summary
    Route::post('/student-behaviors/init-classroom', [App\Http\Controllers\StudentBehaviorsMainController::class, 'initForClassroom']);

    // Attendance
    Route::post('/student-attendance', [StudentBehaviorController::class, 'updateAttendance']);
    Route::post('/student-attendance/batch', [StudentBehaviorController::class, 'batchUpdateAttendance']);

    // Recent Actions
    Route::get('/student-behaviors/recent-actions', [StudentBehaviorController::class, 'recentActions']);
    Route::post('/student-behaviors/actions/{actionId}/cancel', [StudentBehaviorController::class, 'cancelAction']);

    // Avatars
    Route::post('/students/{student}/avatar', [App\Http\Controllers\StudentController::class, 'uploadAvatar']);
    Route::delete('/students/{student}/avatar', [App\Http\Controllers\StudentController::class, 'deleteAvatar']);

    // Classroom Layouts
    Route::post('/classroom-layouts/save', [App\Http\Controllers\Api\ClassroomLayoutController::class, 'saveLayouts']);
    Route::get('/classroom-layouts/load', [App\Http\Controllers\Api\ClassroomLayoutController::class, 'loadLayouts']);

    // 🛠️ Tracker Updates (Classroom Helper)
    Route::post('/student-behaviors/update-tracker', [StudentBehaviorController::class, 'updateTracker']);

    // Leaderboard
    Route::get('/leaderboard', [StudentBehaviorController::class, 'leaderboard']);

    // Behavior Incidents
    Route::apiResource('behavior-incidents', BehaviorIncidentController::class);
    Route::get('/behavior-incidents/student/{studentId}/report', [BehaviorIncidentController::class, 'studentReport']);

    // Classroom Records
    Route::prefix('classroom-records')->group(function () {
        Route::get('/', [ClassroomRecordController::class, 'index']);
        Route::get('/metadata', [ClassroomRecordController::class, 'metadata']);
        Route::patch('/{classroomRecord}', [ClassroomRecordController::class, 'update']);
    });

    // Classroom Records v2 API
    Route::prefix('cr')->group(function () {
        // Category mappings (scoring categories)
        Route::get('/category-mappings', [App\Http\Controllers\Api\Cr\CrCategoryMappingsController::class, 'index']);
        Route::post('/category-mappings', [App\Http\Controllers\Api\Cr\CrCategoryMappingsController::class, 'store']);
        Route::patch('/category-mappings/{mapping}', [App\Http\Controllers\Api\Cr\CrCategoryMappingsController::class, 'update']);
        Route::delete('/category-mappings/{mapping}', [App\Http\Controllers\Api\Cr\CrCategoryMappingsController::class, 'destroy']);
    });

    // V8 Presentation Save/Load/Share API
    Route::prefix('v8-presentations')->group(function () {
        Route::post('/save', [App\Http\Controllers\Api\PresentationController::class, 'saveV8Presentation']);
        Route::get('/', [App\Http\Controllers\Api\PresentationController::class, 'listV8Presentations']);
        Route::get('/{id}', [App\Http\Controllers\Api\PresentationController::class, 'loadV8Presentation']);
        Route::put('/{id}', [App\Http\Controllers\Api\PresentationController::class, 'updateV8Presentation']);
        Route::delete('/{id}', [App\Http\Controllers\Api\PresentationController::class, 'deleteV8Presentation']);
        Route::get('/{id}/statistics', [App\Http\Controllers\Api\PresentationController::class, 'getV8Statistics']);
        Route::get('/{id}/attempts', [App\Http\Controllers\Api\PresentationController::class, 'getV8AttemptHistory']);
    });

    // AI Assistant API
    Route::post('/ai/complete', [App\Http\Controllers\AIController::class, 'complete']);

    // Quiz System
    Route::prefix('quiz')->group(function () {
        Route::get('/fetch', [App\Http\Controllers\QuizController::class, 'show']);
        Route::post('/attempts', [App\Http\Controllers\QuizAttemptController::class, 'store']);
        Route::post('/attempts/{attemptId}/answers', [App\Http\Controllers\QuizAttemptController::class, 'submitAnswer']);
        Route::put('/attempts/{attemptId}/complete', [App\Http\Controllers\QuizAttemptController::class, 'complete']);
        Route::get('/attempts/{attemptId}/results', [App\Http\Controllers\QuizAttemptController::class, 'results']);
        Route::post('/questions/import', [App\Http\Controllers\QuestionController::class, 'import']);
        Route::apiResource('questions', App\Http\Controllers\QuestionController::class);
    });

    // Quiz Management
    Route::prefix('quizzes')->group(function () {
        Route::get('/filter-options', [App\Http\Controllers\QuizController::class, 'filterOptions']);
        Route::get('/', [App\Http\Controllers\QuizController::class, 'index']);
        Route::post('/', [App\Http\Controllers\QuizController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\QuizController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\QuizController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\QuizController::class, 'destroy']);
        Route::post('/{id}/duplicate', [App\Http\Controllers\QuizController::class, 'duplicate']);
        Route::get('/{id}/export', [App\Http\Controllers\QuizController::class, 'export']);
        Route::get('/{id}/analytics', [App\Http\Controllers\QuizController::class, 'analytics']);
    });

    // Question Management
    Route::prefix('questions')->group(function () {
        Route::post('/import', [App\Http\Controllers\QuestionController::class, 'import']);
        Route::get('/export', [App\Http\Controllers\QuestionController::class, 'export']);
        Route::get('/', [App\Http\Controllers\QuestionController::class, 'index']);
        Route::post('/', [App\Http\Controllers\QuestionController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\QuestionController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\QuestionController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\QuestionController::class, 'destroy']);
        Route::post('/{id}/duplicate', [App\Http\Controllers\QuestionController::class, 'duplicate']);
        Route::patch('/{id}/status', [App\Http\Controllers\QuestionController::class, 'updateStatus']);
    });

    // Metadata
    Route::get('/question-types', [App\Http\Controllers\QuestionTypeController::class, 'index']);
    Route::get('/grades', [App\Http\Controllers\GradeController::class, 'apiIndex']);
    Route::get('/subjects', [App\Http\Controllers\SubjectController::class, 'apiIndex']);
    Route::get('/topics', [App\Http\Controllers\SubjectController::class, 'getTopics']);

    // Exam File Management (Ready to Print)
    Route::prefix('exam/ready-to-print')->group(function () {
        Route::post('/save-exam', [\App\Http\Controllers\ExamFileController::class, 'saveExam']);
        Route::get('/list-saved-exams', [\App\Http\Controllers\ExamFileController::class, 'listSavedExams']);
        Route::get('/load-saved-exam/{examId}', [\App\Http\Controllers\ExamFileController::class, 'loadSavedExam']);
        Route::get('/print-html/{examId}', [\App\Http\Controllers\ExamFileController::class, 'getPrintHtml']);
        Route::get('/generate-pdf/{examId}', [\App\Http\Controllers\ExamFileController::class, 'generatePdf']);
        Route::delete('/delete-saved-exam/{examId}', [\App\Http\Controllers\ExamFileController::class, 'deleteSavedExam']);
    });

});
