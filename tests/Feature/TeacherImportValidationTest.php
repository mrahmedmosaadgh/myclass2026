<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TeacherImportValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role
        \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        
        // Create a test user for HR first
        $hrUser = User::factory()->create([
            'role' => 'hr_admin'
        ]);
        
        // Create an HR record
        $this->hr = \App\Models\HR::create([
            'name' => 'Test HR',
            'user_id' => $hrUser->id,
            'data' => [],
            'active' => true
        ]);
        
        // Create a test school
        $this->school = School::factory()->create([
            'name' => 'Test School',
            'h_r_id' => $this->hr->id
        ]);
        
        // Create a test user with admin role
        $this->user = User::factory()->create([
            'role' => 'admin',
            'school_id' => $this->school->id
        ]);
        
        // Assign the admin role using Spatie
        $this->user->assignRole('admin');

        // Create an active academic year
        $this->academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'active' => true
        ]);
    }

    public function test_validate_file_rejects_oversized_files()
    {
        // Create a fake file larger than 10MB
        $file = UploadedFile::fake()->create('large_file.xlsx', 11 * 1024); // 11MB

        $response = $this->actingAs($this->user)
            ->post(route('teachers.import.validate_file'), [
                'file' => $file
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'File size exceeds the maximum limit of 10MB',
            'error_type' => 'file_size'
        ]);
    }

    public function test_validate_file_rejects_invalid_formats()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->user)
            ->post(route('teachers.import.validate_file'), [
                'file' => $file
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid file format. Only .xlsx and .xls files are allowed',
            'error_type' => 'file_format'
        ]);
    }

    public function test_validate_file_accepts_valid_excel_files()
    {
        $file = UploadedFile::fake()->create('teachers.xlsx', 100);

        $response = $this->actingAs($this->user)
            ->post(route('teachers.import.validate_file'), [
                'file' => $file
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'File validation passed'
        ]);
    }

    public function test_validate_import_rejects_missing_required_columns()
    {
        $response = $this->actingAs($this->user)
            ->post(route('teachers.import.validate'), [
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'data' => [
                    [
                        'classroom' => 'Grade 1A',
                        'subject' => 'Math',
                        // Missing teacher_name and periods_per_week
                    ]
                ]
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'summary' => [
                'total_rows',
                'valid_rows',
                'invalid_rows',
                'errors'
            ]
        ]);
    }

    public function test_validate_import_validates_periods_per_week()
    {
        $response = $this->actingAs($this->user)
            ->post(route('teachers.import.validate'), [
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'data' => [
                    [
                        'classroom' => 'Grade 1A',
                        'subject' => 'Math',
                        'teacher_name' => 'John Doe',
                        'periods_per_week' => -1 // Invalid negative value
                    ]
                ]
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('summary.invalid_rows', 1);
    }

    public function test_validate_import_validates_email_format()
    {
        $response = $this->actingAs($this->user)
            ->post(route('teachers.import.validate'), [
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'data' => [
                    [
                        'classroom' => 'Grade 1A',
                        'subject' => 'Math',
                        'teacher_name' => 'John Doe',
                        'periods_per_week' => 5,
                        'teacher_email' => 'invalid-email' // Invalid email format
                    ]
                ]
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('summary.invalid_rows', 1);
    }

    public function test_validate_import_validates_gender_values()
    {
        $response = $this->actingAs($this->user)
            ->post(route('teachers.import.validate'), [
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'data' => [
                    [
                        'classroom' => 'Grade 1A',
                        'subject' => 'Math',
                        'teacher_name' => 'John Doe',
                        'periods_per_week' => 5,
                        'gender' => 'Other' // Invalid gender value
                    ]
                ]
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('summary.invalid_rows', 1);
    }

    public function test_validate_import_validates_date_of_birth_format()
    {
        $response = $this->actingAs($this->user)
            ->post(route('teachers.import.validate'), [
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'data' => [
                    [
                        'classroom' => 'Grade 1A',
                        'subject' => 'Math',
                        'teacher_name' => 'John Doe',
                        'periods_per_week' => 5,
                        'date_of_birth' => '2030-01-01' // Future date
                    ]
                ]
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('summary.invalid_rows', 1);
    }

    public function test_validate_import_passes_with_valid_data()
    {
        $response = $this->actingAs($this->user)
            ->post(route('teachers.import.validate'), [
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
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

    public function test_validate_import_handles_mixed_valid_invalid_rows()
    {
        $response = $this->actingAs($this->user)
            ->post(route('teachers.import.validate'), [
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'data' => [
                    [
                        'classroom' => 'Grade 1A',
                        'subject' => 'Math',
                        'teacher_name' => 'John Doe',
                        'periods_per_week' => 5
                    ],
                    [
                        'classroom' => 'Grade 1B',
                        'subject' => 'Science',
                        'teacher_name' => '', // Invalid empty name
                        'periods_per_week' => -1 // Invalid negative periods
                    ]
                ]
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('summary.total_rows', 2);
        $response->assertJsonPath('summary.valid_rows', 1);
        $response->assertJsonPath('summary.invalid_rows', 1);
    }
}