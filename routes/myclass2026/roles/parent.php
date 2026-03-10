<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Parent Role Routes (MyClass2026)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'web', 'role:parent'])->prefix('myclass2026/parent')->name('parent.')->group(function () {
    
    // --- Communication ---
    Route::prefix('communication')->name('communication.')->group(function () {
        
        // Messages / Chat
        Route::get('/messages', function () {
            // Replicating ConversationController@index
            return Inertia::render('myclass2026/features/Communication/Conversations/Index');
        })->name('messages.index');

    });

});
