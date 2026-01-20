<?php

namespace App\Http\Controllers;

use App\Models\Behavior;
use Illuminate\Http\Request;

class BehaviorController extends Controller
{
    public function index()
    {
        return response()->json(Behavior::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:positive,negative',
            'points' => 'required|integer',
            'school_id' => 'required|integer|exists:schools,id',
            'year_id' => 'nullable|integer',
        ]);

        // Ensure we associate with the correct active academic year
        $school = \App\Models\School::find($validated['school_id']);
        if ($school) {
            // Force the year_id to be the school's active year to avoid foreign key errors
            // with stale frontend data (e.g. sending year_id=2 when only 1 exists)
            $validated['year_id'] = $school->academic_year_id;
        }

        $exists = Behavior::where('name', $validated['name'])
            ->where('type', $validated['type'])
            ->where('school_id', $validated['school_id'])
            ->where('year_id', $validated['year_id'])
            ->first();

        if ($exists) {
            return response()->json(['message' => 'Behavior already exists.'], 409);
        }

        $behavior = Behavior::create($validated);
        return response()->json($behavior, 201);
    }

    public function update(Request $request, Behavior $behavior)
    {
        $behavior->update($request->only(['name', 'type', 'points']));
        return response()->json($behavior);
    }

    public function destroy(Behavior $behavior)
    {
        $behavior->delete();
        return response()->json(['message' => 'Behavior deleted']);
    }
}
