<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeriodActivity;
use App\Models\Calendar;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\StudentPeriodRecord;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriodActivityController extends Controller
{


    public function get_teacher_data($user_id)
    {
return Teacher::where('user_id',$user_id)->with(['school'])->first();
    }


    public function index(Request $request)
    {
        // Get teacher data
        $teacher = $this->get_teacher_data(Auth::user()->id);

        if (!$teacher) {
            return response()->json([
                'message' => 'No teacher data found',
                'error' => 'teacher_not_found'
            ], 404);
        }

        $school_id = $teacher->school_id;

        // Initialize the query
        $query = PeriodActivity::with(['schedule', 'calendar', 'teacher', 'substituteTeacher', 'event'])
            ->whereHas('calendar', function ($q) use ($school_id) {
                $q->where('school_id', $school_id);
            });

        // Apply filters
        if ($request->has('calendar_id')) {
            $query->where('calendar_id', $request->calendar_id);
        }

        if ($request->has('schedule_id')) {
            $query->where('schedule_id', $request->schedule_id);
        }

        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // If both schedule_id and calendar_date are provided, find the period activity for that specific day
        $calendar = null;
        if ($request->has('schedule_id') && $request->has('calendar_date')) {
            $calendar = Calendar::where('date', $request->calendar_date)
                ->where('school_id', $school_id)
                ->first();

            if ($calendar) {
                $query->where('calendar_id', $calendar->id);
            } else {
                // Calendar not found - return a clear message
                return response()->json([
                    'message' => 'Calendar entry not found for the specified date. Please create a calendar entry first.',
                    'error' => 'calendar_not_found',
                    'date' => $request->calendar_date
                ], 404);
            }
        }

        // Get the results
        $results = $query->orderBy('created_at', 'desc')->get();

        // If no results and we have the necessary data to create a period activity
        if ($results->isEmpty() &&
            $request->has('schedule_id') &&
            $request->has('calendar_date') &&
            $request->has('teacher_id')) {

            try {
                // We already have the calendar from above, but if not, get it again
                if (!$calendar) {
                    $calendar = Calendar::where('date', $request->calendar_date)
                        ->where('school_id', $school_id)
                        ->first();

                    if (!$calendar) {
                        return response()->json([
                            'message' => 'Calendar entry not found for the specified date. Please create a calendar entry first.',
                            'error' => 'calendar_not_found',
                            'date' => $request->calendar_date
                        ], 404);
                    }
                }

                // Get the classroom ID from the schedule
                $schedule = Schedule::find($request->schedule_id);
                if (!$schedule) {
                    return response()->json([
                        'message' => 'Schedule not found',
                        'error' => 'schedule_not_found'
                    ], 404);
                }

                // Get classroom ID from the schedule's CST relationship
                $classroomId = $schedule->cst->classroom_id ?? $request->classroom_id;

                if (!$classroomId) {
                    return response()->json([
                        'message' => 'Classroom ID not found',
                        'error' => 'classroom_not_found'
                    ], 404);
                }

                // Use transaction to ensure data consistency
                DB::beginTransaction();

                // Prepare data for creating period activity
                $data = [
                    'schedule_id' => $request->schedule_id,
                    'calendar_id' => $calendar->id,
                    'teacher_id' => $request->teacher_id,
                    'teacher_present' => true,
                    'period_status' => 'completed',
                    'classroom_id' => $classroomId
                ];

                // Don't set created_by and updated_by here, let getOrCreatePeriodActivity handle it

                // Get or create period activity
                $result = $this->getOrCreatePeriodActivity($data);
                $periodActivity = $result['period_activity'];
                $isNewRecord = $result['is_new'];

                // Get or create student records if this is a new period activity
                $studentRecords = [];
                if ($isNewRecord) {
                    $studentRecords = $this->createStudentPeriodRecords($periodActivity->id, $classroomId);
                } else {
                    // Load existing student records
                    $studentRecords = \App\Models\StudentPeriodRecord::where('period_activity_id', $periodActivity->id)
                        ->with('student')
                        ->get();
                }

                DB::commit();

                // Return the newly created period activity
                return response()->json([
                    'period_activity' => $periodActivity->load(['schedule', 'calendar', 'teacher', 'event']),
                    'student_records' => $studentRecords,
                    'message' => $isNewRecord ? 'Period activity created successfully' : 'Period activity already exists',
                    'is_new' => $isNewRecord
                ], $isNewRecord ? 201 : 200);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Failed to create period activity: ' . $e->getMessage(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        }

        return response()->json([
            'records' => $results,
            'message' => 'Period activities retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'calendar_id' => 'required|exists:calendars,id',
            'teacher_id' => 'required|exists:teachers,id',
            'teacher_substitute_id' => 'nullable|exists:teachers,id',
            'teacher_present' => 'boolean',
            'period_status' => 'required|string|in:completed,cancelled,modified,event_affected',
            'event_id' => 'nullable|exists:calendar_events,id',
            'lesson_notes' => 'nullable|string',
            'improvement_notes' => 'nullable|string',
            'was_duty_period' => 'boolean',
            'duty_notes' => 'nullable|string',
            'classroom_id' => 'required|exists:classrooms,id',
            'lesson_code' => 'nullable|string|max:20'
        ]);

        try {
            // Use transaction to ensure data consistency
            DB::beginTransaction();

            // Get or create period activity
            $result = $this->getOrCreatePeriodActivity($validated);
            $periodActivity = $result['period_activity'];
            $isNewRecord = $result['is_new'];

            // Get or create student records if this is a new period activity
            $studentRecords = [];
            if ($isNewRecord) {
                $studentRecords = $this->createStudentPeriodRecords($periodActivity->id, $validated['classroom_id']);
            } else {
                // Load existing student records
                $studentRecords = StudentPeriodRecord::where('period_activity_id', $periodActivity->id)
                    ->with('student')
                    ->get();
            }

            DB::commit();

            return response()->json([
                'period_activity' => $periodActivity->load(['schedule', 'calendar', 'teacher', 'event']),
                'student_records' => $studentRecords,
                'message' => $isNewRecord ? 'Period activity created successfully' : 'Period activity already exists',
                'is_new' => $isNewRecord
            ], $isNewRecord ? 201 : 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create period activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(PeriodActivity $periodActivity)
    {
        return $periodActivity->load(['schedule', 'calendar', 'teacher', 'event']);
    }

    public function update(Request $request, PeriodActivity $periodActivity)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'calendar_id' => 'required|exists:calendars,id',
            'teacher_id' => 'required|exists:teachers,id',
            'teacher_substitute_id' => 'nullable|exists:teachers,id',
            'teacher_present' => 'boolean',
            'period_status' => 'required|string|in:completed,cancelled,modified,event_affected',
            'event_id' => 'nullable|exists:calendar_events,id',
            'student_attendance' => 'nullable|array',
            'student_behavior' => 'nullable|array',
            'student_participation' => 'nullable|array',
            'homework_records' => 'nullable|array',
            'lesson_notes' => 'nullable|string',
            'improvement_notes' => 'nullable|string',
            'was_duty_period' => 'boolean',
            'duty_notes' => 'nullable|string',
            'lesson_code' => 'nullable|string|max:20'
        ]);

        // Get the teacher ID for the authenticated user
        $teacher = $this->get_teacher_data(Auth::user()->id);

        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher data not found for the authenticated user',
                'error' => 'teacher_not_found'
            ], 404);
        }

        $validated['updated_by'] = $teacher->id; // Use teacher ID, not user ID

        $periodActivity->update($validated);

        return response()->json($periodActivity->load(['schedule', 'calendar', 'teacher', 'event']));
    }

    public function destroy(PeriodActivity $periodActivity)
    {
        $periodActivity->delete();
        return response()->json(null, 204);
    }

    /**
     * Get existing period activity or create a new one
     *
     * @param array $data Validated data
     * @return array Array with period_activity and is_new flag
     */
    private function getOrCreatePeriodActivity(array $data)
    {
        // Check if period activity already exists
        $existingActivity = PeriodActivity::where('schedule_id', $data['schedule_id'])
            ->where('calendar_id', $data['calendar_id'])
            ->first();

        if ($existingActivity) {
            return [
                'period_activity' => $existingActivity,
                'is_new' => false
            ];
        }

        // Create new period activity
        $teacher = $this->get_teacher_data(Auth::user()->id);

        if (!$teacher) {
            throw new \Exception('Teacher data not found for the authenticated user');
        }

        $data['created_by'] = $teacher->id; // Use teacher ID, not user ID
        $data['updated_by'] = $teacher->id; // Use teacher ID, not user ID

        $periodActivity = PeriodActivity::create($data);

        return [
            'period_activity' => $periodActivity,
            'is_new' => true
        ];
    }

    /**
     * Create student period records for all students in a classroom
     *
     * @param int $periodActivityId Period activity ID
     * @param int $classroomId Classroom ID
     * @return \Illuminate\Database\Eloquent\Collection Collection of created student records
     */
    private function createStudentPeriodRecords(int $periodActivityId, int $classroomId)
    {
        // Get all students in the classroom
        $students = \App\Models\Student::where('classroom_id', $classroomId)->get();

        if ($students->isEmpty()) {
            throw new \Exception('No students found in the specified classroom');
        }

        $studentRecords = [];

        // Create student period records for each student
        foreach ($students as $student) {
            $studentRecords[] = \App\Models\StudentPeriodRecord::create([
                'period_activity_id' => $periodActivityId,
                'student_id' => $student->id,
                'attendance_status' => 'present',
                'late_minutes' => 0,
                'homework_completed' => false,
                'homework_score' => null,
                'behavior_plus_marks' => 0,
                'behavior_minus_marks' => 0,
                'behavior_notes' => '',
                'participation_score' => null,
                'participation_notes' => ''
            ]);
        }

        // Get the student records with their related student models
        return \App\Models\StudentPeriodRecord::where('period_activity_id', $periodActivityId)
            ->with('student')
            ->get();
    }
}





