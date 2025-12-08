<?php

namespace App\Http\Controllers;

use App\Models\QuizSession;
use App\Models\QuizSessionParticipant;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QuizSessionController extends Controller
{
    /**
     * Create a new quiz session
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quiz_id' => 'nullable|exists:quizzes,id',
            'settings' => 'nullable|array',
            'settings.timer' => 'nullable|integer|min:1',
            'settings.auto_submit' => 'nullable|boolean',
            'settings.show_results' => 'nullable|boolean',
            'settings.show_correct_answer' => 'nullable|boolean',
        ]);

        $session = QuizSession::create([
            'quiz_id' => $validated['quiz_id'] ?? null,
            'teacher_id' => Auth::id(),
            'access_code' => QuizSession::generateAccessCode(),
            'status' => 'waiting',
            'settings' => $validated['settings'] ?? [
                'timer' => 60,
                'auto_submit' => true,
                'show_results' => false,
                'show_correct_answer' => false,
            ],
        ]);

        return response()->json([
            'session' => $session->load('quiz', 'teacher'),
            'message' => 'Session created successfully',
        ], 201);
    }

    /**
     * Join a session with access code
     */
    public function join(Request $request)
    {
        $validated = $request->validate([
            'access_code' => 'required|string|exists:quiz_sessions,access_code',
        ]);

        $session = QuizSession::where('access_code', $validated['access_code'])->first();

        if ($session->isCompleted()) {
            return response()->json([
                'message' => 'This session has already ended',
            ], 400);
        }

        // Check if student already joined
        $participant = QuizSessionParticipant::where('quiz_session_id', $session->id)
            ->where('student_id', Auth::id())
            ->first();

        if (!$participant) {
            $participant = QuizSessionParticipant::create([
                'quiz_session_id' => $session->id,
                'student_id' => Auth::id(),
                'status' => 'joined',
            ]);
        }

        return response()->json([
            'session' => $session->load(['currentQuestion.questionType', 'currentQuestion.options', 'participants.student']),
            'participant' => $participant->load('student'),
            'message' => 'Joined session successfully',
        ]);
    }

    /**
     * Update session state (teacher only)
     */
    public function updateState(Request $request, QuizSession $session)
    {
        // Verify teacher ownership
        if ($session->teacher_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'action' => 'required|in:start,next,end,pause',
            'question_id' => 'nullable|exists:questions,id',
        ]);

        switch ($validated['action']) {
            case 'start':
                $session->update([
                    'status' => 'active',
                    'started_at' => now(),
                ]);
                // Update all participants to active
                $session->participants()->update(['status' => 'active']);
                break;

            case 'next':
                if (!isset($validated['question_id'])) {
                    return response()->json(['message' => 'Question ID required'], 400);
                }
                $session->update([
                    'current_question_id' => $validated['question_id'],
                ]);
                break;

            case 'end':
                $session->update([
                    'status' => 'completed',
                    'ended_at' => now(),
                    'current_question_id' => null,
                ]);
                break;

            case 'pause':
                $session->update([
                    'current_question_id' => null,
                ]);
                break;
        }

        return response()->json([
            'session' => $session->fresh()->load(['currentQuestion.questionType', 'currentQuestion.options', 'participants.student']),
            'message' => 'Session updated successfully',
        ]);
    }

    /**
     * Submit answer (student only)
     */
    public function submitAnswer(Request $request, QuizSession $session)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer' => 'required',
        ]);

        $participant = QuizSessionParticipant::where('quiz_session_id', $session->id)
            ->where('student_id', Auth::id())
            ->first();

        if (!$participant) {
            return response()->json(['message' => 'Not a participant'], 403);
        }

        // Check answer correctness
        $question = Question::with(['options', 'questionType'])->find($validated['question_id']);
        $isCorrect = false;

        if ($question->questionType->slug === 'multiple_choice') {
            $correctOption = $question->options->where('is_correct', true)->first();
            $isCorrect = $correctOption && $validated['answer'] == $correctOption->id;
        } elseif ($question->questionType->slug === 'numeric') {
            $isCorrect = $validated['answer'] == $question->correct_answer;
        }

        // Update score if correct
        if ($isCorrect) {
            $points = $session->settings['points_per_question'] ?? 10;
            $participant->incrementScore($points);
        }

        return response()->json([
            'is_correct' => $isCorrect,
            'participant' => $participant->fresh(),
            'message' => 'Answer submitted successfully',
        ]);
    }

    /**
     * Update session settings (teacher only)
     */
    public function updateSettings(Request $request, QuizSession $session)
    {
        if ($session->teacher_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $session->update([
            'settings' => array_merge($session->settings ?? [], $validated['settings']),
        ]);

        return response()->json([
            'session' => $session,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Get session details
     */
    public function show(QuizSession $session)
    {
        return response()->json([
            'session' => $session->load(['quiz', 'teacher', 'currentQuestion.questionType', 'currentQuestion.options', 'participants.student']),
        ]);
    }

    /**
     * Teacher control page
     */
    public function teacherControl()
    {
        return Inertia::render('QuizManagement/Live/TeacherTestPage');
    }

    /**
     * Student join page
     */
    public function studentJoin()
    {
        return Inertia::render('QuizManagement/Live/StudentPage');
    }
}
