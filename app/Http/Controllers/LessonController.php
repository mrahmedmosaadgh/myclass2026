<?php

namespace App\Http\Controllers;

use App\Models\Curriculum;
use App\Models\my_class\Curriculums\CurriculumLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LessonController extends Controller
{
    /**
     * Display lesson management page
     */
    public function manage($curriculumId)
    {
        $curriculum = Curriculum::with(['school', 'subject', 'grade'])
            ->findOrFail($curriculumId);

        return Inertia::render('my_class/admin/Curriculum/LessonManagement', [
            'curriculum' => $curriculum
        ]);
    }

    /**
     * Get lessons for a curriculum
     */
    public function index($curriculumId)
    {
        $curriculum = Curriculum::findOrFail($curriculumId);
        
        $lessons = CurriculumLesson::where('curriculum_id', $curriculumId)
            ->orderBy('topic_number')
            ->orderBy('lesson_number')
            ->get();

        return response()->json([
            'curriculum' => $curriculum,
            'lessons' => $lessons
        ]);
    }

    /**
     * Store a new lesson
     */
    public function store(Request $request)
    {
        $request->validate([
            'curriculum_id' => 'required|exists:curricula,id',
            'topic_number' => 'required|string',
            'topic_title' => 'required|string',
            'lesson_number' => 'required|string',
            'lesson_title' => 'required|string',
            'page_number' => 'nullable|integer',
            'description' => 'nullable|string',
            'standard' => 'nullable|string',
            'strand' => 'nullable|string',
            'content' => 'nullable|string',
            'skill' => 'nullable|string',
            'activities' => 'nullable|string',
            'assignment' => 'nullable|string',
            'assessment' => 'nullable|string',
            'notes_admin' => 'nullable|string',
            'notes_teacher' => 'nullable|string',
            'objective' => 'nullable|string',
            'type' => 'required|in:main,revision,quiz,project,extra',
            'selected' => 'boolean'
        ]);

        $curriculum = Curriculum::findOrFail($request->curriculum_id);

        try {
            $lesson = CurriculumLesson::create([
                'school_id' => $curriculum->school_id,
                'curriculum_id' => $request->curriculum_id,
                'topic_number' => $request->topic_number,
                'topic_title' => $request->topic_title,
                'lesson_number' => $request->lesson_number,
                'lesson_title' => $request->lesson_title,
                'page_number' => $request->page_number,
                'description' => $request->description,
                'standard' => $request->standard,
                'strand' => $request->strand,
                'content' => $request->content,
                'skill' => $request->skill,
                'activities' => $request->activities,
                'assignment' => $request->assignment,
                'assessment' => $request->assessment,
                'notes_admin' => $request->notes_admin,
                'notes_teacher' => $request->notes_teacher,
                'objective' => $request->objective,
                'type' => $request->type,
                'selected' => $request->selected ? 1 : 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lesson created successfully',
                'lesson' => $lesson
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating lesson: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a lesson
     */
    public function update(Request $request, CurriculumLesson $lesson)
    {
        $request->validate([
            'topic_number' => 'required|string',
            'topic_title' => 'required|string',
            'lesson_number' => 'required|string',
            'lesson_title' => 'required|string',
            'page_number' => 'nullable|integer',
            'description' => 'nullable|string',
            'standard' => 'nullable|string',
            'strand' => 'nullable|string',
            'content' => 'nullable|string',
            'skill' => 'nullable|string',
            'activities' => 'nullable|string',
            'assignment' => 'nullable|string',
            'assessment' => 'nullable|string',
            'notes_admin' => 'nullable|string',
            'notes_teacher' => 'nullable|string',
            'objective' => 'nullable|string',
            'type' => 'required|in:main,revision,quiz,project,extra',
            'selected' => 'boolean'
        ]);

        try {
            $lesson->update([
                'topic_number' => $request->topic_number,
                'topic_title' => $request->topic_title,
                'lesson_number' => $request->lesson_number,
                'lesson_title' => $request->lesson_title,
                'page_number' => $request->page_number,
                'description' => $request->description,
                'standard' => $request->standard,
                'strand' => $request->strand,
                'content' => $request->content,
                'skill' => $request->skill,
                'activities' => $request->activities,
                'assignment' => $request->assignment,
                'assessment' => $request->assessment,
                'notes_admin' => $request->notes_admin,
                'notes_teacher' => $request->notes_teacher,
                'objective' => $request->objective,
                'type' => $request->type,
                'selected' => $request->selected ? 1 : 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lesson updated successfully',
                'lesson' => $lesson
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating lesson: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update lesson number only
     */
    public function updateLessonNumber(Request $request, CurriculumLesson $lesson)
    {
        $request->validate([
            'lesson_number' => 'required|string'
        ]);

        try {
            $lesson->update([
                'lesson_number' => $request->lesson_number
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lesson number updated successfully',
                'lesson' => $lesson
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating lesson number: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a lesson
     */
    public function destroy(CurriculumLesson $lesson)
    {
        try {
            $lesson->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lesson deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting lesson: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder lessons
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'lessons' => 'required|array',
            'lessons.*.id' => 'required|exists:curriculum_lessons,id',
            'lessons.*.lesson_number' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->lessons as $lessonData) {
                CurriculumLesson::where('id', $lessonData['id'])
                    ->update(['lesson_number' => $lessonData['lesson_number']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lessons reordered successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error reordering lessons: ' . $e->getMessage()
            ], 500);
        }
    }
}
