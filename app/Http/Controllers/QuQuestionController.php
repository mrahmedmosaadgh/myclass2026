<?php

namespace App\Http\Controllers;

use App\Models\QuQuestion;
use App\Models\Subject;
use App\Models\CurriculumTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'subjects' => Subject::with('curricula.topics')->get(),
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

        return redirect()->route('qu.questions.index')
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
    public function edit(QuQuestion $question)
    {
        $question->load(['subject', 'topic']);

        return Inertia::render('my_class/QuQuestionBankSystem/QuQuestionForm', [
            'question' => $question,
            'subjects' => Subject::with('curricula.topics')->get(),
        ]);
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, QuQuestion $question)
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

        $question->update($validated);

        return redirect()->route('qu.questions.index')
            ->with('success', 'Question updated successfully');
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy(QuQuestion $question)
    {
        $question->delete();

        return redirect()->route('qu.questions.index')
            ->with('success', 'Question deleted successfully');
    }

    /**
     * Bulk import questions from AI-generated JSON.
     */
    public function bulkImport(Request $request)
    {
        $validated = $request->validate([
            'questions' => 'required|array|min:1|max:50',
            'questions.*.subject_id' => 'required|exists:subjects,id',
            'questions.*.topic_id' => 'nullable|exists:curriculum_topics,id',
            'questions.*.question_text' => 'required|string|min:10|max:1000',
            'questions.*.question_type' => 'required|in:mcq,true_false,short,long',
            'questions.*.options' => 'required_if:questions.*.question_type,mcq,true_false|array',
            'questions.*.correct_answer' => 'required|array',
            'questions.*.difficulty' => 'required|in:easy,medium,hard',
            'questions.*.bloom_level' => 'nullable|in:remember,understand,apply,analyze,evaluate,create',
            'questions.*.marks' => 'required|integer|min:1|max:100',
        ]);

        $imported = 0;
        $failed = 0;

        DB::beginTransaction();
        try {
            foreach ($validated['questions'] as $questionData) {
                try {
                    QuQuestion::create([
                        'subject_id' => $questionData['subject_id'],
                        'topic_id' => $questionData['topic_id'] ?? null,
                        'question_text' => $questionData['question_text'],
                        'question_type' => $questionData['question_type'],
                        'options' => $questionData['options'] ?? [],
                        'correct_answer' => $questionData['correct_answer'],
                        'difficulty' => $questionData['difficulty'],
                        'bloom_level' => $questionData['bloom_level'] ?? null,
                        'marks' => $questionData['marks'],
                        'created_by' => auth()->id(),
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Failed to import question', [
                        'question' => $questionData,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('qu.questions.index')
                ->with('success', "Successfully imported {$imported} questions" . ($failed > 0 ? ", {$failed} failed" : ''));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Bulk import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * API endpoint to get questions for quiz builder
     */
    public function apiIndex(Request $request)
    {
        try {
            $validated = $request->validate([
                'subject_id' => 'sometimes|integer|exists:subjects,id',
                'topic_id' => 'sometimes|integer|exists:curriculum_topics,id',
                'difficulty' => 'sometimes|string|in:easy,medium,hard',
                'bloom_level' => 'sometimes|string|in:remember,understand,apply,analyze,evaluate,create',
                'question_type' => 'sometimes|string|in:mcq,true_false,short,long',
                'search' => 'sometimes|string|max:255',
                'per_page' => 'sometimes|integer|min:1|max:100',
                'page' => 'sometimes|integer|min:1',
            ]);

            $query = QuQuestion::with(['subject', 'topic', 'creator']);

            // Apply filters
            if (isset($validated['subject_id'])) {
                $query->where('subject_id', $validated['subject_id']);
            }

            if (isset($validated['topic_id'])) {
                $query->where('topic_id', $validated['topic_id']);
            }

            if (isset($validated['difficulty'])) {
                $query->where('difficulty', strtolower($validated['difficulty']));
            }

            if (isset($validated['bloom_level'])) {
                $query->where('bloom_level', strtolower($validated['bloom_level']));
            }

            if (isset($validated['question_type'])) {
                $query->where('question_type', $validated['question_type']);
            }

            // Search in question text
            if (isset($validated['search'])) {
                $query->where('question_text', 'like', '%' . $validated['search'] . '%');
            }

            // Paginate results
            $perPage = $validated['per_page'] ?? 20;
            $questions = $query->latest()->paginate($perPage);

            // Transform the data to match the expected format for QuizBuilder
            $transformedQuestions = $questions->getCollection()->map(function ($question) {
                // Transform options to the expected format
                $options = [];
                if ($question->options && is_array($question->options)) {
                    $indexCounter = 0;
                    foreach ($question->options as $optionKey => $optionValue) {
                        $options[] = [
                            'id' => $indexCounter + 1,
                            'option_key' => is_numeric($optionKey) ? chr(65 + $indexCounter) : $optionKey,
                            'option_text' => $optionValue,
                            'is_correct' => in_array($optionKey, (array) $question->correct_answer),
                            'order_index' => $indexCounter
                        ];
                        $indexCounter++;
                    }
                }

                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type_id' => $this->mapQuestionType($question->question_type),
                    'difficulty' => ucfirst($question->difficulty ?: 'medium'),
                    'bloom_level' => $this->mapBloomLevel($question->bloom_level),
                    'subject_id' => $question->subject_id,
                    'topic_id' => $question->topic_id,
                    'grade_id' => $question->subject ? $question->subject->grade_id : null,
                    'author_id' => $question->created_by,
                    'status' => 'active',
                    'usage_count' => 0, // QuQuestion doesn't track usage yet
                    'avg_success_rate' => null,
                    'points' => $question->marks ?: 1,
                    'options' => $options,
                    'subject' => $question->subject ? [
                        'id' => $question->subject->id,
                        'name' => $question->subject->name
                    ] : null,
                    'topic' => $question->topic ? [
                        'id' => $question->topic->id,
                        'name' => $question->topic->name
                    ] : null,
                    'author' => $question->creator ? [
                        'id' => $question->creator->id,
                        'name' => $question->creator->name
                    ] : null,
                    'created_at' => $question->created_at,
                    'updated_at' => $question->updated_at,
                ];
            });

            $questions->setCollection($transformedQuestions);

            return response()->json([
                'success' => true,
                'data' => $questions,
                'message' => 'Questions retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve questions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map QuQuestion type to numeric ID
     */
    private function mapQuestionType($questionType)
    {
        $typeMap = [
            'mcq' => 1,
            'true_false' => 2,
            'short' => 3,
            'long' => 4
        ];
        return $typeMap[$questionType] ?? 1;
    }

    /**
     * Map Bloom's taxonomy level to numeric string
     */
    private function mapBloomLevel($bloomLevel)
    {
        if (!$bloomLevel) return null;
        
        $bloomMap = [
            'remember' => '1',
            'understand' => '2',
            'apply' => '3',
            'analyze' => '4',
            'evaluate' => '5',
            'create' => '6'
        ];
        return $bloomMap[strtolower($bloomLevel)] ?? null;
    }
}
