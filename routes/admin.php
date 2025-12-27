<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\ScheduleCopyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\LoadSchoolsController;
use App\Http\Controllers\ClassroomSubjectTeacherController;
use App\Http\Controllers\Student\ScheduleController;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/user_management', [PermissionsController::class, 'index'])->name('admin.user_management');
    Route::post('/permissions', [PermissionsController::class, 'store'])->name('permissions.store');
    Route::put('/permissions/{permission}', [PermissionsController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionsController::class, 'destroy'])->name('permissions.destroy');

    // Roles
    Route::post('/roles', [PermissionsController::class, 'storeRole'])->name('admin.roles.store');
    Route::put('/roles/{role}', [PermissionsController::class, 'updateRole'])->name('admin.roles.update');
    Route::delete('/roles/{role}', [PermissionsController::class, 'destroyRole'])->name('admin.roles.destroy');

    // Users
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::put('/users/{user}/restore', [UserController::class, 'restore'])->name('admin.users.restore');
    Route::put('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('admin.users.reset-password');
    Route::put('/users/{user}/roles', [UserController::class, 'updateRoles'])->name('admin.users.roles.update');

    // Schools
    Route::get('/schools', [LoadSchoolsController::class, 'adminSchools']);
    Route::get('/teacher/{teacher}/schools', [LoadSchoolsController::class, 'teacherSchools']);
    Route::get('/school/{school}/classroom-subject-teachers', [ClassroomSubjectTeacherController::class, 'bySchool']);

    // Schedule Copies
    Route::get('/school/{school}/schedule-copies', [ScheduleCopyController::class, 'bySchool']);

    Route::resource('documentation', DocumentationController::class);

    // Documentation Portal Routes
    Route::get('documentation-portal', [App\Http\Controllers\DocumentationPortalController::class, 'index'])->name('documentation-portal.index');
    Route::get('documentation-portal/search', [App\Http\Controllers\DocumentationPortalController::class, 'search'])->name('documentation-portal.search');
    Route::get('documentation-portal/file-content', [App\Http\Controllers\DocumentationPortalController::class, 'getFileContent'])->name('documentation-portal.file-content');

    Route::get('attendance', function () {
        // resources\js\Pages\my_class\admin\Attendance\Index.vue
        return Inertia::render('my_class/admin/Attendance/Index');
    })->name('admin.attendance');

    // Curriculum Management Routes
    Route::get('curriculum/management', [App\Http\Controllers\Curriculum\CurriculumController::class, 'index'])->name('admin.curriculum.management');

    // Year, Semester & Calendar Management
    Route::get('academic-calendar', [App\Http\Controllers\YearSemesterCalendarController::class, 'index'])->name('admin.academic_calendar.index');
    Route::post('academic-calendar/year', [App\Http\Controllers\YearSemesterCalendarController::class, 'storeYear'])->name('admin.academic_calendar.year.store');
    Route::put('academic-calendar/year/{year}/toggle', [App\Http\Controllers\YearSemesterCalendarController::class, 'toggleYearActive'])->name('admin.academic_calendar.year.toggle');
    Route::get('academic-calendar/year/{year}/calendar-data', [App\Http\Controllers\YearSemesterCalendarController::class, 'getCalendarData'])->name('admin.academic_calendar.year.calendar_data');
    Route::post('academic-calendar/semester/{semester}', [App\Http\Controllers\YearSemesterCalendarController::class, 'updateSemester'])->name('admin.academic_calendar.semester.update');
    Route::post('academic-calendar/semester/{semester}/generate', [App\Http\Controllers\YearSemesterCalendarController::class, 'generateCalendar'])->name('admin.academic_calendar.semester.generate');
    Route::get('academic-calendar/year/{year}/missing-days', [App\Http\Controllers\YearSemesterCalendarController::class, 'getMissingDays'])->name('admin.academic_calendar.year.missing_days');

});

Route::middleware(['auth:sanctum'])->group(function () {
    // Existing routes...

    Route::post('/admin/schedules/update/{schedule}', [ScheduleController::class, 'update_day_period']);
    // Route::get('/admin/schedules/update_period/{schedule_id}', [ScheduleController::class, 'update_period']);
    Route::get('/admin/schedules/{school_id}/{schedule_copy_id}', [ScheduleController::class, 'getScheduleData']);
Route::post('/admin/schedule-copies/create-entries', [ScheduleCopyController::class, 'createScheduleEntries']);
    // Add new school routes
    Route::get('/admin/schools', [LoadSchoolsController::class, 'adminSchools']);
    Route::get('/teacher/{teacher}/schools', [LoadSchoolsController::class, 'teacherSchools']);
});
