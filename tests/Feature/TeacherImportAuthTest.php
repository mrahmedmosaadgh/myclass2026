<?php

namespace Tests\Feature;

use Tests\TestCase;

class TeacherImportAuthTest extends TestCase
{
    public function test_teacher_import_routes_require_authentication()
    {
        // Test that unauthenticated users are redirected to login
        $response = $this->get(route('teachers.import'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('teachers.import.schools'));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('teachers.import.validate'), []);
        $response->assertRedirect(route('login'));

        $response = $this->post(route('teachers.import.process'), []);
        $response->assertRedirect(route('login'));
    }

    public function test_academic_year_route_requires_authentication()
    {
        $response = $this->get(route('teachers.import.academic-year', 1));
        $response->assertRedirect(route('login'));
    }
}