<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Teacher;
use App\Models\User;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\ClassroomSubjectTeacher;
use App\Services\TeacherImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class AdvancedTeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $school;
    protected $academicYear;
    protected $teacherImportService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->school = School::factory()->create();
        $this->academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'active' => true
        ]);
        
        $this->teacherImportService = new TeacherImportService();
    }

    /** @test */
    public function teacher_user_status_synchronization_works()
    {
        // Create a teacher with user
        $teacher = Teacher::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Test Teacher'
        ]);

        // Verify user was created and is active
        $this->assertNotNull($teacher->user);
        $this->assertTrue($teacher->user->is_active);
        $this->assertTrue($teacher->canBeAssignedToClassroom());

        // Soft delete the teacher
        $teacher->delete();

        // Verify user is deactivated
        $teacher->user->refresh();
        $this->assertFalse($teacher->user->is_active);
        $this->assertFalse($teacher->canBeAssignedToClassroom());

        // Restore the teacher
        $teacher->restore();

        // Verify user is reactivated
        $teacher->user->refresh();
        $this->assertTrue($teacher->user->is_active);
        $this->assertTrue($teacher->canBeAssignedToClassroom());
    }

    /** @test */
    public function comprehensive_status_sync_detects_issues()
    {
        // Create a teacher
        $teacher = Teacher::factory()->create([
            'school_id' => $this->school->id
        ]);

        // Manually desync the user status
        $teacher->user->update(['is_active' => false]);

        // Run comprehensive sync
        $result = $teacher->performComprehensiveStatusSync();

        // Should detect and fix the issue
        $this->assertTrue($result['success']);
        $this->assertContains('User active status synchronized', $result['actions_taken']);

        // Verify status is now synced
        $teacher->user->refresh();
        $this->assertTrue($teacher->user->is_active);
    }

    /** @test */
    public function referential_integrity_validation_works()
    {
        // Create a teacher
        $teacher = Teacher::factory()->create([
            'school_id' => $this->school->id
        ]);

        // Test with valid relationships
        $issues = $teacher->validateComprehensiveReferentialIntegrity();
        $this->assertEmpty($issues);

        // Break the user relationship
        $originalUserId = $teacher->user_id;
        $teacher->update(['user_id' => 99999]); // Non-existent user

        $issues = $teacher->validateComprehensiveReferentialIntegrity();
        $this->assertNotEmpty($issues);
        $this->assertStringContains('Invalid user_id', implode(', ', $issues));

        // Fix the relationship
        $teacher->update(['user_id' => $originalUserId]);
        $issues = $teacher->validateComprehensiveReferentialIntegrity();
        $this->assertEmpty($issues);
    }

    /** @test */
    public function inactive_teacher_assignment_prevention_works()
    {
        // Create a teacher and classroom/subject
        $teacher = Teacher::factory()->create(['school_id' => $this->school->id]);
        $classroom = Classroom::factory()->create(['school_id' => $this->school->id]);
        $subject = Subject::factory()->create(['school_id' => $this->school->id]);

        // Should be able to create assignment when teacher is active
        $assignment = ClassroomSubjectTeacher::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'classes_per_week' => 5
        ]);

        $this->assertNotNull($assignment);

        // Soft delete the teacher
        $teacher->delete();

        // Should not be able to create new assignment with inactive teacher
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot assign teacher to classroom');

        ClassroomSubjectTeacher::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'classes_per_week' => 3
        ]);
    }

    /** @test */
    public function historical_data_preservation_works()
    {
        // Create teacher with assignments
        $teacher = Teacher::factory()->create(['school_id' => $this->school->id]);
        $classroom = Classroom::factory()->create(['school_id' => $this->school->id]);
        $subject = Subject::factory()->create(['school_id' => $this->school->id]);

        $assignment = ClassroomSubjectTeacher::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'classes_per_week' => 5
        ]);

        // Soft delete the assignment
        $assignment->delete();

        // Verify historical data is preserved
        $this->assertTrue($teacher->preserveHistoricalData());
        
        // Should still be able to access historical assignments
        $historicalAssignments = $teacher->getAllAssignments();
        $this->assertCount(1, $historicalAssignments);
        
        // Verify assignment maintains historical integrity
        $this->assertTrue($assignment->maintainsHistoricalIntegrity());
    }

    /** @test */
    public function bulk_status_synchronization_works()
    {
        // Create multiple teachers
        $teachers = Teacher::factory()->count(3)->create([
            'school_id' => $this->school->id
        ]);

        // Manually desync some user statuses
        $teachers[0]->user->update(['is_active' => false]);
        $teachers[1]->user->update(['is_active' => false]);

        // Run bulk sync
        $results = Teacher::performBulkStatusSync($this->school->id);

        // Verify results
        $this->assertEquals(3, $results['total_processed']);
        $this->assertEquals(3, $results['successful_syncs']);
        $this->assertEquals(0, $results['failed_syncs']);

        // Verify all users are now active
        foreach ($teachers as $teacher) {
            $teacher->user->refresh();
            $this->assertTrue($teacher->user->is_active);
        }
    }

    /** @test */
    public function school_integrity_check_works()
    {
        // Create teachers and assignments
        $teacher = Teacher::factory()->create(['school_id' => $this->school->id]);
        $classroom = Classroom::factory()->create(['school_id' => $this->school->id]);
        $subject = Subject::factory()->create(['school_id' => $this->school->id]);

        ClassroomSubjectTeacher::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'classes_per_week' => 5
        ]);

        // Run integrity check
        $results = $this->teacherImportService->performSchoolIntegrityCheck($this->school->id);

        // Verify results structure
        $this->assertArrayHasKey('teachers_checked', $results);
        $this->assertArrayHasKey('assignments_checked', $results);
        $this->assertArrayHasKey('integrity_issues', $results);
        $this->assertArrayHasKey('recommendations', $results);

        // Should have checked our data
        $this->assertEquals(1, $results['teachers_checked']);
        $this->assertEquals(1, $results['assignments_checked']);
        
        // Should have no issues with valid data
        $this->assertEmpty($results['integrity_issues']);
        $this->assertEmpty($results['status_sync_issues']);
        $this->assertEmpty($results['historical_data_issues']);
    }

    /** @test */
    public function teacher_assignment_capability_validation_works()
    {
        // Create active and inactive teachers
        $activeTeacher = Teacher::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Active Teacher'
        ]);
        
        $inactiveTeacher = Teacher::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Inactive Teacher'
        ]);
        $inactiveTeacher->delete(); // Soft delete to make inactive

        // Test import data with both teachers
        $importData = [
            ['Teacher Name' => 'Active Teacher'],
            ['Teacher Name' => 'Inactive Teacher'],
            ['Teacher Name' => 'New Teacher'] // This one doesn't exist yet
        ];

        $results = $this->teacherImportService->validateTeacherAssignmentCapability(
            $importData, 
            $this->school->id
        );

        // Should detect the inactive teacher
        $this->assertFalse($results['valid']);
        $this->assertCount(1, $results['unassignable_teachers']);
        $this->assertEquals('Inactive Teacher', $results['unassignable_teachers'][0]['name']);
        $this->assertStringContains('inactive', $results['unassignable_teachers'][0]['reason']);
    }
}