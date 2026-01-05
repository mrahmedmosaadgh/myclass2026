<?php

namespace App\Http\Controllers;

use App\Models\WeeklyPlan;
use App\Models\Schedule;
use App\Models\ScheduleCopy;
use App\Models\Teacher;
use App\Services\WeeklyPlanService;
use App\Services\ScheduleGenerationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WeeklySystemController extends Controller
{
    protected WeeklyPlanService $weeklyPlanService;
    protected ScheduleGenerationService $scheduleService;

    public function __construct(
        WeeklyPlanService $weeklyPlanService,
        ScheduleGenerationService $scheduleService
    ) {
        $this->weeklyPlanService = $weeklyPlanService;
        $this->scheduleService = $scheduleService;
    }

    /**
     * Get authenticated teacher's ID
     */
    protected function getTeacherId(): ?int
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        return $teacher?->id;
    }

    /**
     * Get teacher completion stats for a specific week
     */
    public function getTeacherStats(Request $request): JsonResponse
    {
        $request->validate([
            'week_number' => 'required|integer|min:1',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_number' => 'required|integer|min:1|max:2'
        ]);

        $stats = $this->weeklyPlanService->getTeacherCompletionStats(
            $request->week_number,
            $request->academic_year_id,
            $request->semester_number
        );

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Generate weekly plans for a specific week
     */
    public function generateWeeklyPlans(Request $request): JsonResponse
    {
        $request->validate([
            'copy_id' => 'required|integer|exists:schedule_copies,id',
            'week_number' => 'required|integer|min:1',
            'semester_number' => 'required|integer|min:1|max:2'
        ]);
 
        try {
            $copy = ScheduleCopy::findOrFail($request->copy_id);
            
            $result = $this->weeklyPlanService->generateForWeek(
                $copy,
                $request->week_number,
                $request->semester_number
            );

            return response()->json([
                'success' => true,
                'created' => $result['created'],
                'skipped' => $result['skipped'],
                'message' => "Generated {$result['created']} plans ({$result['skipped']} already existed)"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get teacher's own schedule
     */
    public function getMySchedule(Request $request): JsonResponse
    {
        $teacherId = $this->getTeacherId();

        if (!$teacherId) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. This endpoint is only available for users with a teacher profile.',
                'error_type' => 'no_teacher_profile'
            ], 403);
        }

        $schedules = Schedule::with(['cst.subject', 'cst.classroom', 'cst.teacher', 'copy'])
            ->whereHas('cst', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->whereHas('copy', function ($query) {
                $query->where('status', 'active');
            })
            ->where('active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }

    /**
     * Get teacher's weekly plans for a specific week
     */
    public function getMyWeeklyPlans(Request $request): JsonResponse
    {
        $request->validate([
            'week_number' => 'required|integer|min:1',
            'semester_number' => 'required|integer|min:1|max:2'
        ]);

        $teacherId = $this->getTeacherId();

        if (!$teacherId) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. This endpoint is only available for users with a teacher profile.',
                'error_type' => 'no_teacher_profile'
            ], 403);
        }

        $plans = WeeklyPlan::with(['schedule.cst.subject', 'schedule.cst.classroom', 'schedule.cst.teacher'])
            ->where('week_number', $request->week_number)
            ->where('semester_number', $request->semester_number)
            ->whereHas('schedule.cst', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Get weekly plans (admin can filter by teacher_id)
     */
    public function getWeeklyPlans(Request $request): JsonResponse
    {
        $request->validate([
            'teacher_id' => 'nullable|integer|exists:teachers,id',
            'classroom_id' => 'nullable|integer|exists:classrooms,id',
            'week_number' => 'required|integer|min:1',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_number' => 'required|integer|min:1|max:2'
        ]);

        $query = WeeklyPlan::with(['schedule.cst.subject', 'schedule.cst.classroom', 'schedule.cst.teacher'])
            ->where('week_number', $request->week_number)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('semester_number', $request->semester_number);

        // Filter by teacher if provided
        if ($request->has('teacher_id')) {
            $query->whereHas('schedule.cst', function ($q) use ($request) {
                $q->where('teacher_id', $request->teacher_id);
            });
        }

        // Filter by classroom if provided
        if ($request->has('classroom_id')) {
            $query->whereHas('schedule.cst', function ($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        $plans = $query->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Update a weekly plan (CW/HW/Notes)
     */
    public function updateWeeklyPlan(Request $request, WeeklyPlan $weeklyPlan): JsonResponse
    {
        $request->validate([
            'cw' => 'nullable|string',
            'hw' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        // Optional: Verify the teacher owns this plan
        $teacherId = $this->getTeacherId();
        if ($teacherId && $weeklyPlan->schedule) {
            $planTeacherId = $weeklyPlan->schedule->cst->teacher_id ?? null;
            if ($planTeacherId && $planTeacherId !== $teacherId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only edit your own plans'
                ], 403);
            }
        }

        $weeklyPlan->update([
            'cw' => $request->cw,
            'hw' => $request->hw,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'data' => $weeklyPlan->fresh(),
            'message' => 'Weekly plan updated successfully'
        ]);
    }

    /**
     * Sync a weekly plan with the active schedule
     */
    public function syncHelper(Request $request, WeeklyPlan $weeklyPlan): JsonResponse
    {
        // Check authorization
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Optional: Check if teacher owns the plan
        if ($weeklyPlan->schedule && $weeklyPlan->schedule->cst->teacher_id !== $teacherId) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $success = $this->weeklyPlanService->syncWithSchedule($weeklyPlan);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Plan synced with active schedule',
                'data' => $weeklyPlan->fresh(['schedule.cst'])
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Could not find matching active schedule'
        ], 422);
    }

    /**
     * Bulk sync for a partial week
     */
    public function syncWeek(Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'required|integer',
            'semester_number' => 'required|integer',
            'week_number' => 'required|integer'
        ]);

        // Authorization: Admin only? Or check permission?
        // Assuming Admin or authorized teacher manager.
        // For now, allow auth users (middleware takes care of auth).

        $result = $this->weeklyPlanService->syncWeek(
             $request->academic_year_id,
             $request->semester_number,
             $request->week_number
        );

        return response()->json([
             'success' => true,
             'data' => $result,
             'message' => $result['message'] ?? 'Week synced successfully'
        ]);
    }

    /**
     * Get sync analysis - shows alignment between schedules and weekly plans
     */
    public function getSyncAnalysis(Request $request): JsonResponse
    {
        $request->validate([
            'copy_id' => 'required|integer|exists:schedule_copies,id',
            'week_number' => 'required|integer|min:1',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_number' => 'required|integer|min:1|max:2'
        ]);

        $copyId = $request->copy_id;
        $weekNumber = $request->week_number;
        $academicYearId = $request->academic_year_id;
        $semesterNumber = $request->semester_number;

        // Get all schedules that are placed on the timetable (have day + period)
        // Note: period_order is only for UI display, not a requirement for weekly plans
        $schedules = Schedule::with(['cst.subject', 'cst.classroom', 'cst.teacher'])
            ->where('copy_id', $copyId)
            ->whereNotNull('day_number')
            ->whereNotNull('period_number')
            ->where('active', true)
            ->orderBy('day_number')
            ->orderBy('period_number')
            ->get();

        // Get existing weekly plans
        $existingPlans = WeeklyPlan::where('week_number', $weekNumber)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_number', $semesterNumber)
            ->whereIn('schedule_id', $schedules->pluck('id'))
            ->pluck('schedule_id')
            ->toArray();

        // Aggregate by classroom
        $classroomData = [];
        $totalSlots = 0;
        $totalComplete = 0;
        $totalMissing = 0;

        foreach ($schedules->groupBy('cst.classroom_id') as $classroomId => $classroomSchedules) {
            $classroom = $classroomSchedules->first()->cst->classroom;
            
            $complete = 0;
            $missing = 0;
            $dayBreakdown = [];

            // Group by day
            foreach ($classroomSchedules->groupBy('day_number') as $dayNumber => $daySchedules) {
                $dayComplete = 0;
                $dayMissing = 0;
                $missingPeriods = [];

                foreach ($daySchedules as $schedule) {
                    if (in_array($schedule->id, $existingPlans)) {
                        $dayComplete++;
                        $complete++;
                    } else {
                        $dayMissing++;
                        $missing++;
                        $missingPeriods[] = [
                            'id' => $schedule->id,
                            'period' => $schedule->period_number,
                            'subject' => $schedule->cst->subject->name ?? 'Unknown',
                            'teacher' => $schedule->cst->teacher->name ?? 'Unknown'
                        ];
                    }
                }

                $dayBreakdown[] = [
                    'day' => $this->getDayName($dayNumber),
                    'day_number' => $dayNumber,
                    'total' => count($daySchedules),
                    'complete' => $dayComplete,
                    'missing' => $dayMissing,
                    'missing_periods' => $missingPeriods
                ];
            }

            $classroomTotal = count($classroomSchedules);
            $classroomData[] = [
                'id' => $classroomId,
                'name' => $classroom->name ?? "Classroom {$classroomId}",
                'total_slots' => $classroomTotal,
                'complete' => $complete,
                'missing' => $missing,
                'percentage' => $classroomTotal > 0 ? round(($complete / $classroomTotal) * 100) : 0,
                'days' => $dayBreakdown
            ];

            $totalSlots += $classroomTotal;
            $totalComplete += $complete;
            $totalMissing += $missing;
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total_slots' => $totalSlots,
                'complete' => $totalComplete,
                'missing' => $totalMissing,
                'percentage' => $totalSlots > 0 ? round(($totalComplete / $totalSlots) * 100) : 0
            ],
            'classrooms' => $classroomData
        ]);
    }

    /**
     * Helper to get day name
     */
    private function getDayName($dayNumber): string
    {
        $days = [
            1 => 'Sunday',
            2 => 'Monday',
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
            7 => 'Saturday'
        ];

        return $days[$dayNumber] ?? 'Unknown';
    }
    /**
     * Batch create weekly plans for specific schedule IDs
     */
    public function batchCreate(Request $request): JsonResponse
    {
        $request->validate([
            'schedule_ids' => 'required|array',
            'schedule_ids.*' => 'integer|exists:schedules,id',
            'week_number' => 'required|integer|min:1',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_number' => 'required|integer|min:1|max:2'
        ]);

        $createdCount = 0;
        $skippedCount = 0;

        $schedules = Schedule::whereIn('id', $request->schedule_ids)->get();

        foreach ($schedules as $schedule) {
            $exists = WeeklyPlan::where('schedule_id', $schedule->id)
                ->where('week_number', $request->week_number)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('semester_number', $request->semester_number)
                ->exists();

            if (!$exists) {
                WeeklyPlan::create([
                    'schedule_id' => $schedule->id,
                    'copy_id' => $schedule->copy_id,
                    'week_number' => $request->week_number,
                    'academic_year_id' => $request->academic_year_id,
                    'semester_number' => $request->semester_number,
                    'cw' => '',
                    'hw' => '',
                    'notes' => ''
                ]);
                $createdCount++;
            } else {
                $skippedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully created {$createdCount} plans ({$skippedCount} already existed)",
            'data' => [
                'created' => $createdCount,
                'skipped' => $skippedCount
            ]
        ]);
    }
}
