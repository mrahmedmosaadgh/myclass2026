<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\GradeSubject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class GradeSubjectController extends Controller
{
    public function index()
    {
        $gradeSubjects = GradeSubject::with(['grade', 'subject'])
            ->paginate(40);

        $grades = Grade::select('id', 'name')->get();
        $subjects = Subject::select('id', 'name')->get();

        return Inertia::render('my_class/admin/GradeSubjects/Index', [
            'records' => $gradeSubjects,
            'options' => [
                'grades' => $grades,
                'subjects' => $subjects,
            ]
        ]);
    }

    public function store(Request $request)
    {
        // return $request;
        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        // Check for existing mapping
        $exists = GradeSubject::where('grade_id', $validated['grade_id'])
            ->where('subject_id', $validated['subject_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => ['mapping' => ['This grade-subject mapping already exists.']]
            ], 422);
        }

        GradeSubject::create($validated);

        return response()->json(['message' => 'Grade subject mapping created successfully']);
    }

    public function update(Request $request, GradeSubject $gradeSubject)
    {
        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        // Check for existing mapping (excluding current record)
        $exists = GradeSubject::where('grade_id', $validated['grade_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('id', '!=', $gradeSubject->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => ['mapping' => ['This grade-subject mapping already exists.']]
            ], 422);
        }

        $gradeSubject->update($validated);

        return response()->json(['message' => 'Grade subject mapping updated successfully']);
    }

    public function destroy(GradeSubject $gradeSubject)
    {
        $gradeSubject->delete();
        return redirect()->back()->with('success', 'Grade subject mapping deleted successfully');
    }

    public function validateImport(Request $request)
    {
        $statuses = [];

        foreach ($request->data as $row) {
            $status = 'new';

            // Find grade and subject by name
            $grade = Grade::where('name', $row['grade'])->first();
            $subject = Subject::where('name', $row['subject'])->first();

            if (!$grade || !$subject) {
                $status = 'invalid';
            } else {
                // Check if mapping already exists
                $existing = GradeSubject::where('grade_id', $grade->id)
                    ->where('subject_id', $subject->id)
                    ->first();

                if ($existing) {
                    $status = 'duplicate';
                }
            }

            $statuses[] = $status;
        }

        return response()->json(['statuses' => $statuses]);
    }

    public function import(Request $request)
    {
        $results = [
            'success' => [],
            'errors' => []
        ];

        DB::beginTransaction();
        try {
            foreach ($request->data as $row) {
                // Find grade and subject by name
                $grade = Grade::where('name', $row['grade'])->first();
                $subject = Subject::where('name', $row['subject'])->first();

                if (!$grade) {
                    $results['errors'][] = "Grade not found: {$row['grade']}";
                    continue;
                }

                if (!$subject) {
                    $results['errors'][] = "Subject not found: {$row['subject']}";
                    continue;
                }

                // Check for existing mapping
                $existing = GradeSubject::where('grade_id', $grade->id)
                    ->where('subject_id', $subject->id)
                    ->first();

                if ($existing) {
                    $results['errors'][] = "Mapping already exists for grade '{$row['grade']}' and subject '{$row['subject']}'";
                    continue;
                }

                // Create new mapping
                GradeSubject::create([
                    'grade_id' => $grade->id,
                    'subject_id' => $subject->id
                ]);

                $results['success'][] = "Created mapping for grade '{$row['grade']}' and subject '{$row['subject']}'";
            }

            DB::commit();
            return response()->json(['results' => $results]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => [],
                'errors' => ['An error occurred while processing the data: ' . $e->getMessage()]
            ], 500);
        }
    }

    public function export()
    {
        $gradeSubjects = GradeSubject::with(['grade', 'subject'])->get();
        // Export logic here
    }
}

