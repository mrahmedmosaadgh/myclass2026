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


    public function index(Request $request)
    {
        $schoolId = auth()->user()->schoolId();
        
        $query = Schedule::with([
            'cst',
            'cst.classroom',
            'cst.subject',
            'cst.teacher',
        ]);

        if ($request->has('copy_id')) {
            $query->where('copy_id', $request->copy_id);
        } else {
            $active_copy = ScheduleCopy::where('active', true);
            if ($schoolId) {
                $active_copy->where('school_id', $schoolId);
            }
            $active_copy = $active_copy->first();
            
            if ($active_copy) {
                $query->where('copy_id', $active_copy->id);
            }
        }

        if ($request->has('classroom_id')) {
            $query->whereHas('cst', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        $records = $query->orderBy('day_number')->orderBy('period_number')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $records
            ]);
        }

        $active_copy = ScheduleCopy::where('active', true)->first(); // Fallback for Inertia view

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
                'copy_id' => 'required|exists:schedule_copies,id',
                'school_id' => 'nullable|exists:schools,id'
            ]);

            // Set school_id from request, copy, or user
            if (empty($validated['school_id'])) {
                $copy = ScheduleCopy::find($validated['copy_id']);
                $validated['school_id'] = $copy->school_id ?? auth()->user()->schoolId();
            }

            // Use day and period_number directly as day_number and period_number
            $validated['day_number'] = $validated['day'];
            $validated['period_number'] = $validated['period_number'];
            unset($validated['day']); // Remove the original 'day' field

            $schedule = Schedule::create($validated);
            $schedule->load(['cst.classroom', 'cst.subject', 'cst.teacher']);

            return response()->json([
                'success' => true,
                'message' => 'Schedule created successfully',
                'data' => $schedule
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create schedule: ' . $e->getMessage()
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
            // Handle clearing the schedule slot
            if ($request->has('cst_id') && is_null($request->cst_id)) {
                $schedule->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Schedule slot cleared successfully'
                ]);
            }

            $validated = $request->validate([
                'cst_id' => 'required|exists:classroom_subject_teachers,id',
                'day' => 'required|integer|min:1|max:7',
                'period_number' => 'required|integer|min:1|max:12', // Adjusted max values reasonable for school
                'active' => 'boolean',
                'notes' => 'nullable|string|max:1000',
                'copy_id' => 'exists:schedule_copies,id'
            ]);

            // Map day to day_number
            $validated['day_number'] = $validated['day'];
            unset($validated['day']);

            // Get the CST record to check teacher conflicts
            $cst = ClassroomSubjectTeacher::find($validated['cst_id']);

            // Check for conflicts using day_number and period_number
            $conflicts = $this->checkForConflicts([
                'day_number' => $validated['day_number'],
                'period_number' => $validated['period_number'],
                'classroom_id' => $cst->classroom_id,
                'teacher_id' => $cst->teacher_id,
                'cst_id' => $validated['cst_id'],
                'current_schedule_id' => $schedule->id
            ]);

            if ($conflicts['exists']) {
                $conflictingSchedule = $conflicts['conflict'];
                $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                
                // Adjust index safely
                $dayIndex = ($conflictingSchedule->day_number - 1);
                $dayName = $dayNames[$dayIndex] ?? 'Day ' . $conflictingSchedule->day_number;

                $message = $conflicts['type'] === 'teacher'
                    ? sprintf(
                        'Teacher conflict: %s is already teaching %s in %s at %s period %d',
                        $conflictingSchedule->cst->teacher->name,
                        $conflictingSchedule->cst->subject->name,
                        $conflictingSchedule->cst->classroom->name,
                        $dayName,
                        $conflictingSchedule->period_number
                    )
                    : sprintf(
                        'Classroom conflict: %s is already scheduled for %s at period %d in %s with %s',
                        $conflictingSchedule->cst->subject->name,
                        $dayName,
                        $conflictingSchedule->period_number,
                        $conflictingSchedule->cst->classroom->name,
                        $conflictingSchedule->cst->teacher->name
                    );

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'conflict' => $conflictingSchedule
                ], 422);
            }
            
            $schedule->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'data' => $schedule->fresh(['cst.classroom', 'cst.subject', 'cst.teacher'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
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
            'day_number' => $data['day_number'],
            'period_number' => $data['period_number'],
            'classroom_id' => $data['classroom_id'],
            'current_schedule_id' => $data['current_schedule_id'] ?? null
        ]);

        // Get the teacher_id from the CST record
        $cst = ClassroomSubjectTeacher::find($data['cst_id']);
        $teacher_id = $cst->teacher_id;

        // Check for classroom conflicts using day/period
        $classroomConflict = Schedule::where('day_number', $data['day_number'])
            ->where('period_number', $data['period_number'])
            ->whereHas('cst', function ($query) use ($data) {
                $query->where('classroom_id', $data['classroom_id']);
            });

        // Check for teacher conflicts using day/period
        $teacherConflict = Schedule::where('day_number', $data['day_number'])
            ->where('period_number', $data['period_number'])
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

    /**
     * Get slot availability information for conflict detection.
     * Returns all teacher assignments for the given day/period across all classrooms.
     */
    public function getSlotAvailability(Request $request)
    {
        try {
            $validated = $request->validate([
                'copy_id' => 'required|exists:schedule_copies,id',
                'classroom_id' => 'required|exists:classrooms,id',
                'day' => 'required|integer|min:1|max:7',
                'period' => 'required|integer|min:1|max:12'
            ]);

            // Get all schedules for this day/period in this copy (all classrooms)
            $existingAssignments = Schedule::where('copy_id', $validated['copy_id'])
                ->where('day_number', $validated['day'])
                ->where('period_number', $validated['period'])
                ->whereNotNull('cst_id')
                ->with(['cst.teacher', 'cst.subject', 'cst.classroom'])
                ->get();

            // Build a map of busy teachers: teacher_id => assignment info
            $busyTeachers = [];
            foreach ($existingAssignments as $schedule) {
                if (!$schedule->cst || !$schedule->cst->teacher_id) {
                    continue;
                }
                $teacherId = $schedule->cst->teacher_id;
                $busyTeachers[$teacherId] = [
                    'teacher_id' => $teacherId,
                    'teacher_name' => $schedule->cst->teacher->name ?? 'Unknown',
                    'assigned_to_classroom' => $schedule->cst->classroom->name ?? 'Unknown',
                    'assigned_to_classroom_id' => $schedule->cst->classroom_id,
                    'subject' => $schedule->cst->subject->name ?? 'Unknown',
                    'schedule_id' => $schedule->id,
                    'is_current_classroom' => $schedule->cst->classroom_id == $validated['classroom_id']
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'busy_teachers' => $busyTeachers,
                    'day' => $validated['day'],
                    'period' => $validated['period'],
                    'total_busy' => count($busyTeachers)
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get slot availability: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all teacher conflicts for a schedule copy.
     * Returns teachers who are assigned to multiple classrooms at the same day/period.
     */
    public function getTeacherConflicts(Request $request)
    {
        try {
            $validated = $request->validate([
                'copy_id' => 'required|exists:schedule_copies,id',
                'classroom_id' => 'nullable|exists:classrooms,id'
            ]);

            // Get all schedules for this copy with CST relationships
            $allSchedules = Schedule::where('copy_id', $validated['copy_id'])
                ->whereNotNull('cst_id')
                ->with(['cst.teacher', 'cst.subject', 'cst.classroom'])
                ->get();

            // Group schedules by day_number and period_number
            $slotGroups = $allSchedules->groupBy(function ($schedule) {
                return $schedule->day_number . '-' . $schedule->period_number;
            });

            // Find conflicts: same teacher in multiple classrooms at same time
            $conflicts = [];
            
            foreach ($slotGroups as $slotKey => $schedulesInSlot) {
                // Group by teacher_id within this slot
                $teacherGroups = $schedulesInSlot->groupBy(function ($schedule) {
                    return $schedule->cst?->teacher_id;
                });

                foreach ($teacherGroups as $teacherId => $teacherSchedules) {
                    if (!$teacherId) continue;
                    
                    // If a teacher appears more than once in the same slot = conflict
                    if ($teacherSchedules->count() > 1) {
                        $first = $teacherSchedules->first();
                        $dayNumber = $first->day_number;
                        $periodNumber = $first->period_number;
                        
                        // Create list of all classrooms where this teacher is assigned
                        $assignedClassrooms = $teacherSchedules->map(function ($s) {
                            return [
                                'schedule_id' => $s->id,
                                'classroom_id' => $s->cst?->classroom_id,
                                'classroom_name' => $s->cst?->classroom?->name ?? 'Unknown',
                                'subject_name' => $s->cst?->subject?->name ?? 'Unknown',
                                'cst_id' => $s->cst_id
                            ];
                        })->values()->toArray();

                        // Mark each schedule as having a conflict
                        foreach ($teacherSchedules as $schedule) {
                            $conflicts[$schedule->id] = [
                                'schedule_id' => $schedule->id,
                                'teacher_id' => $teacherId,
                                'teacher_name' => $schedule->cst?->teacher?->name ?? 'Unknown',
                                'day_number' => $dayNumber,
                                'period_number' => $periodNumber,
                                'conflict_count' => $teacherSchedules->count(),
                                'assigned_to' => $assignedClassrooms,
                                'other_classrooms' => collect($assignedClassrooms)
                                    ->filter(fn($c) => $c['schedule_id'] !== $schedule->id)
                                    ->values()
                                    ->toArray()
                            ];
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'conflicts' => $conflicts,
                    'total_conflicts' => count($conflicts)
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get teacher conflicts: ' . $e->getMessage()
            ], 500);
        }
    }
}






















