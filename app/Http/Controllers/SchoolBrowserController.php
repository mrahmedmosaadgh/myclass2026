<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\ClassroomSubjectTeacher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolBrowserController extends Controller
{
    /**
     * Display the school browser page
     */
    public function index()
    {
        return Inertia::render('my_table_mnger/weekly_system/admin/SchoolBrowser');
    }

    /**
     * Get comprehensive school data with all relationships
     */
    public function getSchoolData(Request $request)
    {
        $schoolId = $request->input('school_id');

        // If no school_id provided, get all schools with basic info
        if (!$schoolId) {
            $schools = School::select('id', 'name')
                ->orderBy('name')
                ->get();

            return response()->json([
                'schools' => $schools,
                'selected_school' => null
            ]);
        }

        // Get detailed school data with all relationships
        $school = School::with([
            'stages' => function ($query) {
                $query->select('id', 'name', 'school_id')
                    ->orderBy('name');
            },
            'grades' => function ($query) {
                $query->select('id', 'name', 'stage_id', 'school_id')
                    ->with('stage:id,name')
                    ->orderBy('name');
            },
            'classrooms' => function ($query) {
                $query->select('id', 'name', 'capacity', 'grade_id', 'stage_id', 'school_id')
                    ->with([
                        'grade:id,name,stage_id',
                        'stage:id,name'
                    ])
                    ->orderBy('name');
            },
            'subjects' => function ($query) {
                $query->select('id', 'name', 'school_id', 'color_bg', 'color_text', 'active')
                    ->where('active', true)
                    ->orderBy('name');
            },
            'teachers' => function ($query) {
                $query->select('id', 'name', 'email', 'phone_number', 'school_id')
                    ->whereNull('deleted_at')
                    ->orderBy('name');
            }
        ])->findOrFail($schoolId);

        // Get classroom-subject-teacher assignments for this school
        $assignments = ClassroomSubjectTeacher::where('school_id', $schoolId)
            ->with([
                'classroom:id,name,grade_id',
                'classroom.grade:id,name',
                'subject:id,name,color_bg,color_text',
                'teacher:id,name'
            ])
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'classroom_id' => $assignment->classroom_id,
                    'classroom_name' => $assignment->classroom->name ?? 'N/A',
                    'grade_name' => $assignment->classroom->grade->name ?? 'N/A',
                    'subject_id' => $assignment->subject_id,
                    'subject_name' => $assignment->subject->name ?? 'N/A',
                    'subject_color_bg' => $assignment->subject->color_bg ?? '#cccccc',
                    'subject_color_text' => $assignment->subject->color_text ?? '#000000',
                    'teacher_id' => $assignment->teacher_id,
                    'teacher_name' => $assignment->teacher->name ?? 'N/A',
                    'classes_per_week' => $assignment->classes_per_week,
                    'color_custom' => $assignment->color_custom,
                    'color_custom_text' => $assignment->color_custom_text,
                ];
            });

        // Calculate statistics
        $stats = [
            'total_classrooms' => $school->classrooms->count(),
            'total_subjects' => $school->subjects->count(),
            'total_teachers' => $school->teachers->count(),
            'total_assignments' => $assignments->count(),
            'stages_count' => $school->stages->count(),
            'grades_count' => $school->grades->count(),
        ];

        // Organize classrooms by stage and grade
        $classroomHierarchy = $school->stages->map(function ($stage) use ($school) {
            $stageGrades = $school->grades->where('stage_id', $stage->id);
            
            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'grades' => $stageGrades->map(function ($grade) use ($school, $assignments) {
                    $gradeClassrooms = $school->classrooms->where('grade_id', $grade->id);
                    
                    return [
                        'id' => $grade->id,
                        'name' => $grade->name,
                        'classrooms' => $gradeClassrooms->map(function ($classroom) use ($assignments) {
                            $classroomAssignments = $assignments->where('classroom_id', $classroom->id);
                            
                            return [
                                'id' => $classroom->id,
                                'name' => $classroom->name,
                                'capacity' => $classroom->capacity,
                                'assignments_count' => $classroomAssignments->count(),
                                'subjects' => $classroomAssignments->pluck('subject_name')->unique()->values(),
                            ];
                        })->values()
                    ];
                })->values()
            ];
        });

        return response()->json([
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
            ],
            'stats' => $stats,
            'hierarchy' => $classroomHierarchy,
            'subjects' => $school->subjects,
            'teachers' => $school->teachers,
            'assignments' => $assignments,
        ]);
    }
}
