<?php

namespace App\Http\Controllers;

use App\Models\ClassroomRecord;
use Illuminate\Http\Request;

class ClassroomRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassroomRecord::with(['teacher','classroom','student','subject']);

        if ($request->teacher_id) $query->where('teacher_id', $request->teacher_id);
        if ($request->subject_id) $query->where('subject_id', $request->subject_id);
        if ($request->classroom_id) $query->where('classroom_id', $request->classroom_id);
        if ($request->period_code) $query->where('period_code', $request->period_code);
        if ($request->date) $query->whereDate('date', $request->date);

        return response()->json($query->get());
    }

    public function update(Request $request, ClassroomRecord $classroomRecord)
    {
        $classroomRecord->update($request->all());
        return response()->json(['success' => true, 'data' => $classroomRecord]);
    }

    public function metadata()
    {
        return response()->json([
            'teachers' => \App\Models\Teacher::select('id','name')->get(),
            'subjects' => \App\Models\Subject::select('id','name')->get(),
            'classrooms' => \App\Models\Classroom::select('id','name')->get(),
        ]);
    }
}
