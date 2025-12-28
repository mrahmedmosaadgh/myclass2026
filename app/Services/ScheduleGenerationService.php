<?php

namespace App\Services;

use App\Models\ScheduleCopy;
use App\Models\Schedule;
use App\Models\Classroom;
use App\Models\ClassroomSubjectTeacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleGenerationService
{
    /**
     * Number of days in a week (Sunday-Thursday)
     */
    const DAYS_PER_WEEK = 5;

    /**
     * Number of periods per day
     */
    const PERIODS_PER_DAY = 8;

    /**
     * Generate empty schedule slots for a schedule copy
     *
     * @param ScheduleCopy $copy
     * @return array ['created' => int, 'skipped' => int]
     * @throws \Exception
     */
    public function generateForCopy(ScheduleCopy $copy): array
    {
        // Validate no duplicate generation
        if ($this->hasExistingSchedules($copy)) {
            throw new \Exception('Schedules already exist for this copy. Delete existing schedules first.');
        }

        // Validate active status
        if ($copy->status === 'active') {
            $this->validateActiveStatus($copy);
        }

        // Get all classrooms for the school
        $classrooms = Classroom::where('school_id', $copy->school_id)
            ->where('active', true)
            ->get();

        if ($classrooms->isEmpty()) {
            throw new \Exception('No active classrooms found for this school.');
        }

        $createdCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            // Get all CSTs for the school to create schedule entries
            $csts = ClassroomSubjectTeacher::where('school_id', $copy->school_id)
                ->where('academic_year_id', $copy->academic_year_id)
                ->get()
                ->groupBy('classroom_id');

            foreach ($classrooms as $classroom) {
                // Get CSTs for this classroom
                $classroomCsts = $csts->get($classroom->id, collect());
                
                for ($day = 1; $day <= self::DAYS_PER_WEEK; $day++) {
                    for ($period = 1; $period <= self::PERIODS_PER_DAY; $period++) {
                        // Create schedule slot - cst_id is NULL initially (empty slot)
                        Schedule::create([
                            'copy_id' => $copy->id,
                            'school_id' => $copy->school_id,
                            'cst_id' => null, // Will be assigned later by admin
                            'day_number' => $day,
                            'period_number' => $period,
                            'period_code' => "d{$day}p{$period}",
                            'active' => true,
                        ]);
                        $createdCount++;
                    }
                }
            }

            DB::commit();
            
            Log::info("Generated {$createdCount} schedule slots for copy {$copy->id}");
            
            return [
                'created' => $createdCount,
                'skipped' => $skippedCount,
                'classrooms' => $classrooms->count()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to generate schedules: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if schedules already exist for this copy
     *
     * @param ScheduleCopy $copy
     * @return bool
     */
    public function hasExistingSchedules(ScheduleCopy $copy): bool
    {
        return Schedule::where('copy_id', $copy->id)->exists();
    }

    /**
     * Validate only ONE active copy per school+year+semester
     *
     * @param ScheduleCopy $copy
     * @return bool
     * @throws \Exception
     */
    public function validateActiveStatus(ScheduleCopy $copy): bool
    {
        $existingActive = ScheduleCopy::where('school_id', $copy->school_id)
            ->where('academic_year_id', $copy->academic_year_id)
            ->where('semester_id', $copy->semester_id)
            ->where('status', 'active')
            ->where('id', '!=', $copy->id)
            ->exists();

        if ($existingActive) {
            throw new \Exception(
                'An active schedule copy already exists for this school, academic year, and semester. ' .
                'Please archive the existing active copy before activating this one.'
            );
        }

        return true;
    }

    /**
     * Delete all schedules for a copy (for regeneration)
     *
     * @param ScheduleCopy $copy
     * @return int Number of deleted schedules
     */
    public function deleteSchedulesForCopy(ScheduleCopy $copy): int
    {
        return Schedule::where('copy_id', $copy->id)->delete();
    }

    /**
     * Get statistics for a schedule copy
     *
     * @param ScheduleCopy $copy
     * @return array
     */
    public function getStatistics(ScheduleCopy $copy): array
    {
        $schedules = Schedule::where('copy_id', $copy->id);
        
        $total = $schedules->count();
        $assigned = Schedule::where('copy_id', $copy->id)
            ->whereNotNull('cst_id')
            ->count();
        $empty = $total - $assigned;

        return [
            'total_slots' => $total,
            'assigned_slots' => $assigned,
            'empty_slots' => $empty,
            'completion_percentage' => $total > 0 ? round(($assigned / $total) * 100, 1) : 0
        ];
    }
}
