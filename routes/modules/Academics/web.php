<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Academics\SubjectController;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/academics/subjects', [SubjectController::class, 'index'])->name('academics.subjects.index');
    Route::get('/academics/subjects/create', [SubjectController::class, 'create'])->name('academics.subjects.create');
});

require __DIR__.'/QuExams/web.php';
