<?php

namespace App\Http\Controllers;

use App\Models\ScheduleCopy;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\ClassroomSubjectTeacher;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleCopyController extends Controller
{
    public function bySchool(School $school)
    {

//   return $school;
        $academicYearId = AcademicYear::where('school_id', $school->id)
            ->where('active', 1)
            ->value('id');

        if (!$academicYearId) {
            throw new \Exception("No active academic year found for this school.");
        }
        //  return ScheduleCopy::all();
        return ScheduleCopy::where('school_id', $school->id)->
        where('academic_year_id', $academicYearId)
            // ->orderBy('active', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get(['id', 'name', 'description', 'status']);
    }

    /**
     * Create schedule entries for a schedule copy
     *
     * @param ScheduleCopy $scheduleCopy
     * @param array $validated
     * @throws \Exception
     * @return void
     */
    public function createScheduleEntries(ScheduleCopy $scheduleCopy, array $validated)
    {
       $schedule_copy_id = $scheduleCopy->id;
       $school_id = $scheduleCopy->school_id;
       $academicYearId = $scheduleCopy->academic_year_id;

        if (!$academicYearId) {
            Log::warning("No academic year found for schedule copy {$scheduleCopy->id}");
            return;
        }

        // Get all classroom subject teachers for this school
        $csts = ClassroomSubjectTeacher::where('school_id', $school_id)
            ->where('academic_year_id', $academicYearId)
            ->get();

        if ($csts->isEmpty()) {
            Log::info("No classroom subject teachers found for school {$school_id} and academic year {$academicYearId}");
            return;
        }

        // Create schedule entries for each CST
        foreach ($csts as $cst) {
            // Validate classes_per_week
            if (!isset($cst->classes_per_week) || !is_numeric($cst->classes_per_week) || $cst->classes_per_week < 1) {
                throw new \Exception("Invalid classes_per_week value for classroom: {$cst->classroom->name}, subject: {$cst->subject->name}, teacher: {$cst->teacher->name}");
            }

            // Create entries with auto-numbered period_order (1, 2, 3... up to classes_per_week)
            for ($i = 1; $i <= $cst->classes_per_week; $i++) {
                Schedule::create([
                    'copy_id' => $schedule_copy_id,
                    'cst_id' => $cst->id,
                    'school_id' => $cst->school_id,
                    'period_order' => $i,  // Auto-numbered: 1, 2, 3...
                    'active' => true,
                    'day_number' => null,  // Will be set later when scheduling
                    'period_number' => null  // Will be set later when scheduling
                ]);
            }
        }
    }

    public function index(Request $request)
    {
        $schoolId = auth()->user()->schoolId();

        $query = ScheduleCopy::with(['school', 'academicYear', 'semester', 'createdBy']);

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->orderBy('created_at', 'desc')
            ->paginate(40);

        return response()->json($records);
    }

    // New API method to get schedule copies for a specific school
    public function apiIndex(Request $request)
    {
        $schoolId = $request->query('school_id');
        
        $query = ScheduleCopy::select('id', 'name', 'description', 'status', 'created_at');

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        } else {
            // If no school_id provided, try to get the authenticated user's school
            $userSchoolId = auth()->user()->schoolId();
            if ($userSchoolId) {
                $query->where('school_id', $userSchoolId);
            }
        }

        $scheduleCopies = $query->orderBy('created_at', 'DESC')->get();

        return response()->json($scheduleCopies);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request
            $validated = $request->validate([
                'school_id' => 'required|exists:schools,id',
                'name' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('schedule_copies')->where(function ($query) use ($request) {
                        return $query->where('school_id', $request->school_id);
                    })
                ],
                'description' => 'nullable|string',
                'active' => 'boolean',
                'copy_date' => 'nullable|date',
                'academic_year_id' => 'required|exists:academic_years,id',
                'semester_id' => 'nullable|exists:semesters,id',
                'week_number' => 'nullable|integer|between:1,52',
                'status' => 'required|in:draft,pending,active,archived',
                'metadata' => 'nullable|json',
                'notes' => 'nullable|string'
            ]);

            // Ensure user can only create for their own school
            $schoolId = auth()->user()->schoolId();
            if ($schoolId && $validated['school_id'] != $schoolId) {
                 return response()->json([
                    'message' => 'You do not have permission to create schedule copies for this school.',
                    'status' => 'error'
                ], 403);
            }

            $validated['created_by'] = auth()->id();
            $validated['last_modified_by'] = auth()->id();

            // Create the schedule copy
            $scheduleCopy = ScheduleCopy::create($validated);

            // Create related schedule entries
            $this->createScheduleEntries($scheduleCopy, $validated);

            DB::commit();

            return response()->json([
                'message' => 'Schedule copy and related schedules created successfully',
                'record' => $scheduleCopy->load([
                    'school',
                    'academicYear',
                    'semester',
                    'createdBy:id,name',
                    'lastModifiedBy:id,name'
                ]),
                'status' => 'success'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Schedule Copy Creation Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'Failed to create schedule copy: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function update(Request $request, ScheduleCopy $scheduleCopy)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'school_id' => 'required|exists:schools,id',
                'name' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('schedule_copies')
                        ->where(function ($query) use ($request) {
                            return $query->where('school_id', $request->school_id);
                        })
                        ->ignore($scheduleCopy->id)
                ],
                'description' => 'nullable|string',
                'active' => 'boolean',
                'copy_date' => 'nullable|date',
                'academic_year_id' => 'required|exists:academic_years,id',
                'semester_id' => 'nullable|exists:semesters,id',
                'week_number' => 'nullable|integer|between:1,52',
                'status' => 'required|in:draft,pending,active,archived',
                'metadata' => 'nullable|json',
                'notes' => 'nullable|string'
            ]);

            // Add last_modified_by field for updates
            $validated['last_modified_by'] = auth()->id();

            // Update the schedule copy
            $scheduleCopy->update($validated);

            // Delete existing schedule entries
            Schedule::where('copy_id', $scheduleCopy->id)->delete();

            // Create new schedule entries
            $this->createScheduleEntries($scheduleCopy, $validated);

            DB::commit();

            return response()->json([
                'message' => 'Schedule copy updated successfully',
                'record' => $scheduleCopy->load([
                    'school',
                    'academicYear',
                    'semester',
                    'createdBy:id,name',
                    'lastModifiedBy:id,name'
                ]),
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Schedule Copy Update Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'Failed to update schedule copy: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function destroy(ScheduleCopy $scheduleCopy)
    {
        // Start a database transaction
        DB::beginTransaction();
        
        try {
            // Archive all related schedules by deleting them (they have cascade deletion)
            $scheduleCopy->schedules()->delete();
            
            // Archive all related weekly plans by deleting them
            $scheduleCopy->weeklyPlans()->delete();
            
            // Archive all related schedule dailies by deleting them
            $scheduleCopy->scheduleDailies()->delete();
            
            // Update status to archived
            $scheduleCopy->update([
                'status' => 'archived',
                'last_modified_by' => auth()->id()
            ]);

            // Soft delete the record itself
            $scheduleCopy->delete();

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Schedule copy and all related data archived successfully',
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollback();
            
            Log::error('Error archiving schedule copy: ' . $e->getMessage(), [
                'schedule_copy_id' => $scheduleCopy->id,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'message' => 'Error archiving schedule copy: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Get sync status for a schedule copy
     * Compares actual schedule counts vs expected (classes_per_week) for each CST
     */
    public function getSyncStatus(Request $request, $copyId)
    {
        try {
            $scheduleCopy = ScheduleCopy::with(['school', 'academicYear'])->findOrFail($copyId);

            // Get all CSTs for this school and academic year
            $csts = ClassroomSubjectTeacher::where('school_id', $scheduleCopy->school_id)
                ->where('academic_year_id', $scheduleCopy->academic_year_id)
                ->with(['classroom', 'subject', 'teacher'])
                ->get();

            // Get all schedules for this copy
            $schedules = Schedule::where('copy_id', $copyId)->get();

            // Build breakdown per CST
            $breakdown = [];
            $summary = [
                'total_csts' => $csts->count(),
                'ok' => 0,
                'missing' => 0,
                'extra' => 0,
                'total_expected' => 0,
                'total_actual' => 0
            ];

            // Group by classroom for better UI display
            $byClassroom = [];

            foreach ($csts as $cst) {
                $expected = $cst->classes_per_week ?? 0;
                $actual = $schedules->where('cst_id', $cst->id)->count();
                $diff = $actual - $expected;
                
                $status = 'ok';
                if ($diff < 0) {
                    $status = 'missing';
                    $summary['missing']++;
                } elseif ($diff > 0) {
                    $status = 'extra';
                    $summary['extra']++;
                } else {
                    $summary['ok']++;
                }

                $summary['total_expected'] += $expected;
                $summary['total_actual'] += $actual;

                $classroomId = $cst->classroom_id;
                $classroomName = $cst->classroom->name ?? 'Unknown';

                if (!isset($byClassroom[$classroomId])) {
                    $byClassroom[$classroomId] = [
                        'classroom_id' => $classroomId,
                        'classroom_name' => $classroomName,
                        'items' => []
                    ];
                }

                $byClassroom[$classroomId]['items'][] = [
                    'cst_id' => $cst->id,
                    'subject_name' => $cst->subject->name ?? 'Unknown',
                    'teacher_name' => $cst->teacher->name ?? 'Unknown',
                    'expected' => $expected,
                    'actual' => $actual,
                    'diff' => $diff,
                    'status' => $status
                ];
            }

            // Sort classrooms by name
            usort($byClassroom, function($a, $b) {
                return strcmp($a['classroom_name'], $b['classroom_name']);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'copy_id' => $copyId,
                    'copy_name' => $scheduleCopy->name,
                    'school_name' => $scheduleCopy->school->name ?? 'Unknown',
                    'summary' => $summary,
                    'by_classroom' => array_values($byClassroom)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get sync status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply sync fixes - create missing or delete extra schedule records
     */
    public function applySyncFixes(Request $request, $copyId)
    {
        try {
            $validated = $request->validate([
                'actions' => 'required|array',
                'actions.*.cst_id' => 'required|exists:classroom_subject_teachers,id',
                'actions.*.action' => 'required|in:create_missing,delete_extra'
            ]);

            $scheduleCopy = ScheduleCopy::findOrFail($copyId);

            $results = [
                'created' => 0,
                'deleted' => 0,
                'errors' => []
            ];

            DB::beginTransaction();

            foreach ($validated['actions'] as $actionData) {
                $cstId = $actionData['cst_id'];
                $action = $actionData['action'];

                $cst = ClassroomSubjectTeacher::find($cstId);
                if (!$cst) {
                    $results['errors'][] = "CST ID {$cstId} not found";
                    continue;
                }

                $expected = $cst->classes_per_week ?? 0;
                $currentSchedules = Schedule::where('copy_id', $copyId)
                    ->where('cst_id', $cstId)
                    ->orderBy('period_order')
                    ->get();
                $actual = $currentSchedules->count();

                if ($action === 'create_missing' && $actual < $expected) {
                    // Find the highest period_order currently used
                    $maxOrder = $currentSchedules->max('period_order') ?? 0;
                    
                    // Create missing records
                    for ($i = $actual + 1; $i <= $expected; $i++) {
                        $maxOrder++;
                        Schedule::create([
                            'copy_id' => $copyId,
                            'cst_id' => $cstId,
                            'school_id' => $scheduleCopy->school_id,
                            'period_order' => $maxOrder,
                            'active' => true,
                            'day_number' => null,
                            'period_number' => null
                        ]);
                        $results['created']++;
                    }
                } elseif ($action === 'delete_extra' && $actual > $expected) {
                    // Delete extra records (those without day/period assignment first, then by highest period_order)
                    $toDelete = $actual - $expected;
                    
                    // Prioritize deleting unassigned schedules
                    $unassigned = $currentSchedules->filter(function($s) {
                        return is_null($s->day_number) || is_null($s->period_number);
                    })->sortByDesc('period_order');

                    $assigned = $currentSchedules->filter(function($s) {
                        return !is_null($s->day_number) && !is_null($s->period_number);
                    })->sortByDesc('period_order');

                    $deleteList = $unassigned->take($toDelete);
                    if ($deleteList->count() < $toDelete) {
                        // Need to delete some assigned ones too
                        $remaining = $toDelete - $deleteList->count();
                        $deleteList = $deleteList->merge($assigned->take($remaining));
                    }

                    foreach ($deleteList as $schedule) {
                        $schedule->delete();
                        $results['deleted']++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Sync completed: {$results['created']} created, {$results['deleted']} deleted",
                'results' => $results
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply sync fixes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get CST (Classroom-Subject-Teacher) overview statistics
     * Shows per-classroom count and total expected schedule entries
     */
    public function getCSTOverview(Request $request)
    {
        try {
            $validated = $request->validate([
                'school_id' => 'required|exists:schools,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'include_deleted' => 'boolean'
            ]);

            $schoolId = $validated['school_id'];
            $academicYearId = $validated['academic_year_id'];
            $includeDeleted = $validated['include_deleted'] ?? false;

            // Get all CSTs for this school and academic year
            $query = ClassroomSubjectTeacher::where('school_id', $schoolId)
                ->where('academic_year_id', $academicYearId)
                ->with(['classroom', 'subject', 'teacher']);

            if ($includeDeleted) {
                $query->withTrashed();
            }

            $csts = $query->get();

            if ($csts->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No subject-teacher assignments found',
                    'data' => [
                        'by_classroom' => [],
                        'summary' => [
                            'total_classrooms' => 0,
                            'total_csts' => 0,
                            'total_deleted_csts' => 0,
                            'total_expected_schedules' => 0
                        ]
                    ]
                ]);
            }

            // Group by classroom
            $byClassroom = [];
            $totalExpectedSchedules = 0;
            $totalDeletedCsts = 0;

            foreach ($csts as $cst) {
                $classroomId = $cst->classroom_id;
                $classroomName = $cst->classroom->name ?? 'Unknown';
                $classesPerWeek = $cst->classes_per_week ?? 0;
                $isDeleted = !is_null($cst->deleted_at);

                if ($isDeleted) {
                    $totalDeletedCsts++;
                }

                if (!isset($byClassroom[$classroomId])) {
                    $byClassroom[$classroomId] = [
                        'classroom_id' => $classroomId,
                        'classroom_name' => $classroomName,
                        'cst_count' => 0,
                        'cst_deleted_count' => 0,
                        'total_classes_per_week' => 0,
                        'subjects' => []
                    ];
                }

                if (!$isDeleted) {
                    $byClassroom[$classroomId]['cst_count']++;
                    $byClassroom[$classroomId]['total_classes_per_week'] += $classesPerWeek;
                    $totalExpectedSchedules += $classesPerWeek;
                } else {
                    $byClassroom[$classroomId]['cst_deleted_count']++;
                }

                $byClassroom[$classroomId]['subjects'][] = [
                    'cst_id' => $cst->id,
                    'subject_name' => $cst->subject->name ?? 'Unknown',
                    'teacher_name' => $cst->teacher->name ?? 'Unknown',
                    'classes_per_week' => $classesPerWeek,
                    'deleted_at' => $cst->deleted_at ? $cst->deleted_at->toDateTimeString() : null,
                    'is_deleted' => $isDeleted
                ];
            }

            // Sort classrooms by name
            usort($byClassroom, function($a, $b) {
                return strcmp($a['classroom_name'], $b['classroom_name']);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'by_classroom' => array_values($byClassroom),
                    'summary' => [
                        'total_classrooms' => count($byClassroom),
                        'total_csts' => $csts->filter(fn($c) => is_null($c->deleted_at))->count(),
                        'total_deleted_csts' => $totalDeletedCsts,
                        'total_expected_schedules' => $totalExpectedSchedules
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get CST overview: ' . $e->getMessage()
            ], 500);
        }
    }
}