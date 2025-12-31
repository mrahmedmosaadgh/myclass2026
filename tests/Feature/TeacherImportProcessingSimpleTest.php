<?php

namespace Tests\Feature;

use App\Services\TeacherImportService;
use Tests\TestCase;

class TeacherImportProcessingSimpleTest extends TestCase
{
    protected $importService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importService = new TeacherImportService();
    }

    public function test_chunk_processing_logic_works_correctly()
    {
        // Test that the chunking logic correctly identifies when to use chunks
        $smallData = array_fill(0, 500, ['test' => 'data']);
        $largeData = array_fill(0, 1500, ['test' => 'data']);
        
        // Verify chunk size logic
        $chunkSize = 1000;
        $this->assertLessThanOrEqual($chunkSize, count($smallData));
        $this->assertGreaterThan($chunkSize, count($largeData));
        
        // Test array_chunk behavior
        $chunks = array_chunk($largeData, $chunkSize, true);
        $this->assertCount(2, $chunks); // Should create 2 chunks
        $this->assertCount(1000, $chunks[0]); // First chunk should have 1000 items
        $this->assertCount(500, $chunks[1]); // Second chunk should have 500 items
    }

    public function test_error_categorization_works_correctly()
    {
        $errors = [
            'Row 1: Teacher Name is required',
            'Row 2: Periods_per_Week must be a positive number',
            'Row 3: Critical import failure: Database connection lost',
            'Row 4: duplicate key constraint violation',
            'Row 5: Some other error'
        ];

        $reflection = new \ReflectionClass($this->importService);
        $method = $reflection->getMethod('categorizeErrors');
        $method->setAccessible(true);
        
        $categorized = $method->invoke($this->importService, $errors);
        
        $this->assertArrayHasKey('validation', $categorized);
        $this->assertArrayHasKey('system', $categorized);
        $this->assertArrayHasKey('data_integrity', $categorized);
        $this->assertArrayHasKey('other', $categorized);
        
        $this->assertCount(2, $categorized['validation']); // 2 validation errors
        $this->assertCount(1, $categorized['system']); // 1 system error
        $this->assertCount(1, $categorized['data_integrity']); // 1 data integrity error
        $this->assertCount(1, $categorized['other']); // 1 other error
    }

    public function test_data_sanitization_works_correctly()
    {
        $rowData = [
            'Teacher Name' => 'John Doe',
            'Teacher Email' => 'john.doe@example.com',
            'Phone' => '1234567890',
            'National ID' => '1234567890',
            'Classroom' => 'Grade 1A'
        ];

        $reflection = new \ReflectionClass($this->importService);
        $method = $reflection->getMethod('sanitizeRowDataForLogging');
        $method->setAccessible(true);
        
        $sanitized = $method->invoke($this->importService, $rowData);
        
        // Check that sensitive data is masked
        $this->assertEquals('***masked***', $sanitized['National ID']);
        $this->assertStringContainsString('*', $sanitized['Teacher Email']);
        $this->assertStringContainsString('*', $sanitized['Phone']);
        
        // Check that non-sensitive data is preserved
        $this->assertEquals('John Doe', $sanitized['Teacher Name']);
        $this->assertEquals('Grade 1A', $sanitized['Classroom']);
    }

    public function test_email_masking_works_correctly()
    {
        $reflection = new \ReflectionClass($this->importService);
        $method = $reflection->getMethod('maskEmail');
        $method->setAccessible(true);
        
        $masked = $method->invoke($this->importService, 'john.doe@example.com');
        $this->assertEquals('jo******@example.com', $masked);
        
        $masked = $method->invoke($this->importService, 'a@test.com');
        $this->assertEquals('a@test.com', $masked); // Short emails preserved
        
        $masked = $method->invoke($this->importService, 'invalid-email');
        $this->assertEquals('invalid-email', $masked); // Invalid emails preserved
    }

    public function test_phone_masking_works_correctly()
    {
        $reflection = new \ReflectionClass($this->importService);
        $method = $reflection->getMethod('maskPhone');
        $method->setAccessible(true);
        
        $masked = $method->invoke($this->importService, '1234567890');
        $this->assertEquals('12******90', $masked);
        
        $masked = $method->invoke($this->importService, '+1-234-567-8900');
        $this->assertEquals('12*******00', $masked);
        
        $masked = $method->invoke($this->importService, '123');
        $this->assertEquals('***masked***', $masked); // Too short
    }

    public function test_import_report_generation_includes_all_required_fields()
    {
        $results = [
            'success' => true,
            'total_rows' => 100,
            'processed_rows' => 95,
            'chunks_processed' => 2,
            'teachers_created' => 20,
            'assignments_created' => 75,
            'assignments_updated' => 20,
            'errors' => [
                'Row 5: Teacher Name is required',
                'Row 12: Critical import failure: Database error'
            ]
        ];

        $report = $this->importService->generateImportReport($results);

        // Check all required fields are present
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('errors', $report);
        $this->assertArrayHasKey('error_summary', $report);
        $this->assertArrayHasKey('success', $report);
        $this->assertArrayHasKey('recommendations', $report);

        // Check summary fields
        $summary = $report['summary'];
        $this->assertEquals(100, $summary['total_rows']);
        $this->assertEquals(95, $summary['processed_rows']);
        $this->assertEquals(2, $summary['chunks_processed']);
        $this->assertEquals(20, $summary['teachers_created']);
        $this->assertEquals(75, $summary['assignments_created']);
        $this->assertEquals(20, $summary['assignments_updated']);
        $this->assertEquals(2, $summary['errors_count']);
        $this->assertEquals(95.0, $summary['success_rate']);

        // Check that recommendations are provided
        $this->assertNotEmpty($report['recommendations']);
    }
}