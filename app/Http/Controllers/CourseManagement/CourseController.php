<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Models\CourseManagement\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with(['creator', 'levels'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('CourseManagement/Course/Index', [
            'courses' => $courses
        ]);
    }

    public function create()
    {
        return Inertia::render('CourseManagement/Course/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course = Course::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('course-management.courses.show', $course)
            ->with('success', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        $course->load(['levels.sections.lessons', 'creator']);

        return Inertia::render('CourseManagement/Course/Show', [
            'course' => $course
        ]);
    }

    public function edit(Course $course)
    {
        return Inertia::render('CourseManagement/Course/Edit', [
            'course' => $course
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course->update($validated);

        return redirect()->route('course-management.courses.show', $course)
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('course-management.courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}
