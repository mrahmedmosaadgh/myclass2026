<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    // QU Question Bank System Routes
    Route::prefix('qu')->name('qu.')->group(function () {

        Route::get('quiz-builder', function () {
            return Inertia::render('my_class/QuQuestionBankSystem/QuQuizManagement/QuQuizBuilder');
        })->name('quiz-builder');
 

        // Question Management (Teachers & Admins)
        Route::resource('questions', \App\Http\Controllers\QuQuestionController::class);
        Route::post('questions/bulk-import', [\App\Http\Controllers\QuQuestionController::class, 'bulkImport'])
            ->name('questions.bulk-import');

        // Exam Management (Teachers & Admins)
        Route::resource('exams', \App\Http\Controllers\QuExamController::class);
        Route::get('exams/users/search', [\App\Http\Controllers\QuExamController::class, 'searchUsers'])
            ->name('exams.users.search');
        Route::get('exams/questions/available', [\App\Http\Controllers\QuExamController::class, 'getAvailableQuestions'])
            ->name('exams.questions.available');
        Route::get('exams/{exam}/grading-attempts', [\App\Http\Controllers\QuExamController::class, 'getGradingAttempts'])
            ->name('exms.grading-attempts');
        Route::get('exams/grading/{attempt}/data', [\App\Http\Controllers\QuExamController::class, 'getAttemptGradingData'])
            ->name('exams.grading-data');

        // Teacher Grading Routes
        Route::get('grading', [\App\Http\Controllers\QuExamController::class, 'teacherGradingIndex'])
            ->name('grading.index');
        Route::get('grading/{attempt}', [\App\Http\Controllers\QuExamController::class, 'teacherGradeAttempt'])
            ->name('grading.show');
        Route::post('grading/{attempt}', [\App\Http\Controllers\QuExamController::class, 'saveGrades'])
            ->name('grading.save');

        // Analytics Routes
        Route::get('analytics', [\App\Http\Controllers\QuExamController::class, 'analyticsIndex'])
            ->name('analytics.index');
        Route::get('analytics/{exam}', [\App\Http\Controllers\QuExamController::class, 'examAnalytics'])
            ->name('analytics.exam');

        // Student Exam Taking Routes
        Route::prefix('student')->name('student.')->group(function () {
            Route::get('exams', [\App\Http\Controllers\QuExamController::class, 'studentIndex'])
                ->name('exams.index');
            Route::get('exams/{quExam}', [\App\Http\Controllers\QuExamController::class, 'studentShow'])
                ->name('exams.show');
            Route::post('exams/{quExam}/start', [\App\Http\Controllers\QuExamController::class, 'startExam'])
                ->name('exams.start');
            Route::get('exams/{quExam}/take/{quAttempt}', [\App\Http\Controllers\QuExamController::class, 'takeExam'])
                ->name('exams.take');
            Route::post('exams/{quExam}/auto-save/{quAttempt}', [\App\Http\Controllers\QuExamController::class, 'autoSave'])
                ->name('exams.auto-save');
            Route::post('exams/{quExam}/submit/{quAttempt}', [\App\Http\Controllers\QuExamController::class, 'submitExam'])
                ->name('exams.submit');
            Route::get('exams/{quExam}/results/{quAttempt}', [\App\Http\Controllers\QuExamController::class, 'viewResults'])
                ->name('exams.results');
            Route::get('exams/{quExam}/print', [\App\Http\Controllers\QuExamController::class, 'printExam'])
                ->name('exams.print');
        });
    });

    // Direct route to Qu Exam Management
    Route::get('/qu-exams', function () {
        return redirect()->route('qu.exams.index');
    })->name('qu-exams.index');

    // Direct route to create exam
    Route::get('/qu-exams/create', function () {
        return Inertia::render('my_class/QuQuestionBankSystem/QuQuizManagement/QuQuizBuilder');
    })->name('qu-exams.create');

    // Direct route to edit exam
    Route::get('/qu-exams/{id}/edit', function ($id) {
        return Inertia::render('my_class/QuQuestionBankSystem/QuQuizManagement/QuQuizBuilder', ['quizId' => $id]);
    })->name('qu-exams.edit');

    // Aliases for the store and update routes to match what the frontend expects
    Route::post('/qu-exams', [\App\Http\Controllers\QuExamController::class, 'store'])->name('qu-exams.store');
    Route::put('/qu-exams/{exam}', [\App\Http\Controllers\QuExamController::class, 'update'])->name('qu-exams.update');
    Route::delete('/qu-exams/{exam}', [\App\Http\Controllers\QuExamController::class, 'destroy'])->name('qu-exams.destroy');
    
    // Additional routes needed by the frontend
    Route::get('/qu-exams/{exam}', [\App\Http\Controllers\QuExamController::class, 'show'])->name('qu-exams.show');
    Route::get('/qu-exams/{exam}/data', [\App\Http\Controllers\QuExamController::class, 'getExamData'])->name('qu-exams.data');
    
    // Grading attempts route
    Route::get('/qu-exams/{exam}/grading-attempts', [\App\Http\Controllers\QuExamController::class, 'getGradingAttempts'])->name('qu-exams.grading-attempts');

    // Teacher presentation / demo lesson editor route
    Route::get('/teacher/presentation', function () {
        return Inertia::render('my_class/teacher/peresntation_2/peresentation_2');
    })->name('teacher.presentation');

});