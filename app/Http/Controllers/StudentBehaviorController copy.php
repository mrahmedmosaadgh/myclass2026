<?php

namespace App\Http\Controllers;

use App\Models\StudentBehavior;
use App\Models\StudentBehaviorsMain;
use App\Services\PeriodCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentBehaviorController extends Controller
{
    /**
     * Display a listing of student behaviors.
     */
    public function index(Request $request): JsonResponse
    {
        $query = StudentBehavior::query();

        // Filter by student
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by school
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by behavior main
        if ($request->has('student_behaviors_mains_id')) {
            $query->where('student_behaviors_mains_id', $request->student_behaviors_mains_id);
        }

        $behaviors = $query->with(['student', 'behaviorMain', 'pointActions'])->paginate(20);

        return response()->json($behaviors);
    }

    /**
     * Store a new student behavior record.
     * Standard creation for full record.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'student_behaviors_mains_id' => 'required|exists:student_behaviors_mains,id',
            'student_id' => 'required|exists:students,id',
            'attend' => 'nullable|boolean',
            'points_plus' => 'required|integer|min:0',
            'points_minus' => 'required|integer|min:0',
            // 'points_details' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $behavior = StudentBehavior::create($validated);

        return response()->json($behavior->load(['student', 'behaviorMain', 'pointActions']), 201);
    }

    /**
     * Quick create student behavior from frontend (simplified payload).
     * Handles: student_id, behavior_id, date, period_code, notes
     * Auto-generates: student_behaviors_mains_id, school_id, points_plus/minus
     */
    public function quickCreate(Request $request): JsonResponse
    {
        // Log incoming request for debugging
        \Log::debug('quickCreate request received', [
            'body' => $request->all(),
            'headers' => $request->headers->all(),
            'authenticated' => auth()->check(),
        ]);

        try {
            $validated = $request->validate([
                'student_id' => 'required|integer|exists:students,id',
                'behavior_id' => 'required|integer|exists:behaviors,id',
                'date' => 'required|date',
                'period_code' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            \Log::debug('quickCreate validation passed', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('quickCreate validation failed', [
                'errors' => $e->errors(),
                'request_body' => $request->all(),
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            // Get teacher and school context
            $user = auth()->user();
            if (!$user) {
                \Log::warning('quickCreate: User not authenticated');
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error' => 'No authenticated user found'
                ], 401);
            }

            \Log::debug('quickCreate: User authenticated', ['user_id' => $user->id, 'user_name' => $user->name]);

            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                \Log::warning('quickCreate: User is not a teacher', ['user_id' => $user->id]);
                return response()->json([
                    'message' => 'Validation failed',
                    'error' => 'User is not registered as a teacher'
                ], 422);
            }

            \Log::debug('quickCreate: Teacher found', ['teacher_id' => $teacher->id]);

            $school = $teacher->school;
            if (!$school) {
                \Log::warning('quickCreate: Teacher has no school', ['teacher_id' => $teacher->id]);
                return response()->json([
                    'message' => 'Validation failed',
                    'error' => 'Teacher has no associated school'
                ], 422);
            }

            \Log::debug('quickCreate: School resolved', ['school_id' => $school->id]);

            // Get active academic year
            $year = \App\Models\AcademicYear::where('active', 1)->first();
            if (!$year) {
                \Log::warning('quickCreate: No active academic year found');
                return response()->json([
                    'message' => 'Validation failed',
                    'error' => 'No active academic year configured in the system'
                ], 422);
            }

            \Log::debug('quickCreate: Academic year resolved', ['year_id' => $year->id]);

            // Get behavior and validate it's for the correct school/year
            $behavior = \App\Models\Behavior::where('id', $validated['behavior_id'])
                ->where('school_id', $school->id)
                ->where('year_id', $year->id)
                ->first();
            
            if (!$behavior) {
                \Log::warning('quickCreate: Behavior not found or not for this school/year', [
                    'behavior_id' => $validated['behavior_id'],
                    'school_id' => $school->id,
                    'year_id' => $year->id,
                ]);
                return response()->json([
                    'message' => 'Validation failed',
                    'error' => 'Behavior not found or not available for this year'
                ], 404);
            }

            \Log::debug('quickCreate: Behavior resolved', [
                'behavior_id' => $behavior->id,
                'behavior_name' => $behavior->name,
                'behavior_type' => $behavior->type,
                'behavior_points' => $behavior->points,
            ]);

            // Get student
            $student = \App\Models\Student::find($validated['student_id']);
            if (!$student) {
                \Log::warning('quickCreate: Student not found', ['student_id' => $validated['student_id']]);
                return response()->json([
                    'message' => 'Validation failed',
                    'error' => 'Student not found'
                ], 404);
            }

            \Log::debug('quickCreate: Student resolved', ['student_id' => $student->id, 'student_name' => $student->name ?? $student->user_id]);

            \Log::debug('quickCreate context resolved', [
                'school_id' => $school->id,
                'year_id' => $year->id,
                'teacher_id' => $teacher->id,
                'behavior_id' => $behavior->id,
                'behavior_type' => $behavior->type,
                'behavior_points' => $behavior->points,
                'student_id' => $student->id,
            ]);

            $points = $behavior->points ?? 0;
            $type = $behavior->type ?? 'positive';

            // Derive classroom from student's current assignment
            // First try to get from classroom_subject_teacher assignments
            $classroomId = 1; // Fallback
            $subjectId = 1; // Fallback
            
            $studentClassroom = \DB::table('classroom_subject_teachers')
                ->where('teacher_id', $teacher->id)
                ->where('academic_year_id', $year->id)
                ->first();
            
            if ($studentClassroom) {
                $classroomId = $studentClassroom->classroom_id ?? 1;
                $subjectId = $studentClassroom->subject_id ?? 1;
            }

            \Log::debug('quickCreate: Resolved classroom context', [
                'classroom_id' => $classroomId,
                'subject_id' => $subjectId,
            ]);

            // Generate period codes using service
            $periodCodeMain = PeriodCodeService::generateMainCode($classroomId, $subjectId, $teacher->id);
            $periodCode = $validated['period_code'] ?? '';

            \Log::debug('quickCreate: Generated period codes', [
                'period_code_main' => $periodCodeMain,
                'period_code' => $periodCode,
            ]);

            // Check if StudentBehaviorsMain record already exists for this period
            // Deduplication based on: school_id, year_id, classroom_id, period_code_main, period_code, date
            $query = \App\Models\StudentBehaviorsMain::where('school_id', $school->id)
                ->where('year_id', $year->id)
                ->where('period_code_main', $periodCodeMain)
                ->where('date', $validated['date']);

            // Include classroom_id in query if available
            if (!empty($classroomId)) {
                $query->where('classroom_id', $classroomId);
            }

            // Include period_code in query only if provided
            if (!empty($periodCode)) {
                $query->where('period_code', $periodCode);
            }

            $existingMain = $query->first();

            if ($existingMain) {
                \Log::debug('quickCreate: Found existing StudentBehaviorsMain record (deduplication)', [
                    'id' => $existingMain->id,
                    'period_code_main' => $periodCodeMain,
                    'period_code' => $periodCode,
                    'date' => $existingMain->date,
                ]);
                $behaviorMain = $existingMain;
            } else {
                // Create new StudentBehaviorsMain record (session-level, not per-student)
                $behaviorMain = \App\Models\StudentBehaviorsMain::create([
                    'school_id' => $school->id,
                    'year_id' => $year->id,
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subjectId,
                    'classroom_id' => $classroomId,
                    'period_code_main' => $periodCodeMain,
                    'period_code' => $periodCode,
                    'date' => $validated['date'],
                    'notes' => $validated['notes'],
                ]);

                \Log::debug('quickCreate: StudentBehaviorsMain created (new)', [
                    'id' => $behaviorMain->id,
                    'period_code_main' => $periodCodeMain,
                    'period_code' => $periodCode,
                    'date' => $behaviorMain->date,
                ]);
            }

            // Find or create the StudentBehavior record for this student in this session
            $studentBehavior = StudentBehavior::firstOrCreate([
                'school_id' => $school->id,
                'student_behaviors_mains_id' => $behaviorMain->id,
                'student_id' => $validated['student_id'],
            ], [
                'attend' => true,
                'points_plus' => 0, // Default value, will be calculated by model accessor
                'points_minus' => 0, // Default value, will be calculated by model accessor
                'notes' => $validated['notes'],
            ]);

            // Create the point action record
            $pointValue = $type === 'positive' ? $points : -$points;
            
            \App\Models\StudentBehaviorsPointAction::create([
                'student_behaviors_id' => $studentBehavior->id,
                'reason_id' => $behavior->id,
                'value' => $pointValue,
                'action_type' => $type,
                'note' => $validated['notes'],
                'created_by' => $user->id,
            ]);

            \Log::debug('quickCreate: StudentBehavior created', [
                'id' => $studentBehavior->id,
                'points_plus' => $studentBehavior->points_plus,
                'points_minus' => $studentBehavior->points_minus,
            ]);
            
            return response()->json($studentBehavior->load(['student', 'behaviorMain']), 201);
        } catch (\Exception $e) {
            \Log::error('quickCreate exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Failed to create behavior record',
                'error' => $e->getMessage(),
                'exception' => class_basename($e),
            ], 500);
        }
    }

    /**
     * Display the specified student behavior.
     */
    public function show(StudentBehavior $studentBehavior): JsonResponse
    {
        return response()->json(
            $studentBehavior->load(['student', 'behaviorMain', 'pointActions.behavior', 'pointActions.createdBy', 'pointActions.canceledBy'])
        );
    }

    /**
     * Update the specified student behavior.
     */
    public function update(Request $request, StudentBehavior $studentBehavior): JsonResponse
    {
        $validated = $request->validate([
            'attend' => 'nullable|boolean',
            // 'points_details' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $studentBehavior->update($validated);

        return response()->json($studentBehavior->refresh());
    }

    /**
     * Delete the specified student behavior.
     */
    public function destroy(StudentBehavior $studentBehavior): JsonResponse
    {
        $studentBehavior->delete();

        return response()->json(['message' => 'Student behavior deleted successfully']);
    }

    /**
     * Get summary of student behavior (points totals).
     */
    public function getSummary(StudentBehavior $studentBehavior): JsonResponse
    {
        return response()->json([
            'student_id' => $studentBehavior->student_id,
            'points_plus' => $studentBehavior->points_plus,
            'points_minus' => $studentBehavior->points_minus,
            'total_points' => $studentBehavior->total_points,
            'attend' => $studentBehavior->attend,
            // 'details' => $studentBehavior->points_details,
        ]);
    }

    /**
     * Get all point actions for a specific behavior.
     */
    public function getPointActions(StudentBehavior $studentBehavior): JsonResponse
    {
        $actions = $studentBehavior->pointActions()
            ->with(['behavior', 'createdBy', 'canceledBy'])
            ->get();

        return response()->json($actions);
    }

    /**
     * Show aggregated behavior summary for a specific student.
     * This is used by the frontend to display positive/negative/total counts.
     */
    public function studentSummary($studentId): JsonResponse
    {
        $behaviors = StudentBehavior::where('student_id', $studentId)
            ->with('pointActions')
            ->get();

        $summary = [
            'positive' => 0,
            'negative' => 0,
            'total' => 0,
        ];

        foreach ($behaviors as $behavior) {
            $summary['positive'] += $behavior->points_plus;
            $summary['negative'] += $behavior->points_minus;
            $summary['total'] += $behavior->total_points;
        }

        return response()->json($summary);
    }

    /**
     * Get leaderboard data for students in a classroom within a date range
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'classroom_id' => 'nullable|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'limit' => 'nullable|integer|min:1|max:20'
        ]);

        $limit = $validated['limit'] ?? 10;
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $classroomId = $validated['classroom_id'] ?? null;

        // Get current teacher and school
        $teacher = null;
        if (auth()->check()) {
            $teacher = \App\Models\Teacher::where('user_id', auth()->id())->first();
        }

        if (!$teacher) {
            return response()->json(['message' => 'Authenticated user is not a teacher'], 422);
        }

        $query = StudentBehaviorsMain::with(['student', 'behaviors.pointActions'])
            ->where('school_id', $teacher->school_id)
            ->whereBetween('date', [$startDate, $endDate]);

        // If classroom_id is provided, filter by classroom
        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }

        $behaviorMains = $query->get();

        // Group by student and calculate totals
        $studentTotals = [];

        foreach ($behaviorMains as $behaviorMain) {
            // Each behaviorMain can contain multiple StudentBehavior records (one per student)
            foreach ($behaviorMain->behaviors as $behavior) {
                $studentId = $behavior->student_id;
                $pointsForBehavior = $behavior->total_points;

                if (!isset($studentTotals[$studentId])) {
                    $studentTotals[$studentId] = [
                        'student_id' => $studentId,
                        'positive' => 0,
                        'negative' => 0,
                        'total' => 0
                    ];
                }

                $studentTotals[$studentId]['positive'] += $pointsForBehavior > 0 ? $pointsForBehavior : 0;
                $studentTotals[$studentId]['negative'] += $pointsForBehavior < 0 ? abs($pointsForBehavior) : 0;
                $studentTotals[$studentId]['total'] += $pointsForBehavior;
            }
        }

        // Filter out students with zero or negative totals and sort by total descending
        $leaderboard = collect($studentTotals)
            ->filter(function ($student) {
                return $student['total'] > 0;
            })
            ->sortByDesc('total')
            ->take($limit)
            ->values()
            ->toArray();

        return response()->json($leaderboard);
    }
}
