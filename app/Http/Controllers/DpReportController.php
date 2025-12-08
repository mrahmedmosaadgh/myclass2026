<?php

namespace App\Http\Controllers;

use App\Models\DpDailyTask;
use App\Models\DpFocusLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DpReportController extends Controller
{
    public function index()
    {
        $user_id = auth()->id();
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Daily Stats
        $dailyCompleted = DpDailyTask::where('user_id', $user_id)
            ->whereDate('date', $today)
            ->where('status', 'completed')
            ->count();
        $dailyTotal = DpDailyTask::where('user_id', $user_id)
            ->whereDate('date', $today)
            ->count();
        $dailyFocus = DpFocusLog::where('user_id', $user_id)
            ->whereDate('start_time', $today)
            ->sum('duration_minutes');

        // Weekly Stats
        $weeklyCompleted = DpDailyTask::where('user_id', $user_id)
            ->whereBetween('date', [$startOfWeek, $today])
            ->where('status', 'completed')
            ->count();
        $weeklyFocus = DpFocusLog::where('user_id', $user_id)
            ->whereBetween('start_time', [$startOfWeek, $today])
            ->sum('duration_minutes');

        return Inertia::render('dailyTasks/dp_Reports', [
            'daily' => [
                'completed' => $dailyCompleted,
                'total' => $dailyTotal,
                'focus_minutes' => $dailyFocus,
            ],
            'weekly' => [
                'completed' => $weeklyCompleted,
                'focus_minutes' => $weeklyFocus,
            ],
        ]);
    }
}
