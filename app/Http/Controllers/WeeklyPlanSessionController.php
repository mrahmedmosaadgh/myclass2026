<?php

namespace App\Http\Controllers;

use App\Models\WeeklyPlanSession;
use App\Models\WeeklyPlan;
use App\Models\Teacher;
use App\Http\Requests\WeeklyPlanSessionRequest;
use App\Services\WeeklyPlanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class WeeklyPlanSessionController extends Controller
{
    /**
     * Get the authenticated teacher's ID
     */
    private function getTeacherId()
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }
        
        $teacher = Teacher::where('user_id', $user->id)->first();
        return $teacher ? $teacher->id : null;
    }

    /**
     * Display a listing of sessions for a weekly plan.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'weekly_plan_id' => 'required|exists:weekly_plans,id',
        ]);

        // Check authorization
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $weeklyPlan = WeeklyPlan::with('classroomSubjectTeacher')->findOrFail($request->weekly_plan_id);
        if ($weeklyPlan->classroomSubjectTeacher->teacher_id !== $teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sessions = WeeklyPlanSession::where('weekly_plan_id', $request->weekly_plan_id)
            ->orderBy('session_index')
            ->get();

        return response()->json($sessions);
    }

    /**
     * Store a newly created session.
     */
    public function store(WeeklyPlanSessionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $session = WeeklyPlanSession::create($validated);
        return response()->json($session, 201);
    }

    /**
     * Display the specified session.
     */
    public function show(WeeklyPlanSession $session): JsonResponse
    {
        // Check authorization
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $session->load('weeklyPlan.classroomSubjectTeacher');
        if ($session->weeklyPlan->classroomSubjectTeacher->teacher_id !== $teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($session);
    }

    /**
     * Update the specified session.
     */
    public function update(WeeklyPlanSessionRequest $request, WeeklyPlanSession $session): JsonResponse
    {
        $validated = $request->validated();
        $session->update($validated);
        return response()->json($session);
    }

    /**
     * Remove the specified session.
     */
    public function destroy(WeeklyPlanSession $session): JsonResponse
    {
        // Check authorization
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $session->load('weeklyPlan.classroomSubjectTeacher');
        if ($session->weeklyPlan->classroomSubjectTeacher->teacher_id !== $teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $weeklyPlanId = $session->weekly_plan_id;
        $sessionIndex = $session->session_index;
        
        DB::transaction(function () use ($session, $weeklyPlanId, $sessionIndex) {
            // Delete the session
            $session->delete();
            
            // Adjust session indices for remaining sessions
            WeeklyPlanSession::where('weekly_plan_id', $weeklyPlanId)
                ->where('session_index', '>', $sessionIndex)
                ->decrement('session_index');
        });

        return response()->json(null, 204);
    }

    /**
     * Reorder sessions within a weekly plan.
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'weekly_plan_id' => 'required|exists:weekly_plans,id',
            'sessions' => 'required|array',
            'sessions.*.id' => 'required|exists:weekly_plan_sessions,id',
            'sessions.*.session_index' => 'required|integer|min:1',
        ]);

        // Check authorization
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $weeklyPlan = WeeklyPlan::with('classroomSubjectTeacher')->findOrFail($validated['weekly_plan_id']);
        if ($weeklyPlan->classroomSubjectTeacher->teacher_id !== $teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated['sessions'] as $sessionData) {
                WeeklyPlanSession::where('id', $sessionData['id'])
                    ->where('weekly_plan_id', $validated['weekly_plan_id']) // Ensure session belongs to the weekly plan
                    ->update(['session_index' => $sessionData['session_index']]);
            }
        });

        $sessions = WeeklyPlanSession::where('weekly_plan_id', $validated['weekly_plan_id'])
            ->orderBy('session_index')
            ->get();

        return response()->json($sessions);
    }

    /**
     * Bulk update sessions data.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sessions' => 'required|array',
            'sessions.*.id' => 'required|exists:weekly_plan_sessions,id',
            'sessions.*.title' => 'sometimes|string|max:255',
            'sessions.*.type' => 'sometimes|in:lesson,quiz,exam,extra,note',
            'sessions.*.data' => 'sometimes|nullable|array',
        ]);

        // Check authorization first
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        DB::transaction(function () use ($validated, $teacherId) {
            foreach ($validated['sessions'] as $sessionData) {
                $session = WeeklyPlanSession::with('weeklyPlan.classroomSubjectTeacher')->find($sessionData['id']);
                
                // Check authorization for each session
                if ($session->weeklyPlan->classroomSubjectTeacher->teacher_id !== $teacherId) {
                    throw new \Exception('Unauthorized access to session ID: ' . $sessionData['id']);
                }
                
                $session->update(collect($sessionData)->except('id')->toArray());
            }
        });

        return response()->json(['message' => 'Sessions updated successfully']);
    }
}