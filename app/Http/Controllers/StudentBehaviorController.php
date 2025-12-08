<?php

namespace App\Http\Controllers;

use App\Models\Student;
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
            'points_details' => 'required|string',
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

            $points = abs($behavior->points ?? 0); // Always use absolute value
            $type = $behavior->type ?? 'positive';
            
            // If points are negative in database, infer type from that
            if (($behavior->points ?? 0) < 0 && $type === 'positive') {
                $type = 'negative';
                \Log::warning('quickCreate: Behavior has negative points but positive type, correcting', [
                    'behavior_id' => $behavior->id,
                    'original_points' => $behavior->points,
                    'corrected_type' => $type,
                ]);
            }

            \Log::debug('quickCreate: Extracted behavior values', [
                'points' => $points,
                'type' => $type,
                'will_create_value' => ($type === 'positive' ? $points : -$points),
            ]);

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

            // Check if student is marked as absent
            if ($studentBehavior->attend === false) {
                \Log::warning('quickCreate: Attempted to add behavior to absent student', [
                    'student_id' => $validated['student_id'],
                    'behavior_id' => $behavior->id,
                ]);
                return response()->json([
                    'message' => 'Cannot add behavior to absent student',
                    'error' => 'This student is marked as absent for this session'
                ], 422);
            }

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
            'points_details' => 'nullable|string',
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
            'details' => $studentBehavior->points_details,
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

        // Use optimized database aggregation
        $query = \DB::table('student_behaviors as sb')
            ->join('student_behaviors_point_actions as pa', 'sb.id', '=', 'pa.student_behaviors_id')
            ->join('student_behaviors_mains as sm', 'sb.student_behaviors_mains_id', '=', 'sm.id')
            ->where('pa.canceled', false)
            ->where('sm.school_id', $teacher->school_id)
            ->whereBetween('sm.date', [$startDate, $endDate]);

        if ($classroomId) {
            $query->where('sm.classroom_id', $classroomId);
        }

        $leaderboard = $query
            ->groupBy('sb.student_id')
            ->selectRaw('
                sb.student_id,
                SUM(CASE WHEN pa.value > 0 THEN pa.value ELSE 0 END) as positive,
                SUM(CASE WHEN pa.value < 0 THEN ABS(pa.value) ELSE 0 END) as negative,
                SUM(pa.value) as total
            ')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return response()->json($leaderboard);
    }

    /**
     * Update attendance for a single student
     */
    public function updateAttendance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'attend' => 'required|boolean',
            'date' => 'required|date',
            'period_code' => 'nullable|string',
            'classroom_id' => 'nullable|integer',
        ]);

        try {
            $user = auth()->user();
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
            
            if (!$teacher) {
                return response()->json(['message' => 'User is not a teacher'], 422);
            }

            $year = \App\Models\AcademicYear::where('active', 1)->first();
            if (!$year) {
                return response()->json(['message' => 'No active academic year'], 422);
            }

            // Find the student behavior record for this date/period
            $query = StudentBehavior::where('student_id', $validated['student_id'])
                ->whereHas('behaviorMain', function($q) use ($validated, $teacher, $year) {
                    $q->where('school_id', $teacher->school_id)
                      ->where('year_id', $year->id)
                      ->where('date', $validated['date']);
                    
                    if (!empty($validated['period_code'])) {
                        $q->where('period_code', $validated['period_code']);
                    }
                    if (!empty($validated['classroom_id'])) {
                        $q->where('classroom_id', $validated['classroom_id']);
                    }
                });

            $studentBehavior = $query->first();

            if ($studentBehavior) {
                // If marking as absent, cancel all point actions for this session
                if ($validated['attend'] === false) {
                    $canceledCount = \App\Models\StudentBehaviorsPointAction::where('student_behaviors_id', $studentBehavior->id)
                        ->where('canceled', false)
                        ->update([
                            'canceled' => true,
                            'canceled_by' => $user->id,
                            'canceled_at' => now(),
                            'cancel_reason' => 'Student marked absent'
                        ]);
                    
                    \Log::info("Canceled {$canceledCount} point actions for student {$validated['student_id']} due to absence");
                }
                
                $studentBehavior->update(['attend' => $validated['attend']]);
                
                return response()->json([
                    'success' => true,
                    'message' => $validated['attend'] 
                        ? 'Attendance updated successfully' 
                        : 'Student marked absent and points removed',
                    'data' => $studentBehavior
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Student behavior record not found for this period'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('updateAttendance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch update attendance for multiple students
     */
    public function batchUpdateAttendance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|integer|exists:students,id',
            'attendance.*.attend' => 'required|boolean',
            'attendance.*.date' => 'required|date',
            'attendance.*.period_code' => 'nullable|string',
            'attendance.*.classroom_id' => 'nullable|integer',
        ]);

        try {
            $user = auth()->user();
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
            
            if (!$teacher) {
                return response()->json(['message' => 'User is not a teacher'], 422);
            }

            $year = \App\Models\AcademicYear::where('active', 1)->first();
            if (!$year) {
                return response()->json(['message' => 'No active academic year'], 422);
            }

            $updated = 0;
            $failed = 0;
            $pointsRemoved = 0;

            foreach ($validated['attendance'] as $record) {
                $query = StudentBehavior::where('student_id', $record['student_id'])
                    ->whereHas('behaviorMain', function($q) use ($record, $teacher, $year) {
                        $q->where('school_id', $teacher->school_id)
                          ->where('year_id', $year->id)
                          ->where('date', $record['date']);
                        
                        if (!empty($record['period_code'])) {
                            $q->where('period_code', $record['period_code']);
                        }
                        if (!empty($record['classroom_id'])) {
                            $q->where('classroom_id', $record['classroom_id']);
                        }
                    });

                $studentBehavior = $query->first();

                if ($studentBehavior) {
                    // If marking as absent, cancel all point actions for this session
                    if ($record['attend'] === false) {
                        $canceled = \App\Models\StudentBehaviorsPointAction::where('student_behaviors_id', $studentBehavior->id)
                            ->where('canceled', false)
                            ->update([
                                'canceled' => true,
                                'canceled_by' => $user->id,
                                'canceled_at' => now(),
                                'cancel_reason' => 'Student marked absent (batch)'
                            ]);
                        $pointsRemoved += $canceled;
                    }
                    
                    $studentBehavior->update(['attend' => $record['attend']]);
                    $updated++;
                } else {
                    $failed++;
                }
            }

            $message = "Updated {$updated} students";
            if ($pointsRemoved > 0) {
                $message .= ", removed {$pointsRemoved} point actions";
            }
            if ($failed > 0) {
                $message .= ", {$failed} failed";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated' => $updated,
                'failed' => $failed,
                'points_removed' => $pointsRemoved
            ]);

        } catch (\Exception $e) {
            \Log::error('batchUpdateAttendance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to batch update attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent point actions (history) with ability to undo
     */
    public function recentActions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'classroom_id' => 'nullable|integer',
            'date' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        $limit = $validated['limit'] ?? 10;

        try {
            $user = auth()->user();
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
            
            if (!$teacher) {
                return response()->json(['message' => 'User is not a teacher'], 422);
            }

            $query = \App\Models\StudentBehaviorsPointAction::with([
                'studentBehavior.student',
                'behavior',
                'createdBy',
                'canceledBy'
            ])
            ->whereHas('studentBehavior.behaviorMain', function($q) use ($teacher, $validated) {
                $q->where('school_id', $teacher->school_id);
                
                if (!empty($validated['classroom_id'])) {
                    $q->where('classroom_id', $validated['classroom_id']);
                }
                if (!empty($validated['date'])) {
                    $q->where('date', $validated['date']);
                }
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit);

            $actions = $query->get();

            return response()->json([
                'success' => true,
                'data' => $actions
            ]);

        } catch (\Exception $e) {
            \Log::error('recentActions error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent actions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel (undo) a point action
     */
    public function cancelAction(Request $request, $actionId): JsonResponse
    {
        $validated = $request->validate([
            'cancel_reason' => 'nullable|string|max:255'
        ]);

        try {
            $action = \App\Models\StudentBehaviorsPointAction::findOrFail($actionId);

            if ($action->canceled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action is already canceled'
                ], 422);
            }

            $action->update([
                'canceled' => true,
                'canceled_by' => auth()->id(),
                'canceled_at' => now(),
                'cancel_reason' => $validated['cancel_reason'] ?? 'Undone by teacher'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Action canceled successfully',
                'data' => $action->fresh(['studentBehavior.student', 'behavior', 'canceledBy'])
            ]);

        } catch (\Exception $e) {
            \Log::error('cancelAction error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel action',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}