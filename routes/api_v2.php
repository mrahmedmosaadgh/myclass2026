<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V2 API Routes
|--------------------------------------------------------------------------
|
| API routes for V2, loaded by bootstrap/app.php with 'api' middleware group.
|
*/

Route::prefix('v2')->group(function () {
    // Public V2 APIs can go here
    
    // Authenticated V2 APIs
    Route::middleware(['auth:sanctum'])->group(function () {
        // Shared APIs
    });
});
