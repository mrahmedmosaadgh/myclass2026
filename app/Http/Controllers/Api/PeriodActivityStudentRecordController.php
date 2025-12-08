<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeriodActivity;
use App\Models\StudentPeriodRecord;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriodActivityStudentRecordController extends Controller
{
    /**
     * Get all student records for a period activity
     */
    public function index(PeriodActivity $periodActivity)
    {
        $records = StudentPeriodRecord::with('student')
            ->where('period_activity_id', $periodActivity->id)
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'student_id' => $record->student_id,
                    'name' => $record->student->name,
                    'attendance_status' => $record->attendance_status,
                    'late_minutes' => $record->late_minutes,
                    'homework_completed' => $record->homework_completed,
                    'homework_score' => $record->homework_score,
                    'behavior_plus_marks' => $record->behavior_plus_marks,
                    'behavior_minus_marks' => $record->behavior_minus_marks,
                    'behavior_notes' => $record->behavior_notes,
                    'participation_score' => $record->participation_score,
                    'participation_notes' => $record->participation_notes
                ];
            });

        return response()->json($records);
    }

    /**
     * Create a new student record
     */
    public function store(Request $request, PeriodActivity $periodActivity)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'attendance_status' => 'required|string|in:present,absent,late,excused',
            'late_minutes' => 'nullable|integer|min:0',
            'homework_completed' => 'boolean',
            'homework_score' => 'nullable|numeric|min:0',
            'behavior_plus_marks' => 'integer|min:0',
            'behavior_minus_marks' => 'integer|min:0',
            'behavior_notes' => 'nullable|string',
            'participation_score' => 'nullable|integer|min:0',
            'participation_notes' => 'nullable|string'
        ]);

        $validated['period_activity_id'] = $periodActivity->id;

        $record = StudentPeriodRecord::create($validated);

        return response()->json($record->load('student'), 201);
    }

    /**
     * Update a specific student record for a period activity
     */
    public function update(Request $request, PeriodActivity $periodActivity, $id)
    {
        $validated = $request->validate([
            'attendance_status' => 'nullable|string|in:present,absent,late,excused',
            'late_minutes' => 'nullable|integer|min:0',
            'homework_completed' => 'boolean',
            'homework_score' => 'nullable|numeric|min:0',
            'behavior_plus_marks' => 'nullable|integer|min:0',
            'behavior_minus_marks' => 'nullable|integer|min:0',
            'behavior_notes' => 'nullable|string',
            'participation_score' => 'nullable|integer|min:0',
            'participation_notes' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $record = StudentPeriodRecord::where('id', $id)
            ->where('period_activity_id', $periodActivity->id)
            ->firstOrFail();

        $record->update($validated);

        return response()->json($record);
    }

    /**
     * Update a student record directly by ID (without period activity context)
     */
    public function updateRecord(Request $request, $id)
    {
        $validated = $request->validate([
            'attendance_status' => 'nullable|string|in:present,absent,late,excused',
            'late_minutes' => 'nullable|integer|min:0',
            'homework_completed' => 'boolean',
            'homework_score' => 'nullable|numeric|min:0',
            'behavior_plus_marks' => 'nullable|integer|min:0',
            'behavior_minus_marks' => 'nullable|integer|min:0',
            'behavior_notes' => 'nullable|string',
            'participation_score' => 'nullable|integer|min:0',
            'participation_notes' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $record = StudentPeriodRecord::findOrFail($id);
        $record->update($validated);

        return response()->json($record);
    }

    /**
     * Batch create student records
     */
    public function batchStore(Request $request, PeriodActivity $periodActivity)
    {
        $request->validate([
            'records' => 'required|array',
            'records.*.student_id' => 'required|exists:students,id',
            'records.*.attendance_status' => 'required|string|in:present,absent,late,excused',
            'records.*.late_minutes' => 'nullable|integer|min:0',
            'records.*.homework_completed' => 'boolean',
            'records.*.homework_score' => 'nullable|numeric|min:0',
            'records.*.behavior_plus_marks' => 'integer|min:0',
            'records.*.behavior_minus_marks' => 'integer|min:0',
            'records.*.behavior_notes' => 'nullable|string',
            'records.*.participation_score' => 'nullable|integer|min:0',
            'records.*.participation_notes' => 'nullable|string'
        ]);

        $records = [];

        DB::beginTransaction();
        try {
            foreach ($request->records as $recordData) {
                $recordData['period_activity_id'] = $periodActivity->id;
                $record = StudentPeriodRecord::create($recordData);
                $records[] = $record;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create records', 'error' => $e->getMessage()], 500);
        }

        // Load student data for each record
        $records = StudentPeriodRecord::with('student')
            ->where('period_activity_id', $periodActivity->id)
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'student_id' => $record->student_id,
                    'name' => $record->student->name,
                    'attendance_status' => $record->attendance_status,
                    'late_minutes' => $record->late_minutes,
                    'homework_completed' => $record->homework_completed,
                    'homework_score' => $record->homework_score,
                    'behavior_plus_marks' => $record->behavior_plus_marks,
                    'behavior_minus_marks' => $record->behavior_minus_marks,
                    'behavior_notes' => $record->behavior_notes,
                    'participation_score' => $record->participation_score,
                    'participation_notes' => $record->participation_notes
                ];
            });

        return response()->json($records, 201);
    }

    /**
     * Batch update student records
     */
    public function batchUpdate(Request $request, PeriodActivity $periodActivity)
    {
        $request->validate([
            'records' => 'required|array',
            'records.*.id' => 'required|exists:student_period_records,id',
            'records.*.attendance_status' => 'required|string|in:present,absent,late,excused',
            'records.*.late_minutes' => 'nullable|integer|min:0',
            'records.*.homework_completed' => 'boolean',
            'records.*.homework_score' => 'nullable|numeric|min:0',
            'records.*.behavior_plus_marks' => 'integer|min:0',
            'records.*.behavior_minus_marks' => 'integer|min:0',
            'records.*.behavior_notes' => 'nullable|string',
            'records.*.participation_score' => 'nullable|integer|min:0',
            'records.*.participation_notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->records as $recordData) {
                $record = StudentPeriodRecord::find($recordData['id']);
                
                // Ensure the record belongs to this period activity
                if ($record->period_activity_id !== $periodActivity->id) {
                    throw new \Exception('Record does not belong to this period activity');
                }
                
                $record->update([
                    'attendance_status' => $recordData['attendance_status'],
                    'late_minutes' => $recordData['late_minutes'],
                    'homework_completed' => $recordData['homework_completed'],
                    'homework_score' => $recordData['homework_score'],
                    'behavior_plus_marks' => $recordData['behavior_plus_marks'],
                    'behavior_minus_marks' => $recordData['behavior_minus_marks'],
                    'behavior_notes' => $recordData['behavior_notes'],
                    'participation_score' => $recordData['participation_score'],
                    'participation_notes' => $recordData['participation_notes']
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update records', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Records updated successfully']);
    }
}

