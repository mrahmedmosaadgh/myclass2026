<?php

namespace App\Services;

use App\Models\Skill;
use App\Models\UserSkillProgress;
use App\Models\SkillPracticeSession;
use App\Models\SkillPracticeAnswer;
use App\Models\SkillAward;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SkillProgressService
{
    protected $smartScoreService;
    
    public function __construct()
    {
        $this->smartScoreService = new SmartScoreService();
    }

    /**
     * Initialize user progress for a skill if it doesn't exist
     *
     * @param int $userId The user ID
     * @param int $skillId The skill ID
     * @return UserSkillProgress The user's skill progress record
     */
    public function initializeProgress($userId, $skillId)
    {
        $progress = UserSkillProgress::firstOrCreate([
            'user_id' => $userId,
            'skill_id' => $skillId,
        ], [
            'smart_score' => 0,
            'questions_answered' => 0,
            'correct_answers' => 0,
            'current_streak' => 0,
            'best_streak' => 0,
            'mastery_level' => 'beginner',
        ]);
        
        return $progress;
    }

    /**
     * Update user's progress after answering a question
     *
     * @param int $userId The user ID
     * @param int $skillId The skill ID
     * @param bool $isCorrect Whether the answer was correct
     * @param int $scoreChange The change in smart score
     * @param int $timeTakenMs Time taken to answer in milliseconds
     * @param int $difficultyAtTime Difficulty of the question
     * @return UserSkillProgress Updated progress record
     */
    public function updateProgress($userId, $skillId, $isCorrect, $scoreChange, $timeTakenMs, $difficultyAtTime)
    {
        $progress = $this->initializeProgress($userId, $skillId);
        
        // Update basic counters
        $progress->questions_answered++;
        if ($isCorrect) {
            $progress->correct_answers++;
        }
        
        // Update smart score
        $oldScore = $progress->smart_score;
        $newScore = max(0, $progress->smart_score + $scoreChange); // Prevent negative scores
        $progress->smart_score = $newScore;
        
        // Update streaks
        if ($isCorrect) {
            $progress->incrementStreak();
        } else {
            $progress->resetStreak();
        }
        
        // Update mastery level
        $progress->mastery_level = $this->smartScoreService->getMasteryLevel($newScore);
        
        // Update last practiced time
        $progress->last_practiced_at = now();
        
        $progress->save();
        
        // Check for awards after progress update
        $this->checkAndAwardBadges($userId, $skillId, $isCorrect, $scoreChange);
        
        return $progress;
    }

    /**
     * Check if user has earned any badges and award them
     *
     * @param int $userId The user ID
     * @param int $skillId The skill ID
     * @param bool $isCorrect Whether the last answer was correct
     * @param int $scoreChange The score change from the last answer
     * @return Collection Collection of awarded badges
     */
    public function checkAndAwardBadges($userId, $skillId, $isCorrect, $scoreChange)
    {
        $awards = collect();
        $progress = UserSkillProgress::where('user_id', $userId)
            ->where('skill_id', $skillId)
            ->first();
            
        if (!$progress) {
            return $awards;
        }
        
        // Check for streak badge (getting 5+ correct answers in a row)
        if ($progress->current_streak >= 5) {
            $award = $this->awardBadge($userId, $skillId, 'streak_5', [
                'streak_count' => $progress->current_streak,
                'date' => now()
            ]);
            
            if ($award) {
                $awards->push($award);
            }
        }
        
        // Check for first mastery badge (reaching master level for first time)
        if ($progress->mastery_level === 'master') {
            $existingMasterAward = SkillAward::where('user_id', $userId)
                ->where('skill_id', $skillId)
                ->where('award_type', 'first_mastery')
                ->first();
                
            if (!$existingMasterAward) {
                $award = $this->awardBadge($userId, $skillId, 'first_mastery', [
                    'score_reached' => $progress->smart_score,
                    'date' => now(),
                    'questions_answered' => $progress->questions_answered
                ]);
                
                if ($award) {
                    $awards->push($award);
                }
            }
        }
        
        // Check for rapid responder badge (consistently answering quickly)
        if ($timeThreshold = $this->checkRapidResponder($userId, $skillId)) {
            $award = $this->awardBadge($userId, $skillId, 'rapid_responder', [
                'avg_response_time' => $timeThreshold,
                'date' => now()
            ]);
            
            if ($award) {
                $awards->push($award);
            }
        }
        
        // Check for accuracy badge (high accuracy rate)
        if ($accuracy = $this->checkAccuracyBadge($progress)) {
            $award = $this->awardBadge($userId, $skillId, 'accuracy_master', [
                'accuracy_rate' => $accuracy,
                'date' => now()
            ]);
            
            if ($award) {
                $awards->push($award);
            }
        }
        
        return $awards;
    }

    /**
     * Award a badge to a user for a skill
     *
     * @param int $userId The user ID
     * @param int $skillId The skill ID
     * @param string $awardType The type of award
     * @param array $metadata Additional data about the award
     * @return SkillAward|null The created award or null if already awarded
     */
    protected function awardBadge($userId, $skillId, $awardType, $metadata)
    {
        // Check if this award was already given
        $existingAward = SkillAward::where('user_id', $userId)
            ->where('skill_id', $skillId)
            ->where('award_type', $awardType)
            ->first();
            
        if ($existingAward) {
            return null; // Already awarded
        }
        
        $award = new SkillAward([
            'user_id' => $userId,
            'skill_id' => $skillId,
            'award_type' => $awardType,
            'metadata' => $metadata
        ]);
        
        $award->save();
        
        return $award;
    }
    
    /**
     * Check if user qualifies for rapid responder badge
     *
     * @param int $userId The user ID
     * @param int $skillId The skill ID
     * @return float|false Average response time if qualified, false otherwise
     */
    protected function checkRapidResponder($userId, $skillId)
    {
        // Get the last 10 answers from this skill
        $recentAnswers = SkillPracticeAnswer::whereHas('session', function($q) use ($userId, $skillId) {
            $q->where('user_id', $userId)
              ->where('skill_id', $skillId);
        })
        ->latest('id')
        ->limit(10)
        ->get();
        
        if ($recentAnswers->count() < 10) {
            return false; // Need at least 10 answers
        }
        
        $totalTime = $recentAnswers->sum('time_taken_ms');
        $avgTime = $totalTime / $recentAnswers->count();
        $avgTimeInSeconds = $avgTime / 1000;
        
        // Award if average response time is under 10 seconds
        if ($avgTimeInSeconds < 10) {
            return $avgTimeInSeconds;
        }
        
        return false;
    }
    
    /**
     * Check if user qualifies for accuracy badge
     *
     * @param UserSkillProgress $progress The user's progress
     * @return float|false Accuracy rate if qualified, false otherwise
     */
    protected function checkAccuracyBadge($progress)
    {
        if ($progress->questions_answered < 20) {
            return false; // Need at least 20 questions answered
        }
        
        $accuracy = $progress->questions_answered > 0 
            ? ($progress->correct_answers / $progress->questions_answered) * 100 
            : 0;
            
        // Award if accuracy is 85% or higher
        if ($accuracy >= 85) {
            return $accuracy;
        }
        
        return false;
    }
}