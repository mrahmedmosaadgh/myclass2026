<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\School;
use App\Models\Classroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function measureExecutionTime(callable $callback)
    {
        $start = microtime(true);
        $callback();
        return microtime(true) - $start;
    }

    private function countQueries(callable $callback)
    {
        $queryCount = 0;
        DB::listen(function($query) use (&$queryCount) {
            $queryCount++;
        });

        $callback();
        return $queryCount;
    }

    public function test_auth_data_performance()
    {
        // Create test data
        $school = School::factory()->create();
        $classroom = Classroom::factory()->create(['school_id' => $school->id]);
        
        // Test teacher performance
        $teacher = Teacher::factory()->create([
            'school_id' => $school->id,
        ]);
        $teacherUser = User::factory()->create([
            'role' => 'teacher'
        ]);
        $teacher->user()->associate($teacherUser)->save();
        
        $teacherQueryCount = $this->countQueries(function() use ($teacherUser) {
            $this->actingAs($teacherUser)->get('/api/user');
        });
        
        $teacherResponseTime = $this->measureExecutionTime(function() use ($teacherUser) {
            $this->actingAs($teacherUser)->get('/api/user');
        });

        // Test student performance
        $student = Student::factory()->create([
            'school_id' => $school->id,
            'classroom_id' => $classroom->id
        ]);
        $studentUser = User::factory()->create([
            'role' => 'student'
        ]);
        $student->user()->associate($studentUser)->save();
        
        $studentQueryCount = $this->countQueries(function() use ($studentUser) {
            $this->actingAs($studentUser)->get('/api/user');
        });
        
        $studentResponseTime = $this->measureExecutionTime(function() use ($studentUser) {
            $this->actingAs($studentUser)->get('/api/user');
        });

        // Assertions
        $this->assertLessThan(5, $teacherQueryCount, 'Teacher auth data requires too many queries');
        $this->assertLessThan(5, $studentQueryCount, 'Student auth data requires too many queries');
        $this->assertLessThan(0.5, $teacherResponseTime, 'Teacher auth data response too slow');
        $this->assertLessThan(0.5, $studentResponseTime, 'Student auth data response too slow');
    }

    public function test_cache_performance()
    {
        $school = School::factory()->create();
        $user = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'school_id' => $school->id
        ]);

        // First request - no cache
        $uncachedQueryCount = $this->countQueries(function() use ($user) {
            $this->actingAs($user)->get('/api/user');
        });

        // Second request - should use cache
        $cachedQueryCount = $this->countQueries(function() use ($user) {
            $this->actingAs($user)->get('/api/user');
        });

        $this->assertGreaterThan(
            $cachedQueryCount, 
            $uncachedQueryCount, 
            'Caching is not reducing query count'
        );
    }

    public function test_n_plus_one_prevention()
    {
        $school = School::factory()->create();
        $classrooms = Classroom::factory(10)->create(['school_id' => $school->id]);
        $user = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'school_id' => $school->id
        ]);

        // Attach classrooms to teacher
        $teacher->classrooms()->attach($classrooms->pluck('id'));

        $queryCount = $this->countQueries(function() use ($user) {
            $this->actingAs($user)->get('/api/user');
        });

        // Should not increase with number of classrooms due to eager loading
        $this->assertLessThan(5, $queryCount, 'Possible N+1 query problem detected');
    }
}