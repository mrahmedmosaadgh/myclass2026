<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Academics\SubjectApiController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('academics/subjects', SubjectApiController::class)->names('api.academics.subjects');
});
