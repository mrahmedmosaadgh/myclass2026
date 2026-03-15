<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\ClassroomSubjectTeacher;
use Illuminate\Support\Facades\Hash;

class ClassroomRecordsTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create school
        $school = School::firstOrCreate(
            ['name' => 'Test School'],
            ['current_academic_year_id' => 1]
        );

        // Get or create academic year
        $year = AcademicYear::firstOrCreate(
            ['school_id' => $school->id, 'year' => '2025-2026'],
            ['is_current' => true, 'start_date' => '2025-09-01', 'end_date' => '2026-06-30']
        );

        // Update school with current year
        $school->update(['current_academic_year_id' => $year->id]);

        // Create test teacher user
        $teacherUser = User::firstOrCreate(
            ['email' => 'teacher@test.com'],
            [
                'name' => 'Test Teacher',
                'password' => Hash::make('password'),
                'school_id' => $school->id,
            ]
        );

        // Create teacher record
        $teacher = Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            [
                'school_id' => $school->id,
                'name' => 'Test Teacher',
                'email' => $teacherUser->email,
            ]
        );

        // Create test classrooms
        $classroom1 = Classroom::firstOrCreate(
            ['name' => 'Class 5A', 'school_id' => $school->id],
            ['grade' => '5', 'section' => 'A']
        );

        $classroom2 = Classroom::firstOrCreate(
            ['name' => 'Class 5B', 'school_id' => $school->id],
            ['grade' => '5', 'section' => 'B']
        );

        // Create test subjects
        $subject1 = Subject::firstOrCreate(
            ['name' => 'Mathematics', 'school_id' => $school->id],
            ['code' => 'MATH']
        );

        $subject2 = Subject::firstOrCreate(
            ['name' => 'Science', 'school_id' => $school->id],
            ['code' => 'SCI']
        );

        // Assign teacher to classroom+subject combinations
        ClassroomSubjectTeacher::firstOrCreate(
            [
                'classroom_id' => $classroom1->id,
                'subject_id' => $subject1->id,
                'teacher_id' => $teacher->id,
            ],
            ['school_id' => $school->id]
        );

        ClassroomSubjectTeacher::firstOrCreate(
            [
                'classroom_id' => $classroom1->id,
                'subject_id' => $subject2->id,
                'teacher_id' => $teacher->id,
            ],
            ['school_id' => $school->id]
        );

        ClassroomSubjectTeacher::firstOrCreate(
            [
                'classroom_id' => $classroom2->id,
                'subject_id' => $subject1->id,
                'teacher_id' => $teacher->id,
            ],
            ['school_id' => $school->id]
        );

        $this->command->info('✅ Classroom Records test data created successfully!');
        $this->command->info('');
        $this->command->info('Teacher Login:');
        $this->command->info('  Email: teacher@test.com');
        $this->command->info('  Password: password');
        $this->command->info('');
        $this->command->info('Available Classrooms:');
        $this->command->info('  - Class 5A (Mathematics, Science)');
        $this->command->info('  - Class 5B (Mathematics)');
        $this->command->info('');
        $this->command->info('Subjects:');
        $this->command->info('  - Mathematics');
        $this->command->info('  - Science');
    }
}
