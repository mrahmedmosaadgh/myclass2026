<?php

namespace App\Services;

use App\Models\WeeklyPlan;
use App\Models\WeeklyPlanSession;
use App\Models\ClassroomSubjectTeacher;
use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\Schedule;

class WeeklyPlanService
{
    /**
     * Generate initial sessions for a weekly plan based on classes_per_week.
     */
    public function generateInitialSessions(WeeklyPlan $weeklyPlan): void
    {
        $cst = $weeklyPlan->classroomSubjectTeacher;
        $classesPerWeek = $cst->classes_per_week;

        // Don't generate if sessions already exist
        if ($weeklyPlan->sessions()->count() > 0) {
            return;
        }

        for ($sessionIndex = 1; $sessionIndex <= $classesPerWeek; $sessionIndex++) {
            $weeklyPlan->sessions()->create([
                'session_index' => $sessionIndex,
                'period_code' => $this->generatePeriodCode(
                    $weeklyPlan->academic_year_id,
                    $weeklyPlan->semester_number,
                    $weeklyPlan->week_number,
                    $sessionIndex
                ),
                'type' => 'lesson',
                'title' => "Session {$sessionIndex}",
                'data' => [],
            ]);
        }
    }

    /**
     * Generate period code in format: year.semester.week.session
     */
    public function generatePeriodCode(int $academicYearId, int $semester, int $week, int $session): string
    {
        // Get the last 2 digits of academic year for shorter code
        $academicYear = AcademicYear::find($academicYearId);
        $yearCode = $academicYear ? substr($academicYear->name, -2) : substr($academicYearId, -2);
        
        return sprintf('%s.%d.%d.%d', $yearCode, $semester, $week, $session);
    }

    /**
     * Validate period code format.
     */
    public function validatePeriodCode(string $periodCode): bool
    {
        return preg_match('/^\d+\.\d+\.\d+\.\d+$/', $periodCode) === 1;
    }

    /**
     * Parse period code into components.
     */
    public function parsePeriodCode(string $periodCode): array
    {
        $parts = explode('.', $periodCode);
        
        return [
            'year' => $parts[0] ?? null,
            'semester' => (int)($parts[1] ?? 0),
            'week' => (int)($parts[2] ?? 0),
            'session' => (int)($parts[3] ?? 0),
        ];
    }

    /**
     * Calculate next available session index for a weekly plan.
     */
    public function getNextSessionIndex(WeeklyPlan $weeklyPlan): int
    {
        $maxIndex = $weeklyPlan->sessions()->max('session_index');
        return ($maxIndex ?? 0) + 1;
    }

    /**
     * Reorder sessions within a weekly plan.
     */
    public function reorderSessions(WeeklyPlan $weeklyPlan, array $sessionOrder): void
    {
        foreach ($sessionOrder as $index => $sessionId) {
            WeeklyPlanSession::where('id', $sessionId)
                ->where('weekly_plan_id', $weeklyPlan->id)
                ->update(['session_index' => $index + 1]);
        }
    }

    /**
     * Bulk update period codes for schedule changes.
     */
    public function updatePeriodCodes(array $updates): array
    {
        $results = [];
        
        foreach ($updates as $update) {
            $session = WeeklyPlanSession::find($update['session_id']);
            
            if ($session && $this->validatePeriodCode($update['new_period_code'])) {
                $session->update(['period_code' => $update['new_period_code']]);
                $results[] = [
                    'session_id' => $session->id,
                    'status' => 'updated',
                    'old_period_code' => $update['old_period_code'] ?? null,
                    'new_period_code' => $update['new_period_code']
                ];
            } else {
                $results[] = [
                    'session_id' => $update['session_id'],
                    'status' => 'failed',
                    'error' => $session ? 'Invalid period code format' : 'Session not found'
                ];
            }
        }
        
        return $results;
    }

