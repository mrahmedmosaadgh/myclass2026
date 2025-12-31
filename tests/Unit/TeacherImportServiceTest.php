<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TeacherImportService;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\School;
use App\Models\HR;
use App\Models\Stage;
use App\Models\Grade;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeacherImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TeacherImportService $service;
    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TeacherImportService();
        
        // Create a user for HR (user_id is required by migration)
        $hrUser = User::create([
            'name' => 'HR User',
            'email' => 'hr@example.com',
            'password' => 'password',
            'role' => 'hr_admin',
            'is_active' => 1,
        ]);

        // Create HR first with required user_id
        $hr = HR::create([
            'name' => 'Test HR',
            'active' => true,
            'user_id' => $hrUser->id,
        ]);
        
        // Create test school with HR
        $this->school = School::create([
            'name' => 'Test School',
            'h_r_id' => $hr->id
        ]);

        // Create default stage and grade for the school (required by classrooms)
        $stage = Stage::create([
            'name' => 'Primary',
            'school_id' => $this->school->id,
        ]);
        Grade::create([
            'name' => 'Grade 1',
            'school_id' => $this->school->id,
            'stage_id' => $stage->id,
        ]);
    }

    public function test_create_or_update_teacher_creates_new_teacher_with_user()
    {
        $teacherData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '1234567890'
        ];

        $teacher = $this->service->createOrUpdateTeacher($teacherData, $this->school->id);

        // Verify teacher was created
        $this->assertInstanceOf(Teacher::class, $teacher);
        $this->assertEquals('John Doe', $teacher->name);
        $this->assertEquals('john@example.com', $teacher->email);
        $this->assertEquals($this->school->id, $teacher->school_id);
        $this->assertNotNull($teacher->t_id);
        $this->assertTrue(str_starts_with($teacher->t_id, 't'));

        // Verify user was created
        $this->assertNotNull($teacher->user);
        $this->assertEquals('John Doe', $teacher->user->name);
        $this->assertEquals('john@example.com', $teacher->user->email);
        $this->assertEquals('teacher', $teacher->user->role);
        $this->assertTrue((bool) $teacher->user->is_active);
    }

    public function test_create_or_update_teacher_defaults_email_to_t_id()
    {
        $teacherData = [
            'name' => 'Jane Smith',
            'phone_number' => '0987654321'
        ];

        $teacher = $this->service->createOrUpdateTeacher($teacherData, $this->school->id);

        // Verify email defaults to t_id
        $this->assertEquals($teacher->t_id, $teacher->user->email);
    }

    public function test_create_or_update_teacher_updates_existing_teacher()
    {
        // Create initial teacher manually
        $teacher = Teacher::create([
            'name' => 'Existing Teacher',
            'school_id' => $this->school->id,
            'phone_number' => '1111111111'
        ]);

        $updateData = [
            'name' => 'Existing Teacher',
            'phone_number' => '2222222222',
            'email' => 'updated@example.com'
        ];

        $updatedTeacher = $this->service->createOrUpdateTeacher($updateData, $this->school->id);

        // Verify it's the same teacher but updated
        $this->assertEquals($teacher->id, $updatedTeacher->id);
        $this->assertEquals('2222222222', $updatedTeacher->phone_number);
        $this->assertEquals('updated@example.com', $updatedTeacher->email);
    }

    public function test_create_or_update_classroom_creates_new_classroom()
    {
        $classroom = $this->service->createOrUpdateClassroom('Grade 1A', $this->school->id);

        $this->assertInstanceOf(Classroom::class, $classroom);
        $this->assertEquals('Grade 1A', $classroom->name);
        $this->assertEquals($this->school->id, $classroom->school_id);
    }

    public function test_create_or_update_classroom_returns_existing_classroom()
    {
        // Create initial classroom manually
        $existingClassroom = Classroom::create([
            'name' => 'Grade 2B',
            'school_id' => $this->school->id,
            'capacity' => 30,
            'stage_id' => \App\Models\Stage::where('school_id', $this->school->id)->value('id'),
            'grade_id' => \App\Models\Grade::where('school_id', $this->school->id)->value('id'),
        ]);

        $classroom = $this->service->createOrUpdateClassroom('Grade 2B', $this->school->id);

        // Should return the existing classroom
        $this->assertEquals($existingClassroom->id, $classroom->id);
    }

    public function test_create_or_update_subject_creates_new_subject()
    {
        $subject = $this->service->createOrUpdateSubject('Mathematics', $this->school->id);

        $this->assertInstanceOf(Subject::class, $subject);
        $this->assertEquals('Mathematics', $subject->name);
        $this->assertEquals($this->school->id, $subject->school_id);
    }

    public function test_create_or_update_subject_returns_existing_subject()
    {
        // Create initial subject manually
        $existingSubject = Subject::create([
            'name' => 'Science',
            'school_id' => $this->school->id
        ]);

        $subject = $this->service->createOrUpdateSubject('Science', $this->school->id);

        // Should return the existing subject
        $this->assertEquals($existingSubject->id, $subject->id);
    }
}