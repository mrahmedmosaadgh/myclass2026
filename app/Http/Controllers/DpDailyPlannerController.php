<?php

namespace App\Http\Controllers;

use App\Models\DpDailyTask;
use App\Models\DpTask;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DpDailyPlannerController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $tasks = DpDailyTask::where('user_id', auth()->id())
            ->whereDate('date', $today)
            ->orderBy('start_time')
            ->get();

        if ($tasks->isEmpty()) {
            $this->generateDailyTasks($today);
            $tasks = DpDailyTask::where('user_id', auth()->id())
                ->whereDate('date', $today)
                ->orderBy('start_time')
                ->get();
        }

        return Inertia::render('dailyTasks/dp_DailyPlanner', ['tasks' => $tasks]);
    }

    private function generateDailyTasks($date)
    {
        $masterTasks = DpTask::where('user_id', auth()->id())->where('is_active', true)->get();
        foreach ($masterTasks as $task) {
            DpDailyTask::create([
                'user_id' => auth()->id(),
                'dp_task_id' => $task->id,
                'title' => $task->title,
                'start_time' => $task->start_time,
                'end_time' => $task->end_time,
                'description' => $task->description,
                'date' => $date,
                'status' => 'pending',
            ]);
        }
    }

    public function update(Request $request, DpDailyTask $dpDailyTask)
    {
        if ($dpDailyTask->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,completed,skipped',
        ]);

        $dpDailyTask->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ]);

        return redirect()->back();
    }
}
