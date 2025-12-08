<?php

namespace App\Services;

use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class QuizService
{
    /**
     * Cache TTL constants (in seconds)
     */
    const CACHE_TTL_QUESTIONS = 3600; // 1 hour
    const CACHE_TTL_QUESTION_TYPES = 86400; // 24 hours
    const CACHE_TTL_USER_ATTEMPTS = 300; // 5 minutes

    /**
     * Get all question types with caching.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getQuestionTypes()
    {
        return Cache::remember('question_types:all', self::CACHE_TTL_QUESTION_TYPES, function () {
            return QuestionType::all();
        });
    }

    /**
     * Get questions by IDs with caching.
     * 
     * @param array $questionIds Array of question IDs
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getQuestionsByIds(array $questionIds)
    {
        // Create a cache key based on sorted question IDs
        $cacheKey = 'questions:' . md5(implode(',', sort($questionIds)));
        
        return Cache::remember($cacheKey, self::CACHE_TTL_QUESTIONS, function () use ($questionIds) {
            return Question::with(['questionType', 'options' => function ($query) {
                $query->orderBy('order_index');
            }])
            ->whereIn('id', $questionIds)
            ->where('status', 'active')
            ->get();
        });
    }

    /**
     * Get recent quiz attempts for a user with caching.
     * 
     * @param User $user The user
     * @param int $limit Number of attempts to retrieve
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserRecentAttempts(User $user, int $limit = 10)
    {
        $cacheKey = "user:{$user->id}:attempts:recent:{$limit}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL_USER_ATTEMPTS, function () use ($user, $limit) {
            return QuizAttempt::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->orderBy('completed_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Invalidate question cache.
     * 
     * @param int|array $questionIds Question ID(s) to invalidate
     * @return void
     */
    public function invalidateQuestionCache($questionIds): void
    {
        if (!is_array($questionIds)) {
            $questionIds = [$questionIds];
        }

        // Invalidate the specific questions cache
        $cacheKey = 'questions:' . md5(implode(',', sort($questionIds)));
        Cache::forget($cacheKey);

        Log::debug('Question cache invalidated', ['question_ids' => $questionIds]);
    }

    /**
     * Invalidate question types cache.
     * 
     * @return void
     */
    public function invalidateQuestionTypesCache(): void
    {
        Cache::forget('question_types:all');
        Log::debug('Question types cache invalidated');
    }

    /**
     * Invalidate user attempts cache.
     * 
     * @param int $userId User ID
     * @return void
     */
    public function invalidateUserAttemptsCache(int $userId): void
    {
        // Clear all cached attempts for this user
        // We need to clear all possible limit variations
        for ($limit = 1; $limit <= 50; $limit++) {
            Cache::forget("user:{$userId}:attempts:recent:{$limit}");
        }

        Log::debug('User attempts cache invalidated', ['user_id' => $userId]);
    }

    /**
     * Start a new quiz attempt for a user.
     * 
     * @param User $user The user taking the quiz
     * @param array $questionIds Array of question IDs for this quiz
     * @param int|null $quizId Optional quiz ID if this is part of a formal quiz
     * @return QuizAttempt The created quiz attempt
     * @throws \Exception If the transaction fails, it will be rolled back automatically
     */
    public function startAttempt(User $user, array $questionIds, ?int $quizId = null): QuizAttempt
    {
        // Transaction ensures atomicity - if any operation fails, all changes are rolled back
        return DB::transaction(function () use ($user, $questionIds, $quizId) {
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quizId,
                'started_at' => now(),
                'total_questions' => count($questionIds),
                'correct_answers' => 0,
                'percentage' => 0,
                'metadata' => [
                    'question_ids' => $questionIds,
                    'started_at_timestamp' => now()->timestamp,
                ],
            ]);

            Log::info('Quiz attempt started', [
                'attempt_id' => $attempt->id,
                'user_id' => $user->id,
                'total_questions' => count($questionIds),
            ]);

            // Invalidate user attempts cache
            $this->invalidateUserAttemptsCache($user->id);

            return $attempt;
        });
    }

    /**
     * Submit an answer for a question in a quiz attempt.
     * 
     * @param QuizAttempt $attempt The quiz attempt
     * @param Question $question The question being answered
     * @param int|null $selectedOptionId The ID of the selected option (for option-based questions)
     * @param string|null $selectedText The text answer (for text-based questions)
     * @param int $timeSpentSec Time spent on this question in seconds
     * @return QuizAttemptAnswer The created answer record
     * @throws \Exception If the transaction fails, it will be rolled back automatically
     */
    public function submitAnswer(
        QuizAttempt $attempt,
        Question $question,
        ?int $selectedOptionId = null,
        ?string $selectedText = null,
        int $timeSpentSec = 0
    ): QuizAttemptAnswer {
        // Transaction ensures atomicity - if any operation fails, all changes are rolled back
        return DB::transaction(function () use ($attempt, $question, $selectedOptionId, $selectedText, $timeSpentSec) {
            // Determine if the answer is correct
            $isCorrect = false;
            
            if ($selectedOptionId) {
                // For option-based questions
                $option = QuestionOption::find($selectedOptionId);
                $isCorrect = $option ? $option->is_correct : false;
            } elseif ($selectedText !== null) {
                // For text-based questions, we'll need to implement comparison logic
                // For now, we'll mark as false and let the teacher grade it
                $isCorrect = false;
            }

            // Create or update the answer record
            $answer = QuizAttemptAnswer::updateOrCreate(
                [
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                ],
                [
                    'selected_option_id' => $selectedOptionId,
                    'selected_text' => $selectedText,
                    'is_correct' => $isCorrect,
                    'time_spent_sec' => $timeSpentSec,
                    'answered_at' => now(),
                ]
            );

            Log::info('Answer submitted', [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'is_correct' => $isCorrect,
            ]);

            return $answer;
        });
    }

    /**
     * Complete a quiz attempt and calculate final results.
     * 
     * @param QuizAttempt $attempt The quiz attempt to complete
     * @return QuizAttempt The updated quiz attempt with results
     * @throws \Exception If the transaction fails, it will be rolled back automatically
     */
    public function completeAttempt(QuizAttempt $attempt): QuizAttempt
    {
        // Transaction ensures atomicity - if any operation fails, all changes are rolled back
        return DB::transaction(function () use ($attempt) {
            // Mark the attempt as completed
            $attempt->update([
                'completed_at' => now(),
            ]);

            // Calculate the results
            $attempt->calculateResults();

            // Update question analytics for all answered questions
            $this->updateQuestionAnalytics($attempt);

            Log::info('Quiz attempt completed', [
                'attempt_id' => $attempt->id,
                'correct_answers' => $attempt->correct_answers,
                'percentage' => $attempt->percentage,
            ]);

            // Invalidate user attempts cache
            $this->invalidateUserAttemptsCache($attempt->user_id);

            return $attempt->fresh();
        });
    }

    /**
     * Update analytics data for all questions in a completed quiz attempt.
     * 
     * @param QuizAttempt $attempt The completed quiz attempt
     * @return void
     * @throws \Exception If the transaction fails, it will be rolled back automatically
     */
    public function updateQuestionAnalytics(QuizAttempt $attempt): void
    {
        // Transaction ensures atomicity - if any operation fails, all changes are rolled back
        DB::transaction(function () use ($attempt) {
            $answers = $attempt->answers()->with('question')->get();

            foreach ($answers as $answer) {
                $question = $answer->question;
                
                if (!$question) {
                    continue;
                }

                // Increment usage count
                $question->increment('usage_count');

                // Recalculate average success rate
                $totalAttempts = QuizAttemptAnswer::where('question_id', $question->id)->count();
                $correctAttempts = QuizAttemptAnswer::where('question_id', $question->id)
                    ->where('is_correct', true)
                    ->count();

                if ($totalAttempts > 0) {
                    $question->avg_success_rate = ($correctAttempts / $totalAttempts) * 100;
                }

                // Calculate discrimination index if we have enough data
                if ($totalAttempts >= 10) {
                    $discriminationIndex = $this->calculateDiscriminationIndex($question);
                    $question->discrimination_index = $discriminationIndex;
                }

                $question->save();

                Log::debug('Question analytics updated', [
                    'question_id' => $question->id,
                    'usage_count' => $question->usage_count,
                    'avg_success_rate' => $question->avg_success_rate,
                ]);

                // Invalidate question cache when analytics are updated
                $this->invalidateQuestionCache($question->id);
            }
        });
    }

    /**
     * Calculate the discrimination index for a question.
     * 
     * The discrimination index measures how well a question differentiates
     * between high and low performers. It ranges from -1 to 1, where:
     * - Values near 1 indicate the question discriminates well (high performers answer correctly)
     * - Values near 0 indicate poor discrimination
     * - Negative values indicate reverse discrimination (low performers do better)
     * 
     * @param Question $question The question to calculate discrimination index for
     * @return float|null The discrimination index, or null if insufficient data
     */
    protected function calculateDiscriminationIndex(Question $question): ?float
    {
        // Get all quiz attempts that included this question
        $answers = QuizAttemptAnswer::where('question_id', $question->id)
            ->with('attempt')
            ->get();

        if ($answers->count() < 10) {
            return null;
        }

        // Sort attempts by overall quiz performance (percentage)
        $sortedAnswers = $answers->sortByDesc(function ($answer) {
            return $answer->attempt->percentage ?? 0;
        });

        // Split into top 27% and bottom 27% (standard practice)
        $groupSize = (int) ceil($sortedAnswers->count() * 0.27);
        $topGroup = $sortedAnswers->take($groupSize);
        $bottomGroup = $sortedAnswers->reverse()->take($groupSize);

        // Calculate proportion correct in each group
        $topCorrect = $topGroup->where('is_correct', true)->count();
        $bottomCorrect = $bottomGroup->where('is_correct', true)->count();

        $topProportion = $groupSize > 0 ? $topCorrect / $groupSize : 0;
        $bottomProportion = $groupSize > 0 ? $bottomCorrect / $groupSize : 0;

        // Discrimination index = proportion_top - proportion_bottom
        return $topProportion - $bottomProportion;
    }

    /**
     * Get quiz results for a completed attempt.
     * 
     * @param QuizAttempt $attempt The quiz attempt
     * @return array Structured quiz results
     */
    public function getQuizResults(QuizAttempt $attempt): array
    {
        $answers = $attempt->answers()
            ->with(['question.options', 'selectedOption'])
            ->get();

        $answerRecords = $answers->map(function ($answer) {
            $correctOption = $answer->question->options->firstWhere('is_correct', true);
            
            return [
                'question_id' => $answer->question_id,
                'question_text' => $answer->question->question_text,
                'selected_option_id' => $answer->selected_option_id,
                'selected_text' => $answer->selectedOption?->option_text ?? $answer->selected_text,
                'is_correct' => $answer->is_correct,
                'correct_option_text' => $correctOption?->option_text,
                'time_spent_sec' => $answer->time_spent_sec,
                'answered_at' => $answer->answered_at,
            ];
        });

        return [
            'attempt_id' => $attempt->id,
            'total_questions' => $attempt->total_questions,
            'correct_answers' => $attempt->correct_answers,
            'percentage' => $attempt->percentage,
            'started_at' => $attempt->started_at,
            'completed_at' => $attempt->completed_at,
            'answers' => $answerRecords,
            'metadata' => $attempt->metadata,
        ];
    }
}
