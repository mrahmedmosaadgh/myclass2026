<?php

namespace App\Http\Controllers\WeeklySystem;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\CurriculumVersion;
use App\Models\my_class\Curriculums\CurriculumLesson;
use Illuminate\Http\Request;

class CurriculumLessonController extends Controller
{
    /**
     * Get lessons for a curriculum (via active version).
     */
    public function index($curriculumId)
    {
        $curriculum = Curriculum::with('activeVersion')->findOrFail($curriculumId);
        
        $version = $curriculum->activeVersion;
        if (!$version) {
            // Fallback: get the first or create a default if missing
            $version = CurriculumVersion::firstOrCreate(
                ['curriculum_id' => $curriculumId, 'status' => 'active'],
                ['title' => 'Default Version', 'version_number' => 1]
            );
        }

        $lessons = CurriculumLesson::where('curriculum_version_id', $version->id)
            ->orderBy('lesson_number')
            ->get();

        return response()->json($lessons);
    }

    /**
     * Store a new lesson in the curriculum's active version.
     */
    public function store(Request $request, $curriculumId)
    {
        $request->validate([
            'lesson_number' => 'required|string',
            'lesson_title' => 'required|string',
            'page_number' => 'nullable|integer',
            'type' => 'nullable|string'
        ]);

        $curriculum = Curriculum::with('activeVersion')->findOrFail($curriculumId);
        $version = $curriculum->activeVersion;

        if (!$version) {
            $version = CurriculumVersion::create([
                'curriculum_id' => $curriculumId,
                'title' => 'Default Version',
                'status' => 'active',
                'version_number' => 1
            ]);
        }

        $lesson = CurriculumLesson::create([
            'curriculum_version_id' => $version->id,
            'lesson_number' => $request->lesson_number,
            'lesson_title' => $request->lesson_title,
            'page_number' => $request->page_number,
            'type' => $request->type ?? 'main'
        ]);

        return response()->json([
            'message' => 'Lesson added successfully.',
            'lesson' => $lesson
        ], 201);
    }

    /**
     * Update a specific lesson.
     */
    public function update(Request $request, $id)
    {
        $lesson = CurriculumLesson::findOrFail($id);

        $request->validate([
            'lesson_number' => 'sometimes|required|string',
            'lesson_title' => 'sometimes|required|string',
            'page_number' => 'nullable|integer',
            'type' => 'nullable|string'
        ]);

        $lesson->update($request->only(['lesson_number', 'lesson_title', 'page_number', 'type']));

        return response()->json([
            'message' => 'Lesson updated successfully.',
            'lesson' => $lesson
        ]);
    }

    /**
     * Delete a lesson.
     */
    public function destroy($id)
    {
        $lesson = CurriculumLesson::findOrFail($id);
        $lesson->delete();

        return response()->json([
            'message' => 'Lesson removed successfully.'
        ]);
    }

    /**
     * Bulk store lessons for a curriculum.
     */
    public function bulkStore(Request $request, $curriculumId)
    {
        $request->validate([
            'lessons' => 'required|array',
            'lessons.*.lesson_number' => 'required|string',
            'lessons.*.lesson_title' => 'required|string',
            'lessons.*.page_number' => 'nullable|integer',
            'lessons.*.type' => 'nullable|string'
        ]);

        $curriculum = Curriculum::with('activeVersion')->findOrFail($curriculumId);
        $version = $curriculum->activeVersion;

        if (!$version) {
            $version = CurriculumVersion::create([
                'curriculum_id' => $curriculumId,
                'title' => 'Default Version',
                'status' => 'active',
                'version_number' => 1
            ]);
        }

        $createdLessons = [];
        foreach ($request->lessons as $lessonData) {
            $createdLessons[] = CurriculumLesson::create([
                'curriculum_version_id' => $version->id,
                'lesson_number' => $lessonData['lesson_number'],
                'lesson_title' => $lessonData['lesson_title'],
                'page_number' => $lessonData['page_number'] ?? null,
                'type' => $lessonData['type'] ?? 'main'
            ]);
        }

        return response()->json([
            'message' => count($createdLessons) . ' lessons added successfully.',
            'lessons' => $createdLessons
        ], 201);
    }
}