    /**
     * Find sessions with invalid period codes.
     */
    public function findInvalidPeriodCodes(): array
    {
        $sessions = WeeklyPlanSession::all();
        $invalid = [];
        
        foreach ($sessions as $session) {
            if (!$this->validatePeriodCode($session->period_code)) {
                $invalid[] = [
                    'session_id' => $session->id,
                    'weekly_plan_id' => $session->weekly_plan_id,
                    'period_code' => $session->period_code,
                    'title' => $session->title,
                ];
            }
        }
        
        return $invalid;
    }

    /**
     * Generate weekly plans for an entire semester.
     */
    public function generateSemesterPlans(int $cstId, int $academicYearId, int $semester, int $totalWeeks = 18): array
    {
        $cst = ClassroomSubjectTeacher::findOrFail($cstId);
        $plans = [];
        
        // Find a valid schedule for this CST to satisfy foreign keys
        // We assume valid schedules are active
        $schedule = Schedule::where('cst_id', $cstId)
            ->where('active', true)
            ->first();
            
        $scheduleId = $schedule ? $schedule->id : null;

        for ($week = 1; $week <= $totalWeeks; $week++) {
            // Prepare attributes for creation
            $attributes = [
                // 'copy_id' => null, // Removed
                'schedule_id' => $scheduleId,
            ];

            // If we don't have required foreign keys and they match table constraints, 
            // creation might fail. But we assume a schedule exists if we are generating plans.
            // If strictly required by DB, we must have them.
            
            $plan = WeeklyPlan::firstOrCreate(
                [
                    'academic_year_id' => $academicYearId,
                    'semester_number' => $semester,
                    'week_number' => $week,
                    'cst_id' => $cstId,
                ],
                $attributes
            );
            
            // Generate initial sessions if none exist
            $this->generateInitialSessions($plan);
            
            $plans[] = $plan->load(['sessions' => function($query) {
                $query->orderBy('session_index');
            }]);
        }
        
        return $plans;
    }

    /**
     * Sync a weekly plan with the active schedule to update teacher/CST.
     */
    public function syncWithSchedule(WeeklyPlan $weeklyPlan): bool
    {
        // Get current CST to find meaningful match
        $currentCst = $weeklyPlan->classroomSubjectTeacher;
        if (!$currentCst) {
             return false;
        }

        // Find a schedule in the active copy (active=true) that matches the class and subject
        // We filter by school/academic_year implicitly via CST relationships if needed,
        // but since we are looking for a replacement schedule in the SAME context but maybe different teacher/slot,
        // we assume the plan belongs to a specific school context.

        $newSchedule = Schedule::where('school_id', $currentCst->school_id) // Match school
            // ->where('academic_year_id', $weeklyPlan->academic_year_id) // Schedules might not have AY directly without join
            ->where('active', true)
            ->whereHas('cst', function($q) use ($currentCst) {
                $q->where('classroom_id', $currentCst->classroom_id)
                  ->where('subject_id', $currentCst->subject_id);
            })
            ->first();

        if ($newSchedule) {
            $weeklyPlan->update([
                // 'copy_id' => null, // Removed
                'schedule_id' => $newSchedule->id,
                'cst_id' => $newSchedule->cst_id, // Update teacher if changed
            ]);
            return true;
        }

        return false;
    }

