<?php

namespace App\Http\Controllers;

use App\Models\SkillCategory;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;

class SkillCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = SkillCategory::with(['skills' => function($q) {
            $q->where('is_active', true);
        }, 'grade', 'subject']);

        // Filter by grade if provided
        if ($request->has('grade_id')) {
            $query->where('grade_id', $request->input('grade_id'));
        }

        // Filter by subject if provided
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        // Allow filtering by active status
        if ($request->has('active_only') && $request->input('active_only') == 'true') {
            $query->whereHas('skills', function($q) {
                $q->where('is_active', true);
            });
        }

        $categories = $query->orderBy('display_order')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = SkillCategory::with([
            'skills' => function($q) {
                $q->where('is_active', true)->withCount(['questions']);
            },
            'grade',
            'subject'
        ])->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Skill category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }
}