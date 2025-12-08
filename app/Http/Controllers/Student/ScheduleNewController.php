<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\PeriodDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ScheduleNewController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student;

        $schedules = Schedule::with(['subject', 'teacher', 'co_teacher', 'teacher_substitute'])
            ->where('classroom_id', $student->classroom_id)
            ->where('active', true)
            ->get();

        $classroom = $student->classroom()->with('grade')->first();

        $periodDetails = PeriodDetail::orderBy('period')->get();

        return Inertia::render('Student/Schedule/Index', [
            'schedules' => $schedules,
            'classroom' => $classroom,
            'periodDetails' => $periodDetails
        ]);
    }

    public function currentWeek()
    {
        $student = auth()->user()->student;
        $currentWeek = Carbon::now()->weekOfYear;

        $schedules = Schedule::with(['subject', 'teacher', 'co_teacher', 'teacher_substitute'])
            ->where('classroom_id', $student->classroom_id)
            ->where('active', true)
            ->where('week', $currentWeek)
            ->get();

        return Inertia::render('Student/Schedule/WeekView', [
            'schedules' => $schedules,
            'classroom' => $student->classroom()->with('grade')->first(),
            'periodDetails' => PeriodDetail::orderBy('period')->get(),
            'weekNumber' => $currentWeek,
            'isCurrentWeek' => true
        ]);
    }

    public function nextWeek()
    {
        $student = auth()->user()->student;
        $nextWeek = Carbon::now()->addWeek()->weekOfYear;

        $schedules = Schedule::with(['subject', 'teacher', 'co_teacher', 'teacher_substitute'])
            ->where('classroom_id', $student->classroom_id)
            ->where('active', true)
            ->where('week', $nextWeek)
            ->get();

        return Inertia::render('Student/Schedule/WeekView', [
            'schedules' => $schedules,
            'classroom' => $student->classroom()->with('grade')->first(),
            'periodDetails' => PeriodDetail::orderBy('period')->get(),
            'weekNumber' => $nextWeek,
            'isCurrentWeek' => false
        ]);
    }

    public function update_day_period(Request $request, Schedule $schedule){

// return [$schedule ,$request->update];
// return [$schedule->id,$request->all()];
 if(isset($request->day)){

     Schedule::where('id',$schedule->id)->update([
     'day'=>$request->day
     ]);
 }

 if(isset($request->period_number)){

    Schedule::where('id',$schedule->id)->update([
    'period_number'=>$request->period_number
    ]);
}
return response()->json([
    'msg' => 'updated',
    // 'periodDetails' => PeriodDetail::orderBy('period')->get()
]);
    }
//     public function update_day(Request $request){

// return $request->all();

//     }


    public function getScheduleData2(Request $request)
    {
        $student = auth()->user()->student;
        $week = $request->input('week', Carbon::now()->weekOfYear);

        $schedules = Schedule::with(['subject', 'teacher', 'co_teacher', 'teacher_substitute'])
            ->where('classroom_id', $student->classroom_id)
            ->where('active', true)
            ->where('week', $week)
            ->get();

        return response()->json([
            'schedules' => $schedules,
            'periodDetails' => PeriodDetail::orderBy('period')->get()
        ]);
    }
    public function getScheduleData($school_id, $schedule_copy_id)
    {
        $scheduleData = Schedule::with(['cst.teacher', 'cst.subject', 'cst.classroom'])
            ->where('school_id', $school_id)
            ->where('copy_id', $schedule_copy_id)
            ->get();

        return response()->json($scheduleData);
    }
    public function print()
    {
        $student = auth()->user()->student;

        $schedules = Schedule::with(['subject', 'teacher', 'co_teacher', 'teacher_substitute'])
            ->where('classroom_id', $student->classroom_id)
            ->where('active', true)
            ->get();

        $classroom = $student->classroom()->with('grade')->first();
        $periodDetails = PeriodDetail::orderBy('period')->get();

        return Inertia::render('Student/Schedule/Print', [
            'schedules' => $schedules,
            'classroom' => $classroom,
            'periodDetails' => $periodDetails,
            'student' => $student->only(['id', 'name', 'student_id']),
            'printDate' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}

