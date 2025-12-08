<?php

namespace App\Http\Controllers;

use App\Models\PeriodActivity;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PeriodActivityController extends Controller
{
    public function index(Request $request)
    {
        // First ensure we have the necessary data to potentially create records
        if ($request->has('schedule_id') && $request->has('calendar_id') && $request->has('classroom_id')) {
            try {
                // Prepare data for getOrCreatePeriodActivity
                $data = [
                    'schedule_id' => $request->schedule_id,
                    'calendar_id' => $request->calendar_id,
                    'teacher_id' => $request->teacher_id ?? auth()->id(),
                    'period_status' => 'completed',
                    'classroom_id' => $request->classroom_id
                ];
                
                // Get or create the period activity
                $result = $this->getOrCreatePeriodActivity($data);
                $periodActivity = $result['period_activity'];
                $isNewRecord = $result['is_new'];
                
                // If this is a new record, create student records
                if ($isNewRecord) {
                    $this->createStudentPeriodRecords($periodActivity->id, $request->classroom_id);
                }
            } catch (\Exception $e) {
                // Log the error but continue with the index display
                \Log::error('Failed to create period activity: ' . $e->getMessage());
            }
        }

        // Continue with the regular index functionality
        $query = PeriodActivity::with(['schedule', 'calendar', 'teacher', 'substituteTeacher', 'event', 'studentRecords.student'])
            ->when(auth()->check(), function ($q) {
                $q->whereHas('calendar', function ($query) {
                    $query->where('school_id', auth()->user()->school_id);
                });
            });

        // Apply filters if provided
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
        if ($request->has('schedule_id') && $request->has('calendar_date')) {
            $calendar = \App\Models\Calendar::where('date', $request->calendar_date)
                ->when(auth()->check(), function ($q) {
                    $q->where('school_id', auth()->user()->school_id);
                })
                ->first();

            if ($calendar) {
                $query->where('calendar_id', $calendar->id);
            }
        }

        $records = $query->orderBy('created_at', 'desc')->paginate(10);

        return Inertia::render('my_class/admin/PeriodActivities/Index', [
            'records' => $records,
            'options' => [
                'schools' => \App\Models\School::select('id', 'name')->get(),
                'teachers' => \App\Models\Teacher::select('id', 'name')->get(),
                'statusOptions' => [
                    ['value' => 'completed', 'label' => 'Completed'],
                    ['value' => 'cancelled', 'label' => 'Cancelled'],
                    ['value' => 'modified', 'label' => 'Modified'],
                    ['value' => 'event_affected', 'label' => 'Event Affected']
                ],
                'filters' => $request->only(['calendar_id', 'schedule_id', 'teacher_id', 'calendar_date', 'classroom_id'])
            ]
        ]);
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
    $data['created_by'] = Auth::id();
    $data['updated_by'] = Auth::id();
    
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
    $students = Student::where('classroom_id', $classroomId)->get();
    
    if ($students->isEmpty()) {
        throw new \Exception('No students found in the specified classroom');
    }
    
    $studentRecords = [];
    
    // Create student period records for each student
    foreach ($students as $student) {
        $studentRecords[] = StudentPeriodRecord::create([
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
    
    return collect($studentRecords)->load('student');
}






    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'school_id' => 'required|exists:schools,id',
            'active' => 'boolean'
        ]);

        PeriodActivity::create($validated);

        return redirect()->route('period-activities.index');
    }

    public function update(Request $request, PeriodActivity $periodActivity)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'school_id' => 'required|exists:schools,id',
            'active' => 'boolean'
        ]);

        $periodActivity->update($validated);

        return redirect()->route('period-activities.index');
    }

    public function destroy(PeriodActivity $periodActivity)
    {
        $periodActivity->delete();

        return redirect()->route('period-activities.index');
    }
}

