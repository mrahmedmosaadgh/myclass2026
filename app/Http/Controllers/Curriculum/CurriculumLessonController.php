<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\my_class\Curriculums\CurriculumLesson;
use App\Models\my_class\Curriculums\CurriculumTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurriculumLessonController extends Controller
{
    /**
     * Get all topics with their lessons for a curriculum
     */
    public function index(Curriculum $curriculum)
    {
        $topics = CurriculumTopic::where('curriculum_id', $curriculum->id)
            ->with('lessons')
            ->orderBy('number')
            ->get();

        return response()->json($topics);
    }

    /**
     * Store a new lesson
     */
    public function store(Request $request)
    {
        $request->validate([
            'topic_id' => 'required|exists:curriculum_topics,id',
            'lesson_number' => 'required|string|max:255',
            'lesson_title' => 'required|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'page_number' => 'nullable|integer',
            'standard' => 'nullable|string',
            'strand' => 'nullable|string',
            'skill' => 'nullable|string',
            'activities' => 'nullable|string',
            'assignment' => 'nullable|string',
            'assessment' => 'nullable|string',
            'objective' => 'nullable|string',
            'type' => 'nullable|in:main,revision,quiz,project,extra'
        ]);

        try {
            $lesson = CurriculumLesson::create([
                'topic_id' => $request->topic_id,
                'school_id' => $request->school_id,
                'lesson_number' => $request->lesson_number,
                'lesson_title' => $request->lesson_title,
                'description' => $request->description,
                'content' => $request->content,
                'page_number' => $request->page_number,
                'standard' => $request->standard,
                'strand' => $request->strand,
                'skill' => $request->skill,
                'activities' => $request->activities,
                'assignment' => $request->assignment,
                'assessment' => $request->assessment,
                'objective' => $request->objective,
                'type' => $request->type ?? 'main'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lesson created successfully',
                'lesson' => $lesson->load('topic')
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
            'lesson_number' => 'required|string|max:255',
            'lesson_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'page_number' => 'nullable|integer',
            'standard' => 'nullable|string',
            'strand' => 'nullable|string',
            'skill' => 'nullable|string',
            'activities' => 'nullable|string',
            'assignment' => 'nullable|string',
            'assessment' => 'nullable|string',
            'objective' => 'nullable|string',
            'type' => 'nullable|in:main,revision,quiz,project,extra'
        ]);

        try {
            $lesson->update([
                'lesson_number' => $request->lesson_number,
                'lesson_title' => $request->lesson_title,
                'description' => $request->description,
                'content' => $request->content,
                'page_number' => $request->page_number,
                'standard' => $request->standard,
                'strand' => $request->strand,
                'skill' => $request->skill,
                'activities' => $request->activities,
                'assignment' => $request->assignment,
                'assessment' => $request->assessment,
                'objective' => $request->objective,
                'type' => $request->type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lesson updated successfully',
                'lesson' => $lesson->load('topic')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating lesson: ' . $e->getMessage()
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
     * Reorder lessons within a topic
     */
    public function reorder(Request $request, CurriculumTopic $topic)
    {
        $request->validate([
            'lessons' => 'required|array',
            'lessons.*.id' => 'required|integer',
            'lessons.*.lesson_number' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->lessons as $lessonData) {
                CurriculumLesson::where('id', $lessonData['id'])
                    ->update([
                        'lesson_number' => $lessonData['lesson_number']
                    ]);
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
