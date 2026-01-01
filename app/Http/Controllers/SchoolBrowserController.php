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
    public function getSchoolData_old(Request $request)
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

 public function getSchoolData(Request $request)
    {
        try {
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

            // Validate school_id is numeric
            if (!is_numeric($schoolId)) {
                return response()->json([
                    'error' => 'Invalid school ID format',
                    'message' => 'School ID must be a valid number'
                ], 400);
            }

            // Get detailed school data with all relationships
            // Using try-catch to handle ModelNotFoundException
            try {
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
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'error' => 'School not found',
                    'message' => "No school found with ID: {$schoolId}"
                ], 404);
            }

            // Get classroom-subject-teacher assignments for this school
            // Using eager loading to prevent N+1 queries
            $assignments = ClassroomSubjectTeacher::where('school_id', $schoolId)
                ->with([
                    'classroom:id,name,grade_id',
                    'classroom.grade:id,name',
                    'subject:id,name,color_bg,color_text',
                    'teacher:id,name'
                ])
                ->get()
                ->map(function ($assignment) {
                    // Use null-safe operators (?->) to prevent errors on missing relationships
                    return [
                        'id' => $assignment->id,
                        'classroom_id' => $assignment->classroom_id,
                        'classroom_name' => $assignment->classroom?->name,
                        'grade_name' => $assignment->classroom?->grade?->name,
                        'subject_id' => $assignment->subject_id,
                        'subject_name' => $assignment->subject?->name,
                        'subject_color_bg' => $assignment->subject?->color_bg,
                        'subject_color_text' => $assignment->subject?->color_text,
                        'teacher_id' => $assignment->teacher_id,
                        'teacher_name' => $assignment->teacher?->name,
                        'classes_per_week' => $assignment->classes_per_week,
                        'color_custom' => $assignment->color_custom,
                        'color_custom_text' => $assignment->color_custom_text,
                    ];
                });

            // Calculate statistics safely
            $stats = [
                'total_classrooms' => $school->classrooms?->count() ?? 0,
                'total_subjects' => $school->subjects?->count() ?? 0,
                'total_teachers' => $school->teachers?->count() ?? 0,
                'total_assignments' => $assignments->count(),
                'stages_count' => $school->stages?->count() ?? 0,
                'grades_count' => $school->grades?->count() ?? 0,
            ];

            // Organize classrooms by stage and grade with null safety
            $classroomHierarchy = collect($school->stages ?? [])->map(function ($stage) use ($school, $assignments) {
                // Safely get grades for this stage
                $stageGrades = collect($school->grades ?? [])->where('stage_id', $stage->id);
                
                return [
                    'id' => $stage->id,
                    'name' => $stage->name,
                    'grades' => $stageGrades->map(function ($grade) use ($school, $assignments) {
                        // Safely get classrooms for this grade
                        $gradeClassrooms = collect($school->classrooms ?? [])->where('grade_id', $grade->id);
                        
                        return [
                            'id' => $grade->id,
                            'name' => $grade->name,
                            'classrooms' => $gradeClassrooms->map(function ($classroom) use ($assignments) {
                                // Safely get assignments for this classroom
                                $classroomAssignments = $assignments->where('classroom_id', $classroom->id);
                                
                                return [
                                    'id' => $classroom->id,
                                    'name' => $classroom->name,
                                    'capacity' => $classroom->capacity,
                                    'assignments_count' => $classroomAssignments->count(),
                                    'subjects' => $classroomAssignments
                                        ->pluck('subject_name')
                                        ->filter() // Remove null values
                                        ->unique()
                                        ->values(),
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
                'subjects' => $school->subjects ?? [],
                'teachers' => $school->teachers ?? [],
                'assignments' => $assignments,
            ]);

        } catch (ModelNotFoundException $e) {
            // Already handled above, but catch again for safety
            Log::error('School not found in getSchoolData', [
                'school_id' => $request->input('school_id'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'School not found',
                'message' => $e->getMessage()
            ], 404);

        } catch (\Illuminate\Database\QueryException $e) {
            // Database-related errors
            Log::error('Database error in getSchoolData', [
                'school_id' => $request->input('school_id'),
                'error' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'N/A'
            ]);
            
            return response()->json([
                'error' => 'Database error',
                'message' => 'An error occurred while fetching school data. Please try again later.'
            ], 500);

        } catch (\Exception $e) {
            // Catch any other unexpected errors
            Log::error('Unexpected error in getSchoolData', [
                'school_id' => $request->input('school_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Server error',
                'message' => 'An unexpected error occurred. Please contact support if this persists.'
            ], 500);
        }
    }

}
