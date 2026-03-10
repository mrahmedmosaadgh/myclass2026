<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| School Admin Role Routes (MyClass2026)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'web', 'role:school_admin|admin|super_admin'])->prefix('myclass2026/school-admin')->name('school-admin.')->group(function () {
    
    // --- Curriculum ---
    Route::prefix('curriculum')->name('curriculum.')->group(function () {
        
        // Course Management Entry
        Route::get('/courses', function () {
            // Replicating basic render for the top-level index
            return Inertia::render('myclass2026/roles/school-admin/curriculum/CourseManagement/Course/Index');
        })->name('courses.index');
        
    });

    // --- Standalone Features (Admin View) ---
    Route::prefix('modules')->name('modules.')->group(function () {
        
        // Skill Management
        Route::get('/skills', function () {
            return Inertia::render('myclass2026/features/SkillManagement/SkillCategoryList');
        })->name('skills.index');

        // Gamification / Behavior Management
        Route::get('/gamification', function () {
            // Renamed from reward_sys to Gamification in the new architecture
            return Inertia::render('myclass2026/features/Gamification/admin/BehaviorManagement');
        })->name('gamification.index');

    });

    // --- Users & Profiles ---
    Route::prefix('users')->name('users.')->group(function () {
        
        Route::get('/students', function () {
            // Replicating StudentController.php admin index view
            return Inertia::render('myclass2026/roles/school-admin/users/Students/Index');
        })->name('students.index');

        Route::get('/teachers', function () {
            // Replicating TeacherManagementController.php or TeacherHome index view
            return Inertia::render('myclass2026/roles/school-admin/users/Teacher/TeacherHome');
        })->name('teachers.index');

        Route::get('/parents', function () {
            // Replicating StudentParentController.php admin index view
            return Inertia::render('myclass2026/roles/school-admin/users/StudentParents/Index');
        })->name('parents.index');

    });

});
