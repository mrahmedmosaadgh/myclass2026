<?php

namespace App\Http\Controllers;

use App\Models\WeeklyPlan;
use App\Models\ClassroomSubjectTeacher;
use App\Models\Teacher;
use App\Http\Requests\WeeklyPlanRequest;
use App\Services\WeeklyPlanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WeeklyPlanController extends Controller
{
    /**
     * Get the authenticated teacher's ID
     */
    private function getTeacherId()
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }
        
        $teacher = Teacher::where('user_id', $user->id)->first();
        return $teacher ? $teacher->id : null;
    }

    /**
     * Display a listing of weekly plans for authenticated teacher.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'cst_id' => 'sometimes|exists:classroom_subject_teachers,id',
            'academic_year_id' => 'sometimes|exists:academic_years,id',
            'semester_number' => 'sometimes|in:1,2',
        ]);

        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = WeeklyPlan::with(['sessions' => function($query) {
            $query->orderBy('session_index');
        }, 'classroomSubjectTeacher.classroom', 'classroomSubjectTeacher.subject', 'academicYear'])
        ->whereHas('classroomSubjectTeacher', function($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        });

        if ($request->has('cst_id')) {
            $query->where('cst_id', $request->cst_id);
        }

        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->has('semester_number')) {
            $query->where('semester_number', $request->semester_number);
        }

        $plans = $query->orderBy('week_number')->get();

        return response()->json($plans);
    }

    /**
     * Store a newly created weekly plan.
     */
    public function store(WeeklyPlanRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Check if plan already exists
        $existing = WeeklyPlan::where([
            'academic_year_id' => $validated['academic_year_id'],
            'semester_number' => $validated['semester_number'],
            'week_number' => $validated['week_number'],
            'cst_id' => $validated['cst_id'],
        ])->first();

        if ($existing) {
            return response()->json($existing->load(['sessions' => function($query) {
                $query->orderBy('session_index');
            }]), 200);
        }

        $plan = WeeklyPlan::create($validated);
        return response()->json($plan->load(['sessions' => function($query) {
            $query->orderBy('session_index');
        }]), 201);
    }

    /**
     * Display the specified weekly plan.
     */
    public function show(WeeklyPlan $weeklyPlan): JsonResponse
    {
        // Check authorization
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $weeklyPlan->load('classroomSubjectTeacher');
        if ($weeklyPlan->classroomSubjectTeacher->teacher_id !== $teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($weeklyPlan->load(['sessions' => function($query) {
            $query->orderBy('session_index');
        }]));
    }

    /**
     * Update the specified weekly plan.
     */
    public function update(WeeklyPlanRequest $request, WeeklyPlan $weeklyPlan): JsonResponse
    {
        // Check authorization
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $weeklyPlan->load('classroomSubjectTeacher');
        if ($weeklyPlan->classroomSubjectTeacher->teacher_id !== $teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();
        $weeklyPlan->update($validated);
        
        return response()->json($weeklyPlan->load(['sessions' => function($query) {
            $query->orderBy('session_index');
        }]));
    }

    /**
     * Remove the specified weekly plan.
     */
    public function destroy(WeeklyPlan $weeklyPlan): JsonResponse
    {
        // Check authorization
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $weeklyPlan->load('classroomSubjectTeacher');
        if ($weeklyPlan->classroomSubjectTeacher->teacher_id !== $teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $weeklyPlan->delete();
        return response()->json(null, 204);
    }

    /**
     * Get or create weekly plans for all weeks in a semester.
     */
    public function generateSemesterPlans(Request $request, WeeklyPlanService $service): JsonResponse
    {
        $request->validate([
            'cst_id' => 'required|exists:classroom_subject_teachers,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_number' => 'required|in:1,2',
            'total_weeks' => 'required|integer|min:1|max:36',
        ]);

        // Check authorization - user must own the CST
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cst = ClassroomSubjectTeacher::findOrFail($request->cst_id);
        if ($cst->teacher_id !== $teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $plans = $service->generateSemesterPlans(
            $request->cst_id,
            $request->academic_year_id,
            $request->semester_number,
            $request->total_weeks
        );

        return response()->json($plans);
    }

    /**
     * Get weekly plans by academic year for authenticated teacher.
     */
    public function getByAcademicYear(int $academicYearId): JsonResponse
    {
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $plans = WeeklyPlan::with(['sessions' => function($query) {
            $query->orderBy('session_index');
        }, 'classroomSubjectTeacher.classroom', 'classroomSubjectTeacher.subject'])
        ->where('academic_year_id', $academicYearId)
        ->whereHas('classroomSubjectTeacher', function($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })
        ->orderBy('semester_number')
        ->orderBy('week_number')
        ->get();

        return response()->json($plans);
    }

    /**
     * Get weekly plans by semester for authenticated teacher.
     */
    public function getBySemester(int $academicYearId, int $semester): JsonResponse
    {
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $plans = WeeklyPlan::with(['sessions' => function($query) {
            $query->orderBy('session_index');
        }, 'classroomSubjectTeacher.classroom', 'classroomSubjectTeacher.subject'])
        ->where('academic_year_id', $academicYearId)
        ->where('semester_number', $semester)
        ->whereHas('classroomSubjectTeacher', function($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })
        ->orderBy('week_number')
        ->get();

        return response()->json($plans);
    }

    /**
     * Get weekly plans by specific week for authenticated teacher.
     */
    public function getByWeek(int $academicYearId, int $semester, int $weekNumber): JsonResponse
    {
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $plans = WeeklyPlan::with(['sessions' => function($query) {
            $query->orderBy('session_index');
        }, 'classroomSubjectTeacher.classroom', 'classroomSubjectTeacher.subject'])
        ->where('academic_year_id', $academicYearId)
        ->where('semester_number', $semester)
        ->where('week_number', $weekNumber)
        ->whereHas('classroomSubjectTeacher', function($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })
        ->get();

        return response()->json($plans);
    }

    /**
     * Get weekly plans by CST for authenticated teacher.
     */
    public function getByCst(int $cstId): JsonResponse
    {
        // Check authorization
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cst = ClassroomSubjectTeacher::findOrFail($cstId);
        if ($cst->teacher_id !== $teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $plans = WeeklyPlan::with(['sessions' => function($query) {
            $query->orderBy('session_index');
        }, 'academicYear'])
        ->where('cst_id', $cstId)
        ->orderBy('academic_year_id')
        ->orderBy('semester_number')
        ->orderBy('week_number')
        ->get();

        return response()->json($plans);
    }
}
