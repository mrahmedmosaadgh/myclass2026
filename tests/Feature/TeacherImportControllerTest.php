<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherImportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role
        \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        
        // Create a test school
        $this->school = School::factory()->create([
            'name' => 'Test School'
        ]);
        
        // Create a test user with admin role
        $this->user = User::factory()->create([
            'role' => 'admin',
            'school_id' => $this->school->id
        ]);
        
        // Assign the admin role using Spatie
        $this->user->assignRole('admin');
    }

    public function test_index_returns_teacher_import_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('teachers.import'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('my_class/admin/TeacherImport', false) // Disable component existence check
        );
    }

    public function test_get_schools_returns_accessible_schools()
    {
        $response = $this->actingAs($this->user)
            ->get(route('teachers.import.schools'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'schools' => [
                [
                    'id' => $this->school->id,
                    'name' => 'Test School'
                ]
            ]
        ]);
    }

    public function test_get_active_academic_year_returns_error_when_no_active_year()
    {
        $response = $this->actingAs($this->user)
            ->get(route('teachers.import.academic-year', $this->school->id));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'No active academic year found for this school. Please create an active academic year first.',
            'requires_academic_year' => true
        ]);
    }

    public function test_get_active_academic_year_returns_active_year_when_exists()
    {
        // Create an active academic year
        $academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'active' => true
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('teachers.import.academic-year', $this->school->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'academic_year' => [
                'id' => $academicYear->id,
                'name' => '2024-2025',
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
                'active' => true
            ]
        ]);
    }

    public function test_validate_import_requires_authentication()
    {
        $response = $this->post(route('teachers.import.validate'), []);

        $response->assertRedirect(route('login'));
    }

    public function test_validate_import_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('teachers.import.validate'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['school_id', 'academic_year_id', 'data']);
    }

    public function test_validate_import_validates_academic_year_belongs_to_school()
    {
        // Create another school and academic year
        $otherSchool = School::factory()->create();
        $otherAcademicYear = AcademicYear::factory()->create([
            'school_id' => $otherSchool->id,
            'active' => true
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('teachers.import.validate'), [
                'school_id' => $this->school->id,
                'academic_year_id' => $otherAcademicYear->id,
                'data' => [
                    [
                        'classroom' => 'Grade 1A',
                        'subject' => 'Math',
                        'teacher_name' => 'John Doe',
                        'periods_per_week' => 5
                    ]
                ]
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid or inactive academic year for the selected school'
        ]);
    }

    public function test_validate_import_passes_with_valid_data()
    {
        $academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'active' => true
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('teachers.import.validate'), [
                'school_id' => $this->school->id,
                'academic_year_id' => $academicYear->id,
                'data' => [
                    [
                        'classroom' => 'Grade 1A',
                        'subject' => 'Math',
                        'teacher_name' => 'John Doe',
                        'periods_per_week' => 5,
                        'teacher_email' => 'john@example.com',
                        'phone' => '1234567890',
                        'national_id' => 'ID123456',
                        'gender' => 'Male',
                        'date_of_birth' => '1990-01-01'
                    ]
                ]
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Data validation passed',
            'rows_count' => 1
        ]);
    }
}