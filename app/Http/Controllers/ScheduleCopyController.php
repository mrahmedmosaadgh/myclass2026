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
            ->get(['id', 'name', 'description', 'active']);
    }

    /**
     * Create schedule entries for a schedule copy
     *
     * @param ScheduleCopy $scheduleCopy
     * @param array $validated
     * @throws \Exception
     * @return void
     */
    public function createScheduleEntries(Request $request)
    // public function createScheduleEntries(ScheduleCopy $scheduleCopy, array $validated)
    {
        // return $request->all();

       $schedule_copy_id= $request->schedule_copy_id;
       $school_id= $request->school_id;

        $academicYearId = AcademicYear::where('school_id', $school_id)
            ->where('active', 1)
            ->value('id');

        if (!$academicYearId) {
            throw new \Exception("No active academic year found for this school.");
        }

        // Get all classroom subject teachers for this school
        $csts = ClassroomSubjectTeacher::where('school_id', $school_id)
            ->where('academic_year_id', $academicYearId)
            ->get();

        if ($csts->isEmpty()) {
            throw new \Exception("No classroom subject teachers found for this school and academic year.");
        }

        // Create schedule entries for each CST
        foreach ($csts as $cst) {
            // Validate classes_per_week
            if (!isset($cst->classes_per_week) || !is_numeric($cst->classes_per_week) || $cst->classes_per_week < 1) {
                throw new \Exception("Invalid classes_per_week value for classroom: {$cst->classroom->name}, subject: {$cst->subject->name}, teacher: {$cst->teacher->name}");
            }

            // Create multiple entries based on classes_per_week
            for ($i = 1; $i <= $cst->classes_per_week; $i++) {
                $scheduleData = [
                    'copy_id' => $schedule_copy_id,
                    'cst_id' => $cst->id,
                    'school_id' => $cst->school_id,
                    // 'week' => $validated['week_number'],
                    // 'semester' => $validated['semester_id'],
                    'period_order' => $i,
                    'active' => true,
                    'day' => null,  // Will be set later when scheduling
                    'period_detail_id' => null  // Will be set later when scheduling
                ];

                // Check for existing record
                $existingSchedule = Schedule::where([
                    'copy_id' => $schedule_copy_id,
                    'cst_id' => $cst->id,
                    'period_order' => $i,
                ])->first();

                if ($existingSchedule) {
                    $existingSchedule->update($scheduleData);
                } else {
                    Schedule::create($scheduleData);
                }
            }
        }
    }

    public function index()
    {
        $records = ScheduleCopy::with(['school', 'academicYear', 'semester', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(40);

        return Inertia::render('my_class/admin/ScheduleCopies/Index', [
            'records' => $records,
            'options' => [
                'schools' => School::select('id', 'name')->get(),
                'academicYears' => AcademicYear::select('id', 'name')->get(),
                'semesters' => Semester::select('id', 'name')->get(),
                'statuses' => [
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'archived', 'label' => 'Archived'],
                ]
            ]
        ]);
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
        // Update status and active fields
        $scheduleCopy->update([
            'status' => 'archived',
            'active' => false,
            'last_modified_by' => auth()->id()
        ]);

        // Soft delete the record
        $scheduleCopy->delete();

        return response()->json([
            'message' => 'Schedule copy deleted successfully',
            'status' => 'success'
        ]);
    }
}
