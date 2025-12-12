<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Acadimy\AcadimyUserManagerController;
 
 
 
use Illuminate\Support\Facades\Auth;
 
use Illuminate\Support\Facades\Storage;
 
 
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Task routes
    // Route::get('/developer/ticktick', [App\Http\Controllers\Developer\TaskController::class, 'index'])->name('developer.ticktick');
     Route::get('/acadimy/user-manager', [AcadimyUserManagerController::class, 'index'])->name('acadimy.admin.users.index');
    //  Route::get('/acadimy/user-manager', [AcadimyUserManagerController::class, 'index'])->name('acadimy.admin.users.index');

    });
 