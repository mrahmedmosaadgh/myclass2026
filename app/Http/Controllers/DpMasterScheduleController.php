<?php

namespace App\Http\Controllers;

use App\Models\DpTask;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DpMasterScheduleController extends Controller
{
    public function index()
    {
        $tasks = DpTask::where('user_id', auth()->id())->get();
        return Inertia::render('dailyTasks/dp_MasterSchedule', ['tasks' => $tasks]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        $request->user()->dpTasks()->create($validated);

        return redirect()->back();
    }

    public function update(Request $request, DpTask $dpTask)
    {
        if ($dpTask->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $dpTask->update($validated);

        return redirect()->back();
    }

    public function destroy(DpTask $dpTask)
    {
        if ($dpTask->user_id !== auth()->id()) {
            abort(403);
        }
        $dpTask->delete();
        return redirect()->back();
    }
}
