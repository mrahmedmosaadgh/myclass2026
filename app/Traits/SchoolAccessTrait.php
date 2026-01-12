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
// $hasRole=[
//     'isSuperAdmin' => $isSuperAdmin,
//     'isAdmin' => $isAdmin,
//     'isTeacher' => $isTeacher
// ];
        $schools = [];
        if ($isSuperAdmin) {
            // Super admin can see all schools
            $schools = School::with(['stages', 'grades', 'classrooms'])->get();
        } elseif ($isAdmin) {
            // Admin sees schools they own (h_r_id)
            $schools = School::where('h_r_id', $user->id)
                ->with(['stages', 'grades', 'classrooms'])
                ->get();
            
            // Fallback: if no schools found via h_r_id, get all schools
            if ($schools->isEmpty()) {
                $schools = School::with(['stages', 'grades', 'classrooms'])->get();
            }
        } elseif ($isTeacher) {
            // Teacher sees their assigned schools
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher && $teacher->schools) {
                $schools = School::whereIn('id', $teacher->schools->pluck('id'))
                    ->with(['stages', 'grades', 'classrooms'])
                    ->get();
            }
        } else {
            // Fallback for any other role - get all schools $user->id
            $schools = School::where('id', $user->school_id)
                ->with(['stages', 'grades', 'classrooms'])
                ->get();




            // $schools = School::with(['stages', 'grades', 'classrooms'])->get();
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