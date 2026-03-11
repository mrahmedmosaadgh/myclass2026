<?php

namespace App\Http\Controllers;

use App\Models\Bm2Assessment;
use App\Models\Bm2AssessmentQuestion;
use App\Models\Bm2LearningPath;
use App\Models\Bm2QuestionBank;
use App\Services\Bm2AdaptiveScoringService;
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
        private Bm2AdaptiveScoringService $scoringService
    ) {}

    /**
     * Start a new assessment session.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:placement,progress,final',
            'grade_level' => 'nullable|in:K,1,2',
        ]);

        $student = $request->user();

        // Create new assessment
        $assessment = Bm2Assessment::create([
            'student_id' => $student->id,
            'title' => 'Basic Math Placement Test',
            'type' => $validated['type'] ?? 'placement',
            'started_at' => now(),
            'is_active' => true,
        ]);

        // Get first question (adaptive)
        $firstQuestion = $this->scoringService->getNextQuestion($assessment, null);

        return response()->json([
            'success' => true,
            'data' => [
                'assessment' => $assessment,
                'question' => $firstQuestion,
            ],
            'message' => 'Assessment started successfully',
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
     * Complete the assessment and generate results.
     * 
     * @param int $assessmentId
     * @return JsonResponse
     */
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

        return response()->json([
            'success' => true,
            'data' => [
                'assessment' => $assessment,
                'final_score' => $finalScore,
                'performance_level' => $performanceLevel,
                'skill_breakdown' => $skillBreakdown,
                'learning_path' => $learningPath,
            ],
            'message' => 'Assessment completed successfully',
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
