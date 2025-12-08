<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::where('school_id', Auth::user()->school_id);

        if ($request->has('subject_id')) {
            $query->whereHas('subjects', function ($q) use ($request) {
                $q->where('subjects.id', $request->subject_id);
            });
        }

        return $query->orderBy('name')->get();
    }

    public function show(Teacher $teacher)
    {
        return $teacher->load('subjects');
    }

    /**
     * Get students by classroom
     */
    public function getStudentsByClassroom(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id'
        ]);

        $students = Student::where('classroom_id', $request->classroom_id)
            ->where('school_id', Auth::user()->school_id)
            ->select('id', 'name')
            ->get();

        return response()->json($students);
    }
}
