<?php

namespace App\Http\Controllers;

use App\Models\Curriculum;
use App\Models\QuestionBank;
use App\Models\School;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuestionBankController extends Controller
{
    public function index()
    {
        $questions = QuestionBank::with(['school', 'subject', 'curriculum', 'curriculumLesson', 'createdBy'])
            ->paginate(40);

        return Inertia::render('my_class/admin/QuestionBanks/Index', [
            'records' => $questions,
            'options' => [
                'schools' => School::select('id', 'name')->get(),
                'subjects' => Subject::select('id', 'name')->get(),
                'curricula' => Curriculum::select('id', 'name')->get(),
                'types' => [
                    ['value' => 'mcq', 'label' => 'Multiple Choice'],
                    ['value' => 'true_false', 'label' => 'True/False'],
                    ['value' => 'fill_blank', 'label' => 'Fill in the Blank']
                ]
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = validator($request->all(), [
            'school_id' => 'required|exists:schools,id',
            'subject_id' => 'required|exists:subjects,id',
            'curriculum_id' => 'required|exists:curricula,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|string',
            'score' => 'required|integer|min:1',
            'difficulty' => 'required|string',
            'notes' => 'nullable|string'
        ])->validate();

        // Get the authenticated user's ID and verify it exists in teachers table
        $teacher = Teacher::where('user_id', auth()->id())->first();

        if (!$teacher) {
            return back()->with('error', 'Unauthorized. User is not a teacher.');
        }

        // Merge the arrays directly
        $validated['options'] = $request->input('options');
        $validated['explanation'] = $request->input('explanation');
        $validated['created_by_id'] = $teacher->id;

        try {
            $questionBank = QuestionBank::create($validated);

            return back()->with('success', 'Question created successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create question. Please verify your teacher account.');
        }
    }

    public function update(Request $request, QuestionBank $questionBank)
    {
        $validated = validator($request->all(), [
            'school_id' => 'required|exists:schools,id',
            'subject_id' => 'required|exists:subjects,id',
            'curriculum_id' => 'required|exists:curricula,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|string',
            'score' => 'required|integer|min:1',
            'difficulty' => 'required|string',
            'notes' => 'nullable|string'
        ])->validate();

        // Merge the arrays directly
        $validated['options'] = $request->input('options');
        $validated['explanation'] = $request->input('explanation');
        // $validated['created_by_id'] = auth()->id();



        try {
            $questionBank->update($validated);

            return back()->with('success', 'Question updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update question. Please verify your teacher account.');
        }
        return response()->json([
            'message' => 'Question updated successfully',
            'record' => $questionBank->fresh()->load(['school', 'subject', 'curriculum'])
        ]);
    }

    public function destroy($id)
    {
        $question = QuestionBank::findOrFail($id);
        $question->delete();

        return redirect()->back()->with('success', 'Question deleted successfully');
    }

    protected function handleFormData($data)
    {
        // Handle explanation data
        if (isset($data['explanation']) && is_array($data['explanation'])) {
            $data['explanation'] = array_map(function($step) {
                return [
                    'id' => $step['id'] ?? null,
                    'step' => trim($step['step'] ?? ''),
                    'note' => trim($step['note'] ?? '')
                ];
            }, array_filter($data['explanation'], function($step) {
                return !empty($step['step']) || !empty($step['note']);
            }));
        }

        // Handle options data
        if (isset($data['options']) && is_array($data['options'])) {
            $data['options'] = array_map(function($option) {
                return [
                    'option' => trim($option['option'] ?? ''),
                    'isCorrect' => (bool)($option['isCorrect'] ?? false),
                    'feedback' => trim($option['feedback'] ?? '')
                ];
            }, array_filter($data['options'], function($option) {
                return !empty($option['option']);
            }));
        }

        // Ensure required fields are present
        $data['school_id'] = $data['school_id'] ?? null;
        $data['subject_id'] = $data['subject_id'] ?? null;
        $data['curriculum_id'] = $data['curriculum_id'] ?? null;
        $data['score'] = $data['score'] ?? 1;
        $data['difficulty'] = $data['difficulty'] ?? 'medium';
        $data['type'] = $data['type'] ?? 'mcq';

        return $data;
    }
}





















