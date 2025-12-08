<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\StudentBehavior;
use App\Models\StudentBehaviorsMain;

class AttendanceController extends Controller
{
    /**
     * Persist single student attendance
     * Expects: student_id, attend (boolean), date (YYYY-MM-DD), period_code (optional), classroom_id (optional)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'attend' => 'required|boolean',
            'date' => 'required|date',
            'period_code' => 'nullable|string',
            'classroom_id' => 'nullable|integer',
        ]);

        try {
            $user = auth()->user();
            $teacher = null;
            $schoolId = null;
            $yearId = null;

            if ($user) {
                $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
                $schoolId = $teacher->school_id ?? null;
            }

            // Try to find an existing StudentBehaviorsMain for this classroom/date/period
            $query = StudentBehaviorsMain::where('date', $validated['date']);

            if (!empty($validated['period_code'])) {
                $query->where('period_code', $validated['period_code']);
            }

            if (!empty($validated['classroom_id'])) {
                $query->where('classroom_id', $validated['classroom_id']);
            }

            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }

            $main = $query->first();

            if (!$main) {
                // Create a minimal main record so we can attach a StudentBehavior
                $main = StudentBehaviorsMain::create([
                    'school_id' => $schoolId,
                    'year_id' => $yearId,
                    'teacher_id' => $teacher->id ?? null,
                    'subject_id' => null,
                    'classroom_id' => $validated['classroom_id'] ?? null,
                    'period_code_main' => null,
                    'period_code' => $validated['period_code'] ?? null,
                    'date' => $validated['date'],
                    'notes' => null,
                ]);
            }

            // Find or create StudentBehavior for this main and student
            $behavior = StudentBehavior::where('student_behaviors_mains_id', $main->id)
                ->where('student_id', $validated['student_id'])
                ->first();

            if ($behavior) {
                $behavior->attend = $validated['attend'];
                $behavior->save();
            } else {
                $behavior = StudentBehavior::create([
                    'school_id' => $main->school_id,
                    'student_behaviors_mains_id' => $main->id,
                    'student_id' => $validated['student_id'],
                    'attend' => $validated['attend'],
                    'points_plus' => 0,
                    'points_minus' => 0,
                    'points_details' => null,
                    'notes' => null,
                ]);
            }

            return response()->json([
                'message' => 'Attendance recorded',
                'data' => [
                    'student_behavior_id' => $behavior->id,
                    'student_id' => $behavior->student_id,
                    'attend' => (bool) $behavior->attend,
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Attendance store error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to record attendance', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Batch update attendance
     * Expects: attendance: [{ student_id, attend, date (optional), period_code (optional), classroom_id (optional) }, ...]
     */
    public function batch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|integer|exists:students,id',
            'attendance.*.attend' => 'required|boolean',
            'attendance.*.date' => 'nullable|date',
            'attendance.*.period_code' => 'nullable|string',
            'attendance.*.classroom_id' => 'nullable|integer',
        ]);

        $results = [];

        foreach ($validated['attendance'] as $item) {
            try {
                $req = new Request($item);
                $res = $this->store($req);
                $results[] = json_decode($res->getContent(), true);
            } catch (\Exception $e) {
                $results[] = ['message' => 'error', 'error' => $e->getMessage()];
            }
        }

        return response()->json(['message' => 'Batch attendance processed', 'results' => $results], 200);
    }
}
