<?php

namespace App\Http\Controllers;

use App\Models\WeeklyPlan;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Services\WeeklyPlanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WeeklySystemController extends Controller
{
    protected WeeklyPlanService $weeklyPlanService;

    public function __construct(
        WeeklyPlanService $weeklyPlanService
    ) {
        $this->weeklyPlanService = $weeklyPlanService;
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
    /**
     * Generate weekly plans for a specific week
     */
    public function generateWeeklyPlans(Request $request): JsonResponse
    {
        $request->validate([
            'school_id' => 'required|integer|exists:schools,id',
            'week_number' => 'required|integer|min:1',
            'semester_number' => 'required|integer|min:1|max:2',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
        ]);
 
        try {
            // Updated to pass IDs directly to service instead of ScheduleCopy object
            $result = $this->weeklyPlanService->generateForWeek(
                $request->school_id,
                $request->academic_year_id,
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

        $schedules = Schedule::with(['cst.subject', 'cst.classroom', 'cst.teacher'])
            ->whereHas('cst', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
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
     * Copy weekly plans (CW/HW/Notes) from one classroom to multiple classrooms for the current teacher and week.
     * - Source: all plans for the selected week/semester in the from_classroom
     * - Target: matching schedules in the to classrooms for same teacher, same subject, same day
     * - Important: Use period_order (NOT period_number) for ordering/matching.
     * - Reindex period_order starting from 1 by created_at asc for all involved (from + to) classroom/subject/day combos.
     * - Behavior: update only; do NOT create new WeeklyPlan records if a target plan is missing.
     */
    public function copyPlansClassrooms(Request $request): JsonResponse
    {
        $request->validate([
            'from_classroom_id' => 'required|integer|exists:classrooms,id',
            'to_classroom_ids' => 'nullable|array',
            'to_classroom_ids.*' => 'integer|different:from_classroom_id|exists:classrooms,id',
            'week_number' => 'required|integer|min:1',
            'semester_number' => 'required|integer|min:1|max:2',
            'subject_id' => 'required|integer|exists:subjects,id',
        ]);

        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. This endpoint is only available for users with a teacher profile.',
                'error_type' => 'no_teacher_profile'
            ], 403);
        }

        $fromClassroomId = (int)$request->from_classroom_id;
        $toClassroomIds = collect($request->to_classroom_ids ?? [])->unique()->values()->all();
        $week = (int)$request->week_number;
        $semester = (int)$request->semester_number;
        $selectedSubjectId = (int)$request->subject_id;

        // 1) Load source plans for this teacher + classroom + week/semester
        $sourcePlans = WeeklyPlan::with(['schedule.cst'])
            ->where('week_number', $week)
            ->where('semester_number', $semester)
            ->whereHas('schedule.cst', function ($q) use ($teacherId, $fromClassroomId, $selectedSubjectId) {
                $q->where('teacher_id', $teacherId)
                  ->where('classroom_id', $fromClassroomId)
                  ->where('subject_id', $selectedSubjectId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        if ($sourcePlans->isEmpty()) {
            return response()->json([
                'success' => true,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
            'message' => 'No source plans found for the selected classroom, subject and week.'
            ]);
        }

        // 2) Reindex period_order for all (classroom, subject, day) combos in from/to by created_at asc starting from 1
        $combos = [];
        foreach ($sourcePlans as $plan) {
            $sched = $plan->schedule;
            if (!$sched || !$sched->cst) { continue; }
            $subjectId = $sched->cst->subject_id;
            $day = $sched->day;
            if (!$subjectId || !$day) { continue; }
            // from classroom
            $combos[] = [$fromClassroomId, $subjectId, $day];
            // to classrooms
            foreach ($toClassroomIds as $cid) {
                $combos[] = [(int)$cid, $subjectId, $day];
            }
        }
        // Unique combos
      return  $combos = collect($combos)->unique(function ($c) { return implode('-', $c); })->values();

        foreach ($combos as $c) {
            [$classroomId, $subjectId, $day] = $c;
            $schedules = Schedule::with('cst')
                ->where('day_number', $day)
                ->whereHas('cst', function ($q) use ($teacherId, $classroomId, $subjectId) {
                    $q->where('teacher_id', $teacherId)
                      ->where('classroom_id', $classroomId)
                      ->where('subject_id', $subjectId);
                })
                ->orderBy('created_at', 'asc')
                ->get();

            // Only reorder if any schedule has null/zero period_order; otherwise keep existing orders
            $needsReorder = $schedules->contains(function ($s) {
                return empty($s->period_order) || $s->period_order === 0;
            });
            if ($needsReorder) {
                $i = 1;
                foreach ($schedules as $s) {
                    $s->period_order = $i;
                    $s->save();
                    $i++;
                }
            }
        }

        // 3) Copy CW/HW/Notes to matching schedules in target classrooms (match by subject+day+period_order)
        $created = 0; $updated = 0; $skipped = 0; $skippedDetails = [];
        foreach ($sourcePlans as $plan) {
            $srcSched = $plan->schedule;
            if (!$srcSched || !$srcSched->cst) {
                $skipped++;
                $skippedDetails[] = [
                    'reason' => 'missing_source_schedule_or_cst',
                    'source_weekly_plan_id' => $plan->id,
                    'source_schedule_id' => $plan->schedule_id,
                    'from_classroom_id' => $fromClassroomId,
                    'week_number' => $week,
                    'semester_number' => $semester,
                ];
                continue;
            }
            $subjectId = $srcSched->cst->subject_id;
            $day = $srcSched->day;
            $order = $srcSched->period_order; // set in step 2

            foreach ($toClassroomIds as $cid) {
                // Try to locate matching schedule: same teacher, classroom, subject, day, period
                $targetSchedule = Schedule::with(['cst'])
                    ->where('day_number', $day)
                    ->where('period_order', $order)
                    ->whereHas('cst', function ($q) use ($teacherId, $cid, $subjectId) {
                        $q->where('teacher_id', $teacherId)
                          ->where('classroom_id', (int)$cid)
                          ->where('subject_id', $subjectId);
                    })
                    ->first();

                if (!$targetSchedule) {
                    // Fallback: choose the Nth schedule by created_at asc (N = period_order from source)
                    $allSchedules = Schedule::with(['cst'])
                        ->where('day_number', $day)
                        ->whereHas('cst', function ($q) use ($teacherId, $cid, $subjectId) {
                            $q->where('teacher_id', $teacherId)
                              ->where('classroom_id', (int)$cid)
                              ->where('subject_id', $subjectId);
                        })
                        ->orderBy('created_at', 'asc')
                        ->get();

                    if ($allSchedules->isNotEmpty()) {
                        $idx = max(0, (int)$order - 1);
                        $targetSchedule = $allSchedules->get($idx);
                    }
                }

                if (!$targetSchedule) {
                    $skipped++;
                    $skippedDetails[] = [
                        'reason' => 'no_target_schedule',
                        'to_classroom_id' => (int)$cid,
                        'subject_id' => $subjectId,
                        'day' => $day,
                        'period_order' => $order,
                        'teacher_id' => $teacherId,
                    ];
                    continue;
                }

                $targetPlan = WeeklyPlan::where('schedule_id', $targetSchedule->id)
                    ->where('week_number', $week)
                    ->where('semester_number', $semester)
                    ->first();

                if ($targetPlan) {
                    $targetPlan->update([
                        'cw' => $plan->cw,
                        'hw' => $plan->hw,
                        'notes' => $plan->notes,
                    ]);
                    $updated++;
                } else {
                    // Do not create new plans; skip when no target plan exists
                    $skipped++;
                    $skippedDetails[] = [
                        'reason' => 'no_target_plan_for_week',
                        'to_classroom_id' => (int)$cid,
                        'subject_id' => $subjectId,
                        'day' => $day,
                        'period_order' => $order,
                        'target_schedule_id' => $targetSchedule->id,
                        'week_number' => $week,
                        'semester_number' => $semester,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'skipped_details' => $skippedDetails,
            'message' => "Copied plans: {$updated} updated, {$skipped} skipped."
        ]);
    }

    /**
     * Preview staged copy of weekly plans without applying any changes.
     * Returns a payload the frontend can review and then send back to commit.
     */
    /**
     * Build a read-only preview of copy operations for Weekly Plans.
     *
     * High-level flow:
     * 1) Validate inputs and verify the caller is a teacher
     * 2) Load source WeeklyPlans (for from classroom + subject + week/semester)
     * 3) Compute an in-memory "effective" period_order per (classroom, subject, day_number)
     *    - Uses period_order when present and > 0; otherwise falls back to sequence index
     * 4) Produce a source summary (day + period_order + CW/HW/Notes) and sort by day, period_order
     * 5) For each target classroom:
     *    - Try to match schedules by SAME effective period_order (ignore day_number)
     *    - If no exact match, fallback to Nth-by-order within the subject (N = source effective order)
     *    - Fetch the existing WeeklyPlan (update-only semantics)
     *    - Record a match row (with source/target order and target current CW/HW/Notes)
     *    - If target WeeklyPlan exists, queue an operation; otherwise record as skipped
     * 6) Sort each target's matches by day_number, then target period_order; return the preview payload
     */
    public function previewCopyPlansClassrooms(Request $request): JsonResponse
    {
        // 1) Validate incoming request and access
        $request->validate([
            'from_classroom_id' => 'required|integer|exists:classrooms,id',
            'to_classroom_ids' => 'required|array|min:1',
            'to_classroom_ids.*' => 'integer|different:from_classroom_id|exists:classrooms,id',
            'week_number' => 'required|integer|min:1',
            'semester_number' => 'required|integer|min:1|max:2',
            'subject_id' => 'required|integer|exists:subjects,id',
        ]);

        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. This endpoint is only available for users with a teacher profile.',
                'error_type' => 'no_teacher_profile'
            ], 403);
        }

        // Normalize and extract filter inputs
        $fromClassroomId = (int)$request->from_classroom_id;
        $toClassroomIds = collect($request->to_classroom_ids)->unique()->values()->all();
        $week = (int)$request->week_number;
        $semester = (int)$request->semester_number;
        $subjectId = (int)$request->subject_id;

        // 2) Load source plans for selected classroom + subject + week/semester
        $sourcePlans = WeeklyPlan::with(['schedule.cst'])
            ->where('week_number', $week)
            ->where('semester_number', $semester)
            ->whereHas('schedule.cst', function ($q) use ($teacherId, $fromClassroomId, $subjectId) {
                $q
                // ->where('teacher_id', $teacherId)
                  ->where('classroom_id', $fromClassroomId)
                  ->where('subject_id', $subjectId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        if ($sourcePlans->isEmpty()) {
            return response()->json([
                'success' => true,
                'from_classroom_id' => $fromClassroomId,
                'subject_id' => $subjectId,
                'week_number' => $week,
                'semester_number' => $semester,
                'source' => [],
                'targets' => [],
                'operations' => [],
                'skipped_details' => [],
                'message' => 'No source plans found for the selected classroom, subject and week.'
            ]);
        }

        // 3) Build effective period_order map per (classroom, subject) without writing to DB (ignore day_number)
        $comboIds = [];
        foreach ($sourcePlans as $plan) {
            $sched = $plan->schedule; if (!$sched || !$sched->cst) continue;
            $comboIds[] = [$fromClassroomId, $subjectId];
            foreach ($toClassroomIds as $cid) { $comboIds[] = [(int)$cid, $subjectId]; }
        }
        $comboIds = collect($comboIds)->unique(function ($c) { return implode('-', $c); })->values();

        $effectiveOrder = [];
        foreach ($comboIds as $c) {
            [$classroomId, $subj] = $c;
            $schedules = Schedule::with('cst')
                ->whereHas('cst', function ($q) use ($teacherId, $classroomId, $subj) {
                    $q
                      // ->where('teacher_id', $teacherId)
                      ->where('classroom_id', $classroomId)
                      ->where('subject_id', $subj);
                })
                // Use explicit period_order ascending, then created_at for stability
                ->orderBy('period_order', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();
            // Assign an "effective" order for any schedule lacking a positive period_order
            $i = 1;
            foreach ($schedules as $s) {
                $effectiveOrder[$s->id] = !empty($s->period_order) && $s->period_order > 0 ? (int)$s->period_order : $i;
                $i++;
            }
        }

        // 4) Build source summary for UI (and use effective order when needed)
        $sourceSummary = $sourcePlans->map(function ($plan) use ($effectiveOrder) {
            $sched = $plan->schedule;
            return [
                'weekly_plan_id' => $plan->id,
                'schedule_id' => $plan->schedule_id,
                'day' => $sched?->day_number,
                'period_order' => $sched ? (isset($effectiveOrder[$sched->id])
                    ? (int)$effectiveOrder[$sched->id]
                    : (!empty($sched->period_order) ? (int)$sched->period_order : null)) : null,
                'cw' => $plan->cw,
                'hw' => $plan->hw,
                'notes' => $plan->notes,
            ];
        })->values();
        // Sort source rows: by period_order only (nulls/unknown last)
        $sourceSummary = $sourceSummary->sort(function ($a, $b) {
            $ao = $a['period_order'] ?? PHP_INT_MAX; $bo = $b['period_order'] ?? PHP_INT_MAX;
            return $ao <=> $bo;
        })->values();

        // Results across all target classrooms
        $targets = [];
        $operations = [];
        $skippedDetails = [];

        foreach ($toClassroomIds as $cid) {
            // Collect per-target classroom match rows
            $matches = [];
            foreach ($sourcePlans as $plan) {
                $srcSched = $plan->schedule; if (!$srcSched || !$srcSched->cst) {
                    $matches[] = [
                        'source_weekly_plan_id' => $plan->id,
                        'reason' => 'missing_source_schedule_or_cst'
                    ];
                    continue;
                }
                $day = $srcSched->day_number;
                $order = isset($effectiveOrder[$srcSched->id]) ? (int)$effectiveOrder[$srcSched->id]
                    : (!empty($srcSched->period_order) ? (int)$srcSched->period_order : null);

                // 5a) Try to locate matching schedule: same classroom + subject + SAME effective period_order (ignore day_number)
                $targetSchedule = Schedule::with(['cst'])
                    ->whereHas('cst', function ($q) use ($teacherId, $cid, $subjectId) {
                        $q
                        // ->where('teacher_id', $teacherId)
                          ->where('classroom_id', (int)$cid)
                          ->where('subject_id', $subjectId);
                    })
                    ->get()
                    ->sortBy('period_order')
                    ->values()
                    ->first(function ($s) use ($order, $effectiveOrder) {
                        return (($effectiveOrder[$s->id] ?? $s->period_order ?? null) == $order);
                    });

                if (!$targetSchedule) {
                    // 5b) Fallback: the Nth schedule by order within the subject (N = source effective order)
                    $allSchedules = Schedule::with(['cst'])
                        ->whereHas('cst', function ($q) use ($teacherId, $cid, $subjectId) {
                            $q
                            // ->where('teacher_id', $teacherId)
                              ->where('classroom_id', (int)$cid)
                              ->where('subject_id', $subjectId);
                        })
                        ->orderBy('period_order', 'asc')
                        ->orderBy('created_at', 'asc')
                        ->get();
                    if ($allSchedules->isNotEmpty()) {
                        $idx = max(0, (int)$order - 1);
                        $targetSchedule = $allSchedules->get($idx);
                    }
                }

                if (!$targetSchedule) {
                    $matches[] = [
                        'source_weekly_plan_id' => $plan->id,
                        'source_day' => $day,
                        'source_period_order' => $order,
                        'reason' => 'no_target_schedule'
                    ];
                    $skippedDetails[] = [
                        'reason' => 'no_target_schedule',
                        'to_classroom_id' => (int)$cid,
                        'subject_id' => $subjectId,
                        'day' => $day,
                        'period_order' => $order,
                        'teacher_id' => $teacherId,
                    ];
                    continue;
                }

                // 5c) Find existing target WeeklyPlan for this schedule/week/semester (update-only)
                $targetPlan = WeeklyPlan::where('schedule_id', $targetSchedule->id)
                    ->where('week_number', $week)
                    ->where('semester_number', $semester)
                    ->first();

                // Build the match row, including source/target orders and target's current values for preview
                $matches[] = [
                    'source_weekly_plan_id' => $plan->id,
                    'source_day' => $day,
                    'source_period_order' => $order,
                    'target_schedule_id' => $targetSchedule->id,
                    'target_period_order' => isset($effectiveOrder[$targetSchedule->id])
                        ? (int)$effectiveOrder[$targetSchedule->id]
                        : (!empty($targetSchedule->period_order) ? (int)$targetSchedule->period_order : null),
                    'has_target_plan' => (bool)$targetPlan,
                    'target_weekly_plan_id' => $targetPlan?->id,
                    'source_cw' => $plan->cw,
                    'source_hw' => $plan->hw,
                    'source_notes' => $plan->notes,
                    'target_cw' => $targetPlan?->cw,
                    'target_hw' => $targetPlan?->hw,
                    'target_notes' => $targetPlan?->notes,
                ];

                // 5d) If a target plan exists, queue the update operation; otherwise it will be skipped on commit
                if ($targetPlan) {
                    $operations[] = [
                        'target_weekly_plan_id' => $targetPlan->id,
                        'cw' => $plan->cw,
                        'hw' => $plan->hw,
                        'notes' => $plan->notes,
                    ];
                } else {
                    $skippedDetails[] = [
                        'reason' => 'no_target_plan_for_week',
                        'to_classroom_id' => (int)$cid,
                        'subject_id' => $subjectId,
                        'day' => $day,
                        'period_order' => $order,
                        'target_schedule_id' => $targetSchedule->id,
                        'week_number' => $week,
                        'semester_number' => $semester,
                    ];
                }
            }
            // 6) Reorder matches by target period order (nulls last)
            $matches = collect($matches)->sort(function ($a, $b) {
                $ta = $a['target_period_order'] ?? PHP_INT_MAX; $tb = $b['target_period_order'] ?? PHP_INT_MAX;
                return $ta <=> $tb;
            })->values()->all();

            $targets[] = [
                'to_classroom_id' => (int)$cid,
                'matches' => $matches,
            ];
        }

        return response()->json([
            'success' => true,
            'from_classroom_id' => $fromClassroomId,
            'subject_id' => $subjectId,
            'week_number' => $week,
            'semester_number' => $semester,
            'source' => $sourceSummary,
            'targets' => $targets,
            'operations' => $operations, // what will be updated on commit
            'skipped_details' => $skippedDetails,
        ]);
    }

    /**
     * Commit staged copy by applying the provided operations (update-only).
     */
    public function commitCopyPlansClassrooms(Request $request): JsonResponse
    {
        $request->validate([
            'operations' => 'required|array|min:1',
            'operations.*.target_weekly_plan_id' => 'required|integer|exists:weekly_plans,id',
            'operations.*.cw' => 'nullable|string',
            'operations.*.hw' => 'nullable|string',
            'operations.*.notes' => 'nullable|string',
        ]);

        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. This endpoint is only available for users with a teacher profile.',
                'error_type' => 'no_teacher_profile'
            ], 403);
        }

        $updated = 0; $skipped = 0; $skippedDetails = [];

        foreach ($request->operations as $op) {
            $wp = WeeklyPlan::with('schedule.cst')->find($op['target_weekly_plan_id']);
            if (!$wp) { $skipped++; continue; }
            // Safety: ensure this weekly plan belongs to the current teacher
            if (!$wp->schedule || !$wp->schedule->cst || (int)$wp->schedule->cst->teacher_id !== (int)$teacherId) {
                $skipped++; $skippedDetails[] = ['reason' => 'not_owner', 'target_weekly_plan_id' => $wp->id]; continue;
            }
            $wp->update([
                'cw' => $op['cw'] ?? '',
                'hw' => $op['hw'] ?? '',
                'notes' => $op['notes'] ?? '',
            ]);
            $updated++;
        }

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'skipped' => $skipped,
            'skipped_details' => $skippedDetails,
            'message' => "Committed: {$updated} updated, {$skipped} skipped."
        ]);
    }

    /**
     * Update a schedule's period_order (inline edit from table).
     */
    public function updateSchedulePeriodOrder(Request $request, Schedule $schedule): JsonResponse
    {
        $request->validate([
            'period_order' => 'required|integer|min:1',
        ]);

        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. This endpoint is only available for users with a teacher profile.',
                'error_type' => 'no_teacher_profile'
            ], 403);
        }

        // Ensure teacher owns this schedule
        $schedule->loadMissing('cst');
        if (!$schedule->cst || (int)$schedule->cst->teacher_id !== (int)$teacherId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot modify a schedule that does not belong to you.',
                'error_type' => 'not_owner'
            ], 403);
        }

        $schedule->period_order = (int)$request->period_order;
        $schedule->save();

        return response()->json([
            'success' => true,
            'schedule_id' => $schedule->id,
            'period_order' => $schedule->period_order,
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
    /**
     * Get sync analysis - shows alignment between schedules and weekly plans
     */
    public function getSyncAnalysis(Request $request): JsonResponse
    {
        $request->validate([
            // 'copy_id' => 'required|integer|exists:schedule_copies,id', // Removed
            'week_number' => 'required|integer|min:1',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_number' => 'required|integer|min:1|max:2',
            'school_id' => 'required|integer|exists:schools,id' // Added required school_id
        ]);

        // $copyId = $request->copy_id;
        $weekNumber = $request->week_number;
        $academicYearId = $request->academic_year_id;
        $semesterNumber = $request->semester_number;
        $schoolId = $request->school_id;

        // Get all active schedules for this school
        // Note: We assume active schedules belong to the active academic year context
        $schedules = Schedule::with(['cst.subject', 'cst.classroom', 'cst.teacher'])
            ->where('school_id', $schoolId)
            // ->where('copy_id', $copyId) // Removed
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
                    // 'copy_id' => $schedule->copy_id, // Removed
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
