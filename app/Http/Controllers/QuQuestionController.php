<?php

namespace App\Http\Controllers;

use App\Models\QuQuestion;
use App\Models\Subject;
use App\Models\CurriculumTopic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuQuestionController extends Controller
{
    /**
     * Display a listing of questions with filtering.
     */
    public function index(Request $request)
    {
        $query = QuQuestion::with(['subject', 'topic', 'creator'])
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->topic_id, fn($q) => $q->where('topic_id', $request->topic_id))
            ->when($request->difficulty, fn($q) => $q->where('difficulty', $request->difficulty))
            ->when($request->bloom_level, fn($q) => $q->where('bloom_level', $request->bloom_level))
            ->when($request->question_type, fn($q) => $q->where('question_type', $request->question_type))
            ->latest();

        return Inertia::render('my_class/QuQuestionBankSystem/QuQuestionList', [
            'questions' => $query->paginate(20),
            'subjects' => Subject::all(),
            'filters' => $request->only(['subject_id', 'topic_id', 'difficulty', 'bloom_level', 'question_type'])
        ]);
    }

    /**
     * Show the form for creating a new question.
     */
    public function create()
    {
        return Inertia::render('my_class/QuQuestionBankSystem/QuQuestionForm', [
            'subjects' => Subject::with('curricula.curriculumTopics')->get(),
        ]);
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'topic_id' => 'nullable|exists:curriculum_topics,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:mcq,true_false,short,long',
            'options' => 'required_if:question_type,mcq,true_false|array',
            'correct_answer' => 'required|array',
            'difficulty' => 'required|in:easy,medium,hard',
            'bloom_level' => 'nullable|in:remember,understand,apply,analyze,evaluate,create',
            'marks' => 'required|integer|min:1',
        ]);

        $validated['created_by'] = auth()->id();

        QuQuestion::create($validated);

        return redirect()->route('qu-questions.index')
            ->with('success', 'Question created successfully');
    }

    /**
     * Display the specified question.
     */
    public function show(QuQuestion $quQuestion)
    {
        $quQuestion->load(['subject', 'topic', 'creator']);

        return Inertia::render('my_class/QuQuestionBankSystem/QuQuestionShow', [
            'question' => $quQuestion
        ]);
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(QuQuestion $quQuestion)
    {
        $quQuestion->load(['subject', 'topic']);

        return Inertia::render('my_class/QuQuestionBankSystem/QuQuestionForm', [
            'question' => $quQuestion,
            'subjects' => Subject::with('curricula.curriculumTopics')->get(),
        ]);
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, QuQuestion $quQuestion)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'topic_id' => 'nullable|exists:curriculum_topics,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:mcq,true_false,short,long',
            'options' => 'required_if:question_type,mcq,true_false|array',
            'correct_answer' => 'required|array',
            'difficulty' => 'required|in:easy,medium,hard',
            'bloom_level' => 'nullable|in:remember,understand,apply,analyze,evaluate,create',
            'marks' => 'required|integer|min:1',
        ]);

        $quQuestion->update($validated);

        return redirect()->route('qu-questions.index')
            ->with('success', 'Question updated successfully');
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy(QuQuestion $quQuestion)
    {
        $quQuestion->delete();

        return redirect()->route('qu-questions.index')
            ->with('success', 'Question deleted successfully');
    }
}
