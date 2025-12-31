<?php

namespace Tests\Unit;

use App\Models\Teacher;
use App\Models\User;
use App\Models\School;
use App\Models\ClassroomSubjectTeacher;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Stage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherStatusManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $school;
    protected $academicYear;
    protected $classroom;
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->school = School::factory()->create();
        $this->academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'active' => true
        ]);
        
        // Create stage and grade manually to avoid factory issues
        $stage = Stage::create([
            'name' => 'Primary',
            'school_id' => $this->school->id
        ]);
        
        $grade = Grade::create([
            'name' => 'Grade 1',
            'school_id' => $this->school->id,
            'stage_id' => $stage->id
        ]);
        
        $this->classroom = Classroom::create([
            'name' => 'Class A',
            'capacity' => 30,
            'school_id' => $this->school->id,
            'stage_id' => $stage->id,
            'grade_id' => $grade->id
        ]);
        
        $this->subject = Subject::create([
            'name' => 'Mathematics',
            'school_id' => $this->school->id
        ]);
    }

    public function test_teacher_creation_creates_associated_user_account()
    {
        $teacher = Teacher::create([
            'name' => 'John Doe',
            'school_id' => $this->school->id,
            'email' => 'john@example.com'
        ]);

        $this->assertNotNull($teacher->user);
        $this->assertEquals('teacher', $teacher->user->role);
        $this->assertEquals('john@example.com', $teacher->user->email);
        $this->assertTrue($teacher->user->is_active);
    }

    public function test_teacher_creation_uses_t_id_as_email_when_no_email_provided()
    {
        $teacher = Teacher::create([
            'name' => 'Jane Doe',
            'school_id' => $this->school->id
        ]);

        $this->assertNotNull($teacher->user);
        $this->assertEquals($teacher->t_id, $teacher->user->email);
    }

    public function test_soft_deleting_teacher_deactivates_user_account()
    {
        $teacher = Teacher::create([
            'name' => 'John Doe',
            'school_id' => $this->school->id,
            'email' => 'john@example.com'
        ]);

        $user = $teacher->user;
        $this->assertTrue($user->is_active);

        // Soft delete the teacher
        $teacher->delete();

        $user->refresh();
        $this->assertFalse($user->is_active);
    }

    public function test_restoring_teacher_reactivates_user_account()
    {
        $teacher = Teacher::create([
            'name' => 'John Doe',
            'school_id' => $this->school->id,
            'email' => 'john@example.com'
        ]);

        $user = $teacher->user;
        
        // Soft delete then restore
        $teacher->delete();
        $teacher->restore();

        $user->refresh();
        $this->assertTrue($user->is_active);
    }

    public function test_cannot_assign_inactive_teacher_to_classroom()
    {
        $teacher = Teacher::create([
            'name' => 'John Doe',
            'school_id' => $this->school->id,
            'email' => 'john@example.com'
        ]);

        // Soft delete the teacher
        $teacher->delete();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot assign inactive or non-existent teacher to classroom.');

        ClassroomSubjectTeacher::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $teacher->id,
            'classes_per_week' => 5
        ]);
    }

    public function test_teacher_has_active_assignments_method_works()
    {
        $teacher = Teacher::create([
            'name' => 'John Doe',
            'school_id' => $this->school->id,
            'email' => 'john@example.com'
        ]);

        $this->assertFalse($teacher->hasActiveAssignments());

        ClassroomSubjectTeacher::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $teacher->id,
            'classes_per_week' => 5
        ]);

        $this->assertTrue($teacher->hasActiveAssignments());
    }

    public function test_teacher_referential_integrity_validation_works()
    {
        $teacher = Teacher::create([
            'name' => 'John Doe',
            'school_id' => $this->school->id,
            'email' => 'john@example.com'
        ]);

        $this->assertTrue($teacher->validateReferentialIntegrity());

        // Manually break the user relationship
        $teacher->user_id = 999999;
        $this->assertFalse($teacher->validateReferentialIntegrity());
    }

    public function test_safe_delete_preserves_historical_data()
    {
        $teacher = Teacher::create([
            'name' => 'John Doe',
            'school_id' => $this->school->id,
            'email' => 'john@example.com'
        ]);

        $assignment = ClassroomSubjectTeacher::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $teacher->id,
            'classes_per_week' => 5
        ]);

        $this->assertTrue($teacher->safeDelete());

        // Teacher should be soft deleted
        $this->assertSoftDeleted($teacher);

        // Assignment should still exist (historical data preserved)
        $this->assertDatabaseHas('classroom_subject_teachers', [
            'id' => $assignment->id,
            'teacher_id' => $teacher->id
        ]);

        // User should be deactivated
        $teacher->user->refresh();
        $this->assertFalse($teacher->user->is_active);
    }
}