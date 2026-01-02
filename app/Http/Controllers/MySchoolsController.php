<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\School;
use App\Models\HR;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class MySchoolsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Find the HR record associated with this user
        $hr = HR::where('user_id', $user->id)->first();

        if (!$hr) {
            abort(403, 'User does not have an HR record.');
        }

        $schools = School::where('h_r_id', $hr->id)
            ->withCount(['students', 'teachers', 'classrooms'])
            ->get();

        return Inertia::render('my_class/hr/MySchools/Index', [
            'schools' => $schools,
            'hr' => $hr,
            'currentSchoolId' => $user->school_id
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'established_year' => 'nullable|numeric',
        ]);

        $user = auth()->user();
        $hr = HR::where('user_id', $user->id)->firstOrFail();

        $school = School::create([
            'name' => $validated['name'],
            'name_ar' => $validated['name_ar'] ?? null,
            'h_r_id' => $hr->id,
            'is_active' => true,
            'data' => json_encode([
                'established_year' => $validated['established_year'] ?? date('Y'),
            ])
        ]);

        return redirect()->back()->with('success', 'School created successfully.');
    }

    public function selectSchool(School $school)
    {
        // Switch the context to this school
        $user = auth()->user();
        $hr = HR::where('user_id', $user->id)->firstOrFail();

        if ($school->h_r_id !== $hr->id) {
            abort(403);
        }

        $user->update(['school_id' => $school->id]);

        return redirect()->back()->with('success', 'Switched to ' . $school->name);
    }
}
