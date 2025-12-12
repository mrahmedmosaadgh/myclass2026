<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseManagement\CourseController;
use App\Http\Controllers\CourseManagement\CourseLevelController;
use App\Http\Controllers\CourseManagement\CourseSectionController;
use App\Http\Controllers\CourseManagement\CourseLessonController;



Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('import_students')->name('import_students.')->group(function () {
  
    // Import routes
    Route::get('import', [App\Http\Controllers\CourseManagement\StudentImportController::class, 'index'])->name('import.index');
    Route::get('import/template', [App\Http\Controllers\CourseManagement\StudentImportController::class, 'downloadTemplate'])->name('import.template');
    Route::post('import/validate', [App\Http\Controllers\CourseManagement\StudentImportController::class, 'validateFile'])->name('import.validate');
    Route::post('import/process', [App\Http\Controllers\CourseManagement\StudentImportController::class, 'import'])->name('import.process');
    

});



 


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('course-management')->name('course-management.')->group(function () {
    
    // Course routes
    Route::resource('courses', CourseController::class);
    
    // Nested Level routes
    Route::resource('courses.levels', CourseLevelController::class)->shallow();
    
    // Nested Section routes  
    Route::resource('levels.sections', CourseSectionController::class)->shallow();
    
    // Nested Lesson routes
    Route::resource('sections.lessons', CourseLessonController::class)->shallow();
    
    // Additional utility routes
    Route::post('courses/{course}/levels/reorder', [CourseLevelController::class, 'reorder'])->name('courses.levels.reorder');
    Route::post('levels/{level}/sections/reorder', [CourseSectionController::class, 'reorder'])->name('levels.sections.reorder');
    Route::post('sections/{section}/lessons/reorder', [CourseLessonController::class, 'reorder'])->name('sections.lessons.reorder');
    
    // Import routes
    Route::get('import', [App\Http\Controllers\CourseManagement\CourseImportController::class, 'index'])->name('import.index');
    Route::get('import/template', [App\Http\Controllers\CourseManagement\CourseImportController::class, 'downloadTemplate'])->name('import.template');
    Route::post('import/validate', [App\Http\Controllers\CourseManagement\CourseImportController::class, 'validateFile'])->name('import.validate');
    Route::post('import/process', [App\Http\Controllers\CourseManagement\CourseImportController::class, 'import'])->name('import.process');
    
    // Teacher Assignment routes
    Route::prefix('teachers')->name('teachers.')->group(function () {
        Route::get('/', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'index'])->name('index');
        Route::get('assign-by-course', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'assignByCourse'])->name('assign-by-course');
        Route::get('assign-by-teacher', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'assignByTeacher'])->name('assign-by-teacher');
        Route::post('assign-courses-to-teacher', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'assignCoursesToTeacher'])->name('assign-courses-to-teacher');
        Route::post('assign-teachers-to-course', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'assignTeachersToCourse'])->name('assign-teachers-to-course');
        Route::delete('assignments/{assignment}', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'removeAssignment'])->name('remove-assignment');
        Route::delete('remove-assignment-by-ids', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'removeAssignmentByIds'])->name('remove-assignment-by-ids');
        Route::patch('assignments/{assignment}/toggle', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'toggleAssignment'])->name('toggle-assignment');
        
        // Preview routes
        Route::get('preview-course', [App\Http\Controllers\CourseManagement\TeacherAssignmentController::class, 'previewCourse'])->name('preview-course');
        
        // Teacher Dashboard
        Route::get('dashboard', function () {
            return inertia('CourseManagement/Teacher/TeacherDashboard');
        })->name('dashboard');
    });
    
    // API routes for course structure
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('courses/with-structure', [App\Http\Controllers\CourseManagement\CourseStructureController::class, 'index'])->name('courses.with-structure');
        Route::get('courses/{course}/structure', [App\Http\Controllers\CourseManagement\CourseStructureController::class, 'show'])->name('courses.structure');
        Route::get('teacher/courses', [App\Http\Controllers\CourseManagement\CourseStructureController::class, 'teacherCourses'])->name('teacher.courses');
    });
});