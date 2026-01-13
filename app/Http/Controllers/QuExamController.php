<?php

namespace App\Http\Controllers;

use App\Models\QuExam;
use App\Models\QuQuestion;
use App\Models\Subject;
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
        $customGroups = QuExam::whereNotNull('custom_group')
            ->distinct()
            ->pluck('custom_group')
            ->sort()
            ->values();

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
        return Inertia::render('my_class/QuQuestionBankSystem/QuExamForm', [
            'subjects' => Subject::all(),
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
    public function show(QuExam $quExam)
    {
        $quExam->load(['subject', 'creator', 'questions']);

        return Inertia::render('my_class/QuQuestionBankSystem/QuExamShow', [
            'exam' => $quExam
        ]);
    }

    /**
     * Show the form for editing.
     */
    public function edit(QuExam $quExam)
    {
        $quExam->load(['subject', 'questions']);

        return Inertia::render('my_class/QuQuestionBankSystem/QuExamForm', [
            'exam' => $quExam,
            'subjects' => Subject::all(),
        ]);
    }

    /**
     * Update the specified exam.
     */
    public function update(Request $request, QuExam $quExam)
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
        ]);

        $quExam->update($validated);

        // Sync questions
        if (!empty($validated['bloom_distribution'])) {
            $questionIds = $this->selectQuestionsByBloom(
                $validated['subject_id'],
                $validated['bloom_distribution']
            );
        } else {
            $questionIds = $validated['question_ids'] ?? [];
        }

        $quExam->questions()->sync($questionIds);

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
    public function destroy(QuExam $quExam)
    {
        $quExam->delete();

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
    public function grading(QuExam $exam)
    {
        $exam->load(['attempts.user', 'attempts.answers.question']);

        return Inertia::render('my_class/QuQuestionBankSystem/QuGrading', [
            'exam' => $exam,
            'attempts' => $exam->attempts
        ]);
    }

    /**
     * Grade a specific answer.
     */
    public function gradeAnswer(Request $request, $answerId)
    {
        $validated = $request->validate([
            'marks_obtained' => 'required|integer|min:0'
        ]);

        $answer = \App\Models\QuAnswer::findOrFail($answerId);
        $answer->update($validated);

        // Recalculate attempt score
        $attempt = $answer->attempt;
        $totalScore = $attempt->answers()->sum('marks_obtained');
        $attempt->update(['score' => $totalScore]);

        return back()->with('success', 'Answer graded successfully');
    }
}
