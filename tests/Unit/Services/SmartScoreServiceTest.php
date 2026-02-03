<?php

namespace Tests\Unit\Services;

use App\Services\SmartScoreService;
use Tests\TestCase;

class SmartScoreServiceTest extends TestCase
{
    protected $smartScoreService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->smartScoreService = new SmartScoreService();
    }

    /** @test */
    public function it_calculates_positive_score_change_for_correct_answer()
    {
        $scoreChange = $this->smartScoreService->calculateScoreChange(true, 5, 0, 30000);
        $this->assertGreaterThan(0, $scoreChange);
    }

    /** @test */
    public function it_calculates_negative_score_change_for_incorrect_answer()
    {
        $scoreChange = $this->smartScoreService->calculateScoreChange(false, 5, 0, 30000);
        $this->assertLessThan(0, $scoreChange);
    }

    /** @test */
    public function it_applies_streak_bonus_correctly()
    {
        // Without streak
        $scoreChangeWithoutStreak = $this->smartScoreService->calculateScoreChange(true, 5, 0, 30000);
        
        // With streak
        $scoreChangeWithStreak = $this->smartScoreService->calculateScoreChange(true, 5, 5, 30000);
        
        $this->assertGreaterThanOrEqual($scoreChangeWithoutStreak, $scoreChangeWithStreak);
    }

    /** @test */
    public function it_applies_time_bonus_for_quick_answers()
    {
        // Fast answer
        $scoreChangeFast = $this->smartScoreService->calculateScoreChange(true, 5, 0, 5000); // 5 seconds
        
        // Slow answer
        $scoreChangeSlow = $this->smartScoreService->calculateScoreChange(true, 5, 0, 60000); // 60 seconds
        
        $this->assertGreaterThanOrEqual($scoreChangeSlow, $scoreChangeFast);
    }

    /** @test */
    public function it_adjusts_difficulty_based_on_current_score()
    {
        // Low score should suggest easier questions
        $recommendedDifficultyLow = $this->smartScoreService->getNextDifficulty(20, []);
        
        // High score should suggest harder questions
        $recommendedDifficultyHigh = $this->smartScoreService->getNextDifficulty(80, []);
        
        $this->assertGreaterThanOrEqual($recommendedDifficultyLow, $recommendedDifficultyHigh);
    }
}