    /**
     * Sync all weekly plans for a specific week with the active schedule.
     */
    public function syncWeek(int $academicYearId, int $semester, int $week): array
    {
        // 1. Get all active schedules for this academic year context (via school?)
        // We need a school_id context usually, or we process all?
        // Let's assume this is called for a specific school context usually, but the signature doesn't have school_id.
        // However, we can query schedules that have CSTs in this academic_year context.
        
        $activeSchedules = Schedule::where('active', true)
            ->whereHas('cst', function($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            })
            ->get();

        if ($activeSchedules->isEmpty()) {
            return ['created' => 0, 'updated' => 0, 'message' => 'No active schedules found'];
        }

        $created = 0;
        $updated = 0;

        foreach ($activeSchedules as $schedule) {
            // Find plan for this specific schedule slot and week
            $plan = WeeklyPlan::where('week_number', $week)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_number', $semester)
                ->where('schedule_id', $schedule->id)
                ->first();

            if ($plan) {
                // Update existing plan to ensure it's linked correctly
                $plan->update([
                    // 'copy_id' => null, // Removed
                    'schedule_id' => $schedule->id
                ]);
                $updated++;
            } else {
                // Create missing plan
                WeeklyPlan::create([
                    // 'copy_id' => null, // Removed
                    'schedule_id' => $schedule->id,
                    'academic_year_id' => $academicYearId,
                    'semester_number' => $semester,
                    'week_number' => $week,
                    'cw' => '',
                    'hw' => '',
                    'notes' => ''
                ]);
                
                $created++;
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'total_plans' => $activeSchedules->count(),
            'message' => "Successfully synced week {$week}: {$created} created, {$updated} updated."
        ];
    }

    /**
     * Copy sessions from one week to another.
     */
    public function copyWeekSessions(WeeklyPlan $sourceWeek, WeeklyPlan $targetWeek): void
    {
        // Clear existing sessions in target week
        $targetWeek->sessions()->delete();
        
        // Copy sessions from source week
        foreach ($sourceWeek->sessions as $session) {
            $targetWeek->sessions()->create([
                'session_index' => $session->session_index,
                'period_code' => $this->generatePeriodCode(
                    $targetWeek->academic_year_id,
                    $targetWeek->semester_number,
                    $targetWeek->week_number,
                    $session->session_index
                ),
                'type' => $session->type,
                'title' => $session->title,
                'data' => $session->data,
            ]);
        }
    }

    /**
     * Get teacher completion stats for a specific week.
     */
    public function getTeacherCompletionStats(int $week, int $academicYearId, int $semester): array
    {
        $teachers = Teacher::whereHas('classroomSubjectTeachers', function ($query) use ($academicYearId) {
            // Filter teachers who have active assignments
            $query->whereHas('schedules', function ($q) {
                $q->where('active', true);
            });
        })->get();

        $stats = [];

        foreach ($teachers as $teacher) {
            $plans = WeeklyPlan::where('week_number', $week)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_number', $semester)
                ->whereHas('schedule.cst', function ($query) use ($teacher) {
                    $query->where('teacher_id', $teacher->id);
                })
                ->get();

            $totalPlans = $plans->count();
            if ($totalPlans === 0) {
                // If no plans exist, we do not show any stats for this teacher
                continue;
            }

            $completed = 0;
            $partial = 0;
            $empty = 0;

            foreach ($plans as $plan) {
                $hasCw = !empty(trim($plan->cw ?? ''));
                $hasHw = !empty(trim($plan->hw ?? ''));

                if ($hasCw && $hasHw) {
                    $completed++;
                } elseif ($hasCw || $hasHw) {
                    $partial++;
                } else {
                    $empty++;
                }
            }

            $stats[] = [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name,
                'completed' => $completed,
                'partial' => $partial,
                'empty' => $empty,
                'total' => $totalPlans,
                'percentage' => round(($completed / $totalPlans) * 100)
            ];
        }

        return $stats;
    }

    /**
     * Generate weekly plans for a week based on school/year/semester context.
     */
    public function generateForWeek(int $schoolId, int $academicYearId, int $week, int $semester): array
    {
        $schedules = Schedule::where('school_id', $schoolId)
            ->where('active', true)
            ->with(['cst'])
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($schedules as $schedule) {
            $exists = WeeklyPlan::where('schedule_id', $schedule->id)
                ->where('week_number', $week)
                ->where('semester_number', $semester)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            WeeklyPlan::create([
                'schedule_id' => $schedule->id,
                // 'copy_id' => null, // Removed
                'academic_year_id' => $academicYearId,
                'semester_number' => $semester,
                'week_number' => $week,
                'cw' => '',
                'hw' => '',
                'notes' => ''
            ]);

            $created++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped
        ];
    }
}