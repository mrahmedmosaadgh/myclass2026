<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolSection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolSectionController extends Controller
{
    public function index()
    {
        $sections = SchoolSection::with('school')->paginate(10);
        $schools = School::select('id', 'name')->get();

        return Inertia::render('my_class/admin/SchoolSections/Index', [
            'sections' => $sections,
            'schools' => $schools
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'data' => 'nullable|json'
        ]);

        SchoolSection::create($validated);

        return redirect()->back()->with('success', 'School Section created successfully');
    }

    public function update(Request $request, SchoolSection $section)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'data' => 'nullable|json'
        ]);

        $section->update($validated);

        return redirect()->back()->with('success', 'School Section updated successfully');
    }

    public function destroy(SchoolSection $section)
    {
        $section->delete();
        return redirect()->back()->with('success', 'School Section deleted successfully');
    }
}
