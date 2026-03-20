<?php

namespace App\Http\Controllers;

use App\Models\ClassroomSubjectTeacher;
use App\Models\School;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class ClassroomSubjectTeacherController extends Controller
{
    public function bySchool(School $school)
    {
        return ClassroomSubjectTeacher::with([
                'classroom',
                'subject',
                'teacher'
            ])
            ->where('school_id', $school->id)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'classroom_name' => $item->classroom->name,
                    'subject_name' => $item->subject->name,
                    'teacher_name' => $item->teacher->name
                ];
            });
    }

    public function index()
    {
        $records = ClassroomSubjectTeacher::with(['school', 'grade', 'classroom', 'subject', 'teacher'])
            ->paginate(40);

        return Inertia::render('my_class/admin/ClassroomSubjectTeachers/Index', [
            'records' => $records,
            'options' => [
                'schools' => School::select('id', 'name')->get(),
                'grades' => Grade::select('id', 'name')->get(),
                'classrooms' => Classroom::select('id', 'name')->get(),
                'subjects' => Subject::select('id', 'name')->get(),
                'teachers' => Teacher::select('id', 'name')->get(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        // Get active academic year for the school first
        $activeYear = \App\Models\AcademicYear::where('school_id', $request->school_id)
            ->where('active', true)
            ->firstOrFail();

        // Merge grade_id from classroom before validation
        $classroom = Classroom::findOrFail($request->classroom_id);

        // Create the data array with all required fields
        $data = [
            'school_id' => $request->school_id,
            'academic_year_id' => $activeYear->id,
            'grade_id' => $classroom->grade_id,
            'classroom_id' => $request->classroom_id,
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
            'classes_per_week' => $request->classes_per_week,
            'data' => ['created_at' => now()->toDateTimeString()]
        ];

        // Validate the data
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'classes_per_week' => 'required|integer|min:1',
        ]);

        // Create or update the record
        $record = ClassroomSubjectTeacher::updateOrCreate(
            [
                'school_id' => $request->school_id,
                'academic_year_id' => $activeYear->id,
                'classroom_id' => $request->classroom_id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $request->teacher_id,
            ],
            [
                'grade_id' => $classroom->grade_id,
                'classes_per_week' => $request->classes_per_week,
                'data' => ['created_at' => now()->toDateTimeString(), 'updated_at' => now()->toDateTimeString()]
            ]
        );

        $record->load(['classroom.grade', 'subject', 'teacher']);

        $formattedData = [
            'id' => $record->id,
            'school_id' => $record->school_id,
            'classroom_id' => $record->classroom_id,
            'classroom_name' => $record->classroom?->name,
            'grade_name' => $record->classroom?->grade?->name,
            'subject_id' => $record->subject_id,
            'subject_name' => $record->subject?->name,
            'subject_color_bg' => $record->subject?->color_bg,
            'subject_color_text' => $record->subject?->color_text,
            'teacher_id' => $record->teacher_id,
            'teacher_name' => $record->teacher?->name,
            'classes_per_week' => $record->classes_per_week,
            'color_custom' => $record->color_custom,
            'color_custom_text' => $record->color_custom_text,
        ];

        // Return JSON for API calls
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Assignment created successfully',
                'data' => $formattedData
            ]);
        }

        return redirect()->back()->with('success', 'Record created successfully');
    }

    public function update(Request $request, ClassroomSubjectTeacher $assignment)
    {
        $classroom = Classroom::findOrFail($request->classroom_id);
        $request->merge(['grade_id' => $classroom->grade_id]);

        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'classes_per_week' => 'required|integer|min:1',
        ]);

        // Get active academic year for the school
        $activeYear = \App\Models\AcademicYear::where('school_id', $validated['school_id'])
            ->where('active', true)
            ->first();

        if (!$activeYear) {
            $error = 'No active academic year found for this school';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return redirect()->back()->with('error', $error);
        }

        // Add academic_year_id to validated data
        $validated['academic_year_id'] = $activeYear->id;

        // Preserve existing data and merge new data
        $existingData = $assignment->data ?? [];
        if (is_string($existingData)) {
            $existingData = json_decode($existingData, true) ?? [];
        }
        $validated['data'] = array_merge($existingData, ['updated_at' => now()->toDateTimeString()]);

        try {
            $assignment->update($validated);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            $errorMsg = 'An assignment with this teacher, subject, and classroom already exists.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // Freshly load the updated model with all relationships to ensure we return current data
        $updatedAssignment = ClassroomSubjectTeacher::with(['classroom.grade', 'subject', 'teacher'])
            ->find($assignment->id);

        $formattedData = [
            'id' => $updatedAssignment->id,
            'school_id' => $updatedAssignment->school_id,
            'classroom_id' => $updatedAssignment->classroom_id,
            'classroom_name' => $updatedAssignment->classroom?->name,
            'grade_name' => $updatedAssignment->classroom?->grade?->name,
            'subject_id' => $updatedAssignment->subject_id,
            'subject_name' => $updatedAssignment->subject?->name,
            'subject_color_bg' => $updatedAssignment->subject?->color_bg,
            'subject_color_text' => $updatedAssignment->subject?->color_text,
            'teacher_id' => $updatedAssignment->teacher_id,
            'teacher_name' => $updatedAssignment->teacher?->name,
            'classes_per_week' => $updatedAssignment->classes_per_week,
            'color_custom' => $updatedAssignment->color_custom,
            'color_custom_text' => $updatedAssignment->color_custom_text,
        ];

        // Return JSON for API calls
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Assignment updated successfully',
                'data' => $formattedData
            ]);
        }

        return redirect()->back()->with('success', 'Record updated successfully');
    }

    public function destroy(Request $request, ClassroomSubjectTeacher $assignment)
    {
        $assignment->delete();

        // Return JSON for API calls
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Assignment deleted successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Record deleted successfully');
    }

    public function validateImport(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|array',
            'school_id' => 'required|exists:schools,id'
        ]);

        $validatedData = [];
        $hasErrors = false;

        foreach ($request->data as $item) {
            $errors = [];

            // Look up each entity by name
            $classroom = Classroom::where('name', $item['classroom'] ?? '')
                ->where('school_id', $request->school_id)
                ->first();

            $subject = Subject::where('name', $item['subject'] ?? '')
                ->where('school_id', $request->school_id)
                ->first();

            $teacher = Teacher::where('name_cute', $item['teacher'] ?? '')
                ->where('school_id', $request->school_id)
                ->first();

            // Validate existence
            if (!$classroom) $errors['classroom'] = 'Classroom not found';
            if (!$subject) $errors['subject'] = 'Subject not found';
            if (!$teacher) $errors['teacher'] = 'Teacher not found';
            if (!isset($item['classes_per_week']) || !is_numeric($item['classes_per_week']) || $item['classes_per_week'] < 1)
                $errors['classes_per_week'] = 'Invalid number of classes';

            $validatedItem = [
                'data' => [
                    'classroom_id' => $classroom?->id,
                    'subject_id' => $subject?->id,
                    'teacher_id' => $teacher?->id,
                    'classes_per_week' => $item['classes_per_week'] ?? 0,
                    'original_data' => $item // Keep original names for reference
                ],
                'errors' => $errors
            ];

            if (count($errors)) $hasErrors = true;
            $validatedData[] = $validatedItem;
        }

        return response()->json([
            'success' => true,
            'hasErrors' => $hasErrors,
            'validatedData' => $validatedData,
            'message' => $hasErrors ? 'Validation completed with errors' : 'All data is valid'
        ]);
    }

    public function validateImportNew(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|array',
            'school_id' => 'required|exists:schools,id'
        ]);

        $validatedData = [];
        $hasErrors = false;

        foreach ($request->data as $item) {
            $validator = Validator::make($item, [
                'classroom_id' => 'required|exists:classrooms,id,school_id,'.$request->school_id,
                'subject_id' => 'required|exists:subjects,id,school_id,'.$request->school_id,
                'teacher_id' => 'required|exists:teachers,id,school_id,'.$request->school_id,
                'classes_per_week' => 'required|integer|min:1'
            ]);

            $validatedItem = [
                'data' => $item,
                'errors' => $validator->fails() ? $validator->errors()->toArray() : []
            ];

            if ($validator->fails()) {
                $hasErrors = true;
            }

            $validatedData[] = $validatedItem;
        }

        return response()->json([
            'success' => true,
            'hasErrors' => $hasErrors,
            'validatedData' => $validatedData,
            'message' => $hasErrors ? 'Validation completed with errors' : 'All data is valid'
        ]);
    }

    /**
     * Get teacher's classroom subject assignments for API (used by Weekly Plans)
     */
    public function myAssignments()
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        $teacher = Teacher::where('user_id', $user->id)->first();
        
        if (!$teacher) {
            return response()->json(['error' => 'User is not a teacher'], 400);
        }
        
        if (!$teacher->school_id) {
            return response()->json(['error' => 'Teacher has no school assigned'], 400);
        }

        $assignments = ClassroomSubjectTeacher::with(['classroom', 'subject', 'academicYear'])
            ->where('teacher_id', $teacher->id)
            ->where('school_id', $teacher->school_id)
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'subject_id' => $assignment->subject_id,
                    'grade_id' => $assignment->grade_id,
                    'classroom' => [
                        'id' => $assignment->classroom->id,
                        'name' => $assignment->classroom->name,
                    ],
                    'subject' => [
                        'id' => $assignment->subject->id,
                        'name' => $assignment->subject->name,
                    ],
                    'academic_year' => [
                        'id' => $assignment->academicYear->id,
                        'name' => $assignment->academicYear->name,
                    ],
                    'classes_per_week' => $assignment->classes_per_week,
                    'color_custom' => $assignment->color_custom,
                ];
            });

        return response()->json($assignments);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|array',
            'school_id' => 'required|exists:schools,id'
        ]);

        // Get active academic year
        $academicYear = DB::table('academic_years')
            ->where('active', 1)
            ->first();

        if (!$academicYear) {
            return response()->json([
                'success' => false,
                'message' => 'No active academic year found'
            ], 400);
        }

        $created = [];
        $errors = [];

        foreach ($request->data as $item) {
            try {
                // Look up IDs if names were provided
                $classroomId = $item['classroom_id'] ?? Classroom::where('name', $item['classroom'])
                    ->where('school_id', $request->school_id)
                    ->first()?->id;

                $subjectId = $item['subject_id'] ?? Subject::where('name', $item['subject'])
                    ->where('school_id', $request->school_id)
                    ->first()?->id;

                $teacherId = $item['teacher_id'] ?? Teacher::where('name_cute', $item['teacher'])
                    ->where('school_id', $request->school_id)
                    ->first()?->id;

                // Validate all IDs exist
                if (!$classroomId || !$subjectId || !$teacherId) {
                    $errors[] = [
                        'data' => $item,
                        'error' => 'Missing reference: ' .
                            (!$classroomId ? 'Classroom ' : '') .
                            (!$subjectId ? 'Subject ' : '') .
                            (!$teacherId ? 'Teacher' : '')
                    ];
                    continue;
                }

                // Check for existing assignment
                $existing = ClassroomSubjectTeacher::where([
                    'school_id' => $request->school_id,
                    'classroom_id' => $classroomId,
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                    'academic_year_id' => $academicYear->id
                ])->first();

                if ($existing) {
                    // Update existing record
                    $existing->update(['classes_per_week' => $item['classes_per_week']]);
                    $created[] = $existing;
                } else {
                    // Create new record
                    $assignment = ClassroomSubjectTeacher::create([
                        'academic_year_id' => $academicYear->id,
                        'school_id' => $request->school_id,
                        'classroom_id' => $classroomId,
                        'subject_id' => $subjectId,
                        'teacher_id' => $teacherId,
                        'classes_per_week' => $item['classes_per_week']
                    ]);

                    $created[] = $assignment;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'data' => $item,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'created' => count($created),
            'errors' => $errors,
            'message' => 'Imported ' . count($created) . ' assignments' .
                (count($errors) ? ' with ' . count($errors) . ' errors' : '')
        ]);
    }


    public function my_classes(Request $request)
{
// $my_classes= ClassroomSubjectTeacher:: get();
$my_classes= ClassroomSubjectTeacher::where('teacher_id', auth()->user()->id)->get();
        return response()->json($my_classes);

}

    public function all_classes(Request $request)
{
    $school_id=Teacher::where('user_id', auth()->user()->id)->first()->school_id;
$data= Classroom::where('school_id', $school_id)->get();
        return response()->json($data);

}
    public function all_subjects(Request $request)
{
    $school_id=Teacher::where('user_id', auth()->user()->id)->first()->school_id;
$data= Subject::where('school_id', $school_id)->get();
        return response()->json($data);

}
    public function all_teachers(Request $request)
{
    $school_id=Teacher::where('user_id', auth()->user()->id)->first()->school_id;
$data= Teacher::where('school_id', $school_id)->get();
        return response()->json($data);

}
    public function all_teachers_with_classroom_subject(Request $request)
{
    $school_id=Teacher::where('user_id', auth()->user()->id)->first()->school_id;
$data= Teacher::where('school_id', $school_id)->with('classroomSubjectTeachers')->get();
        return response()->json($data);

}
    public function teacher_classes(Request $request)
{
//   return  $request->teacher_id;
    $school_id=Teacher::where('user_id', auth()->user()->id)->first()->school_id;
$data= ClassroomSubjectTeacher::where('school_id', $school_id)->where('teacher_id',$request->teacher_id)->get();
        return response()->json($data);

}



 public function my_classes_with_students(Request $request)
{
    // Validate input
    // $request->validate([
    //     'teacher_id' => 'required|integer|exists:teachers,id',
    // ]);
    $teacher_id=Teacher::where('user_id', auth()->user()->id)->first()->id;
    $school_id=Teacher::where('user_id', auth()->user()->id)->first()->school_id;

    // Get the current teacher's school
    // $school_id = Teacher::where('user_id', auth()->id())->value('school_id');

    // Fetch all class-subject-teacher relationships with students
    $data = ClassroomSubjectTeacher::where('school_id', $school_id)
        ->where('teacher_id',$teacher_id)
        // ->with(['classroom', 'subject']) // Optional: preload useful relationships
        ->get()
        ->map(function ($item) {
            // Attach students directly
            $item->students = Student::where('classroom_id', $item->classroom_id)->get();
            return $item;
        });
        return response()->json($data);


    return response()->json([
        'teacher' => auth()->user(),
        'classes' => $data,
    ]);
}


    public function apiIndex(Request $request)
    {
        $schoolId = auth()->user()->schoolId();
        
        $query = ClassroomSubjectTeacher::with(['classroom', 'subject', 'teacher']);

        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        } elseif ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        if ($request->has('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    /**
     * Update only the classes_per_week field for a CST
     */
    public function updateClassesPerWeek(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'classes_per_week' => 'required|integer|min:1|max:20'
            ]);

            $cst = ClassroomSubjectTeacher::findOrFail($id);
            
            $cst->classes_per_week = $validated['classes_per_week'];
            $cst->save();

            return response()->json([
                'success' => true,
                'message' => 'Classes per week updated successfully',
                'data' => $cst->load(['classroom', 'subject', 'teacher'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update classes_per_week for multiple CSTs
     */
    public function bulkUpdateClassesPerWeek(Request $request)
    {
        try {
            $validated = $request->validate([
                'updates' => 'required|array',
                'updates.*.cst_id' => 'required|exists:classroom_subject_teachers,id',
                'updates.*.classes_per_week' => 'required|integer|min:1|max:20'
            ]);

            DB::beginTransaction();
            $updatedCount = 0;

            foreach ($validated['updates'] as $update) {
                $cst = ClassroomSubjectTeacher::find($update['cst_id']);
                if ($cst) {
                    $cst->classes_per_week = $update['classes_per_week'];
                    $cst->save();
                    $updatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updatedCount} assignments",
                'data' => ['updated_count' => $updatedCount]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk update: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted CST
     */
    public function restore($id)
    {
        try {
            $cst = ClassroomSubjectTeacher::onlyTrashed()->findOrFail($id);
            $cst->restore();

            return response()->json([
                'success' => true,
                'message' => 'CST restored successfully',
                'data' => $cst->load(['classroom', 'subject', 'teacher'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete a CST
     */
    public function softDelete($id)
    {
        try {
            $cst = ClassroomSubjectTeacher::findOrFail($id);
            
            // Check if it's the last subject for this classroom
            $classroomCstCount = ClassroomSubjectTeacher::where('classroom_id', $cst->classroom_id)
                ->where('id', '!=', $id)
                ->count();
            
            if ($classroomCstCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the last subject for this classroom'
                ], 422);
            }

            $cst->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'CST soft-deleted successfully. You can restore it later if needed.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Get CST (Classroom-Subject-Teacher) overview statistics
     * Shows per-classroom count and total expected schedule entries
     */
    public function getCSTOverview(Request $request)
    {
        try {
            $validated = $request->validate([
                'school_id' => 'required|exists:schools,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'include_deleted' => 'boolean'
            ]);

            $schoolId = $validated['school_id'];
            $academicYearId = $validated['academic_year_id'];
            $includeDeleted = $validated['include_deleted'] ?? false;

            // Get all CSTs for this school and academic year
            $query = ClassroomSubjectTeacher::where('school_id', $schoolId)
                ->where('academic_year_id', $academicYearId)
                ->with(['classroom', 'subject', 'teacher']);

            if ($includeDeleted) {
                $query->withTrashed();
            }

            $csts = $query->get();

            if ($csts->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No subject-teacher assignments found',
                    'data' => [
                        'by_classroom' => [],
                        'summary' => [
                            'total_classrooms' => 0,
                            'total_csts' => 0,
                            'total_deleted_csts' => 0,
                            'total_expected_schedules' => 0
                        ]
                    ]
                ]);
            }

            // Group by classroom
            $byClassroom = [];
            $totalExpectedSchedules = 0;
            $totalDeletedCsts = 0;

            foreach ($csts as $cst) {
                $classroomId = $cst->classroom_id;
                $classroomName = $cst->classroom->name ?? 'Unknown';
                $classesPerWeek = $cst->classes_per_week ?? 0;
                $isDeleted = !is_null($cst->deleted_at);

                if ($isDeleted) {
                    $totalDeletedCsts++;
                }

                if (!isset($byClassroom[$classroomId])) {
                    $byClassroom[$classroomId] = [
                        'classroom_id' => $classroomId,
                        'classroom_name' => $classroomName,
                        'cst_count' => 0,
                        'cst_deleted_count' => 0,
                        'total_classes_per_week' => 0,
                        'subjects' => []
                    ];
                }

                if (!$isDeleted) {
                    $byClassroom[$classroomId]['cst_count']++;
                    $byClassroom[$classroomId]['total_classes_per_week'] += $classesPerWeek;
                    $totalExpectedSchedules += $classesPerWeek;
                } else {
                    $byClassroom[$classroomId]['cst_deleted_count']++;
                }

                $byClassroom[$classroomId]['subjects'][] = [
                    'cst_id' => $cst->id,
                    'subject_name' => $cst->subject->name ?? 'Unknown',
                    'teacher_name' => $cst->teacher->name ?? 'Unknown',
                    'classes_per_week' => $classesPerWeek,
                    'deleted_at' => $cst->deleted_at ? $cst->deleted_at->toDateTimeString() : null,
                    'is_deleted' => $isDeleted
                ];
            }

            // Sort classrooms by name
            usort($byClassroom, function($a, $b) {
                return strcmp($a['classroom_name'], $b['classroom_name']);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'by_classroom' => array_values($byClassroom),
                    'summary' => [
                        'total_classrooms' => count($byClassroom),
                        'total_csts' => $csts->filter(fn($c) => is_null($c->deleted_at))->count(),
                        'total_deleted_csts' => $totalDeletedCsts,
                        'total_expected_schedules' => $totalExpectedSchedules
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get CST overview: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Sync classes_per_week from source source classroom to target classrooms
     * Only updates matching subjects (by subject_id)
     */
    public function syncClassesPerWeek(Request $request)
    {
        try {
            $validated = $request->validate([
                'source_classroom_id' => 'required|exists:classrooms,id',
                'target_classroom_ids' => 'required|array',
                'target_classroom_ids.*' => 'exists:classrooms,id',
                'school_id' => 'required|exists:schools,id'
            ]);

            $sourceClassroomId = $validated['source_classroom_id'];
            $targetClassroomIds = $validated['target_classroom_ids'];
            $schoolId = $validated['school_id'];
            
            // Get source CSTs
            // Only care about active ones (not deleted)
            $sourceCSTs = ClassroomSubjectTeacher::where('school_id', $schoolId)
                ->where('classroom_id', $sourceClassroomId)
                ->select('subject_id', 'classes_per_week')
                ->get()
                ->keyBy('subject_id'); // Map by subject_id for easy lookup
            
            if ($sourceCSTs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Source classroom has no active subjects to sync from'
                ], 422);
            }

            DB::beginTransaction();
            
            $updatedCount = 0;
            $updatedClassrooms = 0;

            foreach ($targetClassroomIds as $targetId) {
                // Skip if target is same as source
                if ($targetId == $sourceClassroomId) continue;
                
                $classroomUpdated = false;

                // Find matching CSTs in target classroom
                $targetCSTs = ClassroomSubjectTeacher::where('school_id', $schoolId)
                    ->where('classroom_id', $targetId)
                    ->get();
                
                foreach ($targetCSTs as $targetCST) {
                    // Check if this subject exists in source
                    if ($sourceCSTs->has($targetCST->subject_id)) {
                        $sourceValue = $sourceCSTs[$targetCST->subject_id]->classes_per_week;
                        
                        // Only update if different
                        if ($targetCST->classes_per_week !== $sourceValue) {
                            $targetCST->classes_per_week = $sourceValue;
                            $targetCST->save();
                            $updatedCount++;
                            $classroomUpdated = true;
                        }
                    }
                }
                
                if ($classroomUpdated) {
                    $updatedClassrooms++;
                }
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Sync complete: Updated {$updatedCount} subject assignments across {$updatedClassrooms} classrooms",
                'data' => [
                    'updated_records' => $updatedCount,
                    'updated_classrooms' => $updatedClassrooms
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
