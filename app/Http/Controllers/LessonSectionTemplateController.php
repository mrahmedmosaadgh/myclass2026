<?php

namespace App\Http\Controllers;

use App\Models\CourseManagement\LessonPlanTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LessonSectionTemplateController extends Controller
{
    public function index()
    {
        // Return ALL templates so we can manage them
        $templates = LessonPlanTemplate::ordered()->get();
        return response()->json($templates);
    }

    public function show($id)
    {
        $template = LessonPlanTemplate::findOrFail($id);
        return response()->json($template);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'structure' => 'required|array',
            'structure.sections' => 'required|array|min:1',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['is_active'] = false; // Default to inactive, user must explicitly activate
        
        $template = LessonPlanTemplate::create($validated);
        return response()->json($template, 201);
    }

    public function update(Request $request, $id)
    {
        $template = LessonPlanTemplate::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'structure' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        // If setting to active, deactivate others first
        if (isset($validated['is_active']) && $validated['is_active'] === true) {
            DB::transaction(function () use ($template, $validated) {
                LessonPlanTemplate::where('id', '!=', $template->id)->update(['is_active' => false]);
                $template->update($validated);
            });
        } else {
            $template->update($validated);
        }

        return response()->json($template);
    }

    public function destroy($id)
    {
        $template = LessonPlanTemplate::findOrFail($id);
        
        if ($template->is_active) {
            return response()->json(['error' => 'Cannot delete active template'], 400);
        }
        
        $template->delete();
        return response()->json(null, 204);
    }

    public function setActive($id)
    {
        DB::beginTransaction();
        try {
            // Deactivate all templates
            LessonPlanTemplate::query()->update(['is_active' => false]);
            
            // Activate this one
            $template = LessonPlanTemplate::findOrFail($id);
            $template->update(['is_active' => true]);
            
            DB::commit();
            return response()->json($template);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
