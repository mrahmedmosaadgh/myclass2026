<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\School;
use App\Models\Subject;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CurriculumController extends Controller
{
    /**
     * Display curriculum management page
     */
    public function index()
    {
        return inertia('my_class/admin/Curriculum/CurriculumManagement');
    }

    /**
     * Get user's schools
     */
    public function getUserSchools()
    {
        $user = Auth::user();
        
        // Get schools based on user role
        $schools = School::query()
            ->when($user->hasRole('superadmin'), function ($query) {
                return $query; // Superadmin sees all schools
            })
            ->when(!$user->hasRole('superadmin'), function ($query) use ($user) {
                // Regular users see only their assigned schools
                return $query->whereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->select('id', 'name', 'name_ar')
            ->orderBy('name')
            ->get();

        return response()->json($schools);
    }

    /**
     * Get subjects for a specific school
     */
    public function getSchoolSubjects($schoolId)
    {
        $subjects = Subject::where('school_id', $schoolId)
            ->where('active', 1)
            ->select('id', 'name', 'description')
            ->orderBy('name')
            ->get();

        return response()->json($subjects);
    }

    /**
     * Get grades for a specific school
     */
    public function getSchoolGrades($schoolId)
    {
        $grades = Grade::where('school_id', $schoolId)
            ->select('id', 'name', 'stage_id')
            ->with('stage:id,name')
            ->orderBy('name')
            ->get();

        return response()->json($grades);
    }

    /**
     * Get curricula with filters
     */
    public function getCurricula(Request $request)
    {
        $query = Curriculum::with(['school:id,name', 'subject:id,name', 'grade:id,name'])
            ->select('id', 'name', 'description', 'school_id', 'subject_id', 'grade_id', 'active', 'created_at');

        // Apply filters
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        if ($request->filled('active')) {
            $query->where('active', $request->active);
        }

        $curricula = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($curricula);
    }

    /**
     * Store a new curriculum
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'school_id' => 'required|exists:schools,id',
            'subject_id' => 'required|exists:subjects,id',
            'grade_id' => 'required|exists:grades,id',
            'active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $curriculum = Curriculum::create([
                'name' => $request->name,
                'description' => $request->description,
                'school_id' => $request->school_id,
                'subject_id' => $request->subject_id,
                'grade_id' => $request->grade_id,
                'active' => $request->active ? 1 : 0
            ]);

            // If setting as active, deactivate others
            if ($request->active) {
                $curriculum->activate();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Curriculum created successfully',
                'curriculum' => $curriculum->load(['school:id,name', 'subject:id,name', 'grade:id,name'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating curriculum: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update curriculum
     */
    public function update(Request $request, Curriculum $curriculum)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $curriculum->update([
                'name' => $request->name,
                'description' => $request->description,
                'active' => $request->active ? 1 : 0
            ]);

            // If setting as active, deactivate others
            if ($request->active) {
                $curriculum->activate();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Curriculum updated successfully',
                'curriculum' => $curriculum->load(['school:id,name', 'subject:id,name', 'grade:id,name'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error updating curriculum: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate curriculum (deactivates others for same school+subject+grade)
     */
    public function activate(Curriculum $curriculum)
    {
        DB::beginTransaction();
        try {
            $curriculum->activate();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Curriculum activated successfully. Other curricula for the same subject and grade have been deactivated.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error activating curriculum: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate curriculum
     */
    public function deactivate(Curriculum $curriculum)
    {
        try {
            $curriculum->deactivate();

            return response()->json([
                'success' => true,
                'message' => 'Curriculum deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deactivating curriculum: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete curriculum
     */
    public function destroy(Curriculum $curriculum)
    {
        try {
            $curriculum->delete();

            return response()->json([
                'success' => true,
                'message' => 'Curriculum deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting curriculum: ' . $e->getMessage()
            ], 500);
        }
    }
}
