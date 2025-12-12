<?php

namespace App\Http\Controllers;

use App\Models\StudentBehaviorsMain;
use App\Models\StudentBehavior;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentBehaviorsMainController extends Controller
{
    /**
     * Display a listing of all student behavior mains.
     */
    public function index(Request $request): JsonResponse
    {
        $query = StudentBehaviorsMain::query();

        // Filter by school
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by year
        if ($request->has('year_id')) {
            $query->where('year_id', $request->year_id);
        }

      
        // Filter by classroom
        if ($request->has('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $behaviorsMains = $query->with(['student', 'teacher', 'subject', 'classroom', 'behaviors'])->paginate(15);

        return response()->json($behaviorsMains);
    }

    /**
     * Store a new student behavior main record.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'year_id' => 'required|exists:academic_years,id',
            // 'student_id' => 'required|exists:students,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'period_code_main' => 'required|string',
            'period_code' => 'required|string',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $behaviorMain = StudentBehaviorsMain::create($validated);

        return response()->json($behaviorMain->load(['student', 'teacher', 'subject', 'classroom']), 201);
    }

    /**
     * Display the specified student behavior main.
     */
    public function show(StudentBehaviorsMain $studentBehaviorsMain): JsonResponse
    {
        return response()->json(
            $studentBehaviorsMain->load(['student', 'teacher', 'subject', 'classroom', 'behaviors.pointActions'])
        );
    }

    /**
     * Update the specified student behavior main.
     */
    public function update(Request $request, StudentBehaviorsMain $studentBehaviorsMain): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'nullable|exists:teachers,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'period_code_main' => 'nullable|string',
            'period_code' => 'nullable|string',
            'date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $studentBehaviorsMain->update($validated);

        return response()->json($studentBehaviorsMain->refresh());
    }

    /**
     * Delete the specified student behavior main.
     */
    public function destroy(StudentBehaviorsMain $studentBehaviorsMain): JsonResponse
    {
        $studentBehaviorsMain->delete();

        return response()->json(['message' => 'Student behavior main deleted successfully']);
    }

    /**
     * Get behaviors for a specific student behavior main.
     */
    public function getBehaviors(StudentBehaviorsMain $studentBehaviorsMain): JsonResponse
    {
        $behaviors = $studentBehaviorsMain->behaviors()
            ->with(['pointActions.behavior', 'pointActions.createdBy', 'pointActions.canceledBy'])
            ->get();

        return response()->json($behaviors);
    }

    /**
     * Initialize a StudentBehaviorsMain session for a classroom and ensure a StudentBehavior
     * exists for every student in that classroom (create if missing).
     * Expects: classroom_id, date, period_code (optional)
     */
    public function initForClassroom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'classroom_id' => 'required|integer',
            'date' => 'required|date',
            'period_code' => 'nullable|string',
        ]);

        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                return response()->json(['message' => 'User is not a teacher'], 422);
            }

            $school = $teacher->school;
            if (!$school) {
                return response()->json(['message' => 'Teacher has no associated school'], 422);
            }

            $year = \App\Models\AcademicYear::where('active', 1)->first();
            if (!$year) {
                return response()->json(['message' => 'No active academic year configured'], 422);
            }

            $classroomId = $validated['classroom_id'];
            $periodCode = $validated['period_code'] ?? null;
            $date = $validated['date'];

            // Verify classroom exists and belongs to the teacher's school
            $classroom = \App\Models\Classroom::find($classroomId);
            if (!$classroom) {
                return response()->json(['message' => 'Classroom not found', 'errors' => ['classroom_id' => ['The selected classroom id is invalid.']]], 422);
            }

            if ($classroom->school_id != $school->id) {
                return response()->json(['message' => 'The selected classroom does not belong to your school.', 'errors' => ['classroom_id' => ['The selected classroom does not belong to your school.']]], 422);
            }

            // Try to resolve a subject_id for this classroom/teacher/year if possible
            $subjectId = null;
            $assignment = \DB::table('classroom_subject_teachers')
                ->where('teacher_id', $teacher->id)
                ->where('academic_year_id', $year->id)
                ->where('classroom_id', $classroomId)
                ->first();

            if ($assignment) {
                $subjectId = $assignment->subject_id ?? null;
            }

            // Generate main period code
            $periodCodeMain = \App\Services\PeriodCodeService::generateMainCode($classroomId, $subjectId ?? 0, $teacher->id);

            // Find or create the session-level StudentBehaviorsMain
            $main = StudentBehaviorsMain::firstOrCreate([
                'school_id' => $school->id,
                'year_id' => $year->id,
                'classroom_id' => $classroomId,
                'period_code_main' => $periodCodeMain,
                'period_code' => $periodCode,
                'date' => $date,
            ], [
                'teacher_id' => $teacher->id,
                'subject_id' => $subjectId,
                'notes' => null,
            ]);

            // Load classroom students
            $classroom = \App\Models\Classroom::with('students')->find($classroomId);
            if (!$classroom) {
                return response()->json(['message' => 'Classroom not found'], 404);
            }

            $created = 0;
            $skipped = 0;
            $createdIds = [];

            foreach ($classroom->students as $student) {
                $exists = StudentBehavior::where('student_behaviors_mains_id', $main->id)
                    ->where('student_id', $student->id)
                    ->first();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $sb = StudentBehavior::create([
                    'school_id' => $school->id,
                    'student_behaviors_mains_id' => $main->id,
                    'student_id' => $student->id,
                    'attend' => true,
                    'points_plus' => 0,
                    'points_minus' => 0,
                     
                    'notes' => null
                ]);

                $created++;
                $createdIds[] = $sb->id;
            }

            // After ensuring all records exist, fetch them with their relations
            $studentBehaviors = StudentBehavior::where('student_behaviors_mains_id', $main->id)
                ->with(['student', 'pointActions']) // Eager load student and point actions
                ->get();

            return response()->json([
                'message' => 'Initialized classroom session',
                'student_behaviors_mains_id' => $main->id,
                'student_behaviors' => $studentBehaviors, // Return the full list of behaviors
                'created' => $created,
                'skipped' => $skipped,
                'created_ids' => $createdIds,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('initForClassroom error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to initialize classroom session', 'error' => $e->getMessage()], 500);
        }
    }
}
