<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\PomodoroSession;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PomodoroController extends Controller
{
    /**
     * Start a new pomodoro session.
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'nullable|exists:tasks,id',
            'type' => 'required|in:work,break',
            'duration' => 'required|integer|min:1|max:60',
        ]);

        // If task_id is provided, verify it belongs to the user
        if ($validated['task_id']) {
            $task = Task::find($validated['task_id']);
            if ($task->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $session = PomodoroSession::create([
            'user_id' => Auth::id(),
            'task_id' => $validated['task_id'],
            'type' => $validated['type'],
            'duration' => $validated['duration'],
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        return response()->json([
            'message' => 'Pomodoro session started',
            'session' => $session,
        ]);
    }

    /**
     * End a pomodoro session.
     */
    public function end(Request $request, PomodoroSession $session)
    {
        // Check if the session belongs to the authenticated user
        if ($session->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
            'status' => 'required|in:completed,interrupted,extended',
        ]);

        $session->update([
            'ended_at' => now(),
            'notes' => $validated['notes'],
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Pomodoro session ended',
            'session' => $session,
        ]);
    }

    /**
     * Get recent pomodoro sessions.
     */
    public function recent()
    {
        $sessions = PomodoroSession::with('task')
            ->where('user_id', Auth::id())
            ->orderBy('started_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    /**
     * Get statistics about pomodoro sessions.
     */
    public function stats()
    {
        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        $stats = [
            'today' => [
                'work_sessions' => PomodoroSession::where('user_id', Auth::id())
                    ->where('type', 'work')
                    ->where('started_at', '>=', $today)
                    ->count(),
                'work_minutes' => PomodoroSession::where('user_id', Auth::id())
                    ->where('type', 'work')
                    ->where('started_at', '>=', $today)
                    ->where('status', 'completed')
                    ->sum('duration'),
            ],
            'week' => [
                'work_sessions' => PomodoroSession::where('user_id', Auth::id())
                    ->where('type', 'work')
                    ->where('started_at', '>=', $weekStart)
                    ->count(),
                'work_minutes' => PomodoroSession::where('user_id', Auth::id())
                    ->where('type', 'work')
                    ->where('started_at', '>=', $weekStart)
                    ->where('status', 'completed')
                    ->sum('duration'),
            ],
            'month' => [
                'work_sessions' => PomodoroSession::where('user_id', Auth::id())
                    ->where('type', 'work')
                    ->where('started_at', '>=', $monthStart)
                    ->count(),
                'work_minutes' => PomodoroSession::where('user_id', Auth::id())
                    ->where('type', 'work')
                    ->where('started_at', '>=', $monthStart)
                    ->where('status', 'completed')
                    ->sum('duration'),
            ],
        ];

        return response()->json($stats);
    }
}
