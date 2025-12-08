<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::where('school_id', Auth::user()->school_id);

        if ($request->has('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        return $query->orderBy('day_of_week')->orderBy('start_time')->get();
    }

    public function show(Schedule $schedule)
    {
        return $schedule;
    }
}
