<?php

namespace App\Http\Controllers;

use App\Models\QuizAttempt;
use App\Models\Question;
use App\Services\QuizService;
use App\Http\Requests\StartQuizAttemptRequest;
use App\Http\Requests\SubmitAnswerRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class QuizAttemptController extends Controller
{
    protected QuizService $quizService;

    public function __construct(QuizService $quizService)
    {
        $this->middleware('auth:sanctum');
        $this->quizService = $quizService;
    }

    /**
     * Start a new quiz attempt.
     * 
     * Creates a new quiz attempt record for the authenticated user with the
     * specified questions. This initializes the quiz session and returns the
     * attempt ID for tracking subsequent answer submissions.
     * 
     * @param StartQuizAttemptRequest $request
     * @return JsonResponse
     */
    public function store(StartQuizAttemptRequest $request): JsonResponse
    {
        try {
            // Get validated data
            $validated = $request->validated();

            $user = $request->user();

            // Verify all questions exist and are active
            $questions = Question::whereIn('id', $validated['question_ids'])
                ->active()
                ->get();

            if ($questions->count() !== count($validated['question_ids'])) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_QUESTIONS',
                        'message' => 'Some questions are not available or inactive',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 422);
            }

            // Start the quiz attempt
            $attempt = $this->quizService->startAttempt(
                $user,
                $validated['question_ids'],
                $validated['quiz_id'] ?? null
            );

            // Merge any additional metadata
            if (isset($validated['metadata'])) {
                $metadata = array_merge($attempt->metadata ?? [], $validated['metadata']);
                $attempt->update(['metadata' => $metadata]);
            }

            Log::info('Quiz attempt created', [
                'attempt_id' => $attempt->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'attempt_id' => $attempt->id,
                    'started_at' => $attempt->started_at,
                    'total_questions' => $attempt->total_questions,
                ],
            ], 201);

        } catch (QueryException $e) {
            Log::error('Database error while creating quiz attempt', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            // Check for specific database constraint violations
            if ($e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DATABASE_CONSTRAINT_ERROR',
                        'message' => 'A database constraint was violated',
                        'details' => config('app.debug') ? $e->getMessage() : null,
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 422);
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DATABASE_ERROR',
                    'message' => 'A database error occurred',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);

        } catch (\Exception $e) {
            Log::error('Quiz attempt creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ATTEMPT_CREATION_ERROR',
                    'message' => 'Failed to start quiz attempt',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    /**
     * Submit an answer for a question in a quiz attempt.
     * 
     * Records the user's answer for a specific question, validates correctness,
     * and tracks time spent. Supports both option-based and text-based answers.
     * 
     * @param SubmitAnswerRequest $request
     * @param int $attemptId
     * @return JsonResponse
     */
    public function submitAnswer(SubmitAnswerRequest $request, int $attemptId): JsonResponse
    {
        try {
            // Get validated data
            $validated = $request->validated();

            $user = $request->user();

            // Find the quiz attempt and verify ownership
            $attempt = QuizAttempt::findOrFail($attemptId);

            if ($attempt->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED_ACCESS',
                        'message' => 'You do not have permission to access this quiz attempt',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 403);
            }

            // Check if attempt is already completed
            if ($attempt->isComplete()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'ATTEMPT_COMPLETED',
                        'message' => 'This quiz attempt has already been completed',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 422);
            }

            // Find the question
            $question = Question::findOrFail($validated['question_id']);

            // Verify the question is part of this attempt
            $questionIds = $attempt->metadata['question_ids'] ?? [];
            if (!in_array($question->id, $questionIds)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_QUESTION',
                        'message' => 'This question is not part of the quiz attempt',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 422);
            }

            // Submit the answer
            $answer = $this->quizService->submitAnswer(
                $attempt,
                $question,
                $validated['selected_option_id'] ?? null,
                $validated['selected_text'] ?? null,
                $validated['time_spent_sec'] ?? 0
            );

            Log::info('Answer submitted', [
                'attempt_id' => $attemptId,
                'question_id' => $question->id,
                'is_correct' => $answer->is_correct,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'answer_id' => $answer->id,
                    'is_correct' => $answer->is_correct,
                    'answered_at' => $answer->answered_at,
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Quiz attempt or question not found',
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 404);

        } catch (QueryException $e) {
            Log::error('Database error while submitting answer', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            // Check for specific database constraint violations
            if ($e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DATABASE_CONSTRAINT_ERROR',
                        'message' => 'A database constraint was violated',
                        'details' => config('app.debug') ? $e->getMessage() : null,
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 422);
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DATABASE_ERROR',
                    'message' => 'A database error occurred',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);

        } catch (\Exception $e) {
            Log::error('Answer submission failed', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ANSWER_SUBMISSION_ERROR',
                    'message' => 'Failed to submit answer',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    /**
     * Complete a quiz attempt and calculate final results.
     * 
     * Marks the quiz attempt as completed, calculates the final score and
     * percentage, and updates question analytics. Returns the final results.
     * 
     * @param Request $request
     * @param int $attemptId
     * @return JsonResponse
     */
    public function complete(Request $request, int $attemptId): JsonResponse
    {
        try {
            $user = $request->user();

            // Find the quiz attempt and verify ownership
            $attempt = QuizAttempt::findOrFail($attemptId);

            if ($attempt->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED_ACCESS',
                        'message' => 'You do not have permission to access this quiz attempt',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 403);
            }

            // Check if already completed
            if ($attempt->isComplete()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'ALREADY_COMPLETED',
                        'message' => 'This quiz attempt has already been completed',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 422);
            }

            // Complete the attempt
            $completedAttempt = $this->quizService->completeAttempt($attempt);

            Log::info('Quiz attempt completed', [
                'attempt_id' => $attemptId,
                'user_id' => $user->id,
                'percentage' => $completedAttempt->percentage,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'attempt_id' => $completedAttempt->id,
                    'completed_at' => $completedAttempt->completed_at,
                    'total_questions' => $completedAttempt->total_questions,
                    'correct_answers' => $completedAttempt->correct_answers,
                    'percentage' => $completedAttempt->percentage,
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Quiz attempt not found',
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 404);

        } catch (QueryException $e) {
            Log::error('Database error while completing quiz attempt', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DATABASE_ERROR',
                    'message' => 'A database error occurred while completing the quiz',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);

        } catch (\Exception $e) {
            Log::error('Quiz attempt completion failed', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPLETION_ERROR',
                    'message' => 'Failed to complete quiz attempt',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }

    /**
     * Get detailed results for a completed quiz attempt.
     * 
     * Returns comprehensive results including all answers, correctness indicators,
     * time spent, and question details. Only accessible by the attempt owner.
     * 
     * @param Request $request
     * @param int $attemptId
     * @return JsonResponse
     */
    public function results(Request $request, int $attemptId): JsonResponse
    {
        try {
            $user = $request->user();

            // Find the quiz attempt and verify ownership
            $attempt = QuizAttempt::findOrFail($attemptId);

            if ($attempt->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED_ACCESS',
                        'message' => 'You do not have permission to access this quiz attempt',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 403);
            }

            // Check if completed
            if (!$attempt->isComplete()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'ATTEMPT_NOT_COMPLETED',
                        'message' => 'This quiz attempt has not been completed yet',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ], 422);
            }

            // Get detailed results
            $results = $this->quizService->getQuizResults($attempt);

            Log::info('Quiz results retrieved', [
                'attempt_id' => $attemptId,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Quiz attempt not found',
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 404);

        } catch (QueryException $e) {
            Log::error('Database error while retrieving quiz results', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DATABASE_ERROR',
                    'message' => 'A database error occurred while retrieving results',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);

        } catch (\Exception $e) {
            Log::error('Quiz results retrieval failed', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESULTS_RETRIEVAL_ERROR',
                    'message' => 'Failed to retrieve quiz results',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }
}
