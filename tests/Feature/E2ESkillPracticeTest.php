<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\QuQuestion;
use App\Models\SkillQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2ESkillPracticeTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $skill;
    protected $questions = [];

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
        
        // Create several questions for the skill
        for ($i = 1; $i <= 5; $i++) {
            $question = QuQuestion::create([
                'question_text' => "What is {$i}+{$i}?",
                'question_type' => 'multiple_choice',
                'options' => json_encode([2*$i - 1, 2*$i, 2*$i + 1, 2*$i + 2]),
                'correct_answer' => (string)(2*$i),
                'subject_id' => 1,
                'topic_id' => 1,
            ]);
            
            $this->questions[] = $question;
            
            // Link the question to the skill
            SkillQuestion::create([
                'skill_id' => $this->skill->id,
                'qu_question_id' => $question->id,
                'difficulty_level' => 3,
            ]);
        }
    }

    /** @test */
    public function it_completes_full_practice_session_flow()
    {
        // Step 1: Start a practice session
        $sessionResponse = $this->actingAs($this->user)
            ->post("/skill-practice/skills/{$this->skill->id}/start", [
                'skill_id' => $this->skill->id
            ]);

        $sessionResponse->assertStatus(200);
        $session = $sessionResponse->json('session');
        $firstQuestion = $sessionResponse->json('first_question');

        $this->assertNotNull($session);
        $this->assertNotNull($firstQuestion);

        // Step 2: Submit answers to multiple questions
        $totalQuestions = 5;
        $correctAnswers = 0;
        
        for ($i = 0; $i < $totalQuestions; $i++) {
            // Get next question
            $nextQuestionResponse = $this->actingAs($this->user)
                ->post('/skill-practice/next-question', [
                    'skill_id' => $this->skill->id,
                    'session_id' => $session['id'],
                ]);

            $nextQuestion = $nextQuestionResponse->json('question');
            
            if ($nextQuestion) {
                // Extract the correct answer from the question options
                $questionText = $nextQuestion['question']['question_text'];
                preg_match('/What is (\d+)\+(\d+)\?/', $questionText, $matches);
                
                $answer = '';
                if (isset($matches[1]) && isset($matches[2])) {
                    $answer = (string)((int)$matches[1] + (int)$matches[2]);
                }
                
                // Submit the answer
                $submitResponse = $this->actingAs($this->user)
                    ->post('/skill-practice/submit-answer', [
                        'session_id' => $session['id'],
                        'skill_question_id' => $nextQuestion['skill_question_id'],
                        'user_answer' => $answer,
                        'time_taken_ms' => 15000,
                    ]);

                $submitResponse->assertStatus(200);
                
                if ($submitResponse->json('is_correct')) {
                    $correctAnswers++;
                }
            }
        }

        // Step 3: End the session
        $endResponse = $this->actingAs($this->user)
            ->post("/skill-practice/end-session/{$session['id']}", [
                'session_id' => $session['id']
            ]);

        $endResponse->assertStatus(200);
        $endResponse->assertJson([
            'success' => true,
            'session_ended' => true,
        ]);

        // Step 4: Verify progress has been updated
        $progressResponse = $this->actingAs($this->user)
            ->get("/skill-practice/progress/{$this->skill->id}");

        $progressResponse->assertStatus(200);
        $progressData = $progressResponse->json('data');
        
        $this->assertNotNull($progressData);
        $this->assertGreaterThanOrEqual($correctAnswers, $progressData['correct_answers']);
        $this->assertEquals($totalQuestions, $progressData['questions_answered']);
    }

    /** @test */
    public function it_allows_student_to_view_skill_browser()
    {
        $response = $this->actingAs($this->user)
            ->get('/skill-practice');

        $response->assertStatus(200);
        $response->assertViewIs('skill-practice.browser');
    }
}