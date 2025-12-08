<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Models\CourseManagement\CourseSection;
use App\Models\CourseManagement\CourseLesson;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CourseLessonController extends Controller
{
    public function index(CourseSection $section)
    {
        $section->load(['level.course', 'lessons']);

        return Inertia::render('CourseManagement/Lesson/Index', [
            'section' => $section
        ]);
    }

    public function create(CourseSection $section)
    {
        $section->load('level.course');

        return Inertia::render('CourseManagement/Lesson/Create', [
            'section' => $section
        ]);
    }

    public function store(Request $request, CourseSection $section)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'text' => 'nullable|string',
            'data' => 'nullable|array',
            'order' => 'nullable|integer|min:0',
        ]);

        $order = $validated['order'] ?? $section->lessons()->max('order') + 1;

        $lesson = $section->lessons()->create([
            'title' => $validated['title'],
            'text' => $validated['text'],
            'data' => $validated['data'] ?? [],
            'order' => $order,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('course-management.lessons.show', $lesson)
            ->with('success', 'Lesson created successfully.');
    }

    public function show(CourseLesson $lesson)
    {
        $lesson->load(['section.level.course', 'creator']);

        return Inertia::render('CourseManagement/Lesson/Show', [
            'lesson' => $lesson
        ]);
    }

    public function edit(CourseLesson $lesson)
    {
        $lesson->load('section.level.course');

        return Inertia::render('CourseManagement/Lesson/Edit', [
            'lesson' => $lesson
        ]);
    }

    public function update(Request $request, CourseLesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'text' => 'nullable|string',
            'data' => 'nullable|array',
            'order' => 'nullable|integer|min:0',
        ]);

        $lesson->update($validated);

        return redirect()->route('course-management.lessons.show', $lesson)
            ->with('success', 'Lesson updated successfully.');
    }

    public function destroy(CourseLesson $lesson)
    {
        $sectionId = $lesson->course_section_id;
        $lesson->delete();

        return redirect()->route('course-management.sections.show', $sectionId)
            ->with('success', 'Lesson deleted successfully.');
    }

    public function reorder(Request $request, CourseSection $section)
    {
        $validated = $request->validate([
            'lessons' => 'required|array',
            'lessons.*.id' => 'required|exists:course_lessons,id',
            'lessons.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['lessons'] as $lessonData) {
            CourseLesson::where('id', $lessonData['id'])
                ->where('course_section_id', $section->id)
                ->update(['order' => $lessonData['order']]);
        }

        return response()->json(['success' => true]);
    }
}
