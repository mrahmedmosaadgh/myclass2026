<?php

namespace App\Services;

use App\Models\SkillPracticeAnswer;

class SmartScoreService
{
    // Constants for score adjustments
    const SCORE_EASY_CORRECT = 2;
    const SCORE_MEDIUM_CORRECT = 5;
    const SCORE_HARD_CORRECT = 8;
    const SCORE_EASY_INCORRECT = -1;
    const SCORE_MEDIUM_INCORRECT = -3;
    const SCORE_HARD_INCORRECT = -5;
    const STREAK_BONUS = 2; // Bonus added when user gets multiple questions right in a row
    
    /**
     * Calculate the score change based on whether the answer was correct,
     * the difficulty level, current streak, and time taken
     *
     * @param bool $isCorrect Whether the user's answer was correct
     * @param int $difficulty The difficulty level of the question (1-10)
     * @param int $streak The current streak of correct answers
     * @param int $timeTakenMs Time taken to answer in milliseconds
     * @return int The score change
     */
    public function calculateScoreChange($isCorrect, $difficulty, $streak, $timeTakenMs)
    {
        $baseScore = 0;
        
        // Determine base score based on difficulty and correctness
        if ($isCorrect) {
            if ($difficulty <= 3) {
                $baseScore = self::SCORE_EASY_CORRECT;
            } elseif ($difficulty <= 7) {
                $baseScore = self::SCORE_MEDIUM_CORRECT;
            } else {
                $baseScore = self::SCORE_HARD_CORRECT;
            }
        } else {
            if ($difficulty <= 3) {
                $baseScore = self::SCORE_EASY_INCORRECT;
            } elseif ($difficulty <= 7) {
                $baseScore = self::SCORE_MEDIUM_INCORRECT;
            } else {
                $baseScore = self::SCORE_HARD_INCORRECT;
            }
        }
        
        // Apply streak bonus if correct and streak is greater than 1
        $streakBonus = 0;
        if ($isCorrect && $streak > 1) {
            $streakBonus = min($streak - 1, 5) * self::STREAK_BONUS; // Cap streak bonus to prevent excessive gains
        }
        
        // Apply time bonus/penalty (faster answers get slight bonus)
        $timeBonus = 0;
        if ($isCorrect && $timeTakenMs > 0) {
            // Reward quick but thoughtful responses (between 5-30 seconds optimal)
            if ($timeTakenMs >= 5000 && $timeTakenMs <= 30000) {
                $timeBonus = 1; // Small bonus for optimal timing
            } elseif ($timeTakenMs > 30000) {
                // Slight penalty for taking too long (indicating guessing)
                $timeBonus = -1;
            }
        }
        
        return $baseScore + $streakBonus + $timeBonus;
    }

    /**
     * Determine the next difficulty level based on current score and recent performance
     *
     * @param int $currentScore The user's current smart score
     * @param array $recentAnswers Array of recent answers ['is_correct', 'difficulty'] 
     * @return int The recommended difficulty level (1-10)
     */
    public function getNextDifficulty($currentScore, $recentAnswers)
    {
        // If we don't have recent answers, start with medium difficulty
        if (empty($recentAnswers)) {
            return 5;
        }
        
        // Calculate average difficulty of recent questions and success rate
        $totalDifficulty = 0;
        $correctCount = 0;
        $answerCount = count($recentAnswers);
        
        foreach ($recentAnswers as $answer) {
            $totalDifficulty += $answer['difficulty_at_time'];
            if ($answer['is_correct']) {
                $correctCount++;
            }
        }
        
        $avgDifficulty = $totalDifficulty / $answerCount;
        $successRate = $answerCount > 0 ? $correctCount / $answerCount : 0;
        
        // Adjust difficulty based on performance
        if ($successRate >= 0.75) {
            // User is doing well, increase difficulty
            return min(10, round($avgDifficulty + 1));
        } elseif ($successRate <= 0.4) {
            // User is struggling, decrease difficulty
            return max(1, round($avgDifficulty - 1));
        } else {
            // Performance is appropriate, maintain similar difficulty
            return round($avgDifficulty);
        }
    }
    
    /**
     * Calculate mastery level based on current smart score
     *
     * @param int $smartScore The user's current smart score
     * @return string Mastery level ('beginner', 'developing', 'proficient', 'advanced', 'master')
     */
    public function getMasteryLevel($smartScore)
    {
        if ($smartScore < 20) {
            return 'beginner';
        } elseif ($smartScore < 50) {
            return 'developing';
        } elseif ($smartScore < 80) {
            return 'proficient';
        } elseif ($smartScore < 100) {
            return 'advanced';
        } else {
            return 'master';
        }
    }
}