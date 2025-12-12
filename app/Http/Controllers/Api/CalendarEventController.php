<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $query = CalendarEvent::where('school_id', Auth::user()->school_id);

        if ($request->has('calendar_id')) {
            $query->where('calendar_id', $request->calendar_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        return $query->orderBy('start_time')->get();
    }

    public function show(CalendarEvent $calendarEvent)
    {
        return $calendarEvent;
    }
}
