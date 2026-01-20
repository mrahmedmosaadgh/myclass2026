<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleTeacherController extends Controller
{
    // public function index()
    // {
    //     return Inertia::render('my_class/admin/Schedules/Index', [
    //         'records' => [],
    //         'options' => [],
    //         'active_copy' => null,
    //         'error' => 'No active schedule copy found. Please activate one copy.'
    //     ]);
    // }

    public function getTeacherScheduleData(Request $request, $school_id, $teacher_id)
    {
        try {
            // Get teacher info
            $teacher = Teacher::find($teacher_id);

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher not found'
                ], 404);
            }

            // Get teacher's schedule data
            // Filter by school_id and active=true
            $scheduleData = Schedule::with(['cst.subject', 'cst.classroom'])
                ->whereHas('cst', function($query) use ($teacher_id) {
                    $query->where('teacher_id', $teacher_id);
                })
                ->where('school_id', $school_id)
                ->where('active', true)
                ->get();

            // Format data for the grid display
            $formattedData = [];

            foreach ($scheduleData as $item) {

                $formattedData[$item->period_code] = [
                    'id' => $item->id,
                    'subject' => $item->cst->subject->name ?? 'Unknown Subject',
                    'subject_cute' => $item->cst->subject->short_name ?? substr($item->cst->subject->name ?? 'Unknown', 0, 10),
                    'classroom' => $item->cst->classroom->name ?? 'Unknown Classroom',
                    'c_text' => $item->cst->c_text  ,
                    'c_bg' => $item->cst->c_bg  ,

                    'period_code' => $item->period_code
                ];
            }

            return response()->json([
                'success' => true,
                'teacher_data' => $formattedData,
                'teacher_name' => $teacher->name,
                'message' => 'Teacher schedule data retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher schedule: ' . $e->getMessage()
            ], 500);
        }
    }
}
