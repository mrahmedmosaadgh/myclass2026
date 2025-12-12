<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassroomSubjectTeacher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeacherDashboardController extends Controller
{
    public function classrooms(Request $request)
    {
        $user = $request->user();
        $teacher = $user?->teacher;

        if (!$teacher) {
            return response()->json(['message' => 'Teacher profile not found'], 404);
        }

        $assignments = ClassroomSubjectTeacher::with([
            'classroom:id,name',
            'subject:id,name',
        ])
        ->where('teacher_id', $teacher->id)
        ->whereNull('deleted_at')
        ->get()
        ->map(function ($cst) {
            return [
                'id' => $cst->id,
                'classroom' => [
                    'id' => $cst->classroom_id,
                    'name' => $cst->classroom?->name,
                ],
                'subject' => [
                    'id' => $cst->subject_id,
                    'name' => $cst->subject?->name,
                ],
                'classes_per_week' => $cst->classes_per_week,
                'colors' => [
                    'bg' => $cst->c_bg ?? $cst->subject?->color_bg ?? '#f8fafc',
                    'text' => $cst->c_text ?? $cst->subject?->color_text ?? '#0f172a',
                ],
            ];
        });

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['data' => $assignments]);
        }

        return Inertia::render('Teacher/Dashboard/Classrooms', [
            'assignments' => $assignments,
        ]);
    }
} 