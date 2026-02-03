<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\SkillQuestion;
use App\Models\QuQuestion;
use App\Models\UserSkillProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PracticeSessionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $skill;
    protected $question;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user
        $this->user = User::factory()->create();
        
        // Create a skill category
        $category = SkillCategory::create([
            'name' => 'Mathematics',
            'grade_id' => 1,
            'subject_id' => 1,
        ]);
        
        // Create a skill
        $this->skill = Skill::create([
            'name' => 'Basic Algebra',
            'category_id' => $category->id,
            'description' => 'Learn basic algebra concepts',
        ]);
        
        // Create a question
        $this->question = QuQuestion::create([
            'question_text' => 'What is 2+2?',
            'question_type' => 'multiple_choice',
            'options' => json_encode(['3', '4', '5', '6']),
            'correct_answer' => '4',
            'subject_id' => 1,
            'topic_id' => 1,
        ]);
        
        // Link the question to the skill
        SkillQuestion::create([
            'skill_id' => $this->skill->id,
            'qu_question_id' => $this->question->id,
            'difficulty_level' => 3,
        ]);
    }

    /** @test */
    public function it_can_start_a_practice_session()
    {
        $response = $this->actingAs($this->user)
            ->post("/skill-practice/skills/{$this->skill->id}/start", [
                'skill_id' => $this->skill->id
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'session' => true,
        ]);
    }

    /** @test */
    public function it_can_submit_an_answer_and_update_progress()
    {
        // Start a session first
        $sessionResponse = $this->actingAs($this->user)
            ->post("/skill-practice/skills/{$this->skill->id}/start", [
                'skill_id' => $this->skill->id
            ]);

        $session = $sessionResponse->json('session');
        $firstQuestion = $sessionResponse->json('first_question');

        // Submit an answer
        $response = $this->actingAs($this->user)
            ->post('/skill-practice/submit-answer', [
                'session_id' => $session['id'],
                'skill_question_id' => $firstQuestion['skill_question_id'],
                'user_answer' => '4', // Correct answer
                'time_taken_ms' => 15000,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_correct' => true,
        ]);
        
        // Check that progress was updated
        $this->assertDatabaseHas('user_skill_progress', [
            'user_id' => $this->user->id,
            'skill_id' => $this->skill->id,
            'correct_answers' => 1,
        ]);
    }

    /** @test */
    public function it_updates_smart_score_correctly()
    {
        // Start a session
        $sessionResponse = $this->actingAs($this->user)
            ->post("/skill-practice/skills/{$this->skill->id}/start", [
                'skill_id' => $this->skill->id
            ]);

        $session = $sessionResponse->json('session');
        $firstQuestion = $sessionResponse->json('first_question');

        // Get initial progress
        $initialProgress = UserSkillProgress::where('user_id', $this->user->id)
            ->where('skill_id', $this->skill->id)
            ->first();

        $initialScore = $initialProgress ? $initialProgress->smart_score : 0;

        // Submit a correct answer
        $response = $this->actingAs($this->user)
            ->post('/skill-practice/submit-answer', [
                'session_id' => $session['id'],
                'skill_question_id' => $firstQuestion['skill_question_id'],
                'user_answer' => '4', // Correct answer
                'time_taken_ms' => 15000,
            ]);

        $response->assertStatus(200);
        
        // Refresh the progress from DB
        $updatedProgress = UserSkillProgress::where('user_id', $this->user->id)
            ->where('skill_id', $this->skill->id)
            ->first();

        $this->assertNotEquals($initialScore, $updatedProgress->smart_score);
        $this->assertGreaterThanOrEqual($initialScore, $updatedProgress->smart_score);
    }
}