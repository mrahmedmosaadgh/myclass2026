<?php

namespace App\Http\Controllers\WeeklySystem;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\CurriculumVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CurriculumController extends Controller
{
    /**
     * Display a listing of the curricula for the user's school.
     */
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        
        // Base query - only school's curricula
        $query = Curriculum::with(['grade', 'subject'])
            ->where('school_id', $schoolId);
            
        // If teacher is requesting, we should ideally filter by their assigned subjects/grades.
        // For Phase 1, if 'teacher_id' is passed, we could filter here, but we will return all
        // school curricula for now and let frontend or a future scope handle CST filtering.
        
        $curricula = $query->get()->map(function ($curriculum) {
            return [
                'id' => $curriculum->id,
                'name' => $curriculum->name,
                'description' => $curriculum->description,
                'grade_name' => $curriculum->grade ? $curriculum->grade->name : 'N/A',
                'subject_name' => $curriculum->subject ? $curriculum->subject->name : 'N/A',
                'edit_lock_date' => $curriculum->edit_lock_date ? $curriculum->edit_lock_date->format('Y-m-d') : null,
                'created_at' => $curriculum->created_at->format('Y-m-d'),
                // For raw IDs if needed by frontend
                'grade_id' => $curriculum->grade_id,
                'subject_id' => $curriculum->subject_id,
            ];
        });

        return response()->json($curricula);
    }

    /**
     * Store a newly created curriculum in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grade_id' => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
            'description' => 'nullable|string',
            'edit_lock_date' => 'nullable|date',
        ]);

        $schoolId = $request->user()->school_id;

        // Check for duplicate name in the same school
        $exists = Curriculum::where('school_id', $schoolId)
            ->where('name', $request->name)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Curriculum name already exists in your school.'
            ], 422);
        }

        $curriculum = Curriculum::create([
            'name' => $request->name,
            'description' => $request->description,
            'school_id' => $schoolId,
            'grade_id' => $request->grade_id,
            'subject_id' => $request->subject_id,
            'edit_lock_date' => $request->edit_lock_date,
        ]);

        // Create a default version so lessons can be attached immediately
        CurriculumVersion::create([
            'curriculum_id' => $curriculum->id,
            'title' => 'Default Version',
            'status' => 'active',
            'version_number' => 1
        ]);

        return response()->json([
            'message' => 'Curriculum created successfully.',
            'curriculum' => $curriculum
        ], 201);
    }

    /**
     * Update the specified curriculum (primarily for edit_lock_date).
     */
    public function update(Request $request, $id)
    {
        $curriculum = Curriculum::where('school_id', $request->user()->school_id)->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'edit_lock_date' => 'nullable|date',
        ]);

        // Check duplicate name if name is being changed
        if ($request->has('name') && $request->name !== $curriculum->name) {
            $exists = Curriculum::where('school_id', $request->user()->school_id)
                ->where('name', $request->name)
                ->where('id', '!=', $id)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Curriculum name already exists in your school.'
                ], 422);
            }
        }

        $curriculum->update($request->only(['name', 'description', 'edit_lock_date']));

        return response()->json([
            'message' => 'Curriculum updated successfully.',
            'curriculum' => $curriculum
        ]);
    }

    /**
     * Remove the specified curriculum from storage.
     */
    public function destroy(Request $request, $id)
    {
        $curriculum = Curriculum::where('school_id', $request->user()->school_id)->findOrFail($id);
        
        $curriculum->delete(); // Soft delete

        return response()->json([
            'message' => 'Curriculum deleted successfully.'
        ]);
    }
}
