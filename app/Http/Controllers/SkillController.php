<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\UserSkillProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class SkillController extends Controller
{
    /**
     * Display the admin skill management page.
     *
     * @return \Inertia\Response
     */
    public function adminIndex()
    {
        return Inertia::render('Admin/SkillManagement/SkillEditor');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Skill::with(['category', 'questions']);

        // Filter by category if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter by active status
        if ($request->has('active_only') && $request->input('active_only') == 'true') {
            $query->where('is_active', true);
        }

        // Filter by grade through category
        if ($request->has('grade_id')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('grade_id', $request->input('grade_id'));
            });
        }

        // Filter by subject through category
        if ($request->has('subject_id')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('subject_id', $request->input('subject_id'));
            });
        }

        $skills = $query->get();

        return response()->json([
            'success' => true,
            'data' => $skills
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:skill_categories,id',
            'description' => 'nullable|string',
            'difficulty_min' => 'nullable|integer|min:1|max:10',
            'difficulty_max' => 'nullable|integer|min:1|max:10',
            'mastery_threshold' => 'nullable|integer|min:0|max:100',
            'estimated_time_minutes' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $skill = new Skill();
        $skill->fill($validator->validated());
        $skill->save();

        return response()->json([
            'success' => true,
            'message' => 'Skill created successfully',
            'data' => $skill
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $skill = Skill::with([
            'category',
            'questions' => function($q) {
                $q->withPivot(['difficulty_level', 'explanation']);
            }
        ])->find($id);

        if (!$skill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found'
            ], 404);
        }

        // If user is authenticated, include their progress
        $userProgress = null;
        if (Auth::check()) {
            $userProgress = UserSkillProgress::where('user_id', Auth::id())
                ->where('skill_id', $skill->id)
                ->first();
        }

        return response()->json([
            'success' => true,
            'data' => $skill,
            'user_progress' => $userProgress
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $skill = Skill::find($id);
        
        if (!$skill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:skill_categories,id',
            'description' => 'nullable|string',
            'difficulty_min' => 'nullable|integer|min:1|max:10',
            'difficulty_max' => 'nullable|integer|min:1|max:10',
            'mastery_threshold' => 'nullable|integer|min:0|max:100',
            'estimated_time_minutes' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $skill->fill($validator->validated());
        $skill->save();

        return response()->json([
            'success' => true,
            'message' => 'Skill updated successfully',
            'data' => $skill
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $skill = Skill::find($id);
        
        if (!$skill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found'
            ], 404);
        }

        $skill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Skill deleted successfully'
        ]);
    }
}