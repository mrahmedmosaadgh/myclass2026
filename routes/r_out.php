<?php

use App\Http\Controllers\GoogleClassroomController;
use Illuminate\Support\Facades\Route;

Route::get('/get-google-courses', function () {
    // Get courses from the session (where we stored them after OAuth)
    $courses = session('google_courses');
    return response()->json(['courses' => $courses]);
});
Route::get('/oauth/google', [GoogleClassroomController::class, 'redirectToGoogle']);
Route::get('/oauth/google/callback', [GoogleClassroomController::class, 'handleGoogleCallback']);















