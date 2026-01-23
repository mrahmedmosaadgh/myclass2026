<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\HR;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::with('hr')->paginate(40);
        $hrs = HR::select('id', 'name')->get();

        return Inertia::render('my_class/admin/Schools/Index', [
            'schools' => $schools,
            'hrs' => $hrs
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'h_r_id' => 'required_without:create_new_hr|exists:h_r_s,id',
            'create_new_hr' => 'boolean',
            'hr_name' => 'required_if:create_new_hr,true|string|max:255',
            'hr_email' => 'required_if:create_new_hr,true|email|unique:users,email',
            'hr_password' => 'required_if:create_new_hr,true|string|min:8',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $hrId = $request->h_r_id;

            if ($request->create_new_hr) {
                // Create User
                $user = \App\Models\User::create([
                    'name' => $validated['hr_name'],
                    'email' => $validated['hr_email'],
                    'password' => \Illuminate\Support\Facades\Hash::make($validated['hr_password']),
                    'role' => 'hr_admin',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
                $user->assignRole('hr_admin');

                // Create HR Record
                $hr = HR::create([
                    'user_id' => $user->id,
                    'name' => $validated['hr_name'],
                    'active' => 1,
                    'data' => json_encode(['notes' => 'Created via School Manager'])
                ]);

                $hrId = $hr->id;
            }

            $school = School::create([
                'name' => $validated['name'],
                'h_r_id' => $hrId
            ]);

            // Update user with school_id
            if (isset($user)) {
                $user->update(['school_id' => $school->id]);
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->back()->with('success', 'School created successfully');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to create school: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'h_r_id' => 'required|exists:h_r_s,id',
        ]);

        $school->update($validated);

        return redirect()->back()->with('success', 'School updated successfully');
    }

    public function destroy(School $school)
    {
        $school->delete();
        return redirect()->back()->with('success', 'School deleted successfully');
    }

    public function toggleStatus(School $school)
    {
        $school->update(['is_active' => !$school->is_active]);
        
        $status = $school->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "School has been $status");
    }
    
    public function getSubjects($schoolId)
    {
        $school = School::find($schoolId);
        if (!$school) {
            return response()->json(['error' => 'School not found'], 404);
        }

        $subjects = $school->subjects; // Assuming a relationship exists
        return response()->json($subjects);
    }

    /**
     * Get all schools for API dropdowns
     */
    public function apiIndex()
    {
        $schoolId = auth()->user()->schoolId();
        
        $query = School::select('id', 'name');

        if ($schoolId) {
            $query->where('id', $schoolId);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('name')->get()
        ]);
    }
    
    /**
     * Get a single school by ID for API
     */
    /**
     * Get a single school by ID for API
     */
    public function apiShow($id)
    {
        $school = School::with([
            'activeAcademicYear:id,name',
            'activeSemester:id,name'
         
        ])->find($id);
        
        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'School not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $school
        ]);
    }
    
    /**
     * Update a school via API
     */
    public function apiUpdate(Request $request, $id)
    {
        $school = School::find($id);
        
        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'School not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
          
        ]);

        $school->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'School updated successfully',
            'data' => $school
        ]);
    }

    /**
     * Get current active term/semester for the user's school
     */
    public function currentTerm(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->schoolId();
        
        if (!$schoolId) {
            return response()->json(['message' => 'User not associated with a school'], 404);
        }

        $school = School::with('activeSemester')->find($schoolId);
        
        if (!$school || !$school->activeSemester) {
            // If no active semester, try to find the latest semester for the active academic year
            if ($school && $school->activeAcademicYear) {
                $latestSemester = \App\Models\Semester::where('academic_year_id', $school->active_academic_year_id)
                    ->orderBy('start_date', 'desc')
                    ->first();
                    
                if ($latestSemester) {
                    return response()->json($latestSemester);
                }
            }
            
            return response()->json(['message' => 'Active semester not found'], 404);
        }

        return response()->json($school->activeSemester);
    }
}