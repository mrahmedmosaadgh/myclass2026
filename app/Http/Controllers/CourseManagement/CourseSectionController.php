<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Models\CourseManagement\CourseLevel;
use App\Models\CourseManagement\CourseSection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CourseSectionController extends Controller
{
    public function index(CourseLevel $level)
    {
        $level->load(['course', 'sections.lessons']);

        return Inertia::render('CourseManagement/Section/Index', [
            'level' => $level
        ]);
    }

    public function create(CourseLevel $level)
    {
        $level->load('course');

        return Inertia::render('CourseManagement/Section/Create', [
            'level' => $level
        ]);
    }

    public function store(Request $request, CourseLevel $level)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $order = $validated['order'] ?? $level->sections()->max('order') + 1;

        $section = $level->sections()->create([
            'title' => $validated['title'],
            'order' => $order,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('course-management.sections.show', $section)
            ->with('success', 'Section created successfully.');
    }

    public function show(CourseSection $section)
    {
        $section->load(['level.course', 'lessons', 'creator']);

        return Inertia::render('CourseManagement/Section/Show', [
            'section' => $section
        ]);
    }

    public function edit(CourseSection $section)
    {
        $section->load('level.course');

        return Inertia::render('CourseManagement/Section/Edit', [
            'section' => $section
        ]);
    }

    public function update(Request $request, CourseSection $section)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $section->update($validated);

        return redirect()->route('course-management.sections.show', $section)
            ->with('success', 'Section updated successfully.');
    }

    public function destroy(CourseSection $section)
    {
        $levelId = $section->course_level_id;
        $section->delete();

        return redirect()->route('course-management.levels.show', $levelId)
            ->with('success', 'Section deleted successfully.');
    }

    public function reorder(Request $request, CourseLevel $level)
    {
        $validated = $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:course_sections,id',
            'sections.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['sections'] as $sectionData) {
            CourseSection::where('id', $sectionData['id'])
                ->where('course_level_id', $level->id)
                ->update(['order' => $sectionData['order']]);
        }

        return response()->json(['success' => true]);
    }
}
