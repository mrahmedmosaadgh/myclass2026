<?php

namespace App\Http\Controllers;

use App\Models\Bm2QuestionBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Bm2QuestionController
 * 
 * CRUD operations for question bank management.
 */
class Bm2QuestionController extends Controller
{
    /**
     * Display a listing of questions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Bm2QuestionBank::query()->active();

        // Apply filters
        if ($request->has('grade_level')) {
            $query->ofGrade($request->grade_level);
        }

        if ($request->has('topic')) {
            $query->ofTopic($request->topic);
        }

        if ($request->has('difficulty')) {
            $query->ofDifficulty($request->difficulty);
        }

        if ($request->has('question_format')) {
            $query->ofFormat($request->question_format);
        }

        if ($request->has('verified')) {
            $query->verified();
        }

        $questions = $query->with('creator')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'questions' => $questions->items(),
                'pagination' => [
                    'current_page' => $questions->currentPage(),
                    'last_page' => $questions->lastPage(),
                    'per_page' => $questions->perPage(),
                    'total' => $questions->total(),
                ],
            ],
        ]);
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'context_description' => 'nullable|string',
            'subject' => 'required|in:math',
            'grade_level' => 'required|in:K,1,2',
            'topic' => 'required|in:addition,subtraction,number_sense,fractions,patterns,measurement',
            'difficulty' => 'required|in:easy,medium,hard',
            'question_format' => 'required|in:multiple_choice,true_false,fill_in_blank,short_answer,matching,drag_drop',
            'options' => 'nullable|array',
            'correct_answer' => 'required',
            'explanation' => 'nullable|string',
            'image_url' => 'nullable|url',
            'visual_properties' => 'nullable|array',
            'estimated_time_seconds' => 'nullable|integer|min:5',
            'points_default' => 'nullable|integer|min:1',
            'allows_calculator' => 'boolean',
            'has_hint' => 'boolean',
            'hints' => 'nullable|array',
        ]);

        $question = Bm2QuestionBank::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'is_active' => true,
            'is_verified' => false,
            'times_used' => 0,
        ]);

        return response()->json([
            'success' => true,
            'data' => ['question' => $question],
            'message' => 'Question created successfully',
        ]);
    }

    /**
     * Display the specified question.
     */
    public function show(int $id): JsonResponse
    {
        $question = Bm2QuestionBank::with(['creator', 'assessmentQuestions'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => ['question' => $question],
        ]);
    }

    /**
     * Update the specified question.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $question = Bm2QuestionBank::findOrFail($id);

        $validated = $request->validate([
            'question_text' => 'sometimes|required|string',
            'context_description' => 'nullable|string',
            'grade_level' => 'sometimes|required|in:K,1,2',
            'topic' => 'sometimes|required|in:addition,subtraction,number_sense,fractions,patterns,measurement',
            'difficulty' => 'sometimes|required|in:easy,medium,hard',
            'question_format' => 'sometimes|required|in:multiple_choice,true_false,fill_in_blank,short_answer,matching,drag_drop',
            'options' => 'nullable|array',
            'correct_answer' => 'sometimes|required',
            'explanation' => 'nullable|string',
            'image_url' => 'nullable|url',
            'visual_properties' => 'nullable|array',
            'estimated_time_seconds' => 'sometimes|required|integer|min:5',
            'points_default' => 'sometimes|required|integer|min:1',
            'allows_calculator' => 'boolean',
            'has_hint' => 'boolean',
            'hints' => 'nullable|array',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
        ]);

        $question->update($validated);

        return response()->json([
            'success' => true,
            'data' => ['question' => $question],
            'message' => 'Question updated successfully',
        ]);
    }

    /**
     * Remove the specified question (soft delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $question = Bm2QuestionBank::findOrFail($id);
        
        // Soft delete by setting is_active to false
        $question->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Question deactivated successfully',
        ]);
    }

    /**
     * Get random questions for practice.
     */
    public function getRandom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'count' => 'required|integer|min:1|max:50',
            'grade_level' => 'required|in:K,1,2',
            'topics' => 'nullable|array',
            'difficulties' => 'nullable|array',
        ]);

        $query = Bm2QuestionBank::query()
            ->active()
            ->ofGrade($validated['grade_level']);

        if (!empty($validated['topics'])) {
            $query->whereIn('topic', $validated['topics']);
        }

        if (!empty($validated['difficulties'])) {
            $query->whereIn('difficulty', $validated['difficulties']);
        }

        $questions = $query->inRandomOrder()->limit($validated['count'])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'questions' => $questions,
                'count' => $questions->count(),
            ],
        ]);
    }
}
