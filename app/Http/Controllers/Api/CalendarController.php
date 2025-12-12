<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $query = Calendar::where('school_id', Auth::user()->school_id);

        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->has('semester_number')) {
            $query->where('semester_number', $request->semester_number);
        }

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        return $query->orderBy('date', 'asc')->get();
    }

    public function show(Calendar $calendar)
    {
        return $calendar;
    }
}

