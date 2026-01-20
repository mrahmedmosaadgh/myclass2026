<?php

namespace App\Http\Controllers;

use App\Models\Behavior;
use Illuminate\Http\Request;

class BehaviorController extends Controller
{
    /**
     * Get current teacher ID if user is a teacher
     */
    protected function getTeacherId(): ?int
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }
        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
        return $teacher?->id;
    }

    /**
     * Get behaviors for current user (school defaults + teacher custom if teacher)
     */
    public function index(Request $request)
    {
        $schoolId = $request->input('school_id', 1); // Get from context/session in production
        $teacherId = $this->getTeacherId();
        
        $behaviors = Behavior::forTeacher($schoolId, $teacherId)
            ->with('teacher')
            ->orderBy('type')
            ->orderBy('name')
            ->get();
            
        return response()->json($behaviors);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:positive,negative',
            'points' => 'required|integer',
            'school_id' => 'required|integer|exists:schools,id',
            'year_id' => 'nullable|integer',
        ]);

        // Ensure we associate with the correct active academic year
        $school = \App\Models\School::find($validated['school_id']);
        if ($school) {
            $validated['year_id'] = $school->academic_year_id;
        }

        // Determine teacher_id based on user role
        $user = auth()->user();
        
        // If user is not authenticated, default to null (school-wide)
        if (!$user) {
            $validated['teacher_id'] = null;
        } elseif (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('super-admin'))) {
            // Admin creates school-wide defaults
            $validated['teacher_id'] = null;
        } else {
            // Teacher creates personal behaviors
            $validated['teacher_id'] = $this->getTeacherId();
        }

        $exists = Behavior::where('name', $validated['name'])
            ->where('type', $validated['type'])
            ->where('school_id', $validated['school_id'])
            ->where('year_id', $validated['year_id'])
            ->where('teacher_id', $validated['teacher_id'])
            ->first();

        if ($exists) {
            return response()->json(['message' => 'Behavior already exists.'], 409);
        }

        $behavior = Behavior::create($validated);
        return response()->json($behavior, 201);
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'behaviors' => 'required|array|min:1|max:50',
            'behaviors.*.name' => 'required|string|max:255',
            'behaviors.*.name_ar' => 'nullable|string|max:255',
            'behaviors.*.type' => 'required|in:positive,negative',
            'behaviors.*.points' => 'required|integer',
            'behaviors.*.description' => 'nullable|string|max:500',
            'school_id' => 'required|integer|exists:schools,id',
        ]);

        $school = \App\Models\School::find($validated['school_id']);
        $yearId = $school ? $school->academic_year_id : null;

        // Determine teacher_id based on user role
        $user = auth()->user();
        $teacherId = null;
        
        if ($user && !method_exists($user, 'hasRole') || (!$user->hasRole('admin') && !$user->hasRole('super-admin'))) {
            $teacherId = $this->getTeacherId();
        }

        $created = [];
        $skipped = [];
        $errors = [];

        foreach ($validated['behaviors'] as $index => $behaviorData) {
            try {
                // Check for duplicates
                $exists = Behavior::where('name', $behaviorData['name'])
                    ->where('type', $behaviorData['type'])
                    ->where('school_id', $validated['school_id'])
                    ->where('year_id', $yearId)
                    ->where('teacher_id', $teacherId)
                    ->first();

                if ($exists) {
                    $skipped[] = [
                        'index' => $index,
                        'name' => $behaviorData['name'],
                        'reason' => 'Already exists'
                    ];
                    continue;
                }

                $behavior = Behavior::create([
                    'name' => $behaviorData['name'],
                    'name_ar' => $behaviorData['name_ar'] ?? null,
                    'type' => $behaviorData['type'],
                    'points' => $behaviorData['points'],
                    'description' => $behaviorData['description'] ?? null,
                    'school_id' => $validated['school_id'],
                    'year_id' => $yearId,
                    'teacher_id' => $teacherId,
                ]);

                $created[] = $behavior;
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'name' => $behaviorData['name'] ?? 'Unknown',
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'summary' => [
                'total' => count($validated['behaviors']),
                'created' => count($created),
                'skipped' => count($skipped),
                'errors' => count($errors)
            ]
        ], 201);
    }

    public function update(Request $request, Behavior $behavior)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'sometimes|required|in:positive,negative',
            'points' => 'sometimes|required|integer',
        ]);

        // Only allow editing if user is admin (for school defaults) or owns the behavior (for teacher custom)
        $teacherId = $this->getTeacherId();
        $user = auth()->user();
        
        // If no user is authenticated, allow editing (for development/testing)
        // In production, you should enforce authentication
        if ($user) {
            if ($behavior->teacher_id === null) {
                // School default - only admin can edit
                if (!method_exists($user, 'hasRole') || (!$user->hasRole('admin') && !$user->hasRole('super-admin'))) {
                    return response()->json(['message' => 'Unauthorized to edit school defaults.'], 403);
                }
            } else {
                // Teacher custom - only owner can edit
                if ($behavior->teacher_id !== $teacherId) {
                    return response()->json(['message' => 'Unauthorized to edit this behavior.'], 403);
                }
            }
        }

        $behavior->update($validated);
        return response()->json($behavior);
    }

    public function destroy(Behavior $behavior)
    {
        // Same authorization logic as update
        $teacherId = $this->getTeacherId();
        $user = auth()->user();
        
        // If no user is authenticated, allow deleting (for development/testing)
        // In production, you should enforce authentication
        if ($user) {
            if ($behavior->teacher_id === null) {
                if (!method_exists($user, 'hasRole') || (!$user->hasRole('admin') && !$user->hasRole('super-admin'))) {
                    return response()->json(['message' => 'Unauthorized to delete school defaults.'], 403);
                }
            } else {
                if ($behavior->teacher_id !== $teacherId) {
                    return response()->json(['message' => 'Unauthorized to delete this behavior.'], 403);
                }
            }
        }

        $behavior->delete();
        return response()->json(['message' => 'Behavior deleted']);
    }
}
