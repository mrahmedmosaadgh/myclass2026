<?php

namespace App\Http\Controllers;

use App\Models\QuExam;
use App\Models\QuQuestion;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\User;
use App\Models\QuAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuExamController extends Controller
{
    /**
     * Display a listing of exams.
     */
    public function index(Request $request)
    {
        $query = QuExam::with(['subject', 'creator', 'questions'])
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->exam_type, fn($q) => $q->where('exam_type', $request->exam_type))
            ->when($request->custom_group, fn($q) => $q->where('custom_group', $request->custom_group))
            ->when($request->status, function($q) use ($request) {
                $now = now();
                switch ($request->status) {
                    case 'draft':
                        return $q->where('is_published', false);
                    case 'upcoming':
                        return $q->where('is_published', true)->where('start_date', '>', $now);
                    case 'active':
                        return $q->where('is_published', true)
                            ->where(function($q) use ($now) {
                                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
                            })
                            ->where(function($q) use ($now) {
                                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
                            });
                    case 'ended':
                        return $q->where('is_published', true)->where('end_date', '<', $now);
                }
            })
            ->latest();

        // Get distinct custom groups for filter
        $customGroups = QuExam::distinct()
            ->whereNotNull('custom_group')
            ->pluck('custom_group')
            ->sort()
            ->values();
        
        // Get teacher's classrooms only
        $user = auth()->user();
        $classrooms = collect();
        
        if ($user && $user->teacher) {
            $classrooms = $user->teacher->classrooms()->load('grade');
        }

        return Inertia::render('my_class/QuQuestionBankSystem/QuExamList', [
            'exams' => $query->paginate(20)->through(function ($exam) {
                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'description' => $exam->description,
                    'subject' => $exam->subject,
                    'exam_type' => $exam->exam_type,
                    'custom_group' => $exam->custom_group,
                    'duration_minutes' => $exam->duration_minutes,
                    'total_marks' => $exam->total_marks,
                    'passing_score' => $exam->passing_score,
                    'questions_count' => $exam->questions->count(),
                    'bloom_distribution' => $exam->bloom_distribution,
                    'is_published' => $exam->is_published,
                    'start_date' => $exam->start_date,
                    'end_date' => $exam->end_date,
                    'status' => $exam->getStatus(),
                    'created_at' => $exam->created_at,
                ];
            }),
            'subjects' => Subject::all(),
            'grades' => Grade::all(['id', 'name']),
            'classrooms' => $classrooms,
            'customGroups' => $customGroups,
            'examTypes' => ['practice', 'quiz', 'midterm', 'final', 'survey'],
            'markCalculationMethods' => ['last', 'best', 'average'],
            'publishResultsTimings' => ['immediate', 'after_end', 'manual'],
            'filters' => $request->only(['subject_id', 'exam_type', 'custom_group', 'status'])
        ]);
    }

    /**
     * Show the form for creating a new exam.
     */
    public function create()
    {
        $customGroups = QuExam::distinct()->whereNotNull('custom_group')->pluck('custom_group');
        
        // Get teacher's classrooms only
        $user = auth()->user();
        $classrooms = collect();
        
        if ($user && $user->teacher) {
            $classrooms = $user->teacher->classrooms()->load('grade');
        }

        return Inertia::render('my_class/QuQuestionBankSystem/QuExamForm', [
            'subjects' => Subject::all(),
            'grades' => Grade::all(['id', 'name']),
            'classrooms' => $classrooms,
            'customGroups' => $customGroups,
            'examTypes' => ['practice', 'quiz', 'midterm', 'final', 'survey'],
            'markCalculationMethods' => ['last', 'best', 'average'],
            'publishResultsTimings' => ['immediate', 'after_end', 'manual'],
        ]);
    }

    /**
     * Store a newly created exam.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'exam_type' => 'required|in:practice,quiz,midterm,final,survey',
            'custom_group' => 'nullable|string|max:100',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'passing_score' => 'nullable|numeric|min:0|max:100',
            'max_attempts' => 'nullable|integer|min:1',
            'mark_calculation_method' => 'required|in:last,best,average',
            'start_date' => 'nullable|date|before:end_date',
            'end_date' => 'nullable|date|after:start_date',
            'publish_results_timing' => 'required|in:immediate,after_end,manual',
            'bloom_distribution' => 'nullable|array',
            'bloom_distribution.remember' => 'integer|min:0',
            'bloom_distribution.understand' => 'integer|min:0',
            'bloom_distribution.apply' => 'integer|min:0',
            'bloom_distribution.analyze' => 'integer|min:0',
            'bloom_distribution.evaluate' => 'integer|min:0',
            'bloom_distribution.create' => 'integer|min:0',
            'question_ids' => 'required_without:bloom_distribution|array',
            'question_ids.*' => 'exists:qu_questions,id',
            'is_published' => 'boolean',
            'settings' => 'nullable|array',
            'settings.shuffle_questions' => 'boolean',
            'settings.shuffle_options' => 'boolean',
            'settings.allow_print' => 'boolean',
            'target_audience' => 'nullable|array',
            'target_audience.roles' => 'nullable|array',
            'target_audience.grade_ids' => 'nullable|array',
            'target_audience.classroom_ids' => 'nullable|array',
            'target_audience.user_ids' => 'nullable|array',
        ]);

        $validated['created_by'] = auth()->id();
        $exam = QuExam::create($validated);

        // If Bloom distribution provided, auto-select questions
        if (!empty($validated['bloom_distribution'])) {
            $questionIds = $this->selectQuestionsByBloom(
                $validated['subject_id'],
                $validated['bloom_distribution']
            );
        } else {
            $questionIds = $validated['question_ids'] ?? [];
        }

        if (!empty($questionIds)) {
            $exam->questions()->attach($questionIds);
        }

        return redirect()->route('qu-exams.index')
            ->with('success', 'Exam created successfully');
    }

    /**
     * Display the specified exam.
     */
    public function show(QuExam $exam)
    {
        $exam->load(['subject', 'creator', 'questions']);

        return Inertia::render('my_class/QuQuestionBankSystem/QuExamShow', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'subject' => $exam->subject,
                'exam_type' => $exam->exam_type,
                'duration_minutes' => $exam->duration_minutes,
                'total_marks' => $exam->total_marks,
                'passing_score' => $exam->passing_score,
                'questions' => $exam->questions,
                'is_published' => $exam->is_published,
                'start_date' => $exam->start_date,
                'end_date' => $exam->end_date,
            ]
        ]);
    }

    /**
     * Show the form for editing.
     */
    public function edit(QuExam $exam)
    {
        $exam->load(['subject', 'questions']);
        
        if (request()->wantsJson() && !request()->header('X-Inertia')) {
            return response()->json($exam);
        }

        $customGroups = QuExam::distinct()->whereNotNull('custom_group')->pluck('custom_group');
        
        // Get teacher's classrooms only
        $user = auth()->user();
        $classrooms = collect();
        
        if ($user && $user->teacher) {
            $classrooms = $user->teacher->classrooms()->load('grade');
        }

        return Inertia::render('my_class/QuQuestionBankSystem/QuExamForm', [
            'exam' => $exam,
            'subjects' => Subject::all(),
            'grades' => Grade::all(['id', 'name']),
            'classrooms' => $classrooms,
            'customGroups' => $customGroups,
            'examTypes' => ['practice', 'quiz', 'midterm', 'final', 'survey'],
            'markCalculationMethods' => ['last', 'best', 'average'],
            'publishResultsTimings' => ['immediate', 'after_end', 'manual'],
        ]);
    }

    /**
     * Update the specified exam.
     */
    public function update(Request $request, QuExam $exam)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'exam_type' => 'required|in:practice,quiz,midterm,final,survey',
            'custom_group' => 'nullable|string|max:100',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'passing_score' => 'nullable|numeric|min:0|max:100',
            'max_attempts' => 'nullable|integer|min:1',
            'mark_calculation_method' => 'required|in:last,best,average',
            'start_date' => 'nullable|date|before:end_date',
            'end_date' => 'nullable|date|after:start_date',
            'publish_results_timing' => 'required|in:immediate,after_end,manual',
            'bloom_distribution' => 'nullable|array',
            'question_ids' => 'required_without:bloom_distribution|array',
            'is_published' => 'boolean',
            'settings' => 'nullable|array',
            'settings.shuffle_questions' => 'boolean',
            'settings.shuffle_options' => 'boolean',
            'settings.allow_print' => 'boolean',
            'target_audience' => 'nullable|array',
            'target_audience.roles' => 'nullable|array',
            'target_audience.grade_ids' => 'nullable|array',
            'target_audience.classroom_ids' => 'nullable|array',
            'target_audience.user_ids' => 'nullable|array',
        ]);

        $exam->update($validated);

        // Sync questions
        if (!empty($validated['bloom_distribution'])) {
            $questionIds = $this->selectQuestionsByBloom(
                $validated['subject_id'],
                $validated['bloom_distribution']
            );
        } else {
            $questionIds = $validated['question_ids'] ?? [];
        }

        $exam->questions()->sync($questionIds);

        return redirect()->route('qu-exams.index')
            ->with('success', 'Exam updated successfully');
    }

    /**
     * Get available questions for manual selection.
     */
    public function getAvailableQuestions(Request $request)
    {
        $query = QuQuestion::with(['subject', 'topic'])
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->difficulty, fn($q) => $q->where('difficulty', $request->difficulty))
            ->when($request->bloom_level, fn($q) => $q->where('bloom_level', $request->bloom_level))
            ->when($request->topic_id, fn($q) => $q->where('topic_id', $request->topic_id))
            ->when($request->question_type, fn($q) => $q->where('question_type', $request->question_type))
            ->when($request->search, fn($q) => $q->where('question_text', 'like', '%' . $request->search . '%'));

        return response()->json([
            'questions' => $query->paginate(50)
        ]);
    }

    /**
     * Remove the specified exam.
     */
    public function destroy(QuExam $exam)
    {
        $exam->delete();

        return redirect()->route('qu-exams.index')
            ->with('success', 'Exam deleted successfully');
    }

    /**
     * Auto-select questions based on Bloom distribution.
     */
    private function selectQuestionsByBloom($subjectId, $bloomDistribution)
    {
        $selectedIds = [];
        
        foreach ($bloomDistribution as $level => $count) {
            if ($count > 0) {
                $questions = QuQuestion::where('subject_id', $subjectId)
                    ->where('bloom_level', $level)
                    ->inRandomOrder()
                    ->limit($count)
                    ->pluck('id')
                    ->toArray();
                
                $selectedIds = array_merge($selectedIds, $questions);
            }
        }
        
        return $selectedIds;
    }

    /**
     * Show grading interface for teachers.
     */
    /**
     * Teacher: List all attempts requiring grading
     */
    public function teacherGradingIndex(Request $request)
    {
        $query = QuAttempt::with(['exam', 'user'])
            ->whereHas('exam', function($q) {
                $q->where('created_by', auth()->id());
            })
            ->where('completed_at', '!=', null);

        // Filter by exam
        if ($request->filled('exam_id')) {
            $query->where('qu_exam_id', $request->exam_id);
        }

        // Filter by grading status
        if ($request->filled('grading_status')) {
            $query->where('grading_status', $request->grading_status);
        }

        $attempts = $query->latest('completed_at')->paginate(20);

        return Inertia::render('my_class/QuQuestionBankSystem/QuGradingList', [
            'attempts' => $attempts,
            'exams' => QuExam::where('created_by', auth()->id())->get(['id', 'title']),
            'filters' => $request->only(['exam_id', 'grading_status'])
        ]);
    }

    /**
     * Teacher: Show grading interface for specific attempt
     */
    public function teacherGradeAttempt(QuAttempt $attempt)
    {
        $attempt->load([
            'exam',
            'user',
            'answers.question'
        ]);

        // Separate auto-graded and manual questions
        $autoGraded = [];
        $manualQuestions = [];

        foreach ($attempt->answers as $answer) {
            $question = $answer->question;
            if (in_array($question->question_type, ['mcq', 'true_false'])) {
                $autoGraded[] = [
                    'question' => $question,
                    'answer' => $answer,
                    'is_correct' => $answer->marks_obtained == $question->marks
                ];
            } else {
                $manualQuestions[] = [
                    'question' => $question,
                    'answer' => $answer
                ];
            }
        }

        return Inertia::render('my_class/QuQuestionBankSystem/QuGrading', [
            'attempt' => $attempt,
            'autoGraded' => $autoGraded,
            'manualQuestions' => $manualQuestions,
            'totalMarks' => $attempt->exam->total_marks
        ]);
    }

    /**
     * Teacher: Save grades for an attempt
     */
    public function saveGrades(Request $request, QuAttempt $attempt)
    {
        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.answer_id' => 'required|exists:qu_answers,id',
            'grades.*.marks_obtained' => 'required|numeric|min:0',
            'grades.*.feedback' => 'nullable|string|max:1000'
        ]);

        foreach ($validated['grades'] as $gradeData) {
            $answer = QuAnswer::find($gradeData['answer_id']);
            
            // Validate marks don't exceed question marks
            if ($gradeData['marks_obtained'] > $answer->question->marks) {
                return back()->withErrors([
                    'grades' => 'Marks awarded cannot exceed question marks'
                ]);
            }

            $answer->update([
                'marks_obtained' => $gradeData['marks_obtained'],
                'feedback' => $gradeData['feedback'] ?? null,
                'graded_at' => now(),
                'graded_by' => auth()->id()
            ]);
        }

        // Recalculate total score
        $attempt->recalculateScore();

        return back()->with('success', 'Grades saved successfully');
    }

    /**
     * Student: List available exams.
     */
    public function studentIndex(Request $request)
    {
        $user = auth()->user();
        
        $exams = QuExam::with(['subject', 'attempts' => function($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->where('is_published', true)
            ->forUser($user)  // Apply access control scope
            ->get()
            ->map(function ($exam) use ($user) {
                // Calculate attempt statistics
                $completedAttempts = $exam->attempts->where('completed_at', '!=', null);
                $inProgressAttempt = $exam->attempts->firstWhere('completed_at', null);
                
                $attemptStats = [
                    'total_attempts' => $exam->attempts->count(),
                    'completed_attempts' => $completedAttempts->count(),
                    'has_in_progress' => $inProgressAttempt !== null,
                    'in_progress_attempt_id' => $inProgressAttempt?->id,
                    'best_score' => $completedAttempts->max('score'),
                    'average_score' => $completedAttempts->avg('score'),
                    'last_score' => $completedAttempts->sortByDesc('completed_at')->first()?->score,
                    'last_attempt_date' => $completedAttempts->sortByDesc('completed_at')->first()?->completed_at,
                ];

                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'description' => $exam->description,
                    'subject' => $exam->subject,
                    'exam_type' => $exam->exam_type,
                    'duration_minutes' => $exam->duration_minutes,
                    'total_marks' => $exam->total_marks,
                    'passing_score' => $exam->passing_score,
                    'max_attempts' => $exam->max_attempts,
                    'start_date' => $exam->start_date,
                    'end_date' => $exam->end_date,
                    'status' => $exam->getStatus(),
                    'is_available' => $exam->isAvailable(),
                    'attempts_count' => $exam->attempts->count(),
                    'remaining_attempts' => $exam->getRemainingAttempts($user),
                    'attempt_stats' => $attemptStats,
                    'settings' => $exam->settings,
                ];
            });

        return Inertia::render('my_class/QuQuestionBankSystem/QuStudentExamList', [
            'exams' => $exams,
            'subjects' => Subject::all(),
            'filters' => $request->only(['subject_id'])
        ]);
    }

    /**
     * Student: Show exam details before starting.
     */
    public function studentShow(QuExam $quExam)
    {
        $userId = auth()->id();
        
        if (!$quExam->isAvailable()) {
            return redirect()->route('qu-student.exams.index')
                ->with('error', 'This exam is not currently available.');
        }

        $quExam->load(['subject', 'attempts' => function($q) use ($userId) {
            $q->where('user_id', $userId)->latest();
        }]);

        $remainingAttempts = $quExam->getRemainingAttempts($userId);
        $inProgressAttempt = $quExam->attempts->firstWhere('completed_at', null);

        return Inertia::render('my_class/QuQuestionBankSystem/QuExamDetails', [
            'exam' => [
                'id' => $quExam->id,
                'title' => $quExam->title,
                'description' => $quExam->description,
                'subject' => $quExam->subject,
                'exam_type' => $quExam->exam_type,
                'duration_minutes' => $quExam->duration_minutes,
                'total_marks' => $quExam->total_marks,
                'passing_score' => $quExam->passing_score,
                'max_attempts' => $quExam->max_attempts,
                'start_date' => $quExam->start_date,
                'end_date' => $quExam->end_date,
                'questions_count' => $quExam->questions()->count(),
            ],
            'remaining_attempts' => $remainingAttempts,
            'previous_attempts' => $quExam->attempts->where('completed_at', '!=', null)->map(function($attempt) {
                return [
                    'id' => $attempt->id,
                    'score' => $attempt->score,
                    'started_at' => $attempt->started_at,
                    'completed_at' => $attempt->completed_at,
                ];
            })->values(),
            'in_progress_attempt' => $inProgressAttempt ? [
                'id' => $inProgressAttempt->id,
                'started_at' => $inProgressAttempt->started_at,
            ] : null,
        ]);
    }

    /**
     * Student: Start a new exam attempt.
     */
    public function startExam(QuExam $quExam)
    {
        $userId = auth()->id();

        // Validate exam is available
        if (!$quExam->isAvailable()) {
            return back()->with('error', 'This exam is not currently available.');
        }

        // Check for existing in-progress attempt
        $existingAttempt = QuAttempt::where('qu_exam_id', $quExam->id)
            ->where('user_id', $userId)
            ->whereNull('completed_at')
            ->first();

        if ($existingAttempt) {
            return redirect()->route('qu-student.exams.take', [
                'quExam' => $quExam->id,
                'quAttempt' => $existingAttempt->id
            ]);
        }

        // Check remaining attempts
        $remainingAttempts = $quExam->getRemainingAttempts($userId);
        if ($remainingAttempts !== null && $remainingAttempts <= 0) {
            return back()->with('error', 'You have no remaining attempts for this exam.');
        }

        // Create new attempt
        $attempt = QuAttempt::create([
            'qu_exam_id' => $quExam->id,
            'user_id' => $userId,
            'started_at' => now(),
        ]);

        return redirect()->route('qu-student.exams.take', [
            'quExam' => $quExam->id,
            'quAttempt' => $attempt->id
        ]);
    }

    /**
     * Student: Take exam interface.
     */
    public function takeExam(QuExam $quExam, QuAttempt $quAttempt)
    {
        // Validate attempt belongs to current user
        if ($quAttempt->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this exam attempt.');
        }

        // Validate attempt is not completed
        if ($quAttempt->isCompleted()) {
            return redirect()->route('qu-student.exams.results', [
                'quExam' => $quExam->id,
                'quAttempt' => $quAttempt->id
            ]);
        }

        // Load exam with questions and existing answers
        $quExam->load(['subject', 'questions']);
        $quAttempt->load('answers');

        // Calculate time remaining
        $elapsedSeconds = now()->diffInSeconds($quAttempt->started_at);
        $totalSeconds = $quExam->duration_minutes * 60;
        $remainingSeconds = max(0, $totalSeconds - $elapsedSeconds);

        // If time expired, auto-submit
        if ($remainingSeconds <= 0) {
            $this->submitExam(new Request(), $quExam, $quAttempt);
            return redirect()->route('qu-student.exams.results', [
                'quExam' => $quExam->id,
                'quAttempt' => $quAttempt->id
            ])->with('info', 'Exam time expired. Your answers have been submitted.');
        }

        // Format existing answers for frontend
        $existingAnswers = $quAttempt->answers->mapWithKeys(function($answer) {
            return [
                $answer->qu_question_id => [
                    'selected_options' => $answer->selected_options ?? [],
                    'answer_text' => $answer->answer_text ?? '',
                ]
            ];
        });

        return Inertia::render('my_class/QuQuestionBankSystem/QuTakeExam', [
            'exam' => [
                'id' => $quExam->id,
                'title' => $quExam->title,
                'subject' => $quExam->subject,
                'duration_minutes' => $quExam->duration_minutes,
                'total_marks' => $quExam->total_marks,
            ],
            'attempt' => [
                'id' => $quAttempt->id,
                'started_at' => $quAttempt->started_at,
                'remaining_seconds' => $remainingSeconds,
            ],
            'questions' => $quExam->questions->when(
                data_get($quExam->settings, 'shuffle_questions'), 
                fn($q) => $q->shuffle()
            )->values()->map(function($question) use ($quExam) {
                // Shuffle options if setting enabled
                $options = $question->options;
                if (data_get($quExam->settings, 'shuffle_options') && is_array($options)) {
                    // Transform object to array of {key, value} objects
                    $normalizedOptions = [];
                    foreach ($options as $key => $value) {
                        $normalizedOptions[] = ['key' => $key, 'value' => $value];
                    }
                    
                    // Shuffle the array
                    shuffle($normalizedOptions);
                    $options = $normalizedOptions;
                }

                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'options' => $options,
                    'marks' => $question->marks,
                    'difficulty' => $question->difficulty,
                    'bloom_level' => $question->bloom_level,
                ];
            }),
            'existing_answers' => $existingAnswers,
        ]);
    }

    /**
     * Student: Auto-save answers.
     */
    public function autoSave(Request $request, QuExam $quExam, QuAttempt $quAttempt)
    {
        // Validate ownership and status
        if ($quAttempt->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($quAttempt->isCompleted()) {
            return response()->json(['error' => 'Exam already completed'], 400);
        }

        $answers = $request->input('answers', []);
        
        foreach ($answers as $questionId => $answerData) {
            \App\Models\QuAnswer::updateOrCreate(
                [
                    'qu_attempt_id' => $quAttempt->id,
                    'qu_question_id' => $questionId
                ],
                [
                    'selected_options' => $answerData['selected_options'] ?? null,
                    'answer_text' => $answerData['answer_text'] ?? null,
                ]
            );
        }

        return response()->json(['success' => true, 'saved_at' => now()]);
    }

    /**
     * Student: Submit exam.
     */
    public function submitExam(Request $request, QuExam $quExam, QuAttempt $quAttempt)
    {
        // Validate ownership and status
        if ($quAttempt->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($quAttempt->isCompleted()) {
            return redirect()->route('qu-student.exams.results', [
                'quExam' => $quExam->id,
                'quAttempt' => $quAttempt->id
            ]);
        }

        // Save final answers if provided
        $answers = $request->input('answers', []);
        foreach ($answers as $questionId => $answerData) {
            \App\Models\QuAnswer::updateOrCreate(
                [
                    'qu_attempt_id' => $quAttempt->id,
                    'qu_question_id' => $questionId
                ],
                [
                    'selected_options' => $answerData['selected_options'] ?? null,
                    'answer_text' => $answerData['answer_text'] ?? null,
                ]
            );
        }

        // Auto-grade MCQ and True/False questions
        $quExam->load('questions');
        $quAttempt->load('answers');
        
        foreach ($quAttempt->answers as $answer) {
            $question = $quExam->questions->firstWhere('id', $answer->qu_question_id);
            
            if (!$question) continue;

            // Auto-grade MCQ and True/False
            if (in_array($question->question_type, ['mcq', 'true_false'])) {
                $isCorrect = false;
                
                if ($question->question_type === 'mcq') {
                    // Skip if no correct answer is set or student didn't answer
                    if (!$question->correct_answer || !$answer->selected_options) {
                        $answer->marks_obtained = 0;
                        $answer->save();
                        continue;
                    }
                    
                    // correct_answer is stored as JSON array, e.g., ["A"] or ["A", "C"]
                    // Get the first element if it's an array, otherwise use as-is
                    $correctAnswer = is_array($question->correct_answer) 
                        ? trim($question->correct_answer[0] ?? '') 
                        : trim((string) $question->correct_answer);
                    
                    // Student answer is a string like "A"
                    $studentAnswer = trim((string) $answer->selected_options);
                    
                    $isCorrect = $studentAnswer === $correctAnswer;
                } elseif ($question->question_type === 'true_false') {
                    $studentAnswer = $answer->selected_options[0] ?? null;
                    $isCorrect = $studentAnswer == $question->correct_answer;
                }
                
                $answer->marks_obtained = $isCorrect ? $question->marks : 0;
                $answer->save();
            }
        }

        // Calculate total score
        $totalScore = $quAttempt->answers()->sum('marks_obtained');
        
        // Mark as completed
        $quAttempt->update([
            'completed_at' => now(),
            'score' => $totalScore,
        ]);

        return redirect()->route('qu-student.exams.results', [
            'quExam' => $quExam->id,
            'quAttempt' => $quAttempt->id
        ])->with('success', 'Exam submitted successfully!');
    }

    /**
     * Student: View exam results.
     */
    public function viewResults(QuExam $quExam, QuAttempt $quAttempt)
    {
        // Validate ownership
        if ($quAttempt->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Validate attempt is completed
        if (!$quAttempt->isCompleted()) {
            return redirect()->route('qu-student.exams.take', [
                'quExam' => $quExam->id,
                'quAttempt' => $quAttempt->id
            ]);
        }

        // Check if results should be shown
        $showResults = true;
        $showCorrectAnswers = false;

        if ($quExam->publish_results_timing === 'after_end') {
            $showResults = $quExam->end_date && now()->gte($quExam->end_date);
        } elseif ($quExam->publish_results_timing === 'manual') {
            $showResults = false; // Teacher must manually publish
        }

        if ($quExam->publish_results_timing === 'immediate') {
            $showCorrectAnswers = true;
        }

        $quExam->load(['subject', 'questions']);
        $quAttempt->load('answers');

        // Calculate statistics
        $totalQuestions = $quExam->questions->count();
        $answeredQuestions = $quAttempt->answers->count();
        $timeTaken = $quAttempt->started_at->diffInMinutes($quAttempt->completed_at);

        return Inertia::render('my_class/QuQuestionBankSystem/QuExamResults', [
            'exam' => [
                'id' => $quExam->id,
                'title' => $quExam->title,
                'subject' => $quExam->subject,
                'total_marks' => $quExam->total_marks,
                'passing_score' => $quExam->passing_score,
            ],
            'attempt' => [
                'id' => $quAttempt->id,
                'score' => $quAttempt->score,
                'started_at' => $quAttempt->started_at,
                'completed_at' => $quAttempt->completed_at,
                'time_taken_minutes' => $timeTaken,
            ],
            'statistics' => [
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'percentage' => $quExam->total_marks > 0 ? round(($quAttempt->score / $quExam->total_marks) * 100, 2) : 0,
                'passed' => $quExam->passing_score ? $quAttempt->score >= $quExam->passing_score : null,
            ],
            'show_results' => $showResults,
            'show_correct_answers' => $showCorrectAnswers,
            'questions_with_answers' => $showResults ? $quExam->questions->map(function($question) use ($quAttempt, $showCorrectAnswers) {
                $answer = $quAttempt->answers->firstWhere('qu_question_id', $question->id);
                
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'options' => $question->options,
                    'marks' => $question->marks,
                    'correct_answer' => $showCorrectAnswers ? $question->correct_answer : null,
                    'student_answer' => $answer ? [
                        'selected_options' => $answer->selected_options,
                        'answer_text' => $answer->answer_text,
                        'marks_obtained' => $answer->marks_obtained,
                    ] : null,
                ];
            }) : [],
        ]);
    }
    /**
     * Print View for Exam
     */
    public function printExam(QuExam $quExam)
    {
        // Check if print is allowed
        if (!($quExam->settings['allow_print'] ?? false)) {
            abort(403, 'Printing is not enabled for this exam.');
        }

        $quExam->load(['questions' => function ($query) {
            $query->orderBy('pivot_order');
        }]);

        // Shuffle questions if enabled (consistent with how student sees it?) 
        // For printing, usually static order is better, but let's respect settings if we want a unique paper
        // Actually for a generic "practice print", standard order is probably best.
        // Let's stick to standard order unless specifically requested.

        // Get student info
        $user = auth()->user();
        $student = $user->student;
        $isStudent = $user->role === 'student' || $student !== null;

        return Inertia::render('my_class/QuQuestionBankSystem/QuExamPrint', [
            'exam' => [
                'id' => $quExam->id,
                'title' => $quExam->title,
                'subject' => $quExam->subject,
                'total_marks' => $quExam->total_marks,
                'duration_minutes' => $quExam->duration_minutes,
                'description' => $quExam->description,
                'questions' => $quExam->questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'question_text' => $question->question_text,
                        'question_type' => $question->question_type,
                        'options' => $question->options,
                        'marks' => $question->marks,
                        'correct_answer' => $question->correct_answer,
                    ];
                }),
            ],
            'student_info' => [
                'name' => $isStudent ? ($student?->name ?? $user->name) : '',
                'school_name' => $isStudent ? ($student?->school_name ?? config('app.name')) : ($user->school?->name ?? config('app.name')),
                'classroom_name' => $isStudent ? ($student?->classroom_name ?? '') : '',
            ]
        ]);
    }

    /**
     * Search users for assignment (students, teachers, etc.)
     */
    public function searchUsers(Request $request)
    {
        $search = $request->input('search');
        if (!$search) {
            return response()->json(['users' => []]);
        }

        $users = User::where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->limit(20)
            ->get(['id', 'name', 'email', 'role']);

        return response()->json(['users' => $users]);
    }
}
