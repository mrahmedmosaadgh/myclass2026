<?php

namespace App\Services;

use App\Models\Bm2Assessment;
use App\Models\Bm2AssessmentQuestion;
use App\Models\Bm2QuestionBank;
use App\Models\Bm2LearningPath;
use Illuminate\Support\Facades\DB;

/**
 * Bm2AdaptiveScoringService
 * 
 * Handles adaptive question selection and scoring calculations.
 */
class Bm2AdaptiveScoringService
{
    /**
     * Get the next question based on adaptive algorithm.
     * 
     * @param Bm2Assessment $assessment
     * @param Bm2AssessmentQuestion|null $lastQuestion
     * @return Bm2QuestionBank|null
     */
    public function getNextQuestion(Bm2Assessment $assessment, ?Bm2AssessmentQuestion $lastQuestion): ?Bm2QuestionBank
    {
        // If first question, start with medium difficulty
        if (!$lastQuestion) {
            return $this->getQuestionByDifficulty($assessment, 'medium');
        }

        // Determine next difficulty based on last answer
        $nextDifficulty = $this->determineNextDifficulty($lastQuestion);

        return $this->getQuestionByDifficulty($assessment, $nextDifficulty);
    }

    /**
     * Get all questions for an assessment (non-adaptive, load all at once).
     * 
     * @param Bm2Assessment $assessment
     * @return array
     */
    public function getAllQuestionsForAssessment(Bm2Assessment $assessment): array
    {
        // Get a balanced mix of questions across difficulties
        $easyQuestions = $this->getQuestionsByDifficultyCount($assessment, 'easy', 5);
        $mediumQuestions = $this->getQuestionsByDifficultyCount($assessment, 'medium', 5);
        $hardQuestions = $this->getQuestionsByDifficultyCount($assessment, 'hard', 3);

        $allQuestions = array_merge($easyQuestions, $mediumQuestions, $hardQuestions);
        
        // Shuffle to mix difficulties
        shuffle($allQuestions);

        return $allQuestions;
    }

    /**
     * Get specific count of questions by difficulty.
     * 
     * @param Bm2Assessment $assessment
     * @param string $difficulty
     * @param int $count
     * @return array
     */
    private function getQuestionsByDifficultyCount(Bm2Assessment $assessment, string $difficulty, int $count): array
    {
        $usedQuestionIds = $assessment->questions()
            ->whereNotNull('question_bank_id')
            ->pluck('question_bank_id')
            ->toArray();

        return Bm2QuestionBank::query()
            ->active()
            ->ofDifficulty($difficulty)
            ->whereNotIn('id', $usedQuestionIds)
            ->inRandomOrder()
            ->limit($count)
            ->get()
            ->toArray();
    }

    /**
     * Get a question of specific difficulty.
     * 
     * @param Bm2Assessment $assessment
     * @param string $difficulty
     * @return Bm2QuestionBank|null
     */
    private function getQuestionByDifficulty(Bm2Assessment $assessment, string $difficulty): ?Bm2QuestionBank
    {
        // Get questions not yet used in this assessment
        $usedQuestionIds = $assessment->questions()
            ->whereNotNull('question_bank_id')
            ->pluck('question_bank_id')
            ->toArray();

        return Bm2QuestionBank::query()
            ->active()
            ->ofDifficulty($difficulty)
            ->whereNotIn('id', $usedQuestionIds)
            ->inRandomOrder()
            ->first();
    }

    /**
     * Determine next difficulty based on performance.
     * 
     * @param Bm2AssessmentQuestion $lastQuestion
     * @return string
     */
    private function determineNextDifficulty(Bm2AssessmentQuestion $lastQuestion): string
    {
        if ($lastQuestion->is_correct) {
            // Correct: increase difficulty or stay at hard
            if ($lastQuestion->difficulty === 'easy') {
                return 'medium';
            } elseif ($lastQuestion->difficulty === 'medium') {
                return 'hard';
            }
            return 'hard';
        } else {
            // Incorrect: decrease difficulty or stay at easy
            if ($lastQuestion->difficulty === 'hard') {
                return 'medium';
            } elseif ($lastQuestion->difficulty === 'medium') {
                return 'easy';
            }
            return 'easy';
        }
    }

    /**
     * Calculate skill breakdown from assessment results.
     * 
     * @param Bm2Assessment $assessment
     * @return array
     */
    public function calculateSkillBreakdown(Bm2Assessment $assessment): array
    {
        $questions = $assessment->questions;

        $skills = [];
        
        // Group by question type (skill)
        foreach ($questions as $question) {
            $skill = $question->question_type;
            
            if (!isset($skills[$skill])) {
                $skills[$skill] = [
                    'total' => 0,
                    'correct' => 0,
                    'percentage' => 0,
                ];
            }

            $skills[$skill]['total']++;
            if ($question->is_correct) {
                $skills[$skill]['correct']++;
            }
        }

        // Calculate percentages
        foreach ($skills as &$skillData) {
            $skillData['percentage'] = $skillData['total'] > 0
                ? ($skillData['correct'] / $skillData['total']) * 100
                : 0;
        }

        return $skills;
    }

    /**
     * Generate learning path recommendations based on skill breakdown.
     * 
     * @param Bm2Assessment $assessment
     * @return array
     */
    public function generateRecommendations(Bm2Assessment $assessment): array
    {
        $skillBreakdown = $this->calculateSkillBreakdown($assessment);

        $recommendations = [];

        foreach ($skillBreakdown as $skill => $data) {
            // If percentage < 70%, recommend practice
            if ($data['percentage'] < 70) {
                $priority = $data['percentage'] < 40 ? 'high' : 'medium';
                
                $recommendations[] = [
                    'topic' => $skill,
                    'priority' => $priority,
                    'reason' => "{$skill}_needs_work",
                    'suggested_lessons' => $this->getSuggestedLessons($skill, $data['percentage']),
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Get suggested lessons for a topic.
     * 
     * @param string $topic
     * @param float $currentPercentage
     * @return array
     */
    private function getSuggestedLessons(string $topic, float $currentPercentage): array
    {
        // This would query actual lessons from database
        // For now, return placeholder structure
        return [
            [
                'module_id' => 1,
                'lesson_id' => 1,
                'title' => "{$topic} Basics",
            ],
            [
                'module_id' => 1,
                'lesson_id' => 2,
                'title' => "{$topic} Practice",
            ],
        ];
    }

    /**
     * Create learning path for student.
     * 
     * @param Bm2Assessment $assessment
     * @return Bm2LearningPath
     */
    public function createLearningPath(Bm2Assessment $assessment): Bm2LearningPath
    {
        $recommendations = $this->generateRecommendations($assessment);

        // Count total suggested lessons
        $totalLessons = collect($recommendations)->sum(function($rec) {
            return count($rec['suggested_lessons'] ?? []);
        });

        return Bm2LearningPath::create([
            'student_id' => $assessment->student_id,
            'assessment_id' => $assessment->id,
            'title' => 'Personalized Math Learning Path',
            'description' => "Generated from assessment on " . $assessment->started_at->format('M d, Y'),
            'recommended_modules' => $recommendations,
            'total_lessons' => $totalLessons,
            'completed_lessons' => 0,
            'completion_percentage' => 0,
            'estimated_minutes' => $totalLessons * 5, // 5 minutes per lesson
            'status' => 'not_started',
            'is_active' => true,
        ]);
    }
}
