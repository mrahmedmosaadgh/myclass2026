<?php

namespace App\Services;

use App\Models\WeeklyPlan;
use App\Models\WeeklyPlanSession;
use App\Models\ClassroomSubjectTeacher;
use App\Models\AcademicYear;

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
        
        for ($week = 1; $week <= $totalWeeks; $week++) {
            $plan = WeeklyPlan::firstOrCreate([
                'academic_year_id' => $academicYearId,
                'semester_number' => $semester,
                'week_number' => $week,
                'cst_id' => $cstId,
            ]);
            
            // Generate initial sessions if none exist
            $this->generateInitialSessions($plan);
            
            $plans[] = $plan->load(['sessions' => function($query) {
                $query->orderBy('session_index');
            }]);
        }
        
        return $plans;
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
}