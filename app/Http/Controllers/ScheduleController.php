<?php

namespace App\Http\Controllers;

use App\Models\ClassroomSubjectTeacher;
use App\Models\Schedule;
use App\Models\ScheduleCopy;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ScheduleController extends Controller
{



    public function load_data()
    {
        try {
            $active_copy = ScheduleCopy::where('active', true)->first();

            if (!$active_copy) {
                return response()->json([
                    'success' => false,
                    'records' => [],
                    'options' => [],
                    'active_copy' => null,
                    'message' => 'No active schedule copy found. Please activate one copy.'
                ], 404);
            }

            $records = Schedule::with([
                'cst',
                'cst.classroom',
                'cst.subject',
                'cst.teacher',
            ])
                ->where('copy_id', $active_copy->id)
                ->orderBy('period_code')
                ->get();

            $csts = ClassroomSubjectTeacher::with(['classroom', 'subject', 'teacher'])->get();

            return response()->json([
                'success' => true,
                'records' => $records,
                'options' => [
                    'csts' => $csts,
                    'activeCopy' => $active_copy
                ],
                'message' => 'Data loaded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load data: ' . $e->getMessage()
            ], 500);
        }
    }


    public function index()
    {
        $active_copy = ScheduleCopy::where('active', true)->first();

        if (!$active_copy) {
            return Inertia::render('my_class/admin/Schedules/Index', [
                'records' => [],
                'options' => [],
                'active_copy' => null,
                'error' => 'No active schedule copy found. Please activate one copy.'
            ]);
        }

        $records = Schedule::with([
            'cst',
            'cst.classroom',
            'cst.subject',
            'cst.teacher',
        ])
            ->where('copy_id', $active_copy->id)
            // ->where('active', true)
            ->orderBy('period_code')
            ->get();

        $options = [
            'csts' => ClassroomSubjectTeacher::with(['classroom', 'subject', 'teacher'])
                ->get()
                ->map(function ($cst) {
                    return [
                        'id' => $cst->id,
                        'classroom' => [
                            'id' => $cst->classroom->id,
                            'name' => $cst->classroom->name,
                            'grade' => $cst->classroom->grade
                        ],
                        'classroom_name' => $cst->classroom->name,
                        'subject_name' => $cst->subject->name,
                        'teacher_name' => $cst->teacher->name
                    ];
                })
        ];

        return Inertia::render('my_class/admin/Schedules/Index', [
            'records' => $records,
            'records2' => $records,
            'options' => $options,
            'active_copy' => $active_copy
        ]);
    }
    public function index2()
    {
        $schedules = Schedule::with(['cst.classroom', 'cst.subject', 'cst.teacher'])
            ->get();


        return response()->json([
            'records' => $schedules,
            'message' => 'Schedules retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'cst_id' => 'required|exists:classroom_subject_teachers,id',
                'day' => 'required|integer|min:1|max:5',
                'period_number' => 'required|integer|min:1|max:8',
                'active' => 'boolean',
                'notes' => 'nullable|string|max:1000',
                'copy_id' => 'exists:schedule_copies,id'  // Optional in validation since we're setting it
            ]);

            // Convert day and period_number to period_code
            $validated['period_code'] = Schedule::makePeriodCode($validated['day'], $validated['period_number']);
            unset($validated['day'], $validated['period_number']);

            $schedule = Schedule::create($validated);
            $schedule->load(['cst.classroom', 'cst.subject', 'cst.teacher']);

            return response()->json([
                'message' => 'Schedule created successfully',
                'record' => $schedule
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'store Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Schedule $schedule)
    {
        return response()->json([
            'record' => $schedule->load(['cst.classroom', 'cst.subject', 'cst.teacher']),
            'message' => 'Schedule retrieved successfully'
        ]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        try {
            $validated = $request->validate([
                'cst_id' => 'required|exists:classroom_subject_teachers,id',
                'day' => 'required|integer|min:1|max:5',
                'period_number' => 'required|integer|min:1|max:8',
                'active' => 'boolean',
                'notes' => 'nullable|string|max:1000'
            ]);
            $active_copy = ScheduleCopy::where('active', true)->first();

            // Add copy_id from active copy
            $validated['copy_id'] = $active_copy->id;

            // Convert day and period_number to period_code
            $validated['period_code'] = Schedule::makePeriodCode($validated['day'], $validated['period_number']);

            // Get the CST record to check teacher conflicts
            $cst = ClassroomSubjectTeacher::find($validated['cst_id']);

            // Check for conflicts using period_code
            $conflicts = $this->checkForConflicts([
                'period_code' => $validated['period_code'],
                'classroom_id' => $cst->classroom_id,
                'teacher_id' => $cst->teacher_id,
                'cst_id' => $validated['cst_id'],
                'current_schedule_id' => $schedule->id
            ]);

            if ($conflicts['exists']) {
                $conflictingSchedule = $conflicts['conflict'];
                $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];

                $message = $conflicts['type'] === 'teacher'
                    ? sprintf(
                        'Teacher conflict: %s is already teaching %s in %s at %s period %d',
                        $conflictingSchedule->cst->teacher->name,
                        $conflictingSchedule->cst->subject->name,
                        $conflictingSchedule->cst->classroom->name,
                        $dayNames[$conflictingSchedule->day - 1],
                        $conflictingSchedule->period_number
                    )
                    : sprintf(
                        'Classroom conflict: %s is already scheduled for %s at period %d in %s with %s',
                        $conflictingSchedule->cst->subject->name,
                        $dayNames[$conflictingSchedule->day - 1],
                        $conflictingSchedule->period_number,
                        $conflictingSchedule->cst->classroom->name,
                        $conflictingSchedule->cst->teacher->name
                    );

                return response()->json([
                    'message' => $message,
                    'conflict' => $conflictingSchedule
                ], 422);
            }

            // Remove day and period_number from validated data since we're using period_code
            unset($validated['day'], $validated['period_number']);
            
            $schedule->update($validated);

            return response()->json([
                'message' => 'Schedule updated successfully',
                'record' => $schedule->fresh(['cst.classroom', 'cst.subject', 'cst.teacher'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update2(Request $request)
    {
        try {
            // Log incoming request data
            \Log::info('Schedule update request:', $request->all());
// return $request->all();
            $schedule = Schedule::find($request->id);

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule not found with ID: ' . $request->id
                ], 404);
            }

            if ($request->remove_session) {
                $schedule->update([
                    'period_code' => null
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Schedule slot cleared successfully',
                    'record' => $schedule
                ]);
            }

            // Handle regular update operation
            $validated = $request->validate([
                'day' => 'required|integer',
                'period_number' => 'required|integer',
                // Add other validation rules
            ]);

            // Convert day and period_number to period_code
            $validated['period_code'] = Schedule::makePeriodCode($validated['day'], $validated['period_number']);
            unset($validated['day'], $validated['period_number']);

            $schedule->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'record' => $schedule
            ]);
        } catch (\Exception $e) {
            \Log::error('Schedule update error:', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update schedule: ' . $e->getMessage()
            ], 500);
        }
    }
    public function destroy(Schedule $schedule)
    {
        try {
            // Check for any dependencies before deletion
            // Add any specific business logic checks here

            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Optional: Add a method to check for conflicts
    private function checkForConflicts($data)
    {
        \Log::info('Checking conflicts with:', [
            'period_code' => $data['period_code'],
            'classroom_id' => $data['classroom_id'],
            'current_schedule_id' => $data['current_schedule_id'] ?? null
        ]);

        // Get the teacher_id from the CST record
        $cst = ClassroomSubjectTeacher::find($data['cst_id']);
        $teacher_id = $cst->teacher_id;

        // Check for classroom conflicts using period_code
        $classroomConflict = Schedule::where('period_code', $data['period_code'])
            ->whereHas('cst', function ($query) use ($data) {
                $query->where('classroom_id', $data['classroom_id']);
            });

        // Check for teacher conflicts using period_code
        $teacherConflict = Schedule::where('period_code', $data['period_code'])
            ->whereHas('cst', function ($query) use ($teacher_id) {
                $query->where('teacher_id', $teacher_id);
            });

        // Exclude current schedule if updating
        if (isset($data['current_schedule_id'])) {
            $classroomConflict->where('id', '!=', $data['current_schedule_id']);
            $teacherConflict->where('id', '!=', $data['current_schedule_id']);
        }

        $existingClassroomSchedule = $classroomConflict->first();
        $existingTeacherSchedule = $teacherConflict->first();

        if ($existingClassroomSchedule) {
            return [
                'exists' => true,
                'conflict' => $existingClassroomSchedule->load(['cst.classroom', 'cst.subject', 'cst.teacher']),
                'type' => 'classroom'
            ];
        }

        if ($existingTeacherSchedule) {
            return [
                'exists' => true,
                'conflict' => $existingTeacherSchedule->load(['cst.classroom', 'cst.subject', 'cst.teacher']),
                'type' => 'teacher'
            ];
        }

        return ['exists' => false];
    }
}






















