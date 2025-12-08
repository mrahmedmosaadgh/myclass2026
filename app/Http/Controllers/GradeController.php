<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\School;
use App\Models\Stage;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradeController extends Controller
{
    public function index()
    {
        $grades = Grade::with(['school', 'stage', 'subjects'])->paginate(10);

        $schools = School::select('id', 'name')->get();
        $stages = Stage::select('id', 'name')->get();
        $subjects = Subject::select('id', 'name')->get();

        return Inertia::render('my_class/admin/Grades/Index', [
            'records' => $grades,
            'schools' => $schools,
            'stages' => $stages,
            'subjects' => $subjects
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'stage_id' => 'required|exists:stages,id',
            'subject_ids' => 'nullable|json'
        ]);

        $grade = Grade::create($validated);

        // If you have a pivot table for grade_subject relationship
        if ($request->subject_ids) {
            $subjectIds = json_decode($request->subject_ids, true);
            if (is_array($subjectIds)) {
                $grade->subjects()->sync($subjectIds);
            }
        }

        return redirect()->back()->with('success', 'Grade created successfully');
    }

    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'stage_id' => 'required|exists:stages,id',
            'subject_ids' => 'nullable|json'
        ]);

        $grade->update($validated);

        // If you have a pivot table for grade_subject relationship
        if ($request->subject_ids) {
            $subjectIds = json_decode($request->subject_ids, true);
            if (is_array($subjectIds)) {
                $grade->subjects()->sync($subjectIds);
            }
        }

        return redirect()->back()->with('success', 'Grade updated successfully');
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();
        return redirect()->back()->with('success', 'Grade deleted successfully');
    }

    /**
     * Get all grades for API (used in question bank filters).
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiIndex(Request $request)
    {
        try {
            // Get authenticated user's school
            $user = $request->user();
            
            // If user has a school_id, filter by that school
            $query = Grade::query();
            
            if ($user && isset($user->school_id)) {
                $query->where('school_id', $user->school_id);
            }
            
            $grades = $query->select('id', 'name', 'school_id', 'stage_id')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $grades,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'GRADES_ERROR',
                    'message' => 'Failed to retrieve grades',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 500);
        }
    }
}


