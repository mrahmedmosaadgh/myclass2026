<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Teacher;
use Illuminate\Http\Request;

class LoadSchoolsController extends Controller
{
    public function adminSchools()
    {
        $user = auth()->guard()->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher || !$teacher->school_id) {
            return response()->json([]);
        }
      // Load the teacher's primary school with HR relationship
      $teacher->load(['school.hr']);

      if (!$teacher->school || !$teacher->school->hr) {
          return response()->json([]);
      }

      $hrId = $teacher->school->hr->id;

      // Get all schools with the same HR ID
      $schools = School::whereHas('hr', function($query) use ($hrId) {
          $query->where('id', $hrId);
      })->get();

      return response()->json($schools);

    }

    public function teacherSchools(Teacher $teacher)
    {


        $schools = [];

        // Load primary school
        $teacher->load('school');
        if ($teacher->school) {
            $schools[] = $teacher->school;
        }

        // Load extra schools
        if ($teacher->school_extra_ids && is_array($teacher->school_extra_ids)) {
            $extraSchools = School::whereIn('id', $teacher->school_extra_ids)->get();
            $schools = array_merge($schools, $extraSchools->all());
        }

        return response()->json($schools);
    }
}
