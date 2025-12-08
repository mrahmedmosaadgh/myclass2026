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
            'school_id' => 'nullable|integer',
            'year_id' => 'nullable|integer',
        ]);

        $exists = Behavior::where('name', $validated['name'])
            ->where('type', $validated['type'])
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
