<?php

namespace App\Services;

use App\Models\Skill;
use App\Models\SkillQuestion;
use App\Models\QuQuestion;
use App\Models\SkillPracticeSession;
use App\Models\UserSkillProgress;
use Illuminate\Support\Facades\DB;

class AdaptiveQuestionService
{
    /**
     * Select the next question for a skill practice session based on the user's current
     * ability level and recent performance
     *
     * @param int $skillId The skill ID
     * @param int $userId The user ID
     * @param int|null $targetDifficulty The target difficulty level (if specified)
     * @return SkillQuestion|null The selected question or null if none available
     */
    public function selectNextQuestion($skillId, $userId, $targetDifficulty = null)
    {
        $skill = Skill::findOrFail($skillId);
        
        // If no target difficulty is specified, determine it based on user's progress
        if (!$targetDifficulty) {
            $targetDifficulty = $this->calculateTargetDifficulty($skillId, $userId);
        }
        
        // Find questions at the target difficulty that the user hasn't seen recently
        $excludedQuestionIds = $this->excludeRecentQuestions($userId, $skillId, 5);
        
        $query = $skill->questions()
            ->wherePivot('difficulty_level', $targetDifficulty)
            ->whereNotIn('qu_questions.id', $excludedQuestionIds)
            ->join('qu_questions', 'skill_questions.qu_question_id', '=', 'qu_questions.id')
            ->orderBy(DB::raw('RAND()'));
            
        $question = $query->first();
        
        // If no questions at target difficulty, expand search
        if (!$question) {
            $difficultiesToTry = $this->getNearbyDifficulties($targetDifficulty);
            
            foreach ($difficultiesToTry as $difficulty) {
                $query = $skill->questions()
                    ->wherePivot('difficulty_level', $difficulty)
                    ->whereNotIn('qu_questions.id', $excludedQuestionIds)
                    ->join('qu_questions', 'skill_questions.qu_question_id', '=', 'qu_questions.id')
                    ->orderBy(DB::raw('RAND()'));
                    
                $question = $query->first();
                
                if ($question) {
                    break;
                }
            }
        }
        
        // If still no question found, try without excluding recent questions
        if (!$question) {
            $query = $skill->questions()
                ->wherePivot('difficulty_level', $targetDifficulty)
                ->join('qu_questions', 'skill_questions.qu_question_id', '=', 'qu_questions.id')
                ->orderBy(DB::raw('RAND()'));
                
            $question = $query->first();
        }
        
        return $question ? $question->skillQuestion : null;
    }

    /**
     * Get IDs of questions the user has seen recently in this skill
     *
     * @param int $userId The user ID
     * @param int $skillId The skill ID
     * @param int $limit Number of recent questions to exclude
     * @return array Array of question IDs to exclude
     */
    public function excludeRecentQuestions($userId, $skillId, $limit = 5)
    {
        // Get the latest practice session for this user and skill
        $latestSession = SkillPracticeSession::where('user_id', $userId)
            ->where('skill_id', $skillId)
            ->latest('id')
            ->first();
            
        $excludedIds = [];
        
        if ($latestSession) {
            // Get the most recent answers from this session
            $recentAnswers = $latestSession->answers()
                ->with('skillQuestion')
                ->latest('id')
                ->limit($limit)
                ->get();
                
            foreach ($recentAnswers as $answer) {
                if ($answer->skillQuestion) {
                    $excludedIds[] = $answer->skillQuestion->id;
                }
            }
        }
        
        // Also include questions from previous sessions if we don't have enough
        if (count($excludedIds) < $limit) {
            $additionalAnswers = SkillPracticeAnswer::whereHas('session', function($q) use ($userId, $skillId) {
                $q->where('user_id', $userId)
                  ->where('skill_id', $skillId);
            })
            ->whereNotIn('skill_question_id', $excludedIds)
            ->with('skillQuestion')
            ->latest('id')
            ->limit($limit - count($excludedIds))
            ->get();
            
            foreach ($additionalAnswers as $answer) {
                if ($answer->skillQuestion) {
                    $excludedIds[] = $answer->skillQuestion->id;
                }
            }
        }
        
        return $excludedIds;
    }

    /**
     * Calculate the target difficulty based on user's current smart score and recent performance
     *
     * @param int $skillId The skill ID
     * @param int $userId The user ID
     * @return int The calculated target difficulty (1-10)
     */
    protected function calculateTargetDifficulty($skillId, $userId)
    {
        // Get user's progress for this skill
        $progress = UserSkillProgress::where('user_id', $userId)
            ->where('skill_id', $skillId)
            ->first();
            
        if (!$progress) {
            // If no progress, initialize and start with medium difficulty
            $smartScoreService = new SmartScoreService();
            $initialDifficulty = $smartScoreService->getMasteryLevel(0) === 'beginner' ? 3 : 5;
            return $initialDifficulty;
        }
        
        // Use SmartScoreService to determine appropriate difficulty
        $recentAnswers = $this->getRecentPerformance($userId, $skillId, 5);
        
        $smartScoreService = new SmartScoreService();
        return $smartScoreService->getNextDifficulty($progress->smart_score, $recentAnswers);
    }

    /**
     * Get recent performance data for difficulty calculation
     *
     * @param int $userId The user ID
     * @param int $skillId The skill ID
     * @param int $limit Number of recent answers to retrieve
     * @return array Array of recent answers with difficulty and correctness
     */
    protected function getRecentPerformance($userId, $skillId, $limit = 5)
    {
        $recentAnswers = SkillPracticeAnswer::whereHas('session', function($q) use ($userId, $skillId) {
            $q->where('user_id', $userId)
              ->where('skill_id', $skillId);
        })
        ->with('skillQuestion')
        ->latest('id')
        ->limit($limit)
        ->get()
        ->map(function ($answer) {
            return [
                'is_correct' => $answer->is_correct,
                'difficulty_at_time' => $answer->difficulty_at_time
            ];
        })
        ->toArray();
        
        return array_reverse($recentAnswers); // Return in chronological order
    }

    /**
     * Get nearby difficulties to try if no questions available at target difficulty
     *
     * @param int $targetDifficulty The target difficulty
     * @return array Array of nearby difficulties to try
     */
    protected function getNearbyDifficulties($targetDifficulty)
    {
        $difficulties = [];
        
        // Try difficulties in order of proximity to target
        for ($offset = 1; $offset <= 5; $offset++) {
            // Add higher difficulty if valid
            if ($targetDifficulty + $offset <= 10) {
                $difficulties[] = $targetDifficulty + $offset;
            }
            
            // Add lower difficulty if valid
            if ($targetDifficulty - $offset >= 1) {
                $difficulties[] = $targetDifficulty - $offset;
            }
        }
        
        return $difficulties;
    }

    /**
     * Get a random question at a specific difficulty level for a skill
     *
     * @param int $skillId The skill ID
     * @param int $difficulty The difficulty level
     * @return SkillQuestion|null A random question at the specified difficulty
     */
    public function getRandomQuestionAtLevel($skillId, $difficulty)
    {
        $skill = Skill::findOrFail($skillId);
        
        $question = $skill->questions()
            ->wherePivot('difficulty_level', $difficulty)
            ->join('qu_questions', 'skill_questions.qu_question_id', '=', 'qu_questions.id')
            ->orderBy(DB::raw('RAND()'))
            ->first();
            
        return $question ? $question->skillQuestion : null;
    }
}