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
                'message' => 'No teacher profile found for this user'
            ], 404);
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
                'message' => 'No teacher profile found for this user'
            ], 404);
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
}
