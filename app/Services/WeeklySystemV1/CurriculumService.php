<?php

namespace App\Services\WeeklySystemV1;

use App\Models\Curriculum;
use App\Models\Teacher;
use Illuminate\Support\Collection;

class CurriculumService
{
    /**
     * Get all curricula for a school
     */
    public function getSchoolCurricula(int $schoolId): Collection
    {
        return Curriculum::with(['grade', 'subject'])
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get curricula assigned to a specific teacher
     */
    public function getTeacherAssignedCurricula(int $teacherId): Collection
    {
        return Curriculum::with(['grade', 'subject'])
            ->whereHas('classroomSubjectTeachers', function($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new curriculum
     */
    public function createCurriculum(array $data, int $schoolId): Curriculum
    {
        // Check for duplicate name in the same school
        $exists = Curriculum::where('school_id', $schoolId)
            ->where('name', $data['name'])
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            throw new \InvalidArgumentException('Curriculum name already exists in your school.');
        }

        $curriculum = Curriculum::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'school_id' => $schoolId,
            'grade_id' => $data['grade_id'],
            'subject_id' => $data['subject_id'],
            'edit_lock_date' => $data['edit_lock_date'] ?? null,
        ]);

        return $curriculum;
    }

    /**
     * Update an existing curriculum
     */
    public function updateCurriculum(Curriculum $curriculum, array $data): Curriculum
    {
        // Check duplicate name if name is being changed
        if (isset($data['name']) && $data['name'] !== $curriculum->name) {
            $exists = Curriculum::where('school_id', $curriculum->school_id)
                ->where('name', $data['name'])
                ->where('id', '!=', $curriculum->id)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                throw new \InvalidArgumentException('Curriculum name already exists in your school.');
            }
        }

        $curriculum->update($data);
        return $curriculum->fresh();
    }

    /**
     * Delete a curriculum (soft delete)
     */
    public function deleteCurriculum(Curriculum $curriculum): bool
    {
        return $curriculum->delete();
    }

    /**
     * Set lock date for a curriculum
     */
    public function setLockDate(Curriculum $curriculum, ?string $date): Curriculum
    {
        $curriculum->update([
            'edit_lock_date' => $date
        ]);

        return $curriculum->fresh();
    }

    /**
     * Check if a curriculum is editable (not locked)
     */
    public function isEditable(Curriculum $curriculum): bool
    {
        if (!$curriculum->edit_lock_date) {
            return true;
        }

        return $curriculum->edit_lock_date->isFuture();
    }

    /**
     * Format curriculum for API response
     */
    public function formatForApi(Curriculum $curriculum): array
    {
        return [
            'id' => $curriculum->id,
            'name' => $curriculum->name,
            'description' => $curriculum->description,
            'grade_name' => $curriculum->grade?->name ?? 'N/A',
            'subject_name' => $curriculum->subject?->name ?? 'N/A',
            'edit_lock_date' => $curriculum->edit_lock_date?->format('Y-m-d'),
            'created_at' => $curriculum->created_at->format('Y-m-d'),
            'grade_id' => $curriculum->grade_id,
            'subject_id' => $curriculum->subject_id,
            'is_editable' => $this->isEditable($curriculum),
        ];
    }
}
