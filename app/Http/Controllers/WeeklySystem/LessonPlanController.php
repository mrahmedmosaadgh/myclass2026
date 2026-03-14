<?php

namespace App\Http\Controllers\WeeklySystem;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\CurriculumLessonPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonPlanController extends Controller
{
    /**
     * Check if a lesson plan is locked for editing.
     */
    private function isLocked(CurriculumLessonPlan $plan): bool
    {
        // 1. Status lock (only Draft is editable)
        if ($plan->status !== 0) return true;
        
        // 2. Date lock (curriculum edit_lock_date passed)
        $curriculum = Curriculum::find($plan->curriculum_id);
        if ($curriculum?->edit_lock_date && now()->startOfDay()->gte($curriculum->edit_lock_date)) {
            return true;
        }
        
        return false;
    }

    /**
     * Display a listing of lesson plans.
     */
    public function index(Request $request)
    {
        $query = CurriculumLessonPlan::where('school_id', $request->user()->school_id);
        
        // Filter by curriculum if provided
        if ($request->has('curriculum_id')) {
            $query->where('curriculum_id', $request->curriculum_id);
        }
        
        // If teacher is viewing, only show their plans
        if ($request->has('teacher_only') && $request->teacher_only == 'true') {
            $query->where('teacher_id', $request->user()->id); // Assuming user ID is teacher ID here
        }

        $plans = $query->with(['teacher', 'subject', 'grade'])->get();
        return response()->json($plans);
    }

    /**
     * Store a newly created lesson plan in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'curriculum_id' => 'required|exists:curricula,id',
            'subject_id' => 'required|exists:subjects,id',
            'grade_id' => 'required|exists:grades,id',
            'curriculum_lesson_id' => 'nullable|exists:curriculum_lessons,id',
            'planning_week_id' => 'nullable|exists:planning_weeks,id',
            'title' => 'required|string|max:255',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'cw' => 'nullable|string',
            'objectives' => 'nullable|string',
            'resources' => 'nullable|array',
            'hw' => 'nullable|string',
            'planned_date' => 'nullable|date',
            'status' => 'nullable|integer',
        ]);

        // Check date lock on curriculum before allowing creation
        $curriculum = Curriculum::find($request->curriculum_id);
        if ($curriculum?->edit_lock_date && now()->startOfDay()->gte($curriculum->edit_lock_date)) {
            return response()->json(['message' => 'Editing is closed for this curriculum.'], 403);
        }

        $plan = CurriculumLessonPlan::create(array_merge($request->all(), [
            'school_id' => $request->user()->school_id,
            'teacher_id' => $request->user()->id, // Assuming user ID maps to teacher ID
            'status' => $request->status ?? 0, // Default to draft
        ]));

        return response()->json(['message' => 'Lesson plan created successfully.', 'plan' => $plan], 201);
    }

    /**
     * Update the specified lesson plan in storage.
     */
    public function update(Request $request, $id)
    {
        $plan = CurriculumLessonPlan::where('school_id', $request->user()->school_id)->findOrFail($id);

        if ($this->isLocked($plan)) {
            return response()->json(['message' => 'This plan is locked and cannot be edited.'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'curriculum_lesson_id' => 'nullable|exists:curriculum_lessons,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'cw' => 'nullable|string',
            'objectives' => 'nullable|string',
            'resources' => 'nullable|array',
            'hw' => 'nullable|string',
            'planned_date' => 'nullable|date',
            'status' => 'nullable|integer',
        ]);

        $plan->update($request->all());

        return response()->json(['message' => 'Lesson plan updated successfully.', 'plan' => $plan]);
    }

    /**
     * Remove the specified lesson plan from storage.
     */
    public function destroy(Request $request, $id)
    {
        $plan = CurriculumLessonPlan::where('school_id', $request->user()->school_id)->findOrFail($id);

        if ($this->isLocked($plan)) {
            return response()->json(['message' => 'This plan is locked and cannot be deleted.'], 403);
        }

        $plan->delete();

        return response()->json(['message' => 'Lesson plan deleted successfully.']);
    }

    /**
     * Admin tracking to see which teachers submitted plans for a given curriculum.
     */
    public function progress(Request $request)
    {
        $request->validate([
            'curriculum_id' => 'required|exists:curricula,id'
        ]);

        $schoolId = $request->user()->school_id;
        $curriculumId = $request->curriculum_id;

        // Get all teachers in the school
        $teachers = DB::table('teachers')
            ->where('school_id', $schoolId)
            ->whereNull('deleted_at')
            ->select('id', 'name')
            ->get();

        // Get teachers who have submitted plans for this curriculum
        $submittedTeacherIds = DB::table('curriculum_lesson_plans')
            ->where('school_id', $schoolId)
            ->where('curriculum_id', $curriculumId)
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('teacher_id')
            ->toArray();

        $submitted = [];
        $notSubmitted = [];

        foreach ($teachers as $teacher) {
            if (in_array($teacher->id, $submittedTeacherIds)) {
                $submitted[] = $teacher;
            } else {
                $notSubmitted[] = $teacher;
            }
        }

        return response()->json([
            'submitted' => $submitted,
            'not_submitted' => $notSubmitted
        ]);
    }
}
