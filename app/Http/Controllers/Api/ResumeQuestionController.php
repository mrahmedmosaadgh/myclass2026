<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResumeQuestion;
use Illuminate\Http\Request;

class ResumeQuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = ResumeQuestion::with('user');

        // Apply filters
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->has('category')) {
            $category = $request->get('category');
            $query->whereJsonContains('category', $category);
        }

        if ($request->has('type')) {
            $type = $request->get('type');
            $query->where('type', $type);
        }

        if ($request->has('language')) {
            $language = $request->get('language');
            $query->where('language', $language);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:text,textarea,select,multi-select,media,file',
            'category' => 'nullable|array',
            'language' => 'required|string|max:10',
            'tags' => 'nullable|array',
            'options' => 'nullable|array',
            'default_answer' => 'nullable|string',
            'is_required' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $data['user_id'] = $request->user()->id;
        $question = ResumeQuestion::create($data);

        return response()->json($question->load('user'), 201);
    }

    public function show($id)
    {
        return ResumeQuestion::with(['user', 'answers.user', 'comments'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $question = ResumeQuestion::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:text,textarea,select,multi-select,media,file',
            'category' => 'nullable|array',
            'language' => 'sometimes|string|max:10',
            'tags' => 'nullable|array',
            'options' => 'nullable|array',
            'default_answer' => 'nullable|string',
            'is_required' => 'sometimes|boolean',
            'description' => 'nullable|string',
        ]);

        $question->update($data);

        return response()->json($question->load('user'));
    }

    public function destroy($id)
    {
        $question = ResumeQuestion::findOrFail($id);
        $question->delete();

        return response()->json(['success' => true]);
    }

    public function getCategories()
    {
        // Get unique categories from existing questions
        $categories = ResumeQuestion::whereNotNull('category')
            ->get()
            ->pluck('category')
            ->flatten()
            ->unique()
            ->values();

        return response()->json($categories);
    }

    public function getQuestionTypes()
    {
        $types = [
            'text' => 'Text Input',
            'textarea' => 'Text Area',
            'select' => 'Single Select',
            'multi-select' => 'Multiple Select',
            'media' => 'Media Upload',
            'file' => 'File Upload'
        ];

        return response()->json($types);
    }
}
