<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\ClassroomSubjectTeacherController;
use App\Http\Controllers\Curriculum\CurriculumController;

use App\Http\Controllers\GradeController;
use App\Http\Controllers\GradeSubjectController;
use App\Http\Controllers\HRController;
use App\Http\Controllers\PeriodDetailController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\ScheduleCopyController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScheduleAdminNewController;
use App\Http\Controllers\ScheduleDailyController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SchoolSectionController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentParentController;
use App\Http\Controllers\SchoolFilterController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SemesterTestController;
use App\Http\Controllers\TeacherManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Resource Routes
Route::get('/teachers', [TeacherManagementController::class, 'index']);

    Route::resource('hr', HRController::class);
    Route::resource('school', SchoolController::class);
    Route::resource('school_section', SchoolSectionController::class);
    Route::resource('subject', SubjectController::class);
    Route::resource('stage', StageController::class);
    Route::resource('grade', GradeController::class);
    Route::resource('classroom', ClassroomController::class);
    Route::resource('teacher', TeacherController::class);
    Route::resource('student-parent', StudentParentController::class);
    Route::get('students/filtered', [StudentController::class, 'getFiltered'])->name('students.filtered');
    Route::resource('students', StudentController::class);
    Route::resource('grade-subject', GradeSubjectController::class);
    Route::resource('semester-test', SemesterTestController::class);

    Route::resource('classroom-subject-teacher', ClassroomSubjectTeacherController::class);
    Route::resource('academic-year', AcademicYearController::class);
    Route::resource('semester', SemesterController::class);
    Route::resource('calendar', CalendarController::class);
    Route::resource('schedule-dailies', ScheduleDailyController::class) ;
    Route::resource('schedule-copies', ScheduleCopyController::class);
    Route::resource('schedules', ScheduleController::class);
    Route::post('schedule/update2', [ScheduleController::class, 'update2'])->name('schedules.update2');
    Route::get('schedule/load_data', [ScheduleController::class, 'load_data'])->name('schedules.load_data');
    Route::get('schedule/new', [ScheduleAdminNewController::class, 'index'])->name('schedules.new');

    Route::resource('curriculum', CurriculumController::class);
    // Route::resource('curriculum-detail', CurriculumDetailController::class);
    // Route::resource('curriculum-map', CurriculumMapController::class);
    // Route::resource('question-bank', QuestionBankController::class);
    // Classroom Routes
    Route::resource('period-details', PeriodDetailController::class);
    // createSchedule
    Route::get('/schedule-copies/{id}/check-schedule-changes', [ScheduleCopyController::class, 'checkScheduleChanges']);
