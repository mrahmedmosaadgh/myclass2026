<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\Grade;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SchoolFilterController extends Controller
{
    public function getStagesBySchool($schoolId)
    {
        try {
            $stages = Stage::where('school_id', $schoolId)
                          ->select('id', 'name')
                          ->orderBy('name')
                          ->get();

            return response()->json($stages);

        } catch (\Exception $e) {
            Log::error('Error fetching stages: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch stages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getGradesByStage($stageId)
    {
        try {
            $grades = Grade::where('stage_id', $stageId)
                         ->select('id', 'name')
                         ->orderBy('name')
                         ->get();

            return response()->json($grades);

        } catch (\Exception $e) {
            Log::error('Error fetching grades: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch grades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getGradesBySchool($schoolId)
    {
        try {
            // Get all grades for all stages in this school
            $grades = Grade::whereHas('stage', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->select('id', 'name', 'stage_id')
            ->orderBy('name')
            ->get();

            return response()->json($grades);

        } catch (\Exception $e) {
            Log::error('Error fetching grades by school: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch grades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getClassroomsBySchool($schoolId)
    {
        try {
            $classrooms = Classroom::where('school_id', $schoolId)
                                 ->select('id', 'name', 'grade_id', 'stage_id')
                                 ->orderBy('name')
                                 ->get();

            return response()->json($classrooms);

        } catch (\Exception $e) {
            Log::error('Error fetching classrooms by school: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getClassroomsByStage($stageId)
    {
        try {
            $classrooms = Classroom::where('stage_id', $stageId)
                                 ->select('id', 'name')
                                 ->orderBy('name')
                                 ->get();

            return response()->json($classrooms);

        } catch (\Exception $e) {
            Log::error('Error fetching classrooms by stage: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getClassroomsByGrade($gradeId)
    {
        try {
            $classrooms = Classroom::where('grade_id', $gradeId)
                                 ->select('id', 'name')
                                 ->orderBy('name')
                                 ->get();

            return response()->json($classrooms);

        } catch (\Exception $e) {
            Log::error('Error fetching classrooms: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}



