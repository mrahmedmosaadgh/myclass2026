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
            ->latest();

        return Inertia::render('my_class/QuQuestionBankSystem/QuExamList', [
            'exams' => $query->paginate(20),
            'subjects' => Subject::all(),
            'filters' => $request->only(['subject_id'])
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
            'title' => 'required|string',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'bloom_distribution' => 'nullable|array',
            'bloom_distribution.remember' => 'integer|min:0',
            'bloom_distribution.understand' => 'integer|min:0',
            'bloom_distribution.apply' => 'integer|min:0',
            'bloom_distribution.analyze' => 'integer|min:0',
            'bloom_distribution.evaluate' => 'integer|min:0',
            'bloom_distribution.create' => 'integer|min:0',
            'question_ids' => 'required_without:bloom_distribution|array',
            'question_ids.*' => 'exists:qu_questions,id',
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
            $questionIds = $validated['question_ids'];
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
            'title' => 'required|string',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'bloom_distribution' => 'nullable|array',
            'question_ids' => 'required_without:bloom_distribution|array',
        ]);

        $quExam->update($validated);

        // Sync questions
        if (!empty($validated['bloom_distribution'])) {
            $questionIds = $this->selectQuestionsByBloom(
                $validated['subject_id'],
                $validated['bloom_distribution']
            );
        } else {
            $questionIds = $validated['question_ids'];
        }

        $quExam->questions()->sync($questionIds);

        return redirect()->route('qu-exams.index')
            ->with('success', 'Exam updated successfully');
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