Route::post('/schedule-copies/{id}/execute-schedule-changes', [ScheduleCopyController::class, 'executeScheduleChanges']);


    Route::get('classroom/export', [ClassroomController::class, 'export'])
        ->name('classroom.export');
        Route::post('classroom/import', [ClassroomController::class, 'import'])
        ->name('classroom.import');
        Route::post('classroom/validate-import', [ClassroomController::class, 'validateImport'])
        ->name('classroom.validate-import');
        Route::post('classroom/undo-import/{importId}', [ClassroomController::class, 'undoImport'])
        ->name('classroom.undo-import');

        Route::post('grade-subject/import', [GradeSubjectController::class, 'import'])
        ->name('grade-subject.import');
        Route::post('grade-subject/validate-import', [GradeSubjectController::class, 'validateImport'])
        ->name('grade-subject.validate-import');
        Route::post('grade-subject/undo-import/{importId}', [GradeSubjectController::class, 'undoImport'])
        ->name('grade-subject.undo-import');

    // Teacher Routes
    Route::get('teacher/export', [TeacherController::class, 'export'])
        ->name('teacher.export');
    Route::post('teacher/import', [TeacherController::class, 'import'])
        ->name('teacher.import');
    Route::post('teacher/validate-import', [TeacherController::class, 'validateImport'])
        ->name('teacher.validate-import');
    Route::post('teacher/undo-import/{importId}', [TeacherController::class, 'undoImport'])
        ->name('teacher.undo-import');

    // Student Parent Routes
    Route::get('student-parent/export', [StudentParentController::class, 'export'])
        ->name('student-parent.export');
    Route::post('student-parent/validate-import', [StudentParentController::class, 'validateImport'])
        ->name('student-parent.validate-import');
    Route::post('student-parent/import', [StudentParentController::class, 'import'])
        ->name('student-parent.import');
    Route::post('student-parent/undo-import/{importId}', [StudentParentController::class, 'undoImport'])
        ->name('student-parent.undo-import');

    // Student Routes
    Route::post('students/validate-import', [StudentController::class, 'validateImport'])
        ->name('students.validate-import');
    Route::post('students/get-school-students/{school_id}', [StudentController::class, 'get_school_students'])
        ->name('students.get-school-students');
    Route::post('students/import', [StudentController::class, 'import'])
        ->name('students.import');


     Route::post('subject/import', [SubjectController::class, 'import'])
        ->name('subjects.import');

     Route::post('subject/validate-import', [SubjectController::class, 'validateImport'])
        ->name('subjects.validate-import');

    // Lesson Plan Templates Routes
    Route::get('subject/{subject}/lesson-plan-templates', [SubjectController::class, 'manageLessonPlanTemplates'])
        ->name('subject.lesson-plan-templates');
    Route::patch('subject/{subject}/lesson-plan-templates', [SubjectController::class, 'updateLessonPlanTemplates'])
        ->name('subject.update-lesson-plan-templates');


    Route::post('students/undo-import/{importId}', [StudentController::class, 'undoImport'])
        ->name('students.undo-import');

    // Filter Routes
    Route::get('stages/by-school/{schoolId}', [SchoolFilterController::class, 'getStagesBySchool'])
        ->name('stages.by-school');
    Route::get('grades/by-stage/{stageId}', [SchoolFilterController::class, 'getGradesByStage'])
        ->name('grades.by-stage');
    Route::get('classrooms/by-grade/{gradeId}', [SchoolFilterController::class, 'getClassroomsByGrade'])
        ->name('classrooms.by-grade');

        // classroom-subject-teacher/validate-import





        Route::post('classroom-subject-teacher/validate-import', [ClassroomSubjectTeacherController::class, 'validateImport'])
        ->name('classroom-subject-teacher.validate-import');
    Route::post('classroom-subject-teacher/import', [ClassroomSubjectTeacherController::class, 'import'])
        ->name('classroom-subject-teacher.import');
    Route::post('classroom-subject-teacher/undo-import/{importId}', [ClassroomSubjectTeacherController::class, 'undoImport'])
        ->name('classroom-subject-teacher.undo-import');




    // Academic Year Routes
    Route::get('academic-year/export', [AcademicYearController::class, 'export'])
        ->name('academic-year.export');
    Route::post('academic-year/import', [AcademicYearController::class, 'import'])
        ->name('academic-year.import');
    Route::post('academic-year/validate-import', [AcademicYearController::class, 'validateImport'])
        ->name('academic-year.validate-import');

    // Calendar Routes
    Route::post('calendar/validate-import', [CalendarController::class, 'validateImport'])
        ->name('calendar.validate-import');
    Route::post('calendar/import', [CalendarController::class, 'import'])
        ->name('calendar.import');

    Route::post('academic-year/{academicYear}/generate-calendar', [AcademicYearController::class, 'generateCalendar'])
        ->name('academic-year.generate-calendar');

    Route::resource('question-banks', QuestionBankController::class);
});



Route:: middleware(['auth', 'verified', 'role:admin'])->prefix('api')->group(function () {
    // Route::get('/teachers', [TeacherManagementController::class, 'index']);
// Route:: middleware(['auth', 'verified', 'role:admin'])->group(function () {
Route::post('/teachers', [TeacherManagementController::class, 'store']);
Route::put('/teachers/{teacher}', [TeacherManagementController::class, 'update']);
Route::delete('/teachers/{teacher}', [TeacherManagementController::class, 'destroy']);
Route::get('/teachers/export', [TeacherManagementController::class, 'export']);
});












