<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseManagement\LessonPlanTemplateController;

use App\Http\Controllers\BehaviorController;
use App\Http\Controllers\BehaviorIncidentController;
use App\Http\Controllers\StudentBehaviorController;
use App\Http\Controllers\ClassroomRecordController;

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

// Auth: Sanctum Only
Route::middleware(['auth:sanctum', 'web'])->get('/user', function (Request $request) {
    return $request->user();
});

// Course Management (Public)
Route::prefix('course-management')->group(function () {
    Route::get('lesson-plan-templates/public', [LessonPlanTemplateController::class, 'index']);
});

// Course Management (Sanctum Only)
Route::prefix('course-management')->middleware(['auth:sanctum', 'web'])->group(function () {
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

// Behaviors Resources (No Middleware specified in route)
Route::apiResource('behaviors', BehaviorController::class);
Route::apiResource('student-behaviors', StudentBehaviorController::class);

Route::prefix('behavior')->group(function () {
    Route::post('/cancel-action/{action}', [StudentBehaviorController::class, 'cancelPointAction']);
});

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

    // AI Assistant
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

    // Quiz Sessions
    Route::prefix('quiz-sessions')->group(function () {
        Route::post('/', [App\Http\Controllers\QuizSessionController::class, 'store']);
        Route::post('/join', [App\Http\Controllers\QuizSessionController::class, 'join']);
        Route::get('/{session}', [App\Http\Controllers\QuizSessionController::class, 'show']);
        Route::post('/{session}/state', [App\Http\Controllers\QuizSessionController::class, 'updateState']);
        Route::patch('/{session}/settings', [App\Http\Controllers\QuizSessionController::class, 'updateSettings']);
        Route::post('/{session}/answers', [App\Http\Controllers\QuizSessionController::class, 'submitAnswer']);
    });

});
