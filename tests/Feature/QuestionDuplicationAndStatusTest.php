<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionDuplicationAndStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected QuestionType $questionType;
    protected Grade $grade;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user
        $this->user = User::factory()->create();
        
        // Create test data
        $this->questionType = QuestionType::create([
            'slug' => 'multiple_choice',
            'name' => 'Multiple Choice',
            'has_options' => true,
            'supports_hints' => true,
            'supports_explanation' => true,
        ]);
        
        $this->grade = Grade::create(['name' => 'Grade 10']);
        $this->subject = Subject::create(['name' => 'Mathematics']);
    }

    /** @test */
    public function it_can_duplicate_a_question()
    {
        // Create a question with options
        $question = Question::create([
            'question_type_id' => $this->questionType->id,
            'question_text' => 'What is 2+2?',
            'grade_id' => $this->grade->id,
            'subject_id' => $this->subject->id,
            'difficulty_level' => 2,
            'bloom_level' => 3,
            'status' => 'active',
            'author_id' => $this->user->id,
            'hints' => ['Hint 1', 'Hint 2'],
            'explanation' => ['text' => 'Explanation', 'revealed_after_attempt' => 1],
        ]);

        QuestionOption::create([
            'question_id' => $question->id,
            'option_key' => 'A',
            'option_text' => '3',
            'is_correct' => false,
            'order_index' => 0,
        ]);

        QuestionOption::create([
            'question_id' => $question->id,
            'option_key' => 'B',
            'option_text' => '4',
            'is_correct' => true,
            'order_index' => 1,
        ]);

        // Duplicate the question
        $response = $this->actingAs($this->user)
            ->postJson("/api/questions/{$question->id}/duplicate");

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Question duplicated successfully',
        ]);

        // Assert new question was created
        $this->assertDatabaseCount('questions', 2);
        
        $newQuestion = Question::where('id', '!=', $question->id)->first();
        
        // Assert question text has "(Copy)" appended
        $this->assertEquals('What is 2+2? (Copy)', $newQuestion->question_text);
        
        // Assert status is set to draft
        $this->assertEquals('draft', $newQuestion->status);
        
        // Assert other properties are copied
        $this->assertEquals($question->question_type_id, $newQuestion->question_type_id);
        $this->assertEquals($question->grade_id, $newQuestion->grade_id);
        $this->assertEquals($question->subject_id, $newQuestion->subject_id);
        $this->assertEquals($question->difficulty_level, $newQuestion->difficulty_level);
        $this->assertEquals($question->bloom_level, $newQuestion->bloom_level);
        $this->assertEquals($question->hints, $newQuestion->hints);
        $this->assertEquals($question->explanation, $newQuestion->explanation);
        
        // Assert usage count is reset
        $this->assertEquals(0, $newQuestion->usage_count);
        $this->assertNull($newQuestion->avg_success_rate);
        $this->assertNull($newQuestion->discrimination_index);
        
        // Assert options are duplicated
        $this->assertDatabaseCount('question_options', 4); // 2 original + 2 duplicated
        $this->assertEquals(2, $newQuestion->options()->count());
        
        // Assert option properties are copied
        $originalOptions = $question->options()->orderBy('order_index')->get();
        $newOptions = $newQuestion->options()->orderBy('order_index')->get();
        
        $this->assertEquals($originalOptions[0]->option_key, $newOptions[0]->option_key);
        $this->assertEquals($originalOptions[0]->option_text, $newOptions[0]->option_text);
        $this->assertEquals($originalOptions[0]->is_correct, $newOptions[0]->is_correct);
        
        $this->assertEquals($originalOptions[1]->option_key, $newOptions[1]->option_key);
        $this->assertEquals($originalOptions[1]->option_text, $newOptions[1]->option_text);
        $this->assertEquals($originalOptions[1]->is_correct, $newOptions[1]->is_correct);
    }

    /** @test */
    public function it_returns_404_when_duplicating_non_existent_question()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/questions/999/duplicate');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => 'Question not found',
            ],
        ]);
    }

    /** @test */
    public function it_can_update_question_status()
    {
        $question = Question::create([
            'question_type_id' => $this->questionType->id,
            'question_text' => 'What is 2+2?',
            'grade_id' => $this->grade->id,
            'subject_id' => $this->subject->id,
            'status' => 'draft',
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/questions/{$question->id}/status", [
                'status' => 'active',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Question status updated successfully',
        ]);

        // Assert status was updated
        $question->refresh();
        $this->assertEquals('active', $question->status);
    }

    /** @test */
    public function it_can_change_status_to_archived()
    {
        $question = Question::create([
            'question_type_id' => $this->questionType->id,
            'question_text' => 'What is 2+2?',
            'grade_id' => $this->grade->id,
            'subject_id' => $this->subject->id,
            'status' => 'active',
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/questions/{$question->id}/status", [
                'status' => 'archived',
            ]);

        $response->assertStatus(200);
        
        $question->refresh();
        $this->assertEquals('archived', $question->status);
    }

    /** @test */
    public function it_validates_status_values()
    {
        $question = Question::create([
            'question_type_id' => $this->questionType->id,
            'question_text' => 'What is 2+2?',
            'grade_id' => $this->grade->id,
            'subject_id' => $this->subject->id,
            'status' => 'draft',
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/questions/{$question->id}/status", [
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_requires_status_field()
    {
        $question = Question::create([
            'question_type_id' => $this->questionType->id,
            'question_text' => 'What is 2+2?',
            'grade_id' => $this->grade->id,
            'subject_id' => $this->subject->id,
            'status' => 'draft',
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/questions/{$question->id}/status", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_returns_404_when_updating_status_of_non_existent_question()
    {
        $response = $this->actingAs($this->user)
            ->patchJson('/api/questions/999/status', [
                'status' => 'active',
            ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => 'Question not found',
            ],
        ]);
    }
}
