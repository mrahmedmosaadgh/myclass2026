<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\User;
use App\Models\School;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $school;
    protected $grade;
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->school = School::factory()->create();
        $this->grade = Grade::factory()->create(['school_id' => $this->school->id]);
        $this->subject = Subject::factory()->create();
        $this->user = User::factory()->create(['school_id' => $this->school->id]);
    }

    public function test_can_list_quizzes()
    {
        // Create some quizzes
        Quiz::factory()->count(3)->create([
            'school_id' => $this->school->id,
            'created_by_id' => $this->user->id,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/quizzes?school_id=' . $this->school->id);

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_quiz()
    {
        $quizData = [
            'name' => 'Test Quiz',
            'description' => 'A test quiz',
            'school_id' => $this->school->id,
            'grade_id' => $this->grade->id,
            'subject_id' => $this->subject->id,
            'status' => 'active',
            'time_limit_minutes' => 30,
            'shuffle_questions' => true,
            'shuffle_options' => false,
            'allow_review' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/quizzes', $quizData);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Quiz']);

        $this->assertDatabaseHas('quizzes', [
            'name' => 'Test Quiz',
            'school_id' => $this->school->id,
        ]);
    }

    public function test_can_show_quiz()
    {
        $quiz = Quiz::factory()->create([
            'school_id' => $this->school->id,
            'created_by_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/quizzes/' . $quiz->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => $quiz->name]);
    }

    public function test_can_update_quiz()
    {
        $quiz = Quiz::factory()->create([
            'school_id' => $this->school->id,
            'created_by_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson('/api/quizzes/' . $quiz->id, [
                'name' => 'Updated Quiz Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Quiz Name']);

        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'name' => 'Updated Quiz Name',
        ]);
    }

    public function test_can_delete_quiz()
    {
        $quiz = Quiz::factory()->create([
            'school_id' => $this->school->id,
            'created_by_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/quizzes/' . $quiz->id);

        $response->assertStatus(200);

        $this->assertSoftDeleted('quizzes', [
            'id' => $quiz->id,
        ]);
    }
}
