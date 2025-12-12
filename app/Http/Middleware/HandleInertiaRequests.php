<?php

namespace App\Http\Middleware;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */

public function share(Request $request): array
{
    $user = $request->user();
    
    return array_merge(parent::share($request), [
        'csrf_token' => csrf_token(),
        
        // Segmented user context for offline-first caching (7-day expiry)
        'user_context' => $user ? [
            'user_profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_role' => $user->role,
            ],
            'user_permissions' => [
                'roles' => $this->getUserRoles($user),
            ],
            'user_school' => [
                'school' => $this->getUserSchool($user),
                'schools' => $this->getUserSchool($user),
            ],
            'user_classroom' => [
                'teacher' => $this->getUserTeacher($user),
                'classroom' => $this->getUserClassroom($user),
            ],
            // 'user_schedule' => [
            //     'schedule' => $this->getUserSchedule($user),
            // ],
        ] : null,
        
        // Backward compatibility - keep original auth structure
        'auth' => [
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'user_role' => $user->role,
                'email' => $user->email,
                'roles' => $this->getUserRoles($user),
                'school' => $this->getUserSchool($user),
                'schools' => $this->getUserSchool($user),
                'teacher' => $this->getUserTeacher($user),
                'classroom' => $this->getUserClassroom($user),
            ] : null,
        ],
        
        // Context metadata for cache management (7 days expiry)
        'context_meta' => [
            'user_profile' => ['expires_at' => now()->addDays(7)->toISOString()],
            'user_permissions' => ['expires_at' => now()->addDays(7)->toISOString()],
            'user_school' => ['expires_at' => now()->addDays(7)->toISOString()],
            'user_classroom' => ['expires_at' => now()->addDays(7)->toISOString()],
            'user_schedule' => ['expires_at' => now()->addDays(7)->toISOString()],
        ],
    ]);
}



    public function share2(Request $request): array
    {
        return array_merge(parent::share($request), [
            'csrf_token' => csrf_token(),
            'auth' => [
                'user' => $request->user() ?
                    [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'user_role' => $request->user()->role,
                        'email' => $request->user()->email,
                        'roles' => $this->getUserRoles($request->user()),
                        'school' => $this->getUserSchool($request->user()),
                        'schools' => $this->getUserSchool($request->user()),
                        'teacher' => $this->getUserTeacher($request->user()),
                        'classroom' => $this->getUserClassroom($request->user()),
                        // 'schedule' => $this->getUserSchedule($request->user()),
                    ] : null,
            ],
            // ... other shared data
        ]);
    }





    private function getUserTeacher($user)
    {
        if (!$user) return null;
        if ($user->getRoleNames()->contains('teacher') || $user->role === 'teacher') {
            return  $teacher = \App\Models\Teacher::where('user_id', $user->id)
                ->with(['school'])
                ->first();
        }
    }

    private function getUserSchool($user)
{
    if (!$user) {
        return [];
    }

    // For teachers
    if ($user->getRoleNames()->contains('teacher') || $user->role === 'teacher') {
        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return [];
        }

        // Combine primary school ID with extra school IDs
        $schoolIds = array_unique(
            array_merge(
                [$teacher->school_id],
                $teacher->school_extra_ids ?? []
            )
        );

        return \App\Models\School::whereIn('id', $schoolIds)
            ->get()
            ->map(function($school) {
                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'is_active' => $school->is_active ?? true
                ];
            })
            ->toArray();
    }

    // For students
    if ($user->role === 'student') {
        $student = \App\Models\Student::where('user_id', $user->id)
            ->with('school')
            ->first();

        return $student && $student->school ? [[
            'id' => $student->school->id,
            'name' => $student->school->name,
            'is_active' => $student->school->is_active ?? true
        ]] : [];
    }

    return [];
}
    private function getUserClassroom($user)
    {
        if (!$user) return null;

        if ($user->getRoleNames()->contains('teacher') || $user->role === 'teacher') {
            $my_teacher = Teacher::where('user_id', $user->id)->first();
            if (!$my_teacher) return null;

            $classroom = \App\Models\ClassroomSubjectTeacher::where('teacher_id', $my_teacher->id)
                ->with(['classroom'])
                ->get()
                ->pluck('classroom')
                ->unique();

            return $classroom;
        }

        if ($user->role === 'student') {
            $student = \App\Models\Student::where('user_id', $user->id)
                ->with(['classroom' => function ($query) {
                    $query->select('id', 'name', 'school_id');
                }])
                ->first();
            return $student ? $student->classroom : null;
        }

        return null;
    }

    private function getUserSchedule($user)
    {
        if (!$user) return null;

        if ($user->role === 'teacher') {
            // Get the teacher record
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
            if (!$teacher) return null;

            // Get active academic year and schedule copy
            $activeYear = \App\Models\AcademicYear::where('active', true)
                ->where('school_id', $teacher->school_id)
                ->first();

            $activeCopy = \App\Models\ScheduleCopy::where('active', true)
                ->where('school_id', $teacher->school_id)
                ->first();

            if (!$activeYear || !$activeCopy) return null;

            // Get all CSTs for this teacher in active academic year
            $cstIds = \App\Models\ClassroomSubjectTeacher::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->where('school_id', $teacher->school_id)
                ->pluck('id');

            // Get schedules for these CSTs
            return \App\Models\Schedule::whereIn('cst_id', $cstIds)
                ->where('copy_id', $activeCopy->id)
                ->where('active', true)
                ->with([
                    'cst.classroom' => function ($query) {
                        $query->select('id', 'name', 'grade_id');
                    },
                    'cst.classroom.grade' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'cst.subject' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'cst.teacher' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'periodDetail'
                ])
                ->orderBy('day')
                ->orderBy('period_order')
                ->get();
        }

        if ($user->role === 'student') {
            $student = \App\Models\Student::where('user_id', $user->id)->first();
            if (!$student) return null;

            // Get active academic year and schedule copy
            $activeYear = \App\Models\AcademicYear::where('active', true)
                ->where('school_id', $student->school_id)
                ->first();

            $activeCopy = \App\Models\ScheduleCopy::where('active', true)
                ->where('school_id', $student->school_id)
                ->first();

            if (!$activeYear || !$activeCopy) return null;

            // Get all CSTs for student's classroom in active academic year
            $cstIds = \App\Models\ClassroomSubjectTeacher::where('classroom_id', $student->classroom_id)
                ->where('academic_year_id', $activeYear->id)
                ->where('school_id', $student->school_id)
                ->pluck('id');

            // Get schedules for these CSTs
            return \App\Models\Schedule::whereIn('cst_id', $cstIds)
                ->where('copy_id', $activeCopy->id)
                ->where('active', true)
                ->with([
                    'cst.classroom',
                    'cst.subject',
                    'cst.teacher',
                    'periodDetail'
                ])
                ->orderBy('day')
                ->orderBy('period_order')
                ->get();
        }

        return null;
    }
    private function getUserRoles($user)
    {
        if (!$user) return [];

        $roles = $user->getRoleNames()->toArray();

        // Auto-assign 'student' role if user exists in students table
        if (!in_array('student', $roles)) {
            if (\App\Models\Student::where('user_id', $user->id)->exists()) {
                $roles[] = 'student';
            }
        }

        // Auto-assign 'teacher' role if user exists in teachers table
        if (!in_array('teacher', $roles)) {
            if (\App\Models\Teacher::where('user_id', $user->id)->exists()) {
                $roles[] = 'teacher';
            }
        }

        return array_unique($roles);
    }
}
