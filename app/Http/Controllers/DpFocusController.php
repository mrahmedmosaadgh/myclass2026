<?php

namespace App\Http\Controllers;

use App\Models\DpFocusLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DpFocusController extends Controller
{
    public function index()
    {
        return Inertia::render('dailyTasks/dp_LiveFocus');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dp_daily_task_id' => 'nullable|exists:dp_daily_tasks,id',
            'start_time' => 'required|date',
        ]);

        $log = DpFocusLog::create([
            'user_id' => auth()->id(),
            'dp_daily_task_id' => $validated['dp_daily_task_id'] ?? null,
            'start_time' => $validated['start_time'],
        ]);

        return response()->json($log);
    }

    public function update(Request $request, DpFocusLog $dpFocusLog)
    {
        if ($dpFocusLog->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'end_time' => 'required|date',
            'duration_minutes' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $dpFocusLog->update($validated);

        return response()->json($dpFocusLog);
    }

    public function logDistraction(Request $request, DpFocusLog $dpFocusLog)
    {
        if ($dpFocusLog->user_id !== auth()->id()) {
            abort(403);
        }
        $dpFocusLog->increment('distraction_count');
        return response()->json(['success' => true]);
    }
}
