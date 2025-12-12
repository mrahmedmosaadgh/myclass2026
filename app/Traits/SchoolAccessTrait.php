<?php

namespace App\Traits;

use App\Models\School;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

trait SchoolAccessTrait
{
    protected function getUserSchoolAccess()
    {
        $user = Auth::user();
        $userRoles = $user->getRoleNames();

        $isSuperAdmin = $user->hasRole('super_admin');
        $isAdmin = $user->hasRole('admin');
        $isTeacher = $user->hasRole('teacher');

        $schools = [];
        if ($isSuperAdmin) {
            $schools = School::with(['stages', 'grades', 'classrooms'])->get();
        } elseif ($isAdmin) {
            $schools = School::where('h_r_id', $user->id)
                ->with(['stages', 'grades', 'classrooms'])
                ->get();
        } elseif ($isTeacher) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $schools = School::whereIn('id', $teacher->schools->pluck('id'))
                    ->with(['stages', 'grades', 'classrooms'])
                    ->get();
            }
        }

        return [
            'schools' => $schools,
            'userRoles' => $userRoles,
            'permissions' => [
                'isSuperAdmin' => $isSuperAdmin,
                'isAdmin' => $isAdmin,
                'isTeacher' => $isTeacher
            ]
        ];
    }
}