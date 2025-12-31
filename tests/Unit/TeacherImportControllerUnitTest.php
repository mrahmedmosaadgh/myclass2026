<?php

namespace Tests\Unit;

use App\Http\Controllers\TeacherImportController;
use App\Models\User;
use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Mockery;

class TeacherImportControllerUnitTest extends TestCase
{
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new TeacherImportController();
    }

    public function test_controller_has_required_methods()
    {
        $this->assertTrue(method_exists($this->controller, 'index'));
        $this->assertTrue(method_exists($this->controller, 'getSchools'));
        $this->assertTrue(method_exists($this->controller, 'getActiveAcademicYear'));
        $this->assertTrue(method_exists($this->controller, 'validateImport'));
        $this->assertTrue(method_exists($this->controller, 'processImport'));
    }

    public function test_get_schools_method_exists_and_returns_json_response()
    {
        // Mock authentication
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('role')->andReturn('hr_admin');
        
        $this->actingAs($user);
        
        // Test that the method exists and can be called
        $this->assertTrue(method_exists($this->controller, 'getSchools'));
        
        // The method should return a JsonResponse
        $response = $this->controller->getSchools();
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
    }

    public function test_get_active_academic_year_method_exists()
    {
        $this->assertTrue(method_exists($this->controller, 'getActiveAcademicYear'));
        
        // Test that it accepts an integer parameter
        $reflection = new \ReflectionMethod($this->controller, 'getActiveAcademicYear');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(1, $parameters);
        $this->assertEquals('schoolId', $parameters[0]->getName());
        $this->assertEquals('int', $parameters[0]->getType()->getName());
    }

    public function test_validate_import_method_signature()
    {
        $this->assertTrue(method_exists($this->controller, 'validateImport'));
        
        $reflection = new \ReflectionMethod($this->controller, 'validateImport');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('Illuminate\Http\Request', $parameters[0]->getType()->getName());
    }

    public function test_process_import_validates_required_fields()
    {
        $request = new Request();
        
        try {
            $response = $this->controller->processImport($request);
            $this->fail('Expected ValidationException was not thrown');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // This is expected - the method should validate required fields
            $this->assertArrayHasKey('school_id', $e->errors());
            $this->assertArrayHasKey('academic_year_id', $e->errors());
            $this->assertArrayHasKey('sync_mode', $e->errors());
            $this->assertArrayHasKey('data', $e->errors());
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}