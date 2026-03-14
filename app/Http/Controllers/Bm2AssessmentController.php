<?php

namespace App\Http\Controllers;

use App\Models\Bm2Assessment;
use App\Models\Bm2AssessmentQuestion;
use App\Models\Bm2LearningPath;
use App\Models\Bm2QuestionBank;
use App\Services\Bm2AdaptiveScoringService;
use App\Services\Bm2GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Bm2AssessmentController
 * 
 * Handles all assessment-related API operations.
 */
class Bm2AssessmentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private Bm2AdaptiveScoringService $scoringService,
        private Bm2GamificationService $gamificationService
    ) {}

    /**
     * Start a new assessment session and load all questions.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:placement,progress,final',
            'grade_level' => 'nullable|in:K,1,2',
            'game_mode' => 'nullable|string|in:falling,orbiting,space,normal',
            'game_settings' => 'nullable|array',
        ]);

        $student = $request->user();

        // Create new assessment
        $assessment = Bm2Assessment::create([
            'student_id' => $student->id,
            'title' => 'Basic Math Placement Test',
            'type' => $validated['type'] ?? 'placement',
            'game_mode' => $validated['game_mode'] ?? 'normal',
            'game_settings' => $validated['game_settings'] ?? null,
            'started_at' => now(),
            'is_active' => true,
        ]);

        // Load all questions for the assessment
        $allQuestions = $this->scoringService->getAllQuestionsForAssessment($assessment);

        return response()->json([
            'success' => true,
            'data' => [
                'assessment' => $assessment,
                'questions' => $allQuestions,
            ],
            'message' => 'Assessment started successfully',
        ]);
    }

    /**
     * Get all questions for an existing assessment.
     * 
     * @param int $assessmentId
     * @return JsonResponse
     */
    public function getAllQuestions(int $assessmentId): JsonResponse
    {
        $assessment = Bm2Assessment::findOrFail($assessmentId);
        
        // Load all questions for this assessment
        $allQuestions = $this->scoringService->getAllQuestionsForAssessment($assessment);

        return response()->json([
            'success' => true,
            'data' => [
                'questions' => $allQuestions,
            ],
        ]);
    }

    /**
     * Submit an answer for a question.
     * 
     * @param Request $request
     * @param int $assessmentId
     * @return JsonResponse
     */
    public function submitAnswer(Request $request, int $assessmentId): JsonResponse
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:bm2_questions_bank,id',
            'student_answer' => 'required|string',
            'time_taken_seconds' => 'nullable|integer|min:0',
            'hints_used' => 'nullable|integer|min:0',
        ]);

        $assessment = Bm2Assessment::findOrFail($assessmentId);

        // Create assessment question record
        $questionBank = Bm2QuestionBank::findOrFail($validated['question_id']);
        
        $assessmentQuestion = Bm2AssessmentQuestion::create([
            'assessment_id' => $assessment->id,
            'question_bank_id' => $questionBank->id,
            'question_text' => $questionBank->question_text,
            'subject' => $questionBank->subject,
            'grade_level' => $questionBank->grade_level,
            'question_type' => $questionBank->topic,
            'difficulty' => $questionBank->difficulty,
            'student_answer' => $validated['student_answer'],
            'correct_answer' => $questionBank->correct_answer,
            'is_correct' => $this->checkAnswerCorrectness($validated['student_answer'], $questionBank->correct_answer),
            'time_taken_seconds' => $validated['time_taken_seconds'] ?? 0,
            'hints_used' => $validated['hints_used'] ?? 0,
            'possible_points' => $questionBank->points_default,
            'answered_at' => now(),
        ]);

        // Calculate points earned
        $pointsEarned = $assessmentQuestion->calculatePoints();
        $assessmentQuestion->save();

        // Update question usage stats
        $questionBank->incrementUsage($assessmentQuestion->is_correct);

        // Get next question (adaptive)
        $nextQuestion = $this->scoringService->getNextQuestion($assessment, $assessmentQuestion);

        return response()->json([
            'success' => true,
            'data' => [
                'current_question' => $assessmentQuestion,
                'points_earned' => $pointsEarned,
                'is_correct' => $assessmentQuestion->is_correct,
                'explanation' => $questionBank->explanation,
                'next_question' => $nextQuestion,
            ],
            'message' => 'Answer submitted successfully',
        ]);
    }

    /**
     * Check if answer is correct.
     * 
     * @param mixed $studentAnswer
     * @param mixed $correctAnswer
     * @return bool
     */
    private function checkAnswerCorrectness(mixed $studentAnswer, mixed $correctAnswer): bool
    {
        // Handle different answer types
        if (is_array($correctAnswer)) {
            return $studentAnswer === $correctAnswer || in_array($studentAnswer, $correctAnswer);
        }
        
        // String comparison (case-insensitive for text answers)
        return strtolower(trim((string)$studentAnswer)) === strtolower(trim((string)$correctAnswer));
    }

    /**
     * Get the next question (adaptive).
     * 
     * @param int $assessmentId
     * @return JsonResponse
     */
    public function getNextQuestion(int $assessmentId): JsonResponse
    {
        $assessment = Bm2Assessment::findOrFail($assessmentId);
        
        // Get last answered question
        $lastQuestion = $assessment->questions()
            ->latest('created_at')
            ->first();

        // Get next adaptive question
        $nextQuestion = $this->scoringService->getNextQuestion($assessment, $lastQuestion);

        if (!$nextQuestion) {
            return response()->json([
                'success' => false,
                'message' => 'No more questions available',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'question' => $nextQuestion,
                'question_number' => $assessment->questions()->count() + 1,
            ],
        ]);
    }

    /**
     * Submit all answers at once (bulk submission).
     * 
     * @param Request $request
     * @param int $assessmentId
     * @return JsonResponse
     */
    public function submitAllAnswers(Request $request, int $assessmentId): JsonResponse
    {
        $validated = $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:bm2_questions_bank,id',
            'answers.*.student_answer' => 'required|string',
            'answers.*.time_taken_seconds' => 'nullable|integer|min:0',
            'answers.*.hints_used' => 'nullable|integer|min:0',
            'answers.*.question_number' => 'nullable|integer|min:1',
            'total_time_seconds' => 'nullable|integer|min:0',
            'game_stats' => 'nullable|array',
            'game_stats.score' => 'nullable|integer',
            'game_stats.combo' => 'nullable|integer',
            'game_stats.max_combo' => 'nullable|integer',
            'game_stats.lives_remaining' => 'nullable|integer',
            'game_stats.power_ups_used' => 'nullable|array',
        ]);

        $assessment = Bm2Assessment::findOrFail($assessmentId);
        $answers = $validated['answers'];
        
        // Process all answers
        $totalScore = 0;
        $correctCount = 0;
        $assessmentQuestions = [];
        
        foreach ($answers as $index => $answerData) {
            $questionBank = Bm2QuestionBank::findOrFail($answerData['question_id']);
            
            // Create assessment question record
            $assessmentQuestion = Bm2AssessmentQuestion::create([
                'assessment_id' => $assessment->id,
                'question_bank_id' => $questionBank->id,
                'question_text' => $questionBank->question_text,
                'subject' => $questionBank->subject,
                'grade_level' => $questionBank->grade_level,
                'question_type' => $questionBank->topic,
                'difficulty' => $questionBank->difficulty,
                'student_answer' => $answerData['student_answer'],
                'correct_answer' => $questionBank->correct_answer,
                'is_correct' => $this->checkAnswerCorrectness($answerData['student_answer'], $questionBank->correct_answer),
                'time_taken_seconds' => $answerData['time_taken_seconds'] ?? 0,
                'hints_used' => $answerData['hints_used'] ?? 0,
                'possible_points' => $questionBank->points_default,
                'question_order' => $answerData['question_number'] ?? ($index + 1),
                'answered_at' => now(),
            ]);
            
            // Calculate points
            $pointsEarned = $assessmentQuestion->calculatePoints();
            $assessmentQuestion->points_earned = $pointsEarned;
            $assessmentQuestion->save();
            
            $totalScore += $pointsEarned;
            if ($assessmentQuestion->is_correct) {
                $correctCount++;
            }
            
            $assessmentQuestions[] = $assessmentQuestion;
            
            // Update question usage stats
            $questionBank->incrementUsage($assessmentQuestion->is_correct);
        }
        
        // Calculate final score percentage
        $finalScorePercentage = $assessment->calculateScore();
        $performanceLevel = $assessment->determinePerformanceLevel();
        
        // Calculate skill breakdown
        $skillBreakdown = $this->scoringService->calculateSkillBreakdown($assessment);
        $assessment->skill_breakdown = $skillBreakdown;
        
        // Determine grade level equivalent
        $assessment->grade_level_equivalent = $this->determineGradeLevelEquivalent($finalScorePercentage, $assessment->type);
        $assessment->performance_level = $performanceLevel;
        
        // Mark as complete
        $assessment->completed_at = now();
        $assessment->total_time_seconds = $validated['total_time_seconds'] ?? $assessment->started_at->diffInSeconds(now());
        $assessment->is_active = false;
        
        // Store game stats if game mode was used
        if (isset($validated['game_stats'])) {
            $assessment->game_stats = $validated['game_stats'];
        }
        
        $assessment->save();
        
        // Generate learning path
        $learningPath = $this->scoringService->createLearningPath($assessment);
        
        // Check and award badges
        $student = $assessment->student;
        $awardedBadges = $this->gamificationService->checkAndAwardBadges($assessment);
        
        // Update streak
        $this->gamificationService->updateStreak($student, $assessment);
        
        return response()->json([
            'success' => true,
            'data' => [
                'assessment' => $assessment,
                'final_score' => $finalScorePercentage,
                'performance_level' => $performanceLevel,
                'skill_breakdown' => $skillBreakdown,
                'learning_path' => $learningPath,
                'awarded_badges' => $awardedBadges,
                'total_points' => $this->gamificationService->getTotalPoints($student),
                'current_streak' => $this->gamificationService->getCurrentStreak($student),
                'questions_answered' => count($answers),
                'correct_answers' => $correctCount,
            ],
            'message' => 'Assessment completed successfully!' . ($awardedBadges ? ' Congratulations on your badges!' : ''),
        ]);
    }
    public function complete(int $assessmentId): JsonResponse
    {
        $assessment = Bm2Assessment::findOrFail($assessmentId);

        // Calculate final score
        $finalScore = $assessment->calculateScore();
        $performanceLevel = $assessment->determinePerformanceLevel();

        // Calculate skill breakdown
        $skillBreakdown = $this->scoringService->calculateSkillBreakdown($assessment);
        $assessment->skill_breakdown = $skillBreakdown;

        // Determine grade level equivalent (simplified logic)
        $assessment->grade_level_equivalent = $this->determineGradeLevelEquivalent($finalScore, $assessment->type);
        $assessment->performance_level = $performanceLevel;

        // Mark as complete
        $assessment->completed_at = now();
        $assessment->total_time_seconds = $assessment->started_at->diffInSeconds(now());
        $assessment->is_active = false;
        $assessment->save();

        // Generate learning path
        $learningPath = $this->scoringService->createLearningPath($assessment);

        // Check and award badges
        $awardedBadges = $this->gamificationService->checkAndAwardBadges($assessment);

        // Update streak
        $this->gamificationService->updateStreak($student, $assessment);

        return response()->json([
            'success' => true,
            'data' => [
                'assessment' => $assessment,
                'final_score' => $finalScore,
                'performance_level' => $performanceLevel,
                'skill_breakdown' => $skillBreakdown,
                'learning_path' => $learningPath,
                'awarded_badges' => $awardedBadges,
                'total_points' => $this->gamificationService->getTotalPoints($student),
                'current_streak' => $this->gamificationService->getCurrentStreak($student),
            ],
            'message' => 'Assessment completed successfully' . ($awardedBadges ? ' - Congratulations on your badges!' : ''),
        ]);
    }

    /**
     * Determine grade level equivalent based on score.
     * 
     * @param float $score
     * @param string $assessmentType
     * @return string
     */
    private function determineGradeLevelEquivalent(float $score, string $assessmentType): string
    {
        // Simplified logic - would need refinement based on actual standards
        if ($score >= 90) {
            return '2'; // Grade 2 level
        } elseif ($score >= 70) {
            return '1'; // Grade 1 level
        } else {
            return 'K'; // Kindergarten level
        }
    }

    /**
     * Get assessment results.
     * 
     * @param int $assessmentId
     * @return JsonResponse
     */
    public function getResults(int $assessmentId): JsonResponse
    {
        $assessment = Bm2Assessment::with(['questions.questionBank', 'learningPath'])->findOrFail($assessmentId);

        if (!$assessment->isComplete()) {
            return response()->json([
                'success' => false,
                'message' => 'Assessment not yet completed',
            ], 400);
        }

        $results = [
            'assessment' => $assessment,
            'overall_score' => $assessment->overall_score,
            'performance_level' => $assessment->performance_level,
            'grade_level_equivalent' => $assessment->grade_level_equivalent,
            'skill_breakdown' => $assessment->skill_breakdown,
            'duration_minutes' => $assessment->duration_minutes,
            'questions_answered' => $assessment->questions->count(),
            'correct_answers' => $assessment->questions->where('is_correct', true)->count(),
            'learning_path' => $assessment->learningPath,
            'question_details' => $assessment->questions->map(fn($q) => [
                'question_text' => $q->question_text,
                'student_answer' => $q->student_answer,
                'correct_answer' => $q->correct_answer,
                'is_correct' => $q->is_correct,
                'time_taken' => $q->time_taken_seconds,
                'points_earned' => $q->points_earned,
            ]),
        ];

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }
}
