<?php

namespace App\Http\Controllers;

use App\Models\SemesterTest;
use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SemesterTestController extends Controller
{
    public function index()
    {
        $semesterTests = SemesterTest::with(['school', 'academicYear'])->paginate(10);
        $schools = School::select('id', 'name')->get();
        $academicYears = AcademicYear::select('id', 'name')->get();

        return Inertia::render('my_class/admin/SemesterTests/Index', [
            'records' => $semesterTests,
            'options' => [
                'schools' => $schools,
                'academicYears' => $academicYears
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'semester_number' => 'required|integer',
            'weeks_number' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'academic_year_id' => 'required|exists:academic_years,id',
            'school_id' => 'required|exists:schools,id',
            'data' => 'nullable|json',
            'active' => 'boolean'
        ]);

        SemesterTest::create($validated);

        return redirect()->back()->with('success', 'Semester Test created successfully');
    }

    public function update(Request $request, SemesterTest $semesterTest)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'semester_number' => 'required|integer',
            'weeks_number' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'academic_year_id' => 'required|exists:academic_years,id',
            'school_id' => 'required|exists:schools,id',
            'data' => 'nullable|json',
            'active' => 'boolean'
        ]);

        $semesterTest->update($validated);

        return redirect()->back()->with('success', 'Semester Test updated successfully');
    }

    public function destroy(SemesterTest $semesterTest)
    {
        $semesterTest->delete();
        return redirect()->back()->with('success', 'Semester Test deleted successfully');
    }
}
