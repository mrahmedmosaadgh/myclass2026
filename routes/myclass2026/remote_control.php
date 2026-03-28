<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MyClass2026\RemoteControl\RemoteControlPageController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Remote Control System Routes
|--------------------------------------------------------------------------
|
| All routes related to the Generic Real-Time Communication System
|
| Features:
| - Generic real-time bidirectional communication
| - Firebase integration with offline support
| - Command queue with retry logic
| - Event logging and analytics
| - Reusable components for any real-time use case
|
*/

Route::prefix('remote-control')->name('remote-control.')->group(function () {
    
    // Main test page for the remote control system
    Route::get('/test-v1', function () {
        return Inertia::render('myclass2026/features/remot_control/v1/test_remote_control_v1');
    })->name('test-v1');
    
    // Examples dashboard page
    Route::get('/examples', function () {
        return Inertia::render('myclass2026/features/remot_control/v1/examples_dashboard');
    })->name('examples');
    
    // API endpoint for testing Firebase connectivity
    Route::get('/api/test-firebase', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'Firebase API test endpoint',
            'timestamp' => now()->toISOString(),
            'environment' => config('app.env'),
            'firebase_enabled' => true // This would be dynamic based on your ToolsSwitcher
        ]);
    })->name('api.test-firebase');
    
    // Simple channel test endpoint
    Route::get('/api/channel/{channelId}/status', function ($channelId) {
        return response()->json([
            'channelId' => $channelId,
            'status' => 'active',
            'message' => 'Channel status endpoint',
            'timestamp' => now()->toISOString()
        ]);
    })->name('api.channel.status');
    
    // Question Response System Routes
    Route::prefix('question-responses')->name('question-responses.')->group(function () {
        
        // Teacher route - create and manage sessions
        Route::get('/teacher', function () {
            return Inertia::render('myclass2026/features/remot_control/v1/examples/question_responses/TeacherView');
        })->name('teacher');
        
        // Student route - join sessions and answer questions
        Route::get('/student', function () {
            return Inertia::render('myclass2026/features/remot_control/v1/examples/question_responses/StudentView');
        })->name('student');
        
        // Direct join route with code parameter
        Route::get('/join/{code}', function ($code) {
            return Inertia::render('myclass2026/features/remot_control/v1/examples/question_responses/StudentView', [
                'sessionCode' => $code
            ]);
        })->name('join');
        
    });
    
});

// Include additional remote control route files if they exist
if (file_exists(base_path('routes/myclass2026/remote_control_v2.php'))) {
    include base_path('routes/myclass2026/remote_control_v2.php');
}
