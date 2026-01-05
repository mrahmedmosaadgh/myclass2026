<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Services\UserContextService;
use Illuminate\Http\Request;

class ContextController extends Controller
{
    protected $userContextService;

    public function __construct(UserContextService $userContextService)
    {
        $this->userContextService = $userContextService;
    }

    /**
     * Get the current active context for the authenticated user
     */
    public function getCurrentContext()
    {
        $context = $this->userContextService->resolveContext();
        
        if (empty($context)) {
            return response()->json([
                'error' => 'No context available'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $context
        ]);
    }

    /**
     * Update the active context for a school
     */
    public function updateContext(Request $request, School $school)
    {
        $validated = $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'schedule_copy_id' => 'nullable|exists:schedule_copies,id',
            'resolved_by' => 'nullable|exists:users,id'
        ]);

        $validated['resolved_by'] = $validated['resolved_by'] ?? auth()->id();
        $validated['resolved_at'] = now();

        $school->update($validated);

        return response()->json([
            'success' => true,
            'data' => $school->refresh()
        ]);
    }

    /**
     * Update contexts for all schools
     */
    public function updateAllSchoolsContext()
    {
        $results = $this->userContextService->updateAllSchoolsContext();
        
        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Set a school context as active
     */
    public function setActiveSchool(School $school)
    {
        // Check if user has access to this school
        $user = auth()->user();
        
        // For admin users, allow setting any school
        // For other users, verify they belong to this school
        if ($user->role !== 'admin' && !$this->userBelongsToSchool($user, $school)) {
            return response()->json([
                'error' => 'Unauthorized to set context for this school'
            ], 403);
        }

        // Update the school context
        $context = $this->userContextService->resolveContextForSchool($school);
        
        return response()->json([
            'success' => true,
            'data' => $context,
            'message' => "Context set for school: {$school->name}"
        ]);
    }

    /**
     * Check if a user belongs to a specific school
     */
    private function userBelongsToSchool($user, $school)
    {
        // This would depend on your user-school relationship implementation
        // For now, we'll check if the user has school_id matching the provided school
        return $user->school_id == $school->id;
    }
}