<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\ClassroomSubjectTeacher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassroomSubjectTeacherImportController extends Controller
{
    public function index()
    {
        return Inertia::render('my_class/admin/ClassroomSubjectTeacherImport');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|array',
            'data.*.classroom' => 'required|string',
            'data.*.subject' => 'required|string',
            'data.*.teacher' => 'required|string',
            'data.*.classes_per_week' => 'required|integer|min:1',
        ]);

        foreach ($validated['data'] as $row) {
            $classroom = Classroom::where('name', $row['classroom'])->first();
            $subject = Subject::where('name', $row['subject'])->first();
            $teacher = Teacher::where('name', $row['teacher'])->first();

            if ($classroom && $subject && $teacher) {
                ClassroomSubjectTeacher::updateOrCreate(
                    [
                        'classroom_id' => $classroom->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id
                    ],
                    ['classes_per_week' => $row['classes_per_week']]
                );
            }
        }

        return back()->with('success', 'Data imported successfully');
    }

    public function validate(Request $request)
    {
        $validated = $request->validate([
            'classroom' => 'required|string',
            'subject' => 'required|string',
            'teacher' => 'required|string'
        ]);

        return [
            'classroom_exists' => Classroom::where('name', $validated['classroom'])->exists(),
            'subject_exists' => Subject::where('name', $validated['subject'])->exists(),
            'teacher_exists' => Teacher::where('name_cute', $validated['teacher'])->exists()
        ];
    }
}
