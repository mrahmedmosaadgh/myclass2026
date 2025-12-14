<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('qdrat')->name('qdrat.')->group(function () {
    // Skill Levels
    Route::resource('skill-levels', \App\Http\Controllers\QudratQuantitative\QdratSkillLevelController::class);

    // Skills
    Route::get('skills/import/template', [\App\Http\Controllers\QudratQuantitative\QdratSkillController::class, 'downloadTemplate'])->name('skills.downloadTemplate');
    Route::post('skills/validate', [\App\Http\Controllers\QudratQuantitative\QdratSkillController::class, 'validateImport'])->name('skills.validateImport');
    Route::post('skills/import', [\App\Http\Controllers\QudratQuantitative\QdratSkillController::class, 'import'])->name('skills.import');
    Route::post('skills/undo/{importId}', [\App\Http\Controllers\QudratQuantitative\QdratSkillController::class, 'undo'])->name('skills.undo');
    Route::resource('skills', \App\Http\Controllers\QudratQuantitative\QdratSkillController::class);

    // Lesson Categories
    Route::resource('lesson-categories', \App\Http\Controllers\QudratQuantitative\QdratLessonCategoryController::class);

    // Lessons
    Route::resource('lessons', \App\Http\Controllers\QudratQuantitative\QdratLessonController::class);

    // Question Difficulties
    Route::resource('question-difficulties', \App\Http\Controllers\QudratQuantitative\QdratQuestionDifficultyController::class);

    // Questions (renamed to avoid conflicts)
    Route::resource('qdrat-questions', \App\Http\Controllers\QudratQuantitative\QdratQuestionController::class);
});