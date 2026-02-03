<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\SkillPracticeSession;
use App\Models\SkillPracticeAnswer;
use App\Models\UserSkillProgress;
use App\Services\AdaptiveQuestionService;
use App\Services\SmartScoreService;
use App\Services\SkillProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SkillPracticeController extends Controller
{
    protected $adaptiveQuestionService;
    protected $smartScoreService;
    protected $skillProgressService;

    public function __construct()
    {
        $this->adaptiveQuestionService = new AdaptiveQuestionService();
        $this->smartScoreService = new SmartScoreService();
        $this->skillProgressService = new SkillProgressService();
    }

    /**
     * Start a new practice session for a skill
     *
     * @param Request $request
     * @param Skill $skill
     * @return \Illuminate\Http\Response
     */
    public function start(Request $request, Skill $skill)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        // Validate that skill exists and is active
        if (!$skill || !$skill->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found or not active'
            ], 404);
        }

        // Create a new practice session
        $session = new SkillPracticeSession([
            'user_id' => $user->id,
            'skill_id' => $skill->id,
            'questions_attempted' => 0,
            'questions_correct' => 0,
            'start_score' => 0,
            'end_score' => 0,
            'time_spent_seconds' => 0,
            'started_at' => now(),
        ]);

        // Get the user's current smart score to store as start score
        $progress = UserSkillProgress::where('user_id', $user->id)
            ->where('skill_id', $skill->id)
            ->first();
        
        if ($progress) {
            $session->start_score = $progress->smart_score;
        }

        $session->save();

        // Get the first question for the session
        $firstQuestion = $this->adaptiveQuestionService->selectNextQuestion($skill->id, $user->id);

        return response()->json([
            'success' => true,
            'session' => $session,
            'first_question' => $firstQuestion ? [
                'id' => $firstQuestion->question->id,
                'question_text' => $firstQuestion->question->question_text,
                'question_type' => $firstQuestion->question->question_type,
                'options' => $firstQuestion->question->options,
                'skill_question_id' => $firstQuestion->id,
                'difficulty_level' => $firstQuestion->pivot->difficulty_level
            ] : null,
            'message' => 'Practice session started successfully'
        ]);
    }

    /**
     * Get the next adaptive question for the practice session
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function nextQuestion(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $request->validate([
            'skill_id' => 'required|exists:skills,id',
            'session_id' => 'required|exists:skill_practice_sessions,id'
        ]);

        $skillId = $request->input('skill_id');
        $sessionId = $request->input('session_id');

        // Verify the session belongs to the user
        $session = SkillPracticeSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid session'
            ], 403);
        }

        // Get the next adaptive question
        $nextQuestion = $this->adaptiveQuestionService->selectNextQuestion($skillId, $user->id);

        if (!$nextQuestion) {
            return response()->json([
                'success' => false,
                'message' => 'No more questions available for this skill'
            ]);
        }

        return response()->json([
            'success' => true,
            'question' => [
                'id' => $nextQuestion->question->id,
                'question_text' => $nextQuestion->question->question_text,
                'question_type' => $nextQuestion->question->question_type,
                'options' => $nextQuestion->question->options,
                'skill_question_id' => $nextQuestion->id,
                'difficulty_level' => $nextQuestion->pivot->difficulty_level
            ]
        ]);
    }

    /**
     * Submit an answer for a question in the practice session
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function submitAnswer(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $request->validate([
            'session_id' => 'required|exists:skill_practice_sessions,id',
            'skill_question_id' => 'required|exists:skill_questions,id',
            'user_answer' => 'required',
            'time_taken_ms' => 'required|integer|min:0'
        ]);

        $sessionId = $request->input('session_id');
        $skillQuestionId = $request->input('skill_question_id');
        $userAnswer = $request->input('user_answer');
        $timeTakenMs = $request->input('time_taken_ms');

        // Verify the session belongs to the user
        $session = SkillPracticeSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid session'
            ], 403);
        }

        // Get the skill question to check the correct answer
        $skillQuestion = \App\Models\SkillQuestion::with('question')->find($skillQuestionId);

        if (!$skillQuestion) {
            return response()->json([
                'success' => false,
                'message' => 'Skill question not found'
            ], 404);
        }

        // Check if the answer is correct
        $isCorrect = $this->checkAnswer($skillQuestion->question, $userAnswer);

        // Calculate the score change
        $difficultyAtTime = $skillQuestion->pivot->difficulty_level;
        $currentStreak = $this->getCurrentStreak($user->id, $session->skill_id);
        
        $scoreChange = $this->smartScoreService->calculateScoreChange(
            $isCorrect, 
            $difficultyAtTime, 
            $currentStreak, 
            $timeTakenMs
        );

        // Save the answer
        $answer = new SkillPracticeAnswer([
            'session_id' => $sessionId,
            'skill_question_id' => $skillQuestionId,
            'user_answer' => $userAnswer,
            'is_correct' => $isCorrect,
            'time_taken_ms' => $timeTakenMs,
            'difficulty_at_time' => $difficultyAtTime,
            'score_change' => $scoreChange
        ]);
        $answer->save();

        // Update session stats
        $session->questions_attempted++;
        if ($isCorrect) {
            $session->questions_correct++;
        }
        $session->save();

        // Update user progress
        $progress = $this->skillProgressService->updateProgress(
            $user->id, 
            $session->skill_id, 
            $isCorrect, 
            $scoreChange, 
            $timeTakenMs, 
            $difficultyAtTime
        );

        // Get next question
        $nextQuestion = $this->adaptiveQuestionService->selectNextQuestion(
            $session->skill_id, 
            $user->id
        );

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'score_change' => $scoreChange,
            'current_smart_score' => $progress->smart_score,
            'streak' => $progress->current_streak,
            'mastery_level' => $progress->mastery_level,
            'session_stats' => [
                'questions_attempted' => $session->questions_attempted,
                'questions_correct' => $session->questions_correct,
                'accuracy' => $session->calculateAccuracy()
            ],
            'next_question' => $nextQuestion ? [
                'id' => $nextQuestion->question->id,
                'question_text' => $nextQuestion->question->question_text,
                'question_type' => $nextQuestion->question->question_type,
                'options' => $nextQuestion->question->options,
                'skill_question_id' => $nextQuestion->id,
                'difficulty_level' => $nextQuestion->pivot->difficulty_level
            ] : null,
            'feedback' => $this->generateFeedback($isCorrect, $skillQuestion)
        ]);
    }

    /**
     * End a practice session
     *
     * @param Request $request
     * @param int $sessionId
     * @return \Illuminate\Http\Response
     */
    public function endSession(Request $request, $sessionId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        // Verify the session belongs to the user
        $session = SkillPracticeSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid session'
            ], 403);
        }

        // Update session to mark as ended
        $session->ended_at = now();
        $session->end_score = 0; // Will be updated to actual final score
        
        // Get final score from user progress
        $finalProgress = UserSkillProgress::where('user_id', $user->id)
            ->where('skill_id', $session->skill_id)
            ->first();
        
        if ($finalProgress) {
            $session->end_score = $finalProgress->smart_score;
        }
        
        $session->save();

        return response()->json([
            'success' => true,
            'session' => $session,
            'final_stats' => [
                'questions_attempted' => $session->questions_attempted,
                'questions_correct' => $session->questions_correct,
                'accuracy' => $session->calculateAccuracy(),
                'time_spent_seconds' => $session->time_spent_seconds,
                'score_change' => $session->end_score - $session->start_score
            ]
        ]);
    }

    /**
     * Check if the user's answer is correct
     *
     * @param \App\Models\QuQuestion $question
     * @param mixed $userAnswer
     * @return bool
     */
    private function checkAnswer($question, $userAnswer)
    {
        // Compare the user's answer with the correct answer
        $correctAnswer = $question->correct_answer;
        
        // Handle different question types
        if (is_array($correctAnswer)) {
            return json_encode($correctAnswer) === json_encode($userAnswer);
        } else {
            return $correctAnswer == $userAnswer;
        }
    }

    /**
     * Get the current streak for a user in a skill
     *
     * @param int $userId
     * @param int $skillId
     * @return int
     */
    private function getCurrentStreak($userId, $skillId)
    {
        $progress = UserSkillProgress::where('user_id', $userId)
            ->where('skill_id', $skillId)
            ->first();
        
        return $progress ? $progress->current_streak : 0;
    }

    /**
     * Generate feedback based on the user's answer
     *
     * @param bool $isCorrect
     * @param \App\Models\SkillQuestion $skillQuestion
     * @return string
     */
    private function generateFeedback($isCorrect, $skillQuestion)
    {
        if ($isCorrect) {
            $positiveFeedback = [
                "Excellent!",
                "Great job!",
                "Perfect!",
                "Well done!",
                "Correct!",
                "Awesome!"
            ];
            return $positiveFeedback[array_rand($positiveFeedback)];
        } else {
            // If there's an explanation, use it
            if (!empty($skillQuestion->pivot->explanation)) {
                return "Incorrect. Explanation: " . $skillQuestion->pivot->explanation;
            }
            return "Incorrect. Try reviewing this concept.";
        }
    }
}