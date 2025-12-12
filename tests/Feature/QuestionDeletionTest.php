<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QuestionDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected QuestionType $questionType;
    protected Grade $grade;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create question type
        $this->questionType = QuestionType::create([
            'slug' => 'multiple_choice',
            'name' => 'Multiple Choice',
            'has_options' => true,
            'supports_hints' => true,
            'supports_explanation' => true,
        ]);

        // Create grade and subject
        $this->grade = Grade::create(['name' => 'Grade 5']);
        $this->subject = Subject::create(['name' => 'Mathematics']);
    }

    /** @test */
    public function it_can_delete_a_question_without_options()
    {
        // Create a question without options
        $question = Question::create([
            'question_type_id' => $this->questionType->id,
            'question_text' => 'What is 2+2?',
            'grade_id' => $this->grade->id,
            'subject_id' => $this->subject->id,
            'author_id' => $this->user->id,
            'status' => 'draft',
        ]);

        // Delete the question
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/questions/{$question->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Question deleted successfully',
            ]);

        // Verify question is soft deleted
        $this->assertSoftDeleted('questions', ['id' => $question->id]);
    }

    /** @test */
    public function it_cascades_delete_to_question_options()
    {
        // Create a question with options
        $question = Question::create([
            'question_type_id' => $this->questionType->id,
            'question_text' => 'What is the capital of France?',
            'grade_id' => $this->grade->id,
            'subject_id' => $this->subject->id,
            'author_id' => $this->user->id,
            'status' => 'draft',
        ]);

        // Create options
        $option1 = QuestionOption::create([
            'question_id' => $question->id,
            'option_key' => 'A',
            'option_text' => 'Paris',
            'is_correct' => true,
            'order_index' => 0,
        ]);

        $option2 = QuestionOption::create([
            'question_id' => $question->id,
            'option_key' => 'B',
            'option_text' => 'London',
            'is_correct' => false,
            'order_index' => 1,
        ]);

        // Delete the question
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/questions/{$question->id}");

        $response->assertStatus(200);

        // Verify question is soft deleted
        $this->assertSoftDeleted('questions', ['id' => $question->id]);

        // Verify options are hard deleted (cascade)
        $this->assertDatabaseMissing('question_options', ['id' => $option1->id]);
        $this->assertDatabaseMissing('question_options', ['id' => $option2->id]);
    }

    /** @test */
    public function it_prevents_deletion_if_question_has_quiz_attempts()
    {
        // Create a question
        $question = Question::create([
            'question_type_id' => $this->questionType->id,
            'question_text' => 'What is 2+2?',
            'grade_id' => $this->grade->id,
            'subject_id' => $this->subject->id,
            'author_id' => $this->user->id,
            'status' => 'active',
        ]);

        // Simulate a quiz attempt answer (insert directly to avoid creating full quiz structure)
        DB::table('quiz_attempt_answers')->insert([
            'quiz_attempt_id' => 1, // Dummy ID
            'question_id' => $question->id,
            'selected_option_id' => null,
            'is_correct' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Attempt to delete the question
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/questions/{$question->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'QUESTION_IN_USE',
                    'message' => 'Cannot delete question that has been used in quiz attempts. Consider archiving instead.',
                ],
            ]);

        // Verify question still exists
        $this->assertDatabaseHas('questions', ['id' => $question->id]);
    }

    /** @test */
    public function it_requires_authorization_to_delete_question()
    {
        // Create a question owned by another user
        $otherUser = User::factory()->create();
        $question = Question::create([
            'question_type_id' => $this->questionType->id,
            'question_text' => 'What is 2+2?',
            'grade_id' => $this->grade->id,
            'subject_id' => $this->subject->id,
            'author_id' => $otherUser->id,
            'status' => 'draft',
        ]);

        // Attempt to delete as different user
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/questions/{$question->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'You do not have permission to delete this question',
                ],
            ]);

        // Verify question still exists
        $this->assertDatabaseHas('questions', ['id' => $question->id]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_question()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/questions/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Question not found',
                ],
            ]);
    }

    /** @test */
    public function author_can_delete_their_own_question()
    {
        // Create a question
        $question = Question::create([
            'question_type_id' => $this->questionType->id,
            'question_text' => 'What is 2+2?',
            'grade_id' => $this->grade->id,
            'subject_id' => $this->subject->id,
            'author_id' => $this->user->id,
            'status' => 'draft',
        ]);

        // Delete as author
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/questions/{$question->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('questions', ['id' => $question->id]);
    }
}
