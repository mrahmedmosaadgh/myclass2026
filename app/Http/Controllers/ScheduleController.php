<?php

namespace App\Http\Controllers;

use App\Models\ClassroomSubjectTeacher;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ScheduleController extends Controller
{



    public function load_data()
    {
        try {
            $schoolId = auth()->user()->schoolId();

            $records = Schedule::with([
                'cst',
                'cst.classroom',
                'cst.subject',
                'cst.teacher',
            ])
                ->where('school_id', $schoolId)
                ->orderBy('period_code')
                ->get();

            $csts = ClassroomSubjectTeacher::with(['classroom', 'subject', 'teacher'])
                ->where('school_id', $schoolId)
                ->get();

            return response()->json([
                'success' => true,
                'records' => $records,
                'options' => [
                    'csts' => $csts,
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
        ])->where('school_id', $schoolId);

        // Filter by classroom if requested
        if ($request->has('classroom_id')) {
            $query->whereHas('cst', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        $records = $query->orderBy('day_number')->orderBy('period_number')->get();

        if ($request->wantsJson()) {
            // Get total possible slots (e.g., 5 days × 8 periods = 40 slots)
            $totalPossibleSlots = 40; // Default assumption
            
            // Calculate classroom-specific statistics (current selection)
            $assignedCount = $records->filter(function($schedule) {
                return !is_null($schedule->cst_id) 
                    && !is_null($schedule->day_number) 
                    && !is_null($schedule->period_number);
            })->count();
            
            $classroomStats = [
                'total_slots' => $totalPossibleSlots,
                'assigned_slots' => $assignedCount,
                'unassigned_slots' => $totalPossibleSlots - $assignedCount,
                'conflict_count' => 0 
            ];
            
            // Calculate subject breakdown for classroom
            if ($request->has('classroom_id')) {
                $subjectBreakdown = [];
                foreach ($records as $schedule) {
                    if ($schedule->cst_id && $schedule->cst) {
                        $subjectName = $schedule->cst->subject->name ?? 'Unknown';
                        $teacherName = $schedule->cst->teacher->name ?? 'Unknown';
                        $classesPerWeek = $schedule->cst->classes_per_week ?? 0;
                        $key = $schedule->cst_id; 
                        
                        if (!isset($subjectBreakdown[$key])) {
                            $subjectBreakdown[$key] = [
                                'subject_name' => $subjectName,
                                'teacher_name' => $teacherName,
                                'expected' => $classesPerWeek,
                                'actual' => 0
                            ];
                        }
                        $subjectBreakdown[$key]['actual']++;
                    }
                }
                
                $classroomStats['subject_breakdown'] = array_values($subjectBreakdown);
                usort($classroomStats['subject_breakdown'], function($a, $b) {
                    return strcmp($a['subject_name'], $b['subject_name']);
                });
            }
            
            // Calculate classroom-specific conflicts if classroom_id is provided
            if ($request->has('classroom_id')) {
                $classroomStats['conflict_count'] = $this->calculateClassroomConflictCount(
                    $schoolId, 
                    $request->classroom_id
                );
            }
            
            // Calculate overall statistics
            // Get all schedules for this school
            $allSchedules = Schedule::where('school_id', $schoolId)
                ->with(['cst', 'cst.classroom', 'cst.subject', 'cst.teacher'])
                ->get();
            
            // Count unique classrooms
            $classroomCount = Schedule::where('school_id', $schoolId)
                ->whereHas('cst')
                ->with('cst.classroom')
                ->get()
                ->pluck('cst.classroom.id')
                ->unique()
                ->count();
            
            $totalOverallSlots = $classroomCount * $totalPossibleSlots;
            
            $overallAssignedCount = $allSchedules->filter(function($schedule) {
                return !is_null($schedule->cst_id) 
                    && !is_null($schedule->day_number) 
                    && !is_null($schedule->period_number);
            })->count();
            
            $overallStats = [
                'total_slots' => $totalOverallSlots,
                'assigned_slots' => $overallAssignedCount,
                'unassigned_slots' => $totalOverallSlots - $overallAssignedCount,
                'conflict_count' => $this->calculateConflictCount($schoolId)
            ];
            
            return response()->json([
                'success' => true,
                'data' => $records,
                'stats' => $classroomStats,
                'classroom_stats' => $classroomStats,
                'overall_stats' => $overallStats
            ]);
        }

        $options = [
            'csts' => ClassroomSubjectTeacher::with(['classroom', 'subject', 'teacher'])
                ->where('school_id', $schoolId)
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
            'options' => $options
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
                'notes' => 'nullable|string|max:1000',
                'school_id' => 'nullable|exists:schools,id'
            ]);

            // Set school_id from request or user
            if (empty($validated['school_id'])) {
                $validated['school_id'] = auth()->user()->schoolId();
            }

            // Use day and period_number directly as day_number and period_number
            $validated['day_number'] = $validated['day'];
            unset($validated['day']); 

            // Check for existing schedule in this slot (implicit conflict for same classroom)
            // But we'll rely on our checkForConflicts or DB constraints if any
            // Actually, we should check for conflicts before creating
            
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
                'period_number' => 'required|integer|min:1|max:12', 
                'notes' => 'nullable|string|max:1000',
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
                'current_schedule_id' => $schedule->id,
                'school_id' => $schedule->school_id
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

        $schoolId = $data['school_id'] ?? auth()->user()->schoolId();

        // Get the teacher_id from the CST record
        $cst = ClassroomSubjectTeacher::find($data['cst_id']);
        $teacher_id = $cst->teacher_id;

        // Check for classroom conflicts using day/period
        $classroomConflict = Schedule::where('school_id', $schoolId)
            ->where('day_number', $data['day_number'])
            ->where('period_number', $data['period_number'])
            ->whereHas('cst', function ($query) use ($data) {
                $query->where('classroom_id', $data['classroom_id']);
            });

        // Check for teacher conflicts using day/period
        $teacherConflict = Schedule::where('school_id', $schoolId)
            ->where('day_number', $data['day_number'])
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
                'classroom_id' => 'required|exists:classrooms,id',
                'day' => 'required|integer|min:1|max:7',
                'period' => 'required|integer|min:1|max:12'
            ]);

            $schoolId = auth()->user()->schoolId();

            // Get all schedules for this day/period in this school (all classrooms)
            $existingAssignments = Schedule::where('school_id', $schoolId)
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
     * Get all teacher conflicts for a school.
     * Returns teachers who are assigned to multiple classrooms at the same day/period.
     */
    public function getTeacherConflicts(Request $request)
    {
        try {
            $validated = $request->validate([
                'classroom_id' => 'nullable|exists:classrooms,id'
            ]);
            
            $schoolId = auth()->user()->schoolId();

            // Get all schedules for this school with CST relationships
            // Exclude schedules without proper day/period assignments
            $allSchedules = Schedule::where('school_id', $schoolId)
                ->whereNotNull('cst_id')
                ->whereNotNull('day_number')
                ->whereNotNull('period_number')
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
                        // If classroom_id is requested, check if this conflict involves the requested classroom
                        if (isset($validated['classroom_id'])) {
                            $involvesClassroom = $teacherSchedules->contains(function ($s) use ($validated) {
                                return $s->cst && $s->cst->classroom_id == $validated['classroom_id'];
                            });
                            
                            if (!$involvesClassroom) continue;
                        }

                        $first = $teacherSchedules->first();
                        $dayNumber = $first->day_number;
                        $periodNumber = $first->period_number;
                        
                        // Create list of all classrooms where this teacher is assigned
                        $assignedClassrooms = $teacherSchedules->map(function ($s) {
                            return [
                                'schedule_id' => $s->id,
                                'classroom_id' => $s->cst ? $s->cst->classroom_id : null,
                                'classroom_name' => $s->cst && $s->cst->classroom ? $s->cst->classroom->name : 'Unknown',
                                'subject_name' => $s->cst && $s->cst->subject ? $s->cst->subject->name : 'Unknown',
                                'cst_id' => $s->cst_id
                            ];
                        })->values()->toArray();

                        // For the API response format expected by frontend
                        // We want distinct CONFLICTS (one per teacher per slot), not one per schedule
                        // Use a unique key for the conflict: teacher_id-day-period
                        $conflictKey = $teacherId . '-' . $dayNumber . '-' . $periodNumber;
                        
                        $conflictData = [
                            'teacher_id' => $teacherId,
                            'teacher_name' => $first->cst && $first->cst->teacher ? $first->cst->teacher->name : 'Unknown',
                            'day' => $dayNumber,
                            'period' => $periodNumber,
                            'classrooms' => $assignedClassrooms,
                        ];
                        
                        $conflicts[$conflictKey] = $conflictData;
                        
                        // Also key by schedule_id for TimetableGrid cell display
                        foreach ($teacherSchedules as $schedule) {
                            $conflicts[$schedule->id] = array_merge($conflictData, [
                                'schedule_id' => $schedule->id,
                                'other_classrooms' => collect($assignedClassrooms)
                                    ->filter(fn($c) => $c['schedule_id'] !== $schedule->id)
                                    ->values()
                                    ->toArray()
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'conflicts' => $conflicts,
                    'total_conflicts' => count(array_filter($conflicts, fn($c) => !isset($c['schedule_id'])))
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

    /**
     * Calculate the number of unique conflicts for a school.
     * A conflict occurs when the same teacher is assigned to multiple classrooms at the same day/period.
     * 
     * @param int $schoolId The school ID
     * @return int The number of unique conflicts
     */
    private function calculateConflictCount($schoolId)
    {
        try {
            // Get all schedules for this school with CST relationships
            // Exclude schedules without proper day/period assignments
            $allSchedules = Schedule::where('school_id', $schoolId)
                ->whereNotNull('cst_id')
                ->whereNotNull('day_number')
                ->whereNotNull('period_number')
                ->with(['cst.teacher', 'cst.classroom'])
                ->get();

            // Group schedules by day_number and period_number
            $slotGroups = $allSchedules->groupBy(function ($schedule) {
                return $schedule->day_number . '-' . $schedule->period_number;
            });

            // Count conflicts: same teacher in multiple classrooms at same time
            $conflictCount = 0;
            
            foreach ($slotGroups as $slotKey => $schedulesInSlot) {
                // Group by teacher_id within this slot
                $teacherGroups = $schedulesInSlot->groupBy(function ($schedule) {
                    return $schedule->cst?->teacher_id;
                });

                foreach ($teacherGroups as $teacherId => $teacherSchedules) {
                    if (!$teacherId) continue;
                    
                    // If a teacher appears more than once in the same slot = conflict
                    if ($teacherSchedules->count() > 1) {
                        // Count this as ONE conflict (not multiple)
                        $conflictCount++;
                    }
                }
            }

            return $conflictCount;

        } catch (\Exception $e) {
            \Log::error('Error calculating conflict count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate the number of conflicts for a specific classroom.
     * A conflict occurs when the same teacher is assigned to this classroom and another classroom at the same day/period.
     * 
     * @param int $schoolId The school ID
     * @param int $classroomId The classroom ID
     * @return int The number of conflicts for this classroom
     */
    private function calculateClassroomConflictCount($schoolId, $classroomId)
    {
        try {
            // Get all schedules for this classroom
            // Exclude schedules without proper day/period assignments
            $classroomSchedules = Schedule::where('school_id', $schoolId)
                ->whereNotNull('cst_id')
                ->whereNotNull('day_number')
                ->whereNotNull('period_number')
                ->whereHas('cst', function($q) use ($classroomId) {
                    $q->where('classroom_id', $classroomId);
                })
                ->with(['cst.teacher'])
                ->get();

            $conflictCount = 0;

            foreach ($classroomSchedules as $schedule) {
                if (!$schedule->cst || !$schedule->cst->teacher_id) continue;

                // Check if this teacher is assigned elsewhere at the same time
                $otherAssignments = Schedule::where('school_id', $schoolId)
                    ->where('id', '!=', $schedule->id)
                    ->where('day_number', $schedule->day_number)
                    ->where('period_number', $schedule->period_number)
                    ->whereNotNull('cst_id')
                    ->whereHas('cst', function($q) use ($schedule, $classroomId) {
                        $q->where('teacher_id', $schedule->cst->teacher_id)
                          ->where('classroom_id', '!=', $classroomId);
                    })
                    ->count();

                if ($otherAssignments > 0) {
                    $conflictCount++;
                }
            }

            return $conflictCount;

        } catch (\Exception $e) {
            \Log::error('Error calculating classroom conflict count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Validate AI-generated import JSON
     * Validates structure, required fields, and business rules
     */
    public function validateAIImport(Request $request)
    {
        try {
            $errors = [];
            $data = $request->input('data');

            // Parse JSON if string
            if (is_string($data)) {
                try {
                    $data = json_decode($data, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return response()->json([
                            'success' => false,
                            'errors' => ['Invalid JSON format: ' . json_last_error_msg()]
                        ], 422);
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['Invalid JSON format']
                    ], 422);
                }
            }

            // Validate required top-level fields
            if (!isset($data['classroom_id'])) {
                $errors[] = 'Missing required field: classroom_id';
            }
            if (!isset($data['entries']) || !is_array($data['entries'])) {
                $errors[] = 'Missing or invalid field: entries (must be an array)';
            }

            // If basic structure is invalid, return early
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'errors' => array_slice($errors, 0, 5)
                ], 422);
            }

            // Validate entries
            $allowedDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
            $seenSlots = [];

            foreach ($data['entries'] as $index => $entry) {
                $entryErrors = [];

                // Required fields
                if (!isset($entry['day'])) {
                    $entryErrors[] = "Entry #{$index}: Missing required field 'day'";
                }
                if (!isset($entry['period'])) {
                    $entryErrors[] = "Entry #{$index}: Missing required field 'period'";
                }
                if (!isset($entry['subject'])) {
                    $entryErrors[] = "Entry #{$index}: Missing required field 'subject'";
                }

                // Validate day
                if (isset($entry['day']) && !in_array($entry['day'], $allowedDays)) {
                    $entryErrors[] = "Entry #{$index}: Invalid day '{$entry['day']}'. Must be one of: " . implode(', ', $allowedDays);
                }

                // Validate period
                if (isset($entry['period'])) {
                    if (!is_numeric($entry['period']) || $entry['period'] < 1 || $entry['period'] > 12) {
                        $entryErrors[] = "Entry #{$index}: Period must be between 1 and 12";
                    }
                }

                // Check for duplicates
                if (isset($entry['day']) && isset($entry['period'])) {
                    $slotKey = $entry['day'] . '-' . $entry['period'];
                    if (isset($seenSlots[$slotKey])) {
                        $entryErrors[] = "Entry #{$index}: Duplicate slot ({$entry['day']}, period {$entry['period']})";
                    }
                    $seenSlots[$slotKey] = true;
                }

                $errors = array_merge($errors, $entryErrors);

                // Limit to first 5 errors for better UX
                if (count($errors) >= 5) {
                    break;
                }
            }

            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'errors' => array_slice($errors, 0, 5),
                    'total_errors' => count($errors)
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Validation passed',
                'summary' => [
                    'total_entries' => count($data['entries']),
                    'classroom_id' => $data['classroom_id']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply AI import - Replace entire week for classroom
     */
    /**
     * Apply AI import - Replace entire week for classroom
     */
    public function applyAIImport(Request $request)
    {
        try {
            $validated = $request->validate([
                'data' => 'required',
                'school_id' => 'nullable|exists:schools,id'
            ]);

            $data = $validated['data'];
            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            $schoolId = $validated['school_id'] ?? auth()->user()->schoolId();
            $classroomId = $data['classroom_id'];

            // Delete existing schedules for this classroom in this school
            // Force delete existing schedules for this classroom in this school to prevent soft-delete conflicts
            Schedule::where('school_id', $schoolId)
                ->whereHas('cst', function($q) use ($classroomId) {
                    $q->where('classroom_id', $classroomId);
                })
                ->forceDelete();

            $dayMap = [
                'Sunday' => 1,
                'Monday' => 2,
                'Tuesday' => 3,
                'Wednesday' => 4,
                'Thursday' => 5,
                'Friday' => 6,
                'Saturday' => 7
            ];

            $created = [];
            $failed = [];

            foreach ($data['entries'] as $index => $entry) {
                try {
                    // Find CST by subject name and classroom
                    $cst = ClassroomSubjectTeacher::where('classroom_id', $classroomId)
                        ->whereHas('subject', function($q) use ($entry) {
                            $q->whereRaw('LOWER(name) = ?', [strtolower($entry['subject'])]);
                        })
                        ->first();

                    if (!$cst) {
                        $failed[] = [
                            'index' => $index,
                            'reason' => "Subject '{$entry['subject']}' not found for classroom",
                            'entry' => $entry
                        ];
                        continue;
                    }

                    $schedule = Schedule::create([
                        'school_id' => $schoolId,
                        'cst_id' => $cst->id,
                        'day_number' => $dayMap[$entry['day']],
                        'period_number' => $entry['period'],
                        'notes' => $entry['notes'] ?? null,
                    ]);

                    $created[] = [
                        'day' => $entry['day'],
                        'period' => $entry['period'],
                        'subject' => $entry['subject']
                    ];

                } catch (\Exception $e) {
                    $failed[] = [
                        'index' => $index,
                        'reason' => $e->getMessage(),
                        'entry' => $entry
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Import completed',
                'summary' => [
                    'created' => count($created),
                    'failed' => count($failed),
                    'total' => count($data['entries'])
                ],
                'created' => $created,
                'failed' => $failed
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply AI update - Merge by day+period
     */
    public function applyAIUpdate(Request $request)
    {
        try {
            $validated = $request->validate([
                'data' => 'required',
                'school_id' => 'nullable|exists:schools,id'
            ]);

            $data = $validated['data'];
            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            $schoolId = $validated['school_id'] ?? auth()->user()->schoolId();
            $classroomId = $data['classroom_id'];

            $dayMap = [
                'Sunday' => 1,
                'Monday' => 2,
                'Tuesday' => 3,
                'Wednesday' => 4,
                'Thursday' => 5,
                'Friday' => 6,
                'Saturday' => 7
            ];

            $updated = [];
            $created = [];
            $failed = [];

            foreach ($data['entries'] as $index => $entry) {
                try {
                    $dayNumber = $dayMap[$entry['day']];
                    $periodNumber = $entry['period'];

                    // Find CST by subject name and classroom (case-insensitive)
                    $cst = ClassroomSubjectTeacher::where('classroom_id', $classroomId)
                        ->whereHas('subject', function($q) use ($entry) {
                            $q->whereRaw('LOWER(name) = ?', [strtolower($entry['subject'])]);
                        })
                        ->first();

                    if (!$cst) {
                        $failed[] = [
                            'index' => $index,
                            'reason' => "Subject '{$entry['subject']}' not found for classroom",
                            'entry' => $entry
                        ];
                        continue;
                    }

                    // Find existing schedule for this day/period/classroom
                    $existingSchedule = Schedule::where('school_id', $schoolId)
                        ->where('day_number', $dayNumber)
                        ->where('period_number', $periodNumber)
                        ->whereHas('cst', function($q) use ($classroomId) {
                            $q->where('classroom_id', $classroomId);
                        })
                        ->first();

                    if ($existingSchedule) {
                        // Update existing
                        $existingSchedule->update([
                            'cst_id' => $cst->id,
                            'notes' => $entry['notes'] ?? $existingSchedule->notes,
                        ]);

                        $updated[] = [
                            'day' => $entry['day'],
                            'period' => $entry['period'],
                            'subject' => $entry['subject']
                        ];
                    } else {
                        // Create new
                        Schedule::create([
                            'school_id' => $schoolId,
                            'cst_id' => $cst->id,
                            'day_number' => $dayNumber,
                            'period_number' => $periodNumber,
                            'notes' => $entry['notes'] ?? null,
                        ]);

                        $created[] = [
                            'day' => $entry['day'],
                            'period' => $entry['period'],
                            'subject' => $entry['subject']
                        ];
                    }

                } catch (\Exception $e) {
                    $failed[] = [
                        'index' => $index,
                        'reason' => $e->getMessage(),
                        'entry' => $entry
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Update completed',
                'summary' => [
                    'updated' => count($updated),
                    'created' => count($created),
                    'failed' => count($failed),
                    'total' => count($data['entries'])
                ],
                'updated' => $updated,
                'created' => $created,
                'failed' => $failed
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a preview of random fill assignments for empty periods
     * Does not save anything, just returns what would be filled
     */
    public function generateRandomFillPreview(Request $request)
    {
        try {
            $validated = $request->validate([
                'classroom_id' => 'required|exists:classrooms,id',
                'school_id' => 'nullable|exists:schools,id'
            ]);

            $classroomId = $validated['classroom_id'];
            $schoolId = $validated['school_id'] ?? auth()->user()->schoolId();

            // Get all existing schedules for this classroom
            $existingSchedules = Schedule::where('school_id', $schoolId)
                ->whereHas('cst', function($q) use ($classroomId) {
                    $q->where('classroom_id', $classroomId);
                })
                ->get();

            // Create a map of filled slots
            $filledSlots = [];
            foreach ($existingSchedules as $schedule) {
                if ($schedule->cst_id && $schedule->day_number && $schedule->period_number) {
                    $key = $schedule->day_number . '-' . $schedule->period_number;
                    $filledSlots[$key] = true;
                }
            }

            // Get all CST options for this classroom
            $cstOptions = ClassroomSubjectTeacher::where('classroom_id', $classroomId)
                ->with(['subject', 'teacher'])
                ->get();

            if ($cstOptions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No subject-teacher assignments found for this classroom'
                ], 422);
            }

            // Define all possible slots (days 1-5, periods 1-8)
            $allSlots = [];
            for ($day = 1; $day <= 5; $day++) {
                for ($period = 1; $period <= 8; $period++) {
                    $allSlots[] = ['day' => $day, 'period' => $period];
                }
            }

            // Find empty slots
            $emptySlots = [];
            foreach ($allSlots as $slot) {
                $key = $slot['day'] . '-' . $slot['period'];
                if (!isset($filledSlots[$key])) {
                    $emptySlots[] = $slot;
                }
            }

            if (empty($emptySlots)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No empty slots to fill',
                    'data' => [
                        'filled' => [],
                        'conflicts' => [],
                        'unfillable' => [],
                        'summary' => [
                            'total_empty' => 0,
                            'fillable' => 0,
                            'conflicts' => 0,
                            'unfillable' => 0
                        ]
                    ]
                ]);
            }

            // For each empty slot, find available CSTs
            $filled = [];
            $conflicts = [];
            $unfillable = [];

            foreach ($emptySlots as $slot) {
                $day = $slot['day'];
                $period = $slot['period'];

                // Get all teachers busy at this slot in other classrooms
                $busyTeacherIds = Schedule::where('school_id', $schoolId)
                    ->where('day_number', $day)
                    ->where('period_number', $period)
                    ->whereNotNull('cst_id')
                    ->with('cst')
                    ->get()
                    ->pluck('cst.teacher_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();

                // Find available CSTs (teachers not busy)
                $availableCsts = $cstOptions->filter(function($cst) use ($busyTeacherIds) {
                    return !in_array($cst->teacher_id, $busyTeacherIds);
                });

                if ($availableCsts->isNotEmpty()) {
                    // Randomly pick one available CST
                    $selectedCst = $availableCsts->random();
                    $filled[] = [
                        'day' => $day,
                        'period' => $period,
                        'cst_id' => $selectedCst->id,
                        'subject_name' => $selectedCst->subject->name ?? 'Unknown',
                        'teacher_name' => $selectedCst->teacher->name ?? 'Unknown',
                        'has_conflict' => false
                    ];
                } else {
                    // All teachers are busy, check if we can still fill with conflict
                    if ($cstOptions->isNotEmpty()) {
                        $selectedCst = $cstOptions->random();
                        
                        // Get conflict details
                        $conflictSchedule = Schedule::where('school_id', $schoolId)
                            ->where('day_number', $day)
                            ->where('period_number', $period)
                            ->whereHas('cst', function($q) use ($selectedCst) {
                                $q->where('teacher_id', $selectedCst->teacher_id);
                            })
                            ->with(['cst.classroom'])
                            ->first();

                        $conflicts[] = [
                            'day' => $day,
                            'period' => $period,
                            'cst_id' => $selectedCst->id,
                            'subject_name' => $selectedCst->subject->name ?? 'Unknown',
                            'teacher_name' => $selectedCst->teacher->name ?? 'Unknown',
                            'has_conflict' => true,
                            'conflict_details' => [
                                'teacher_name' => $selectedCst->teacher->name ?? 'Unknown',
                                'busy_in_classroom' => $conflictSchedule ? $conflictSchedule->cst->classroom->name : 'Unknown',
                                'message' => 'Teacher is already assigned to another classroom at this time'
                            ]
                        ];
                    } else {
                        // No CSTs available at all (shouldn't happen, but handle it)
                        $unfillable[] = [
                            'day' => $day,
                            'period' => $period,
                            'reason' => 'No subject-teacher assignments available for this classroom'
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'filled' => $filled,
                    'conflicts' => $conflicts,
                    'unfillable' => $unfillable,
                    'summary' => [
                        'total_empty' => count($emptySlots),
                        'fillable' => count($filled),
                        'conflicts' => count($conflicts),
                        'unfillable' => count($unfillable)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply random fill assignments after user confirmation
     * Accepts array of assignments and optional force_conflicts flag
     */
    public function applyRandomFill(Request $request)
    {
        try {
            $validated = $request->validate([
                'classroom_id' => 'required|exists:classrooms,id',
                'assignments' => 'required|array',
                'assignments.*.day' => 'required|integer|min:1|max:7',
                'assignments.*.period' => 'required|integer|min:1|max:12',
                'assignments.*.cst_id' => 'required|exists:classroom_subject_teachers,id',
                'force_conflicts' => 'boolean',
                'school_id' => 'nullable|exists:schools,id'
            ]);

            $classroomId = $validated['classroom_id'];
            $assignments = $validated['assignments'];
            $forceConflicts = $validated['force_conflicts'] ?? false;
            $schoolId = $validated['school_id'] ?? auth()->user()->schoolId();

            $created = [];
            $updated = [];
            $skipped = [];

            foreach ($assignments as $assignment) {
                $day = $assignment['day'];
                $period = $assignment['period'];
                $cstId = $assignment['cst_id'];

                // Check if slot already exists
                $existingSchedule = Schedule::where('school_id', $schoolId)
                    ->where('day_number', $day)
                    ->where('period_number', $period)
                    ->whereHas('cst', function($q) use ($classroomId) {
                        $q->where('classroom_id', $classroomId);
                    })
                    ->first();

                // Check for teacher conflicts if not forcing
                if (!$forceConflicts) {
                    $cst = ClassroomSubjectTeacher::find($cstId);
                    $teacherConflict = Schedule::where('school_id', $schoolId)
                        ->where('day_number', $day)
                        ->where('period_number', $period)
                        ->where('id', '!=', $existingSchedule?->id ?? 0)
                        ->whereHas('cst', function($q) use ($cst) {
                            $q->where('teacher_id', $cst->teacher_id);
                        })
                        ->exists();

                    if ($teacherConflict) {
                        $skipped[] = [
                            'day' => $day,
                            'period' => $period,
                            'reason' => 'Teacher conflict detected and force_conflicts is false'
                        ];
                        continue;
                    }
                }

                if ($existingSchedule) {
                    // Update existing schedule
                    $existingSchedule->update([
                        'cst_id' => $cstId,
                        // 'active' => true (removed)
                    ]);
                    $updated[] = [
                        'day' => $day,
                        'period' => $period,
                        'schedule_id' => $existingSchedule->id
                    ];
                } else {
                    // Create new schedule
                    $schedule = Schedule::create([
                        'school_id' => $schoolId,
                        'cst_id' => $cstId,
                        'day_number' => $day,
                        'period_number' => $period,
                        // 'active' => true (removed)
                    ]);
                    $created[] = [
                        'day' => $day,
                        'period' => $period,
                        'schedule_id' => $schedule->id
                    ];
                }
            }

            $message = 'Random fill completed successfully';
            if (count($skipped) > 0) {
                $message .= ' (some assignments skipped due to conflicts)';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'summary' => [
                    'created' => count($created),
                    'updated' => count($updated),
                    'skipped' => count($skipped),
                    'total' => count($assignments)
                ],
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply random fill: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Save current live schedule as a draft
     */
    public function saveDraft(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:50',
                'overwrite' => 'boolean'
            ]);
            
            $draftName = $validated['name'];
            $schoolId = auth()->user()->schoolId();

            \DB::transaction(function () use ($schoolId, $draftName) {
                // Get all CSTs for the school
                $csts = ClassroomSubjectTeacher::where('school_id', $schoolId)->get();
                
                // Get all current schedules grouped by cst_id
                $schedules = Schedule::where('school_id', $schoolId)
                    ->get()
                    ->groupBy('cst_id');

                foreach ($csts as $cst) {
                    $cstSchedules = $schedules->get($cst->id);
                    
                    $draftData = [];
                    if ($cstSchedules) {
                        foreach ($cstSchedules as $sch) {
                            $draftData[] = [
                                'day_number' => $sch->day_number,
                                'period_number' => $sch->period_number,
                                // Add other fields if necessary
                            ];
                        }
                    }

                    // Update drafts JSON column
                    $drafts = $cst->drafts ?? [];
                    $drafts[$draftName] = [
                        'timestamp' => now()->toIso8601String(),
                        'entries' => $draftData
                    ];
                    
                    $cst->drafts = $drafts;
                    $cst->save();
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Schedule saved as draft '{$draftName}'"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load a draft to the live schedule (replaces current)
     */
    public function loadDraft(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string'
            ]);
            
            $draftName = $validated['name'];
            $schoolId = auth()->user()->schoolId();

            \DB::transaction(function () use ($schoolId, $draftName) {
                // 1. Clear current live schedule
                Schedule::where('school_id', $schoolId)->delete();

                // 2. Load CSTs and apply draft
                $csts = ClassroomSubjectTeacher::where('school_id', $schoolId)->get();
                
                foreach ($csts as $cst) {
                    $drafts = $cst->drafts;
                    if (isset($drafts[$draftName]) && isset($drafts[$draftName]['entries'])) {
                        foreach ($drafts[$draftName]['entries'] as $entry) {
                            Schedule::create([
                                'cst_id' => $cst->id,
                                'school_id' => $schoolId,
                                'day_number' => $entry['day_number'],
                                'period_number' => $entry['period_number'],
                                // 'active' is implied true by existence in table
                            ]);
                        }
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Draft '{$draftName}' loaded successfully"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load draft: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of available drafts
     */
    public function getDrafts()
    {
        try {
            $schoolId = auth()->user()->schoolId();
            
            // Heuristic: Check the first few CSTs to find common draft names
            // Since drafts are saved globally, any Cst *should* have the keys
            $someCst = ClassroomSubjectTeacher::where('school_id', $schoolId)
                ->whereNotNull('drafts')
                ->first();

            $draftNames = [];
            if ($someCst && $someCst->drafts) {
                $draftNames = array_keys($someCst->drafts);
            }

            return response()->json([
                'success' => true,
                'drafts' => $draftNames
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve drafts'
            ], 500);
        }
    }
}

