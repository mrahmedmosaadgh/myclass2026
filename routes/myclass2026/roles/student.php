<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Student Role Routes (MyClass2026)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'web', 'role:student'])->prefix('myclass2026/student')->name('student.')->group(function () {
    
    // --- Communication ---
    Route::prefix('communication')->name('communication.')->group(function () {
        
        // Messages / Chat
        Route::get('/messages', function () {
            // Replicating ConversationController@index
            return Inertia::render('myclass2026/features/Communication/Conversations/Index');
        })->name('messages.index');

    });

});
