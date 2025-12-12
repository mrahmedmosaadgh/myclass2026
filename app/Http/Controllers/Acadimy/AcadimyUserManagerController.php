<?php

namespace App\Http\Controllers\Acadimy;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class AcadimyUserManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        // return 'ok';

        // Fetch all users with their roles and specific profiles for efficiency
        $users = User::with([
            'roles',
            'teacher.school',
            'student.school', 'student.classroom',
            // 'parent.children.classroom'
        ])->get();

        // Categorize users based on their primary role
        $teachers = [];
        $students = [];
        // $parents = [];
        $otherUsers = [];

        foreach ($users as $user) {
            if ($user->hasRole('teacher')) {
                $teachers[] = $user;
            } elseif ($user->hasRole('student')) {
                $students[] = $user;
            } /* elseif ($user->hasRole('parent')) {
                $parents[] = $user;
            } */ else {
                $otherUsers[] = $user;
            }
        }

        // Prepare the data structure required by the frontend, based on your documentation
        $usersData = [
            'teachers' => collect($teachers)->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->map->only(['id', 'name']),
                    'school_id' => optional(optional($user->teacher)->school)->id,
                    'school_name' => optional(optional($user->teacher)->school)->name ?? 'N/A',
                    'subjects' => optional($user->teacher)->subjects ?? [], // Assuming a 'subjects' relationship exists on the Teacher model
                ];
            }),
            'students' => collect($students)->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->map->only(['id', 'name']),
                    'school_id' => optional(optional($user->student)->school)->id,
                    'school_name' => optional(optional($user->student)->school)->name ?? 'N/A',
                    'classroom_name' => optional(optional($user->student)->classroom)->name ?? 'N/A',
                ];
            }),
            /* 'parents' => collect($parents)->map(function ($user) {
                $childrenClassrooms = optional($user->parent)->children
                    ->map(fn($student) => optional($student->classroom)->name ?? 'N/A')
                    ->unique()
                    ->join(', ');
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->map->only(['id', 'name']),
                    'children_classrooms' => $childrenClassrooms,
                ];
            }), */
            'others' => collect($otherUsers)->map(function ($user) {
                 return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->map->only(['id', 'name']),
                ];
            }),
            'stats' => [
                'total' => $users->count(),
                'teachers' => count($teachers),
                'students' => count($students),
                // 'parents' => count($parents),
                'others' => count($otherUsers),
            ]
        ];
        // return 'ok';
// D:\my_projects\2025\laravel12\myclass8\resources\js\Pages/academy/admin/user_manager/Index.vue
        return Inertia::render('academy/admin/user_manager/Index', [
            'usersData' => $usersData,
            'schools' => School::select('id', 'name')->get(),
            'roles' => Role::select('id', 'name')->get(),
        ]);
    }
}