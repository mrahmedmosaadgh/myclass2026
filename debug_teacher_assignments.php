<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CourseManagement\Course;
use App\Models\CourseManagement\CourseTeacherAssignment;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

echo "=== Teacher Assignment Debug ===\n\n";

// Check total counts
echo "Total Courses: " . Course::count() . "\n";
echo "Total Teachers: " . Teacher::count() . "\n";
echo "Total Assignments: " . CourseTeacherAssignment::count() . "\n";
echo "Active Assignments: " . CourseTeacherAssignment::where('is_active', true)->count() . "\n\n";

// Check assignments table directly
echo "=== Raw Assignments ===\n";
$assignments = DB::table('course_teacher_assignments')
    ->select('course_id', 'teacher_id', 'is_active')
    ->get();

foreach ($assignments as $assignment) {
    echo "Course ID: {$assignment->course_id}, Teacher ID: {$assignment->teacher_id}, Active: " . 
         ($assignment->is_active ? 'Yes' : 'No') . "\n";
}

echo "\n=== Course-Teacher Relationships ===\n";

// Check each course and its teachers
$courses = Course::with('teachers')->get();
foreach ($courses as $course) {
    echo "Course: {$course->name} (ID: {$course->id})\n";
    echo "  Teachers count: " . $course->teachers->count() . "\n";
    
    if ($course->teachers->count() > 0) {
        foreach ($course->teachers as $teacher) {
            echo "  - Teacher: " . ($teacher->user->name ?? $teacher->name ?? 'Unknown') . " (ID: {$teacher->id})\n";
        }
    }
    echo "\n";
}

echo "=== Teacher-Course Relationships ===\n";

// Check teachers with courses
$teachers = Teacher::with(['user', 'courses'])->get();
foreach ($teachers as $teacher) {
    if ($teacher->courses->count() > 0) {
        echo "Teacher: " . ($teacher->user->name ?? $teacher->name ?? 'Unknown') . " (ID: {$teacher->id})\n";
        echo "  Courses count: " . $teacher->courses->count() . "\n";
        
        foreach ($teacher->courses as $course) {
            echo "  - Course: {$course->name} (ID: {$course->id})\n";
        }
        echo "\n";
    }
}

echo "=== Debug Complete ===\n";