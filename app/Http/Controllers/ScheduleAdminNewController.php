<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\School;
use App\Models\Teacher;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\ClassroomSubjectTeacher;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;


class ScheduleAdminNewController extends Controller
{




    public function getScheduleData(Request $request )
    {
        return self::schedule_new_data() ;

        // 'schedule_new_data' => self::schedule_new_data()['teachers_data'],

    }
    /**
     * Update the period_code for a specific schedule entry.
     *
     * @param Request $request
     * @param Schedule $schedule The Schedule model instance (via route model binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePeriodCode(Request $request, Schedule $schedule)
    {
        // Validate the incoming request
        $validated = $request->validate([
            // Allow null or a string matching the 'd{day}p{period}' format
            'period_code' => ['nullable', 'string', 'regex:/^d[1-5]p[1-8]$/i'], // Added 'i' for case-insensitive
        ]);

        // Use a database transaction to ensure atomicity
        DB::beginTransaction();
        try {
            // --- Conflict Check ---
            $newPeriodCode = $validated['period_code'];
            if ($newPeriodCode !== null) {
                // Find if a conflict exists
                $conflictingSchedule = $this->findTeacherPeriodConflict($schedule, $newPeriodCode);

                if ($conflictingSchedule) {
                    // Conflict found: Clear the period_code of the conflicting schedule
                    Log::info('Clearing conflicting schedule period.', ['conflicting_schedule_id' => $conflictingSchedule->id, 'target_schedule_id' => $schedule->id]);
                    $conflictingSchedule->update(['period_code' => null]);
                }
            }
            // --- End Conflict Check ---


            // Update the current schedule record with the new period_code
            Log::info('Updating target schedule period.', ['schedule_id' => $schedule->id, 'new_period_code' => $newPeriodCode]);
            $updated = $schedule->update([
                'period_code' => $validated['period_code']
            ]);

            // Commit the transaction if all updates were successful
            DB::commit();

            // Return a success response
            return response()->json([
                'message' => 'Schedule period updated successfully!',
                'record' => $schedule->fresh() // Return the updated record with potentially eager-loaded relations
            ]);

        } catch (\Exception $e) {
            // Log the error and return an error response
            DB::rollBack(); // Rollback the transaction on error
            Log::error('Error updating schedule period code: ' . $e->getMessage(), ['schedule_id' => $schedule->id, 'request_data' => $request->all(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to update schedule period.'], 500);
        }
    }

    /**
     * Find a conflicting schedule entry for the same teacher at the given period code within the active schedules.
     *
     * @param Schedule $schedule The schedule being updated.
     * @param string|null $newPeriodCode The target period code.
     * @return Schedule|null The conflicting Schedule model instance, or null if no conflict.
     */
    private function findTeacherPeriodConflict(Schedule $schedule, ?string $newPeriodCode): ?Schedule
    {
        // No conflict if the new period code is null (clearing the slot)
        if ($newPeriodCode === null) {
            return null;
        }

        // Ensure the cst relationship is loaded
        $schedule->loadMissing('cst');

        // Check if cst relationship exists and has a teacher_id
        if (!$schedule->cst || !$schedule->cst->teacher_id) {
            Log::warning('Schedule missing CST or teacher_id during conflict check.', ['schedule_id' => $schedule->id]);
            return null;
        }

        $teacherId = $schedule->cst->teacher_id;
        
        // Query for conflicting schedules in ACTIVE set
        $conflictingSchedule = Schedule::where('active', true) 
            ->where('id', '!=', $schedule->id) // Exclude the current schedule
            ->where('period_code', $newPeriodCode)
            ->whereHas('cst', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->first();

        return $conflictingSchedule;
    }


    /**
     * Assign random background and contrasting text colors to ClassroomSubjectTeacher records.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_rand_color(Request $request)
    {
        $request->validate([
            'school_id' => 'required|integer|exists:schools,id',
        ]);

        $schoolId = $request->input('school_id');
        try {
            // Fetch all CSTs for the school
            // Use chunking for potentially large datasets
            DB::beginTransaction();

            ClassroomSubjectTeacher::where('school_id', $schoolId)
                ->chunkById(200, function ($csts) {
                    foreach ($csts as $cst) {


                        // Generate random RGB
                        $r = mt_rand(0, 255);
                        $g = mt_rand(0, 255);
                        $b = mt_rand(0, 255);
                        $bgColor = sprintf('#%02x%02x%02x', $r, $g, $b);

                        // Calculate luminance (Y = 0.299*R + 0.587*G + 0.114*B)
                        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
                        // Determine text color based on luminance threshold (0.5 is common)
                        $textColor = $luminance > 0.5 ? '#000000' : '#FFFFFF'; // Black for light bg, White for dark bg

                        // Update the 'data' JSON column
                        // $data = $cst->data ?? []; // Get existing data or initialize empty array
                        // if (is_string($data)) { // Handle case where data might be a JSON string from DB
                        //     $data = json_decode($data, true) ?? [];
                        // }
                        // $data['c_bg'] = $bgColor;
                        // $data['c_text'] = $textColor;

                        // Save the updated CST using update for mass-assignment protection & efficiency
                        $cst->update([
                            'c_bg' => $bgColor,
                            'c_text' => $textColor,
                        ]);
                    }
                });

            DB::commit();
            return response()->json(['message' => 'Random colors assigned successfully to CST records.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning random colors: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while assigning colors.'], 500);
        }
    }

    public function indexoldooo(Request $request)
    {
        return Inertia::render('my_class/admin/Schedules/new/Schedule_admin_new', [
            'schedule_new_data' => self::schedule_new_data()['teachers_data'],
            'overall_conflict_counter' => self::schedule_new_data()['overall_conflict_counter'],
        ]);
    }
    public function indexooo (Request $request)
    {
        $query = Schedule::with(['school', 'grade', 'cst.classroom', 'cst.subject', 'cst.teacher'])
            ->when($request->search, function($q, $v) {
                $q->where('name', 'like', "%{$v}%")
                  ->orWhereHas('school', fn($q) => $q->where('name', 'like', "%{$v}%"))
                  ->orWhereHas('grade', fn($q) => $q->where('name', 'like', "%{$v}%"));
            })
            ->when($request->school_id, fn($q, $v) => $q->where('school_id', $v))
            ->when($request->grade_id, fn($q, $v) => $q->where('grade_id', $v));

        return Inertia::render('my_class/admin/Schedules/new/Index', [
            'records' => $query->paginate(10),
            'filters' => $request->only(['search', 'school_id', 'grade_id']),
            'schools' => School::select('id', 'name')->get(),
            'grades' => Grade::select('id', 'name')->get(),
            'classrooms' => Classroom::select('id', 'name')->get(),
            'subjects' => Subject::select('id', 'name')->get(),
            'teachers' => Teacher::select('id', 'name')->get()
        ]);
    }
    public function index (Request $request)
    {

        $query = Schedule::with(['school', 'grade', 'cst.classroom', 'cst.subject', 'cst.teacher'])
            ->when($request->search, function($q, $v) {
                $q->where('name', 'like', "%{$v}%")
                  ->orWhereHas('school', fn($q) => $q->where('name', 'like', "%{$v}%"))
                  ->orWhereHas('grade', fn($q) => $q->where('name', 'like', "%{$v}%"));
            })
            ->when($request->school_id, fn($q, $v) => $q->where('school_id', $v))
            ->when($request->grade_id, fn($q, $v) => $q->where('grade_id', $v));


        return Inertia::render('my_class/admin/Schedules/new/Index', [
            'records' => $query->paginate(10),
            'filters' => $request->all(['search', 'school_id', 'grade_id']),
            'schools' => School::select('id', 'name')->get(),
            'grades' => Grade::select('id', 'name')->get(),
            'classrooms' => Classroom::select('id', 'name')->get(),
            'subjects' => Subject::select('id', 'name')->get(),
            'teachers' => Teacher::select('id', 'name')->get(),
            'schedule_new_data' => self::schedule_new_data()['teachers_data'],
            'overall_conflict_counter' => self::schedule_new_data()['overall_conflict_counter'],


        ]);
    }



    static function schedule_new_data()
    {
        // Get teachers with their relationships, filtering schedules by active status
        $teachers = Teacher::with([
            'classroomSubjectTeachers.subject',
            'classroomSubjectTeachers.classroom',
            'classroomSubjectTeachers' => function($query) {
                // Filter schedules by active=true instead of copy_id
                 $query->with(['schedules' => function($query) {
                    // $query->where('copy_id', $activeCopyId); // Removed
                    $query->where('active', true);
                 }]);
            }
        ])->get();

        // Track overall conflict count
        $overallConflictCounter = 0;

        $data = $teachers->map(function ($teacher) use (&$overallConflictCounter) {
            // Generate teacher_cute: first 2 letters of each word
            $nameParts = explode(' ', $teacher->name);
            $cuteName = '';
            foreach ($nameParts as $part) {
                if (!empty($cuteName)) {
                    $cuteName .= ' '; // Add space before appending next part
                }
                $cuteName .= mb_substr($part, 0, 2); // Append first 2 letters
            }

            $row = ['teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'teacher_cute' => $cuteName,
                'show' => true
            ],
                'period_code_empty' => [], // Initialize empty array
                'classroom_conflicts_count' => 0 // Initialize conflict counter for this teacher
            ];

            foreach (range(1, 5) as $day) {
                foreach (range(1, 8) as $period) {
                    $row["d{$day}p{$period}"] = '';
                }
            }

            // --- Efficiently load schedules for all CSTs of this teacher ---
            $cstIds = $teacher->classroomSubjectTeachers->pluck('id')->all();
            $schedulesForTeacher = Schedule::whereIn('cst_id', $cstIds)->where('active', true)->get();
            // --- End of efficient loading ---

            foreach ($teacher->classroomSubjectTeachers as $cst) {
                // Get the schedule(s) for this specific CST
                $schedules = Schedule::where('cst_id', $cst->id)->where('active', true)->get();

                // Prepare data common to this CST
                $subject = $cst->subject->name ?? '';
                $classroom = $cst->classroom->name ?? '';
                $cstData = is_array($cst->data) ? $cst->data : json_decode($cst->data, true);
                $c_text = $cst['c_text'] ?? '#FFFFFF';
                $c_bg = $cst['c_bg'] ?? 'white';

                // Loop through each schedule entry found for this CST
                foreach ($schedules as $schedule) {
                    $key = $schedule->period_code;

                    // Get classroom conflicts
                    $classroomsConflicted = self::classrooms_conflicted_fun($cst->classroom->id, $schedule->period_code);
                    
                    // Update conflict counters
                    $conflictCount = count($classroomsConflicted);
                    if ($conflictCount > 1) {
                        // Only count conflicts once per classroom
                        $row['classroom_conflicts_count'] += 1;  
                        $overallConflictCounter += 1;  
                    }


                    $my_data = [
                        'schedule_id' => $schedule->id,
                        'cst_id' => $cst->id,
                        'period_code' => $schedule->period_code,
                        'subject' => $subject,
                        'data' => $cstData,
                        'classroom' => $classroom,
                        // 'schedules_activeCopyCount' => $schedules_activeCopyCount,
                        'classrooms_conflicted' => $classroomsConflicted,
                        'conflict_count_data' =>  [$cst->classroom->id, $schedule , $schedule->period_code, 'active' ], 
                        'conflict_count' => $conflictCount-1 , 
                        'c_text' => $c_text,
                        'c_bg' => $c_bg,
                        'subject_cute' => mb_strimwidth($subject, 0, 2),
                        'teacher_cute' => $cuteName,
                    ];

                    if (!empty($key)) {
                        $row[$key] = (object) $my_data;
                    } else {
                        $row['period_code_empty'][] = (object) $my_data;
                    }
                }
            }

            return $row;
        });

        return [
            'teachers_data' => $data,
            'overall_conflict_counter' => $overallConflictCounter
        ];
    }

 /**
 * Find schedules that conflict with a given classroom at the specified period code.
 */
 static function schedules_activeCopy_fun($classroomId, $periodCode, $ignoreConfig = null)
{
    // Debug parameters
     Log::info('schedules_activeCopy_fun called with:', [
        'classroomId' => $classroomId,
        'periodCode' => $periodCode
    ]);

    // No results if parameters are invalid
    if ($periodCode === null || empty($periodCode)) {
         Log::info('schedules_activeCopy_fun: periodCode is null or empty');
        return [];
    }

    // Get all schedules for the same period code and active status
    $query = Schedule::where('period_code', $periodCode)
        ->where('active', true) // Filter by active instead of copy_id
        ->whereHas('cst', function($query) use ($classroomId) {
            $query->where('classroom_id', $classroomId);
        });

    $results = $query->with(['cst','cst.classroom', 'cst.teacher', 'cst.subject'])->get();
    return $results;
}

static function classrooms_conflicted_fun($classroomId, $periodCode, $ignoreConfig = null)
{
    // No conflicts if period code is null or empty
    if ($periodCode === null || empty($periodCode)) {
        return [];
    }

    $conflicts = self::schedules_activeCopy_fun($classroomId, $periodCode);


    // Map conflicts to a simplified data structure
    return  collect($conflicts)->map(function ($schedule) {
        return [
            'schedule_id' => $schedule->id,
            'classroom_id' => $schedule->cst->classroom_id,
            'classroom' => $schedule->cst->classroom->name ?? 'Unknown',
            'teacher_id' => $schedule->cst->teacher_id,
            'teacher' => $schedule->cst->teacher->name ?? 'Unknown',
            'subject' => $schedule->cst->subject->name ?? 'Unknown',
            'subject_id' => $schedule->cst->subject_id ?? null,
            'period_code' => $schedule->period_code
        ];
    })->values()->all();
}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'grade_id' => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day' => 'required|integer|between:1,7',
            'period' => 'required|integer|min:1',
            'place' => 'nullable|string|max:120',
            'color_custom' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $schedule = Schedule::create($validated);

        return response()->json([
            'message' => 'Schedule created successfully',
            'record' => $schedule->load(['school', 'grade', 'classroom', 'subject', 'teacher'])
        ]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'grade_id' => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day' => 'required|integer|between:1,7',
            'period' => 'required|integer|min:1',
            'place' => 'nullable|string|max:120',
            'color_custom' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $schedule->update($validated);

        return response()->json([
            'message' => 'Schedule updated successfully',
            'record' => $schedule->fresh(['school', 'grade', 'classroom', 'subject', 'teacher'])
        ]);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->back()->with('success', 'Schedule deleted successfully.');
    }
}
