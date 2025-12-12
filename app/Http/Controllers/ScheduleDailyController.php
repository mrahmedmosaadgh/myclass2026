<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ScheduleDaily;
use App\Models\Schedule;
use App\Models\User;

class ScheduleDailyController extends Controller
{
    public function index()
    {
        $scheduleDailies = ScheduleDaily::with([
            'schedule.cst.subject',
            'schedule.cst.teacher',
            'teacherSubstitute'
        ])
            ->latest()
            ->paginate(10);

        return Inertia::render('my_class/admin/ScheduleDailies/Index', [
            'items' => [
                'data' => $scheduleDailies->items(),
                'meta' => [
                    'current_page' => $scheduleDailies->currentPage(),
                    'from' => $scheduleDailies->firstItem(),
                    'last_page' => $scheduleDailies->lastPage(),
                    'links' => $scheduleDailies->linkCollection()->toArray(),
                    'path' => $scheduleDailies->path(),
                    'per_page' => $scheduleDailies->perPage(),
                    'to' => $scheduleDailies->lastItem(),
                    'total' => $scheduleDailies->total(),
                ],
            ],
            'options' => [
                'schedules' => Schedule::with(['cst.subject', 'cst.teacher'])->get()
                    ->map(fn($schedule) => [
                        'id' => $schedule->id,
                        'name' => "{$schedule->cst->subject->name} - {$schedule->cst->classroom->name}- - {$schedule->cst->teacher->name}- {$schedule->period_order}- {$schedule->day}- {$schedule->period_number}"
                    ]),
                'teachers' => User::role('teacher')->get()
                    ->map(fn($teacher) => [
                        'id' => $teacher->id,
                        'name' => $teacher->name
                    ]),
                'statuses' => [
                    ['id' => 'draft', 'name' => 'Draft'],
                    ['id' => 'published', 'name' => 'Published'],
                    ['id' => 'archived', 'name' => 'Archived']
                ]
            ]
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'schedule_id' => 'required|exists:schedules,id',
                'date' => 'required|date',
                'teacher_substitute_id' => 'nullable|exists:users,id',
                'status' => 'required|in:draft,published,archived',
                'notes' => 'nullable|string|max:1000',
            ]);

            $scheduleDaily = ScheduleDaily::create($validated);

            return response()->json([
                'message' => 'Schedule daily created successfully',
                'record' => $scheduleDaily->load([
                    'schedule.cst.subject',
                    'schedule.cst.teacher',
                    'teacherSubstitute'
                ])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create schedule daily',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, ScheduleDaily $scheduleDaily)
    {
        try {
            $validated = $request->validate([
                'schedule_id' => 'required|exists:schedules,id',
                'date' => 'required|date',
                'teacher_substitute_id' => 'nullable|exists:users,id',
                'status' => 'required|in:draft,published,archived',
                'notes' => 'nullable|string|max:1000',
            ]);

            $scheduleDaily->update($validated);

            return response()->json([
                'message' => 'Schedule daily updated successfully',
                'record' => $scheduleDaily->fresh([
                    'schedule.cst.subject',
                    'schedule.cst.teacher',
                    'teacherSubstitute'
                ])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update schedule daily',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(ScheduleDaily $scheduleDaily)
    {
        try {
            $scheduleDaily->delete();

            return response()->json([
                'message' => 'Schedule daily deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete schedule daily',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}








