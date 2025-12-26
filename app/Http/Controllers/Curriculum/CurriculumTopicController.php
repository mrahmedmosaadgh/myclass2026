<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\my_class\Curriculums\CurriculumTopic;
use App\Models\my_class\Curriculums\CurriculumLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurriculumTopicController extends Controller
{
    /**
     * Get all topics for a curriculum
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
     * Store a new topic
     */
    public function store(Request $request)
    {
        $request->validate([
            'curriculum_id' => 'required|exists:curricula,id',
            'number' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            $topic = CurriculumTopic::create([
                'curriculum_id' => $request->curriculum_id,
                'number' => $request->number,
                'title' => $request->title,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Topic created successfully',
                'topic' => $topic
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating topic: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a topic
     */
    public function update(Request $request, CurriculumTopic $topic)
    {
        $request->validate([
            'number' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            $topic->update([
                'number' => $request->number,
                'title' => $request->title,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Topic updated successfully',
                'topic' => $topic
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating topic: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a topic
     */
    public function destroy(CurriculumTopic $topic)
    {
        try {
            $topic->delete();

            return response()->json([
                'success' => true,
                'message' => 'Topic deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting topic: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder topics
     */
    public function reorder(Request $request, Curriculum $curriculum)
    {
        $request->validate([
            'topics' => 'required|array',
            'topics.*.id' => 'required|integer',
            'topics.*.number' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->topics as $topicData) {
                CurriculumTopic::where('id', $topicData['id'])
                    ->update([
                        'number' => $topicData['number']
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Topics reordered successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error reordering topics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk import topics and lessons for a curriculum
     */
    public function bulkImportTopicsLessons(Request $request, Curriculum $curriculum)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.topic_number' => 'required',
            'data.*.topic_title' => 'required',
            'data.*.lesson_number' => 'required',
            'data.*.lesson_title' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $importedTopicsCount = 0;
            $importedLessonsCount = 0;
            $topicsMap = []; // To cache created topics in this request

            foreach ($request->data as $row) {
                $topicNumber = trim($row['topic_number']);
                $topicTitle = trim($row['topic_title']);
                $topicDescription = $row['topic_description'] ?? '';

                // Create or find topic within this curriculum
                $topicKey = $topicNumber . '_' . $topicTitle;
                if (!isset($topicsMap[$topicKey])) {
                    $topic = CurriculumTopic::updateOrCreate(
                        [
                            'curriculum_id' => $curriculum->id,
                            'number' => $topicNumber,
                        ],
                        [
                            'title' => $topicTitle,
                            'description' => $topicDescription,
                        ]
                    );
                    $topicsMap[$topicKey] = $topic;
                    $importedTopicsCount++;
                } else {
                    $topic = $topicsMap[$topicKey];
                }

                // Create or update lesson
                CurriculumLesson::updateOrCreate(
                    [
                        'topic_id' => $topic->id,
                        'lesson_number' => trim($row['lesson_number']),
                    ],
                    [
                        'school_id' => $curriculum->school_id,
                        'lesson_title' => trim($row['lesson_title']),
                        'description' => $row['lesson_description'] ?? '',
                        'content' => $row['lesson_content'] ?? '',
                        'type' => $row['lesson_type'] ?? 'main',
                        'page_number' => isset($row['page_number']) ? (int)$row['page_number'] : null,
                        'standard' => $row['standard'] ?? null,
                        'strand' => $row['strand'] ?? null,
                        'skill' => $row['skill'] ?? null,
                        'activities' => $row['activities'] ?? null,
                        'assignment' => $row['assignment'] ?? null,
                        'assessment' => $row['assessment'] ?? null,
                        'objective' => $row['objective'] ?? null,
                    ]
                );
                $importedLessonsCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$importedTopicsCount} topics and {$importedLessonsCount} lessons.",
                'imported_topics' => $importedTopicsCount,
                'imported_lessons' => $importedLessonsCount
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error importing data: ' . $e->getMessage()
            ], 500);
        }
    }
}
