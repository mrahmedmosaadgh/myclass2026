<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class PermissionsController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->where('id', '!=', auth()->id())->get();

        // Get enhanced user data with relationships
        $enhancedData = $this->getEnhancedUserData();

        return Inertia::render('my_class/super_admin/Admin/user_management', [
            'roles' => Role::with('permissions')
                ->where('name', '!=', 'super_admin')
                ->get(),
            'permissions' => Permission::where('name', '!=', 'manage app')->get(),
            'users' => $users,
            'enhancedUserData' => $enhancedData
        ]);
    }

    /**
     * Get enhanced user data with relationships for advanced search
     */
    private function getEnhancedUserData()
    {
        // Get all schools for filtering
        $schools = \App\Models\School::select('id', 'name')->get();

        // Get teachers with their subjects and schools
        $teachers = \App\Models\Teacher::with([
            'user.roles',
            'school:id,name',
            'classroomSubjectTeachers.subject:id,name',
            'classroomSubjectTeachers.classroom:id,name'
        ])->get()->map(function ($teacher) {
            return [
                'id' => $teacher->user_id,
                'type' => 'teacher',
                'name' => $teacher->name,
                'email' => $teacher->user->email ?? null,
                'roles' => $teacher->user->roles ?? [],
                'school_name' => $teacher->school->name ?? null,
                'school_id' => $teacher->school_id,
                'subjects' => $teacher->classroomSubjectTeachers->pluck('subject.name')->unique()->values(),
                'classrooms' => $teacher->classroomSubjectTeachers->pluck('classroom.name')->unique()->values(),
                'teacher_data' => $teacher,
                'user_data' => $teacher->user
            ];
        });

        // Get students with their classrooms and schools
        $students = \App\Models\Student::with([
            'user.roles',
            'school:id,name',
            'classroom:id,name',
            'grade:id,name',
            'stage:id,name'
        ])->get()->map(function ($student) {
            return [
                'id' => $student->user_id,
                'type' => 'student',
                'name' => $student->name,
                'email' => $student->user->email ?? null,
                'roles' => $student->user->roles ?? [],
                'school_name' => $student->school->name ?? null,
                'school_id' => $student->school_id,
                'classroom_name' => $student->classroom->name ?? null,
                'grade_name' => $student->grade->name ?? null,
                'stage_name' => $student->stage->name ?? null,
                'student_data' => $student,
                'user_data' => $student->user
            ];
        });

        // Get parents with their students and schools
        $parents = \App\Models\StudentParent::with([
            'user.roles',
            'school:id,name',
            'students.classroom:id,name'
        ])->get()->map(function ($parent) {
            return [
                'id' => $parent->user_id,
                'type' => 'parent',
                'name' => $parent->name,
                'email' => $parent->user->email ?? null,
                'roles' => $parent->user->roles ?? [],
                'school_name' => $parent->school->name ?? null,
                'school_id' => $parent->school_id,
                'children_classrooms' => $parent->students->pluck('classroom.name')->filter()->unique()->values(),
                'children_count' => $parent->students->count(),
                'parent_data' => $parent,
                'user_data' => $parent->user
            ];
        });

        // Get other users (not teachers, students, or parents)
        $teacherUserIds = $teachers->pluck('id')->filter();
        $studentUserIds = $students->pluck('id')->filter();
        $parentUserIds = $parents->pluck('id')->filter();

        $otherUsers = User::with('roles')
            ->whereNotIn('id', $teacherUserIds->merge($studentUserIds)->merge($parentUserIds))
            ->where('id', '!=', auth()->id())
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'type' => 'other',
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles,
                    'user_data' => $user
                ];
            });

        return [
            'schools' => $schools,
            'teachers' => $teachers,
            'students' => $students,
            'parents' => $parents,
            'others' => $otherUsers,
            'stats' => [
                'total' => $teachers->count() + $students->count() + $parents->count() + $otherUsers->count(),
                'teachers' => $teachers->count(),
                'students' => $students->count(),
                'parents' => $parents->count(),
                'others' => $otherUsers->count()
            ]
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name'
        ]);

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $validated['name']]);

        return redirect()->back();
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->back();
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissionIds' => 'nullable|array',
            'permissionIds.*' => 'exists:permissions,id'
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);

        if (!empty($validated['permissionIds'])) {
            $permissions = Permission::whereIn('id', $validated['permissionIds'])->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->back();
    }

    public function updateRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissionIds' => 'nullable|array',
            'permissionIds.*' => 'exists:permissions,id'  // Validate each permission ID
        ]);

        $role->update(['name' => $validated['name']]);

        // Always sync permissions, even if empty array is provided
        $permissions = Permission::whereIn('id', $validated['permissionIds'] ?? [])->get();
        $role->syncPermissions($permissions);

        return redirect()->back();
    }

    public function destroyRole(Role $role)
    {
        $role->delete();

        return redirect()->back();
    }

    public function updateUserRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roleIds' => 'array',
        ]);

        $roles = Role::whereIn('id', $validated['roleIds'])->get();
        $user->syncRoles($roles);

        return redirect()->back();
    }
}



