<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassroomLayoutController extends Controller
{
    /**
     * Save student grouping layouts for a classroom
     */
    public function saveLayouts(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|integer',
            'layouts' => 'required|array'
        ]);

        $classroomId = $request->classroom_id;
        $layouts = $request->layouts;
        $userId = auth()->id();

        try {
            // Get teacher_id from teachers table using user_id
            $teacher = DB::table('teachers')
                ->where('user_id', $userId)
                ->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher record not found for this user'
                ], 404);
            }

            $teacherId = $teacher->id;

            // Try to find existing classroom_subject_teacher record
            $record = DB::table('classroom_subject_teachers')
                ->where('classroom_id', $classroomId)
                ->where('teacher_id', $teacherId)
                ->first();

            if ($record) {
                // Update existing record
                $data = $record->data ? json_decode($record->data, true) : [];
                $data['student_layouts'] = $layouts;

                DB::table('classroom_subject_teachers')
                    ->where('id', $record->id)
                    ->update([
                        'data' => json_encode($data),
                        'updated_at' => now()
                    ]);
            } else {
                // No classroom_subject_teacher record found
                // Store in a dedicated table for user preferences
                
                // Check if classroom exists
                $classroom = DB::table('classrooms')->where('id', $classroomId)->first();
                
                if (!$classroom) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Classroom not found'
                    ], 404);
                }
                
                // Store using teacher_id
                DB::table('user_classroom_data')->updateOrInsert(
                    [
                        'teacher_id' => $teacherId,
                        'classroom_id' => $classroomId
                    ],
                    [
                        'data' => json_encode(['student_layouts' => $layouts]),
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Layouts saved successfully',
                'data' => $layouts
            ]);

        } catch (\Exception $e) {
            \Log::error('Error saving classroom layouts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save layouts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load student grouping layouts for a classroom
     */
    public function loadLayouts(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|integer'
        ]);

        $classroomId = $request->classroom_id;
        $userId = auth()->id();

        try {
            // Get teacher_id from teachers table using user_id
            $teacher = DB::table('teachers')
                ->where('user_id', $userId)
                ->first();

            if (!$teacher) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $teacherId = $teacher->id;

            // First try classroom_subject_teachers
            $record = DB::table('classroom_subject_teachers')
                ->where('classroom_id', $classroomId)
                ->where('teacher_id', $teacherId)
                ->first();

            if ($record && $record->data) {
                $data = json_decode($record->data, true);
                $layouts = $data['student_layouts'] ?? [];
                
                if (!empty($layouts)) {
                    return response()->json([
                        'success' => true,
                        'data' => $layouts
                    ]);
                }
            }

            // If not found, try user_classroom_data table
            $userRecord = DB::table('user_classroom_data')
                ->where('teacher_id', $teacherId)
                ->where('classroom_id', $classroomId)
                ->first();

            if ($userRecord && $userRecord->data) {
                $data = json_decode($userRecord->data, true);
                $layouts = $data['student_layouts'] ?? [];
                
                return response()->json([
                    'success' => true,
                    'data' => $layouts
                ]);
            }

            // No layouts found
            return response()->json([
                'success' => true,
                'data' => []
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading classroom layouts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load layouts: ' . $e->getMessage()
            ], 500);
        }
    }
}
