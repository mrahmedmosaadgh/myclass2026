<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StageController extends Controller
{
    public function index()
    {
        $stages = Stage::with('school')->paginate(10);
        $schools = School::select('id', 'name')->get();

        return Inertia::render('my_class/admin/Stages/Index', [
            'records' => $stages,
            'schools' => $schools
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'school_id' => 'required|exists:schools,id',
        ]);

        Stage::create($validated);

        return redirect()->back()->with('success', 'Stage created successfully');
    }

    public function update(Request $request, Stage $stage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'school_id' => 'required|exists:schools,id',
        ]);

        $stage->update($validated);

        return redirect()->back()->with('success', 'Stage updated successfully');
    }

    public function destroy(Stage $stage)
    {
        $stage->delete();
        return redirect()->back()->with('success', 'Stage deleted successfully');
    }
}

