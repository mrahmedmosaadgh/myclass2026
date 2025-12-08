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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

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

// MyProject Tasks API Routes
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
// Route::apiResource('student-behaviors', StudentBehaviorController::class);


Route::middleware(['auth:sanctum', 'web'])->group(function () {

    // 🧠 Behavior master list (Positive & Negative)
    Route::get('/behaviors', [BehaviorController::class, 'index']);

    // 🎯 Record student behavior (standard full record)
    Route::post('/student-behaviors', [StudentBehaviorController::class, 'store']);

    // 🎯 Quick create student behavior (simplified frontend payload)
    Route::post('/student-behaviors/quick-create', [StudentBehaviorController::class, 'quickCreate']);

    // 📊 Show student behavior summary (by student id)
    Route::get('/student-behaviors/{student}', [StudentBehaviorController::class, 'studentSummary']);

    // Initialize classroom session and ensure student behaviors exist
    Route::post('/student-behaviors/init-classroom', [App\Http\Controllers\StudentBehaviorsMainController::class, 'initForClassroom']);

    // Attendance API (single and batch)
    Route::post('/student-attendance', [StudentBehaviorController::class, 'updateAttendance']);
    Route::post('/student-attendance/batch', [StudentBehaviorController::class, 'batchUpdateAttendance']);

    // Recent actions and undo
    Route::get('/student-behaviors/recent-actions', [StudentBehaviorController::class, 'recentActions']);
    Route::post('/student-behaviors/actions/{actionId}/cancel', [StudentBehaviorController::class, 'cancelAction']);

    // Student avatar upload
    Route::post('/students/{student}/avatar', [App\Http\Controllers\StudentController::class, 'uploadAvatar']);
    Route::delete('/students/{student}/avatar', [App\Http\Controllers\StudentController::class, 'deleteAvatar']);

    // Classroom layouts (student grouping)
    Route::post('/classroom-layouts/save', [App\Http\Controllers\Api\ClassroomLayoutController::class, 'saveLayouts']);
    Route::get('/classroom-layouts/load', [App\Http\Controllers\Api\ClassroomLayoutController::class, 'loadLayouts']);

    // 🏆 Leaderboard
    Route::get('/leaderboard', [StudentBehaviorController::class, 'leaderboard']);

    // 📋 Behavior Incidents
    Route::apiResource('behavior-incidents', BehaviorIncidentController::class);
    Route::get('/behavior-incidents/student/{studentId}/report', [BehaviorIncidentController::class, 'studentReport']);
});

Route::middleware(['auth:sanctum', 'web'])->prefix('classroom-records')->group(function () {
    Route::get('/', [ClassroomRecordController::class, 'index']);
    Route::get('/metadata', [ClassroomRecordController::class, 'metadata']);
    Route::patch('/{classroomRecord}', [ClassroomRecordController::class, 'update']);
});

// Route::middleware(['auth:sanctum', 'web', 'role:teacher'])->group(function () {
//     // routes here
// });
Route::prefix('behavior')->group(function () {
    Route::post('/cancel-action/{action}', [StudentBehaviorController::class, 'cancelPointAction']);
});

Route::apiResource('student-behaviors', StudentBehaviorController::class);

// AI Assistant API
Route::middleware(['auth:sanctum', 'web'])->post('/ai/complete', [App\Http\Controllers\AIController::class, 'complete']);

// Quiz System API Routes
Route::middleware(['auth:sanctum', 'web'])->prefix('quiz')->group(function () {
    // Quiz endpoints - fetch quiz with questions
    Route::get('/fetch', [App\Http\Controllers\QuizController::class, 'show']);
    
    // Quiz attempt endpoints
    Route::post('/attempts', [App\Http\Controllers\QuizAttemptController::class, 'store']);
    Route::post('/attempts/{attemptId}/answers', [App\Http\Controllers\QuizAttemptController::class, 'submitAnswer']);
    Route::put('/attempts/{attemptId}/complete', [App\Http\Controllers\QuizAttemptController::class, 'complete']);
    Route::get('/attempts/{attemptId}/results', [App\Http\Controllers\QuizAttemptController::class, 'results']);
    
    // Question import endpoint (must come before resource routes)
    Route::post('/questions/import', [App\Http\Controllers\QuestionController::class, 'import']);
    
    // Question management endpoints (CRUD)
    Route::apiResource('questions', App\Http\Controllers\QuestionController::class);
});

// Quiz Management API Routes
Route::middleware(['auth:sanctum', 'web'])->prefix('quizzes')->group(function () {
    Route::get('/filter-options', [App\Http\Controllers\QuizController::class, 'filterOptions']);
    Route::get('/', [App\Http\Controllers\QuizController::class, 'index']);
    Route::post('/', [App\Http\Controllers\QuizController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\QuizController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\QuizController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\QuizController::class, 'destroy']);
    
    // Additional quiz operations
    Route::post('/{id}/duplicate', [App\Http\Controllers\QuizController::class, 'duplicate']);
    Route::get('/{id}/export', [App\Http\Controllers\QuizController::class, 'export']);
    Route::get('/{id}/analytics', [App\Http\Controllers\QuizController::class, 'analytics']);
});

// Question Management API Routes (outside quiz prefix for direct access)
Route::middleware(['auth:sanctum', 'web'])->prefix('questions')->group(function () {
    // Import and export must come before resource routes
    Route::post('/import', [App\Http\Controllers\QuestionController::class, 'import']);
    Route::get('/export', [App\Http\Controllers\QuestionController::class, 'export']);
    
    // CRUD operations
    Route::get('/', [App\Http\Controllers\QuestionController::class, 'index']);
    Route::post('/', [App\Http\Controllers\QuestionController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\QuestionController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\QuestionController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\QuestionController::class, 'destroy']);
    
    // Additional operations
    Route::post('/{id}/duplicate', [App\Http\Controllers\QuestionController::class, 'duplicate']);
    Route::patch('/{id}/status', [App\Http\Controllers\QuestionController::class, 'updateStatus']);
});

// Metadata endpoints for question bank
Route::middleware(['auth:sanctum', 'web'])->group(function () {
    Route::get('/question-types', [App\Http\Controllers\QuestionTypeController::class, 'index']);
    Route::get('/grades', [App\Http\Controllers\GradeController::class, 'apiIndex']);
    Route::get('/subjects', [App\Http\Controllers\SubjectController::class, 'apiIndex']);
    Route::get('/topics', [App\Http\Controllers\SubjectController::class, 'getTopics']);
});

// Live Quiz Session API Routes
Route::middleware(['auth:sanctum', 'web'])->prefix('quiz-sessions')->group(function () {
    // Session management
    Route::post('/', [App\Http\Controllers\QuizSessionController::class, 'store']);
    Route::post('/join', [App\Http\Controllers\QuizSessionController::class, 'join']);
    Route::get('/{session}', [App\Http\Controllers\QuizSessionController::class, 'show']);
    
    // Session control (teacher)
    Route::post('/{session}/state', [App\Http\Controllers\QuizSessionController::class, 'updateState']);
    Route::patch('/{session}/settings', [App\Http\Controllers\QuizSessionController::class, 'updateSettings']);
    
    // Answer submission (student)
    Route::post('/{session}/answers', [App\Http\Controllers\QuizSessionController::class, 'submitAnswer']);
});


