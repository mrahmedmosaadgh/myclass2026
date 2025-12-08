<?php

namespace App\Http\Controllers;

use App\Models\PeriodDetail;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PeriodDetailController extends Controller
{
    public function index()
    {
        $records = PeriodDetail::with('school')
            ->orderBy('sequence')
            ->paginate(10);

        return Inertia::render('my_class/admin/PeriodDetails/Index', [
            'records' => $records,
            'options' => [
                'schools' => School::select('id', 'name')->get()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'code' => 'required|integer|min:1',
            'sequence' => 'required|integer|min:1',
            'name' => 'nullable|string|max:255',
            'main' => 'boolean',
            'time_before' => 'nullable|integer|min:0',
            'from' => 'nullable|date_format:H:i',
            'to' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string'
        ]);

        PeriodDetail::create($validated);

        return redirect()->back()->with('success', 'Period detail created successfully.');
    }

    public function update(Request $request, PeriodDetail $periodDetail)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'code' => 'required|integer|min:1',
            'sequence' => 'required|integer|min:1',
            'name' => 'nullable|string|max:255',
            'main' => 'boolean',
            'time_before' => 'nullable|integer|min:0',
            'from' => 'nullable|date_format:H:i',
            'to' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string'
        ]);

        $periodDetail->update($validated);

        return redirect()->back()->with('success', 'Period detail updated successfully.');
    }

    public function destroy(PeriodDetail $periodDetail)
    {
        $periodDetail->delete();
        return redirect()->back()->with('success', 'Period detail deleted successfully.');
    }
}