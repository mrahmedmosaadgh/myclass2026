<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Schedule;
use App\Models\ScheduleTiming;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TeacherNewTimeLineController extends Controller
{
    public function index()
    {
        return Inertia::render('my_class/teacher/schedule/Index');
    }

    public function getTeacherSchedule(Request $request)
    {
        try {
            $school_id = $request->school_id;
            $day_code = $request->day_code ?? 'd' . (date('w') + 1);

            $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();

            $timings = ScheduleTiming::where('school_id', $school_id)->first();

            if (!$timings) {
                return response()->json([
                    'success' => true,
                    'data' => ['schedule' => [], 'timings' => null]
                ]);
            }

            $schedule = Schedule::with(['classroomSubjectTeacher.subject', 'classroomSubjectTeacher.classroom'])
                ->whereHas('classroomSubjectTeacher', function($query) use ($teacher) {
                    $query->where('teacher_id', $teacher->id);
                })
                ->where('active', true)
                ->where('period_code', 'like', $day_code . '%')
                ->get();

            $formattedSchedule = $this->formatScheduleWithTimings($schedule, $timings);

            return response()->json([
                'success' => true,
                'data' => [
                    'schedule' => $formattedSchedule,
                    'timings' => $timings->timing
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function formatScheduleWithTimings($schedule, $timings)
    {
        if (!$timings) {
            return [];
        }

        $events = [];
        $scheduleMap = $schedule->keyBy('period_code');

        // Get the requested day from the first schedule item or default to current day
        $dayCode = $schedule->first() ? substr($schedule->first()->period_code, 0, 2) : 'd' . date('w');

        // Process timings for the specific day
        foreach ($timings->timing[$dayCode] ?? [] as $timing) {
            $periodCode = $timing['period_code'];
            $scheduleItem = $scheduleMap->get($periodCode);

            if (strpos($timing['label'], 'Break') !== false) {
                // Handle break periods
                $events[] = [
                    'id' => uniqid('break_'),
                    'title' => $timing['label'],
                    'classroom' => null,
                    'location' => null,
                    'from' => $timing['timeSlots'][0]['from'],
                    'to' => $timing['timeSlots'][0]['to'],
                    'timeSlots' => $timing['timeSlots'],
                    'label' => $timing['label'],
                    'status' => 'pending',
                    'textColor' => '#FFFFFF',
                    'bgColor' => '#64748b',
                    'periodCode' => str_replace(['d1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd7'], '', $periodCode),
                    'type' => 'break'
                ];
            } elseif ($scheduleItem) {
                // Handle scheduled periods
                $cst = $scheduleItem->classroomSubjectTeacher;
                $events[] = [
                    'id' => $scheduleItem->id,
                    'title' => $cst->subject->name,
                    'classroom' => $cst->classroom->name,
                    'location' => $scheduleItem->place,
                    'from' => $timing['timeSlots'][0]['from'],
                    'to' => $timing['timeSlots'][0]['to'],
                    'timeSlots' => $timing['timeSlots'],
                    'label' => $timing['label'],
                    'status' => 'pending',
                    'textColor' => $cst->c_text,
                    'bgColor' => $cst->c_bg,
                    'periodCode' => $scheduleItem->period_code,
                    'type' => 'period'
                ];
            } else {
                // Handle empty periods
                $events[] = [
                    'id' => uniqid('empty_'),
                    'title' => 'Free Period',
                    'classroom' => null,
                    'location' => null,
                    'from' => $timing['timeSlots'][0]['from'],
                    'to' => $timing['timeSlots'][0]['to'],
                    'timeSlots' => $timing['timeSlots'],
                    'label' => $timing['label'],
                    'status' => 'pending',
                    'textColor' => '#000000',
                    'bgColor' => '#e2e8f0',
                    'periodCode' => $periodCode,
                    'type' => 'free'
                ];
            }
        }

        return collect($events)->sortBy('from')->values()->all();
    }
}
