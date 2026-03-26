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

        // Notify teacher that a student has joined
        event(new \App\Events\RealtimeEvent("quiz_{$session->access_code}_teacher", 'STUDENT_JOINED', [
            'student_id' => Auth::id(),
            'name' => Auth::user()->name,
            'status' => 'online'
        ]));

        return response()->json([
            'success' => true,
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
     * Sync current slide across all participants (teacher only)
     */
    public function syncSlide(Request $request, QuizSession $session)
    {
        if ($session->teacher_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'slideIndex' => 'required|integer'
        ]);

        // Fire real-time signal via Laravel Event (which syncs to Firebase)
        event(new \App\Events\RealtimeEvent("quiz_{$session->access_code}", 'SLIDE_CHANGED', [
            'slideIndex' => $validated['slideIndex']
        ]));

        return response()->json(['success' => true]);
    }

    /**
     * Launch ad-hoc quiz from presentation (teacher only)
     */
    public function launchQuiz(Request $request, QuizSession $session)
    {
        if ($session->teacher_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'question' => 'required|string',
            'options' => 'required|array|min:2',
            'correctAnswer' => 'required|integer',
            'duration' => 'required|integer|min:5'
        ]);

        // Create a real question for validation/history
        $qType = \App\Models\QuestionType::where('slug', 'multiple_choice')->first();
        $newQuestion = \App\Models\Question::create([
            'question_text' => $validated['question'],
            'question_type_id' => $qType->id,
            'author_id' => Auth::id(),
            'status' => 'active'
        ]);

        $optionKeys = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($validated['options'] as $idx => $optText) {
            $newQuestion->options()->create([
                'option_key' => $optionKeys[$idx] ?? (string)$idx,
                'option_text' => $optText,
                'is_correct' => $idx === $validated['correctAnswer'],
                'order_index' => $idx
            ]);
        }

        $endTime = now()->addSeconds($validated['duration'])->timestamp * 1000;

        $session->update([
            'current_question_id' => $newQuestion->id,
            'status' => 'active'
        ]);

        // Broadcast to students
        event(new \App\Events\RealtimeEvent("quiz_{$session->access_code}", 'QUIZ_STARTED', [
            'questionId' => $newQuestion->id,
            'endTime' => $endTime
        ]));

        return response()->json([
            'success' => true,
            'endTime' => $endTime
        ]);
    }
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
        $selectedOptionId = null;

        if ($question->questionType->slug === 'multiple_choice') {
            $correctOption = $question->options->where('is_correct', true)->first();
            $selectedOptionId = $validated['answer'];
            $isCorrect = $correctOption && $selectedOptionId == $correctOption->id;
        } elseif ($question->questionType->slug === 'numeric') {
            $isCorrect = $validated['answer'] == $question->correct_answer;
        }

        // 1. Get or create a QuizAttempt for this student/session
        $attempt = QuizAttempt::firstOrCreate(
            [
                'quiz_session_id' => $session->id,
                'user_id' => Auth::id(),
            ],
            [
                'started_at' => now(),
                'total_questions' => 0,
            ]
        );

        // 2. Record the answer in quiz_attempt_answers
        \App\Models\QuizAttemptAnswer::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $validated['question_id'],
            ],
            [
                'selected_option_id' => $selectedOptionId,
                'selected_text' => is_string($validated['answer']) ? $validated['answer'] : null,
                'is_correct' => $isCorrect,
                'answered_at' => now(),
            ]
        );

        // Update score if correct
        if ($isCorrect) {
            $points = $session->settings['points_per_question'] ?? 10;
            $participant->incrementScore($points);
        }

        return response()->json([
            'success' => true,
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
     * Get live stats for a session (teacher only)
     */
    public function getStats(QuizSession $session)
    {
        if ($session->teacher_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $questionId = $session->current_question_id;
        if (!$questionId) {
            return response()->json(['total' => 0, 'stats' => []]);
        }

        $stats = DB::table('quiz_attempt_answers')
            ->join('quiz_attempts', 'quiz_attempt_answers.attempt_id', '=', 'quiz_attempts.id')
            ->join('question_options', 'quiz_attempt_answers.selected_option_id', '=', 'question_options.id')
            ->where('quiz_attempts.quiz_session_id', $session->id)
            ->where('quiz_attempt_answers.question_id', $questionId)
            ->select('question_options.option_text as text', 'quiz_attempt_answers.selected_option_id', DB::raw('count(*) as count'))
            ->groupBy('question_options.option_text', 'quiz_attempt_answers.selected_option_id')
            ->get();

        // Map to format suitable for frontend
        $total = $stats->sum('count');
        
        return response()->json([
            'total' => $total,
            'stats' => $stats,
            'current_question_id' => $questionId
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
     * Teacher remote control page
     */
    public function teacherRemote(Request $request)
    {
        // Try to find an active session for this teacher
        $session = QuizSession::where('teacher_id', Auth::id())
            ->where('status', '!=', 'completed')
            ->latest()
            ->first();

        // If no active session, create a new ad-hoc one
        if (!$session) {
            $session = QuizSession::create([
                'teacher_id' => Auth::id(),
                'access_code' => QuizSession::generateAccessCode(),
                'status' => 'waiting',
                'settings' => [
                    'timer' => 60,
                    'auto_submit' => true,
                    'show_results' => false,
                    'show_correct_answer' => false,
                ],
            ]);
        }

        return Inertia::render(
            'myclass2026/features/cr/classroom_records_v1/peresentation/v5/remote/TeacherPresenter',
            [
                'title' => 'Teacher Live V5',
                'initialSession' => $session->load(['quiz', 'teacher', 'currentQuestion.options', 'participants.student'])
            ]
        );
    }

    /**
     * Student join page
     */
    public function studentJoin()
    {
        return Inertia::render(
            'myclass2026/features/cr/classroom_records_v1/peresentation/v5/remote/StudentInteract',
        );
    }

    /**
     * Debug Firebase connectivity (bypasses event queue)
     */
    public function debugFirebase(Request $request)
    {
        $validated = $request->validate([
            'access_code' => 'required|string',
        ]);

        $service = app(\App\Services\RealtimeNotificationService::class);
        $result = $service->notifyWithDetails("quiz_{$validated['access_code']}_teacher", [
            'event' => 'DEBUG_PING',
            'context' => [
                'message' => 'Manual debug ping from Laravel',
                'timestamp' => now()->toDateTimeString(),
            ]
        ]);

        return response()->json($result);
    }
}
