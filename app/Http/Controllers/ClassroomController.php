<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\School;
use App\Models\Stage;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::with(['school', 'stage', 'grade'])
            ->paginate(40);

        $schools = School::select('id', 'name')->get();
        $stages = Stage::select('id', 'name')->get();
        $grades = Grade::select('id', 'name')->get();

        return Inertia::render('my_class/admin/Classrooms/Index', [
            'records' => $classrooms,
            'schools' => $schools,
            'stages' => $stages,
            'grades' => $grades,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'capacity' => 'required|integer|min:1',
                'school_id' => 'required|exists:schools,id',
                'stage_id' => 'required|exists:stages,id',
                'grade_id' => 'required|exists:grades,id',
            ]);

            $classroom = Classroom::create($validated);
            $classroom->load(['school', 'stage', 'grade']); // Load relationships

            return response()->json([
                'message' => 'Classroom created successfully',
                'classroom' => $classroom
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function update(Request $request, Classroom $classroom)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'capacity' => 'required|integer|min:1',
                'school_id' => 'required|exists:schools,id',
                'stage_id' => 'required|exists:stages,id',
                'grade_id' => 'required|exists:grades,id',
            ]);

            $classroom->update($validated);
            $classroom->load(['school', 'stage', 'grade']); // Load relationships

            return response()->json([
                'message' => 'Classroom updated successfully',
                'classroom' => $classroom
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();

        return response()->json([
            'message' => 'Classroom deleted successfully'
        ]);
    }
}




