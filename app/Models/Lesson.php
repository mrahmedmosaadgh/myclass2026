<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'curriculum_id',
        'lesson_number',
        'title',
        'page_number',
        'position',
        'description',
        'data',
    ];

    protected $casts = [
        'lesson_number' => 'integer',
        'page_number' => 'integer',
        'position' => 'integer',
        'data' => 'json',
    ];

    /**
     * Get the curriculum that owns the lesson.
     */
    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }

    /**
     * Insert a new lesson at a specific position in the sequence.
     * This will adjust the position of all lessons after the inserted position.
     *
     * @param array $data The lesson data
     * @param int $position The position to insert at (0-based)
     * @return Lesson
     */
    public static function insertAtPosition(array $data, int $position): Lesson
    {
        // Start a database transaction
        return DB::transaction(function () use ($data, $position) {
            // Get the curriculum ID from the data
            $curriculumId = $data['curriculum_id'];

            // Shift positions of existing lessons to make room for the new one
            self::where('curriculum_id', $curriculumId)
                ->where('position', '>=', $position)
                ->increment('position');

            // Set the position in the data
            $data['position'] = $position;

            // Determine the lesson number
            if (!isset($data['lesson_number'])) {
                // Get the highest lesson number for this curriculum
                $maxLessonNumber = self::where('curriculum_id', $curriculumId)
                    ->max('lesson_number') ?? 0;

                $data['lesson_number'] = $maxLessonNumber + 1;
            }

            // Create and return the new lesson
            return self::create($data);
        });
    }

    /**
     * Reorder lessons based on a new sequence of lesson IDs.
     *
     * @param int $curriculumId The curriculum ID
     * @param array $lessonIds Ordered array of lesson IDs
     * @return bool
     */
    public static function reorder(int $curriculumId, array $lessonIds): bool
    {
        return DB::transaction(function () use ($curriculumId, $lessonIds) {
            // Verify all lessons belong to the specified curriculum
            $count = self::whereIn('id', $lessonIds)
                ->where('curriculum_id', $curriculumId)
                ->count();

            if ($count !== count($lessonIds)) {
                return false; // Not all lessons belong to this curriculum
            }

            // Update positions based on the new order
            foreach ($lessonIds as $position => $lessonId) {
                self::where('id', $lessonId)
                    ->update(['position' => $position]);
            }

            // Temporarily set all lesson_numbers to negative values to avoid unique constraint conflicts
            // This works because the unique constraint is on (curriculum_id, lesson_number)
            $lessons = self::where('curriculum_id', $curriculumId)
                ->orderBy('position')
                ->get();

            // First pass: set all to negative values (which won't conflict with existing positive values)
            foreach ($lessons as $index => $lesson) {
                $lesson->lesson_number = -($index + 1);
                $lesson->save();
            }

            // Second pass: set to the final positive values
            foreach ($lessons as $index => $lesson) {
                $lesson->lesson_number = $index + 1;
                $lesson->save();
            }

            return true;
        });
    }

    /**
     * Get lessons for a curriculum ordered by position.
     *
     * @param int $curriculumId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getOrderedLessons(int $curriculumId)
    {
        return self::where('curriculum_id', $curriculumId)
            ->orderBy('position')
            ->get();
    }

    /**
     * Safely update a lesson's lesson_number, avoiding unique constraint violations.
     *
     * @param int $lessonId The ID of the lesson to update
     * @param int $newLessonNumber The new lesson number
     * @return bool Whether the update was successful
     */
    public static function safelyUpdateLessonNumber(int $lessonId, int $newLessonNumber): bool
    {
        return DB::transaction(function () use ($lessonId, $newLessonNumber) {
            // Get the lesson to update
            $lesson = self::findOrFail($lessonId);
            $curriculumId = $lesson->curriculum_id;

            // Check if the new lesson number already exists for this curriculum
            $existingLesson = self::where('curriculum_id', $curriculumId)
                ->where('lesson_number', $newLessonNumber)
                ->where('id', '!=', $lessonId)
                ->first();

            if ($existingLesson) {
                // There's a conflict, so we need to use a temporary number
                // First, set the existing lesson to a temporary negative number
                $existingLesson->lesson_number = -$existingLesson->lesson_number;
                $existingLesson->save();

                // Now update our target lesson
                $lesson->lesson_number = $newLessonNumber;
                $lesson->save();

                // Finally, update the other lesson to the original lesson's number
                $existingLesson->lesson_number = $lesson->getOriginal('lesson_number');
                $existingLesson->save();
            } else {
                // No conflict, just update directly
                $lesson->lesson_number = $newLessonNumber;
                $lesson->save();
            }

            return true;
        });
    }
}
