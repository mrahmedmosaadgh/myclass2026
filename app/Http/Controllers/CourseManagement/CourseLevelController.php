<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Models\CourseManagement\Course;
use App\Models\CourseManagement\CourseLevel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CourseLevelController extends Controller
{
    public function index(Course $course)
    {
        $course->load(['levels.sections']);

        return Inertia::render('CourseManagement/Level/Index', [
            'course' => $course
        ]);
    }

    public function create(Course $course)
    {
        return Inertia::render('CourseManagement/Level/Create', [
            'course' => $course
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $order = $validated['order'] ?? $course->levels()->max('order') + 1;

        $level = $course->levels()->create([
            'title' => $validated['title'],
            'order' => $order,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('course-management.levels.show', $level)
            ->with('success', 'Level created successfully.');
    }

    public function show(CourseLevel $level)
    {
        $level->load(['course', 'sections.lessons', 'creator']);

        return Inertia::render('CourseManagement/Level/Show', [
            'level' => $level
        ]);
    }

    public function edit(CourseLevel $level)
    {
        $level->load('course');

        return Inertia::render('CourseManagement/Level/Edit', [
            'level' => $level
        ]);
    }

    public function update(Request $request, CourseLevel $level)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $level->update($validated);

        return redirect()->route('course-management.levels.show', $level)
            ->with('success', 'Level updated successfully.');
    }

    public function destroy(CourseLevel $level)
    {
        $courseId = $level->course_id;
        $level->delete();

        return redirect()->route('course-management.courses.show', $courseId)
            ->with('success', 'Level deleted successfully.');
    }

    public function reorder(Request $request, Course $course)
    {
        $validated = $request->validate([
            'levels' => 'required|array',
            'levels.*.id' => 'required|exists:course_levels,id',
            'levels.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['levels'] as $levelData) {
            CourseLevel::where('id', $levelData['id'])
                ->where('course_id', $course->id)
                ->update(['order' => $levelData['order']]);
        }

        return response()->json(['success' => true]);
    }
}
