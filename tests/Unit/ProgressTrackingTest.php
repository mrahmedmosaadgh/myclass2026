<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Skill;
use App\Models\UserSkillProgress;
use App\Services\SkillProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $skill;
    protected $progressService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->skill = Skill::create([
            'name' => 'Basic Algebra',
            'category_id' => 1,
            'description' => 'Learn basic algebra concepts',
        ]);
        $this->progressService = new SkillProgressService();
    }

    /** @test */
    public function it_initializes_progress_correctly()
    {
        $progress = $this->progressService->initializeProgress($this->user->id, $this->skill->id);

        $this->assertInstanceOf(UserSkillProgress::class, $progress);
        $this->assertEquals($this->user->id, $progress->user_id);
        $this->assertEquals($this->skill->id, $progress->skill_id);
        $this->assertEquals(0, $progress->smart_score);
        $this->assertEquals(0, $progress->questions_answered);
        $this->assertEquals(0, $progress->current_streak);
    }

    /** @test */
    public function it_updates_progress_correctly_for_correct_answer()
    {
        $progress = $this->progressService->initializeProgress($this->user->id, $this->skill->id);
        
        $this->progressService->updateProgress($this->user->id, $this->skill->id, true, 5, 30000, 5);

        $updatedProgress = UserSkillProgress::where('user_id', $this->user->id)
            ->where('skill_id', $this->skill->id)
            ->first();

        $this->assertEquals(5, $updatedProgress->smart_score);
        $this->assertEquals(1, $updatedProgress->questions_answered);
        $this->assertEquals(1, $updatedProgress->correct_answers);
        $this->assertEquals(1, $updatedProgress->current_streak);
    }

    /** @test */
    public function it_updates_progress_correctly_for_incorrect_answer()
    {
        $progress = $this->progressService->initializeProgress($this->user->id, $this->skill->id);
        
        $this->progressService->updateProgress($this->user->id, $this->skill->id, false, -3, 30000, 5);

        $updatedProgress = UserSkillProgress::where('user_id', $this->user->id)
            ->where('skill_id', $this->skill->id)
            ->first();

        $this->assertEquals(-3, $updatedProgress->smart_score);
        $this->assertEquals(1, $updatedProgress->questions_answered);
        $this->assertEquals(0, $updatedProgress->correct_answers);
        $this->assertEquals(0, $updatedProgress->current_streak);
    }

    /** @test */
    public function it_resets_streak_on_incorrect_answer()
    {
        $progress = $this->progressService->initializeProgress($this->user->id, $this->skill->id);
        
        // Simulate a correct answer to increase streak
        $this->progressService->updateProgress($this->user->id, $this->skill->id, true, 5, 30000, 5);
        
        $progressAfterCorrect = UserSkillProgress::where('user_id', $this->user->id)
            ->where('skill_id', $this->skill->id)
            ->first();
        
        $this->assertEquals(1, $progressAfterCorrect->current_streak);
        
        // Now submit an incorrect answer
        $this->progressService->updateProgress($this->user->id, $this->skill->id, false, -3, 30000, 5);
        
        $progressAfterIncorrect = UserSkillProgress::where('user_id', $this->user->id)
            ->where('skill_id', $this->skill->id)
            ->first();
        
        $this->assertEquals(0, $progressAfterIncorrect->current_streak);
    }

    /** @test */
    public function it_updates_mastery_level_correctly()
    {
        $progress = $this->progressService->initializeProgress($this->user->id, $this->skill->id);
        
        // Submit multiple correct answers to increase smart score
        for ($i = 0; $i < 10; $i++) {
            $this->progressService->updateProgress($this->user->id, $this->skill->id, true, 10, 15000, 5);
        }

        $updatedProgress = UserSkillProgress::where('user_id', $this->user->id)
            ->where('skill_id', $this->skill->id)
            ->first();

        $this->assertGreaterThanOrEqual(50, $updatedProgress->smart_score);
    }
}