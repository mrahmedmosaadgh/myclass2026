<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseManagement\LessonPlanTemplateRequest;
use App\Models\CourseManagement\LessonPlanTemplate;
use Illuminate\Support\Facades\Auth;

class LessonPlanTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = LessonPlanTemplate::with('creator')
            ->active()
            ->ordered();

        if (request()->has('subject_id')) {
            $subjectId = request()->input('subject_id');
            $query->where(function ($q) use ($subjectId) {
                $q->whereNull('subject_id')
                  ->orWhere('subject_id', $subjectId);
            });
        }

        $templates = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LessonPlanTemplateRequest $request)
    {
        try {
            $template = LessonPlanTemplate::create([
                'name' => $request->name,
                'structure' => $request->structure,
                'created_by' => Auth::id(),
                'is_active' => true,
                'subject_id' => $request->subject_id ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'data' => $template->load('creator')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LessonPlanTemplate $lessonPlanTemplate)
    {
        return response()->json([
            'success' => true,
            'data' => $lessonPlanTemplate->load('creator')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LessonPlanTemplateRequest $request, LessonPlanTemplate $lessonPlanTemplate)
    {
        try {
            $data = $request->validated();
            // Allow updating subject_id explicitly
            if ($request->has('subject_id')) {
                $data['subject_id'] = $request->subject_id;
            }
            $lessonPlanTemplate->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully',
                'data' => $lessonPlanTemplate->load('creator')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LessonPlanTemplate $lessonPlanTemplate)
    {
        try {
            $lessonPlanTemplate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}