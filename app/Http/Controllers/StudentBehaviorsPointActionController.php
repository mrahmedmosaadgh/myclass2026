<?php

namespace App\Http\Controllers;

use App\Models\StudentBehaviorsPointAction;
use App\Models\StudentBehavior;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentBehaviorsPointActionController extends Controller
{
    /**
     * Display a listing of point actions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = StudentBehaviorsPointAction::query();

        // Filter by student behavior
        if ($request->has('student_behaviors_id')) {
            $query->where('student_behaviors_id', $request->student_behaviors_id);
        }

        // Filter by action type
        if ($request->has('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by reason (behavior)
        if ($request->has('reason_id')) {
            $query->where('reason_id', $request->reason_id);
        }

        // Include only active (not canceled) actions
        if ($request->boolean('active_only', false)) {
            $query->where('canceled', false);
        }

        $actions = $query->with(['studentBehavior', 'behavior', 'createdBy', 'canceledBy'])->paginate(25);

        return response()->json($actions);
    }

    /**
     * Store a new point action record.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_behaviors_id' => 'required|exists:student_behaviors,id',
            'value' => 'required|integer',
            'reason_id' => 'nullable|exists:behaviors,id',
            'note' => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();
        $data['action_type'] = $data['value'] > 0 ? 'plus' : 'minus';

        $action = StudentBehaviorsPointAction::create($data);

        return response()->json($action->load(['studentBehavior', 'behavior', 'createdBy']), 201);
    }

    /**
     * Display the specified point action.
     */
    public function show(StudentBehaviorsPointAction $action): JsonResponse
    {
        return response()->json(
            $action->load(['studentBehavior', 'behavior', 'createdBy', 'canceledBy'])
        );
    }

    /**
     * Update the specified point action.
     */
    public function update(Request $request, StudentBehaviorsPointAction $action): JsonResponse
    {
        $validated = $request->validate([
            'note' => 'nullable|string',
        ]);

        $action->update($validated);

        return response()->json($action->refresh());
    }

    /**
     * Cancel a point action.
     */
    public function cancel(Request $request, StudentBehaviorsPointAction $action): JsonResponse
    {
        $data = $request->validate([
            'cancel_reason' => 'required|string'
        ]);

        $action->update([
            'canceled' => true,
            'canceled_by' => auth()->id(),
            'canceled_at' => now(),
            'cancel_reason' => $data['cancel_reason'],
            'action_type' => 'cancel',
        ]);

        return response()->json([
            'message' => 'Point action canceled successfully',
            'action' => $action->refresh(),
        ]);
    }

    /**
     * Restore (un-cancel) a point action.
     */
    public function restore(StudentBehaviorsPointAction $action): JsonResponse
    {
        if (!$action->canceled) {
            return response()->json(['message' => 'Action is not canceled'], 400);
        }

        $action->update([
            'canceled' => false,
            'canceled_at' => null,
            'canceled_by' => null,
            'cancel_reason' => null,
        ]);

        return response()->json([
            'message' => 'Point action restored successfully',
            'action' => $action->refresh(),
        ]);
    }

    /**
     * Delete the specified point action.
     */
    public function destroy(StudentBehaviorsPointAction $action): JsonResponse
    {
        $action->delete();

        return response()->json(['message' => 'Point action deleted successfully']);
    }

    /**
     * Get statistics for a student behavior.
     */
    public function getStatistics(StudentBehavior $studentBehavior): JsonResponse
    {
        $actions = $studentBehavior->pointActions()->where('canceled', false)->get();

        $positive = $actions->where('value', '>', 0)->sum('value');
        $negative = $actions->where('value', '<', 0)->sum('value');

        return response()->json([
            'student_id' => $studentBehavior->student_id,
            'positive_points' => $positive,
            'negative_points' => abs($negative),
            'total_points' => $positive + $negative,
            'action_count' => $actions->count(),
            'actions_by_type' => $actions->groupBy('action_type')->map->count(),
        ]);
    }
}
