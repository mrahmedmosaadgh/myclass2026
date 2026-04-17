<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\TimelineAuthController;
use App\Http\Controllers\TimelineDataController;

// Timeline Authentication Routes
Route::prefix('timeline/auth')->group(function () {
    
    // Authentication endpoints
    Route::post('/login', [TimelineAuthController::class, 'login']);
    Route::post('/register', [TimelineAuthController::class, 'register']);
    Route::post('/logout', [TimelineAuthController::class, 'logout']);
    Route::post('/refresh', [TimelineAuthController::class, 'refresh']);
    Route::get('/me', [TimelineAuthController::class, 'me']);
    
    // User data endpoints
    Route::middleware('auth:timeline')->group(function () {
        // Timeline data sync
        Route::get('/data', [TimelineDataController::class, 'getUserData']);
        Route::post('/data', [TimelineDataController::class, 'saveUserData']);
        Route::post('/sync', [TimelineDataController::class, 'syncData']);
        
        // User settings
        Route::get('/settings', [TimelineDataController::class, 'getUserSettings']);
        Route::post('/settings', [TimelineDataController::class, 'saveUserSettings']);
        
        // Profile management
        Route::get('/profile', [TimelineAuthController::class, 'profile']);
        Route::post('/profile', [TimelineAuthController::class, 'updateProfile']);
        Route::post('/change-password', [TimelineAuthController::class, 'changePassword']);
    });
    
    // Device registration for sync
    Route::middleware('auth:timeline')->post('/register-device', [TimelineDataController::class, 'registerDevice']);
    Route::middleware('auth:timeline')->get('/devices', [TimelineDataController::class, 'getUserDevices']);
    Route::middleware('auth:timeline')->delete('/devices/{deviceId}', [TimelineDataController::class, 'removeDevice']);
});

// Public timeline data (for sharing)
Route::get('/timeline/public/{shareToken}', [TimelineDataController::class, 'getSharedTimeline']);
