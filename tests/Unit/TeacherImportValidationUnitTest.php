<?php

namespace Tests\Unit;

use App\Http\Controllers\TeacherImportController;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use ReflectionClass;

class TeacherImportValidationUnitTest extends TestCase
{
    public function test_validate_excel_file_rejects_oversized_files()
    {
        $controller = new TeacherImportController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('validateExcelFile');
        $method->setAccessible(true);

        // Create a fake file larger than 10MB
        $file = UploadedFile::fake()->create('large_file.xlsx', 11 * 1024); // 11MB

        $result = $method->invoke($controller, $file);

        $this->assertFalse($result['success']);
        $this->assertEquals('File size exceeds the maximum limit of 10MB', $result['message']);
        $this->assertEquals('file_size', $result['error_type']);
    }

    public function test_validate_excel_file_rejects_invalid_formats()
    {
        $controller = new TeacherImportController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('validateExcelFile');
        $method->setAccessible(true);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $result = $method->invoke($controller, $file);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid file format. Only .xlsx and .xls files are allowed', $result['message']);
        $this->assertEquals('file_format', $result['error_type']);
    }

    public function test_validate_excel_file_accepts_valid_files()
    {
        $controller = new TeacherImportController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('validateExcelFile');
        $method->setAccessible(true);

        $file = UploadedFile::fake()->create('teachers.xlsx', 100);

        $result = $method->invoke($controller, $file);

        $this->assertTrue($result['success']);
        $this->assertEquals('File validation passed', $result['message']);
    }

    public function test_validate_required_columns_detects_missing_columns()
    {
        $controller = new TeacherImportController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('validateRequiredColumns');
        $method->setAccessible(true);

        $data = [
            [
                'classroom' => 'Grade 1A',
                'subject' => 'Math',
                // Missing teacher_name and periods_per_week
            ]
        ];

        $result = $method->invoke($controller, $data);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Missing required columns', $result['message']);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('errors', $result['summary']);
        
        // Check that the errors contain the missing column messages
        $errorMessages = $result['summary']['errors'];
        $this->assertContains("Required column 'teacher_name' is missing", $errorMessages);
        $this->assertContains("Required column 'periods_per_week' is missing", $errorMessages);
    }

    public function test_validate_required_columns_passes_with_all_required_columns()
    {
        $controller = new TeacherImportController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('validateRequiredColumns');
        $method->setAccessible(true);

        $data = [
            [
                'classroom' => 'Grade 1A',
                'subject' => 'Math',
                'teacher_name' => 'John Doe',
                'periods_per_week' => 5
            ]
        ];

        $result = $method->invoke($controller, $data);

        $this->assertTrue($result['success']);
        $this->assertEquals('Column validation passed', $result['message']);
    }

    public function test_validate_import_data_validates_required_fields()
    {
        $controller = new TeacherImportController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('validateImportData');
        $method->setAccessible(true);

        $data = [
            [
                'classroom' => '',
                'subject' => 'Math',
                'teacher_name' => 'John Doe',
                'periods_per_week' => 5
            ]
        ];

        $result = $method->invoke($controller, $data);

        $this->assertFalse($result['success']);
        $this->assertEquals(1, $result['summary']['invalid_rows']);
        $this->assertContains('Classroom is required', $result['summary']['errors'][0]['errors']);
    }

    public function test_validate_import_data_validates_periods_per_week()
    {
        $controller = new TeacherImportController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('validateImportData');
        $method->setAccessible(true);

        $data = [
            [
                'classroom' => 'Grade 1A',
                'subject' => 'Math',
                'teacher_name' => 'John Doe',
                'periods_per_week' => -1
            ]
        ];

        $result = $method->invoke($controller, $data);

        $this->assertFalse($result['success']);
        $this->assertEquals(1, $result['summary']['invalid_rows']);
        $this->assertContains('Periods per Week must be a positive number', $result['summary']['errors'][0]['errors']);
    }

    public function test_validate_import_data_validates_email_format()
    {
        $controller = new TeacherImportController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('validateImportData');
        $method->setAccessible(true);

        $data = [
            [
                'classroom' => 'Grade 1A',
                'subject' => 'Math',
                'teacher_name' => 'John Doe',
                'periods_per_week' => 5,
                'teacher_email' => 'invalid-email'
            ]
        ];

        $result = $method->invoke($controller, $data);

        $this->assertFalse($result['success']);
        $this->assertEquals(1, $result['summary']['invalid_rows']);
        $this->assertContains('Invalid email format', $result['summary']['errors'][0]['errors']);
    }

    public function test_validate_import_data_validates_gender_values()
    {
        $controller = new TeacherImportController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('validateImportData');
        $method->setAccessible(true);

        $data = [
            [
                'classroom' => 'Grade 1A',
                'subject' => 'Math',
                'teacher_name' => 'John Doe',
                'periods_per_week' => 5,
                'gender' => 'Other'
            ]
        ];

        $result = $method->invoke($controller, $data);

        $this->assertFalse($result['success']);
        $this->assertEquals(1, $result['summary']['invalid_rows']);
        $this->assertContains('Gender must be either Male or Female', $result['summary']['errors'][0]['errors']);
    }

    public function test_validate_import_data_passes_with_valid_data()
    {
        $controller = new TeacherImportController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('validateImportData');
        $method->setAccessible(true);

        $data = [
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
        ];

        $result = $method->invoke($controller, $data);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['summary']['valid_rows']);
        $this->assertEquals(0, $result['summary']['invalid_rows']);
    }
}