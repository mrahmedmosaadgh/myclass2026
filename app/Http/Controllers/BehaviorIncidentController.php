<?php

namespace App\Http\Controllers;

use App\Models\BehaviorIncident;
use App\Models\Student;
use App\Models\Behavior;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BehaviorIncidentController extends Controller
{
    /**
     * Display a listing of behavior incidents
     */
    public function index(Request $request): JsonResponse
    {
        $query = BehaviorIncident::with(['student', 'classroom', 'reportedBy']);

        // Filter by classroom
        if ($request->has('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        // Filter by student
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('occurred_at', $request->date);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('occurred_at', [$request->start_date, $request->end_date]);
        }

        // Filter by severity
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Get current teacher's school
        $user = auth()->user();
        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
        
        if ($teacher) {
            $query->where('school_id', $teacher->school_id);
        }

        $incidents = $query->orderBy('occurred_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $incidents->items(),
            'meta' => [
                'current_page' => $incidents->currentPage(),
                'last_page' => $incidents->lastPage(),
                'per_page' => $incidents->perPage(),
                'total' => $incidents->total(),
            ]
        ]);
    }

    /**
     * Store a newly created behavior incident
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'student_name' => 'required|string|max:100',
            'grade' => 'nullable|integer|min:0|max:12',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'period_code' => 'nullable|string|max:20',
            'incident_type' => 'required|array',
            'location' => 'required|array',
            'behavior' => 'required|array',
            'description' => 'nullable|array',
            'motivation' => 'nullable|array',
            'others_involved' => 'nullable|array',
            'teacher_action' => 'nullable|array',
            'admin_action' => 'nullable|array',
            'severity' => 'nullable|in:minor,moderate,major',
            'follow_up_needed' => 'nullable|boolean',
            'critical_alert' => 'nullable|boolean',
        ]);

        try {
            $user = auth()->user();
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
            
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not a teacher'
                ], 422);
            }

            $year = \App\Models\AcademicYear::where('active', 1)->first();
            
            // Determine severity from incident type if not provided
            $severity = $validated['severity'] ?? 'minor';
            if (isset($validated['incident_type']['en'])) {
                if (in_array(strtolower($validated['incident_type']['en']), ['major', 'سلوك كبير'])) {
                    $severity = 'major';
                }
            }

            // Extract primary codes for fast filtering
            $primaryBehaviorCode = null;
            $primaryLocationCode = null;
            
            if (isset($validated['behavior'][0]['code'])) {
                $primaryBehaviorCode = $validated['behavior'][0]['code'];
            } elseif (isset($validated['behavior']['code'])) {
                $primaryBehaviorCode = $validated['behavior']['code'];
            }
            
            if (isset($validated['location']['code'])) {
                $primaryLocationCode = $validated['location']['code'];
            }

            $incident = BehaviorIncident::create([
                'school_id' => $teacher->school_id,
                'student_id' => $validated['student_id'],
                'student_name' => $validated['student_name'],
                'grade' => $validated['grade'] ?? null,
                'classroom_id' => $validated['classroom_id'] ?? null,
                'period_code' => $validated['period_code'] ?? null,
                'occurred_at' => now(),
                'incident_type' => $validated['incident_type'],
                'location' => $validated['location'],
                'behavior' => $validated['behavior'],
                'description' => $validated['description'] ?? null,
                'motivation' => $validated['motivation'] ?? null,
                'others_involved' => $validated['others_involved'] ?? null,
                'teacher_action' => $validated['teacher_action'] ?? null,
                'admin_action' => $validated['admin_action'] ?? null,
                'primary_behavior_code' => $primaryBehaviorCode,
                'primary_location_code' => $primaryLocationCode,
                'severity' => $severity,
                'status' => 'open',
                'follow_up_needed' => $validated['follow_up_needed'] ?? false,
                'critical_alert' => $validated['critical_alert'] ?? false,
                'points_deducted' => 1, // Default -1 point per incident
                'points_awarded' => 0,
                'visible_to_parent' => true,
                'created_by' => $user->id,
                'reported_by' => $user->id,
                'school_year_id' => $year->id ?? null,
                'submitted_via' => 'web',
                'device_ip' => $request->ip(),
            ]);

            // Apply -1 point to student's behavior record
            $this->applyPointDeduction($validated['student_id'], $teacher->school_id, $year->id ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Behavior incident recorded successfully',
                'data' => $incident->load(['student', 'classroom', 'reportedBy'])
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating behavior incident: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create behavior incident',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified behavior incident
     */
    public function show(BehaviorIncident $behaviorIncident): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $behaviorIncident->load(['student', 'classroom', 'reportedBy', 'reviewedBy'])
        ]);
    }

    /**
     * Update the specified behavior incident
     */
    public function update(Request $request, BehaviorIncident $behaviorIncident): JsonResponse
    {
        $validated = $request->validate([
            'incident_type' => 'nullable|array',
            'location' => 'nullable|array',
            'behavior' => 'nullable|array',
            'description' => 'nullable|array',
            'motivation' => 'nullable|array',
            'others_involved' => 'nullable|array',
            'teacher_action' => 'nullable|array',
            'admin_action' => 'nullable|array',
            'severity' => 'nullable|in:minor,moderate,major',
            'status' => 'nullable|in:open,in_review,resolved,closed',
            'follow_up_needed' => 'nullable|boolean',
            'critical_alert' => 'nullable|boolean',
        ]);

        try {
            $behaviorIncident->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Behavior incident updated successfully',
                'data' => $behaviorIncident->fresh()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating behavior incident: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update behavior incident',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified behavior incident
     */
    public function destroy(BehaviorIncident $behaviorIncident): JsonResponse
    {
        try {
            $behaviorIncident->delete();

            return response()->json([
                'success' => true,
                'message' => 'Behavior incident deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting behavior incident: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete behavior incident',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student behavior report
     */
    public function studentReport(Request $request, $studentId): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $query = BehaviorIncident::where('student_id', $studentId);

        if (isset($validated['start_date']) && isset($validated['end_date'])) {
            $query->whereBetween('occurred_at', [$validated['start_date'], $validated['end_date']]);
        }

        $incidents = $query->orderBy('occurred_at', 'desc')->get();

        $summary = [
            'total_incidents' => $incidents->count(),
            'minor' => $incidents->where('severity', 'minor')->count(),
            'moderate' => $incidents->where('severity', 'moderate')->count(),
            'major' => $incidents->where('severity', 'major')->count(),
            'total_points_deducted' => $incidents->sum('points_deducted'),
            'total_points_awarded' => $incidents->sum('points_awarded'),
            'net_points' => $incidents->sum('points_awarded') - $incidents->sum('points_deducted'),
            'follow_up_needed' => $incidents->where('follow_up_needed', true)->count(),
            'critical_alerts' => $incidents->where('critical_alert', true)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'incidents' => $incidents
            ]
        ]);
    }

    /**
     * Apply -1 point deduction to student's behavior record
     */
    private function applyPointDeduction($studentId, $schoolId, $yearId)
    {
        try {
            // Find or create a "Behavior Incident" behavior type
            $behavior = Behavior::firstOrCreate([
                'school_id' => $schoolId,
                'year_id' => $yearId,
                'name' => 'Behavior Incident',
                'type' => 'negative',
            ], [
                'points' => -1,
                'icon' => '⚠️'
            ]);

            // Apply the behavior using the existing reward system
            // This will be handled by the frontend calling the incident-recorded event
            
        } catch (\Exception $e) {
            \Log::error('Error applying point deduction: ' . $e->getMessage());
        }
    }
}
