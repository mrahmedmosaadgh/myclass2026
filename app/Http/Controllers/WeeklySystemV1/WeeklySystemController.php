<?php

namespace App\Http\Controllers\WeeklySystemV1;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\Teacher;
use App\Models\School;
use App\Services\WeeklySystemV1\CurriculumService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class WeeklySystemController extends Controller
{
    protected CurriculumService $curriculumService;

    public function __construct(CurriculumService $curriculumService)
    {
        $this->curriculumService = $curriculumService;
    }
    /**
     * Get authenticated teacher's ID
     */
    protected function getTeacherId(): ?int
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        return $teacher?->id;
    }

    /**
     * Dashboard - renders different views based on user role
     * 
     * This is the main entry point for the weekly system.
     * Admins see management dashboard, teachers see planning dashboard.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }
        
        // === ADMIN PATH ===
        if ($user->hasRole('school-admin')) {
            return Inertia::render(
                'myclass2026/features/weekly_system_v1/dashboards/AdminDashboard',
                [
                    'schoolName' => $user->school->name ?? 'My School',
                    'canManageCurriculum' => true,
                    'canManageWeeklyPlans' => true,
                    'canManageTimetable' => true,
                ]
            );
        }
        
        // === TEACHER PATH ===
        if ($user->hasRole('teacher')) {
            $teacher = $user->teacher;
            
            if (!$teacher) {
                abort(403, 'User does not have a teacher profile');
            }
            
            // Get teacher's assigned classes count
            $assignedCount = $teacher->classroomSubjectTeachers()
                ->with(['classroom', 'subject'])
                ->count();
            
            return Inertia::render(
                'myclass2026/features/weekly_system_v1/dashboards/TeacherDashboard',
                [
                    'teacherName' => $teacher->name,
                    'assignedClassesCount' => $assignedCount,
                    'canViewCurriculum' => true,
                    'canEditWeeklyPlans' => true,
                ]
            );
        }
        
        // === DEFAULT/FALLBACK ===
        abort(403, 'Unauthorized access to weekly system');
    }

    /**
     * Curriculum Lessons Index - Diverging response based on role
     * 
     * Single route, single controller method, different Inertia renders per role
     */
    public function curriculumLessonsIndex(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }
        
        // === ADMIN PATH: School-wide curriculum management ===
        if ($user->hasRole('school-admin')) {
            return $this->renderAdminCurriculumView($user);
        }
        
        // === TEACHER PATH: Assigned curricula only ===
        if ($user->hasRole('teacher')) {
            return $this->renderTeacherCurriculumView($user);
        }
        
        abort(403, 'Unauthorized access');
    }

    /**
     * Admin view: School-wide curriculum management
     */
    private function renderAdminCurriculumView($user)
    {
        $schoolId = $user->school_id;
        
        // Load ALL school curricula with relationships
        $curricula = Curriculum::with(['grade', 'subject'])
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'description' => $c->description,
                'grade_name' => $c->grade?->name ?? 'N/A',
                'subject_name' => $c->subject?->name ?? 'N/A',
                'edit_lock_date' => $c->edit_lock_date?->format('Y-m-d'),
                'created_at' => $c->created_at->format('Y-m-d'),
            ]);
        
        return Inertia::render(
            'myclass2026/features/weekly_system_v1/curriculum_lessons/AdminCurriculumView',
            [
                'curricula' => $curricula,
                'canCreate' => true,
                'canEdit' => true,
                'canDelete' => true,
                'canSetLockDates' => true,
                'schoolName' => $user->school->name ?? 'School',
            ]
        );
    }

    /**
     * Teacher view: Only assigned curricula
     */
    private function renderTeacherCurriculumView($user)
    {
        $teacher = $user->teacher;
        
        if (!$teacher) {
            abort(403, 'User does not have a teacher profile');
        }
        
        // Get the IDs of classrooms and subjects this teacher teaches
        $teacherAssignments = $teacher->classroomSubjectTeachers()
            ->with(['classroom', 'subject'])
            ->get();
        
        // Extract unique grade_ids from classrooms
        $gradeIds = $teacherAssignments->pluck('classroom.grade_id')->filter()->unique()->values();
        
        // Extract unique subject_ids
        $subjectIds = $teacherAssignments->pluck('subject_id')->filter()->unique()->values();
        
        // Load curricula that match teacher's grades OR subjects
        $curricula = Curriculum::with(['grade', 'subject'])
            ->where(function($query) use ($gradeIds, $subjectIds) {
                if ($gradeIds->isNotEmpty()) {
                    $query->whereIn('grade_id', $gradeIds);
                }
                if ($subjectIds->isNotEmpty()) {
                    $query->orWhereIn('subject_id', $subjectIds);
                }
            })
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'description' => $c->description,
                'grade_name' => $c->grade?->name ?? 'N/A',
                'subject_name' => $c->subject?->name ?? 'N/A',
                'edit_lock_date' => $c->edit_lock_date?->format('Y-m-d'),
                'isEditable' => $c->edit_lock_date?->isFuture() ?? false,
            ]);
        
        return Inertia::render(
            'myclass2026/features/weekly_system_v1/curriculum_lessons/TeacherCurriculumView',
            [
                'curricula' => $curricula,
                'canCreate' => false, // Teachers cannot create school curricula
                'canEdit' => true,    // Can edit their assigned ones
                'canDelete' => false,
                'canSetLockDates' => false,
                'teacherName' => $teacher->name,
            ]
        );
    }

    /**
     * Weekly Plans Manager - Admin sees all, Teacher sees own
     */
    public function weeklyPlansManager(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }
        
        if ($user->hasRole('school-admin')) {
            return $this->renderAdminWeeklyPlansView($user);
        }
        
        if ($user->hasRole('teacher')) {
            return $this->renderTeacherWeeklyPlansView($user);
        }
        
        abort(403);
    }

    /**
     * Admin Weekly Plans View - All teachers' plans
     */
    private function renderAdminWeeklyPlansView($user)
    {
        $teachers = Teacher::with('user')
            ->where('school_id', $user->school_id)
            ->orderBy('name')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'email' => $t->user?->email,
            ]);
        
        return Inertia::render(
            'myclass2026/features/weekly_system_v1/weekly_plans/AdminWeeklyPlansManager',
            [
                'allTeachers' => $teachers,
                'canViewAll' => true,
                'canBulkCopy' => true,
                'canViewStats' => true,
            ]
        );
    }

    /**
     * Teacher Weekly Plans View - Own plans only
     */
    private function renderTeacherWeeklyPlansView($user)
    {
        $teacher = $user->teacher;
        
        if (!$teacher) {
            abort(403, 'No teacher profile');
        }
        
        $assignments = $teacher->classroomSubjectTeachers()
            ->with(['classroom', 'subject'])
            ->get()
            ->map(fn($cst) => [
                'id' => $cst->id,
                'classroom_name' => $cst->classroom?->name ?? 'N/A',
                'subject_name' => $cst->subject?->name ?? 'N/A',
            ]);
        
        return Inertia::render(
            'myclass2026/features/weekly_system_v1/weekly_plans/TeacherWeeklyPlansEditor',
            [
                'myAssignments' => $assignments,
                'canEditOwn' => true,
                'canCopyBetweenClasses' => true,
            ]
        );
    }

    /**
     * My Weekly Plans (Teacher only endpoint)
     */
    public function myWeeklyPlans(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }
        
        if (!$user->hasRole('teacher')) {
            abort(403, 'Teachers only');
        }
        
        $teacher = $user->teacher;
        if (!$teacher) {
            abort(403, 'No teacher profile');
        }
        
        return redirect()->route('weekly-system-v1.weekly-plans-manager');
    }

    // ========================================================================
    // API ENDPOINTS (for frontend components)
    // ========================================================================

    /**
     * Get curricula API - returns data based on user role
     */
    public function getCurriculaApi(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        if ($user->hasRole('school-admin')) {
            // Admin gets all school curricula
            $curricula = Curriculum::with(['grade', 'subject'])
                ->where('school_id', $user->school_id)
                ->orderBy('name')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $curricula
            ]);
        }
        
        if ($user->hasRole('teacher')) {
            // Teacher gets only assigned curricula
            $teacher = $user->teacher;
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'No teacher profile'
                ], 403);
            }
            
            $curricula = Curriculum::with(['grade', 'subject'])
                ->whereHas('classroomSubjectTeachers', function($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id);
                })
                ->orderBy('name')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $curricula
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }
}
