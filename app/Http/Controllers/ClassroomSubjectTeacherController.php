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

        // Create the record with all required fields
        $record = ClassroomSubjectTeacher::create($data);

        return redirect()->back()->with('success', 'Record created successfully');
    }

    public function update(Request $request, ClassroomSubjectTeacher $classroomSubjectTeacher)
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
            return redirect()->back()->with('error', 'No active academic year found for this school');
        }

        // Add academic_year_id to validated data
        $validated['academic_year_id'] = $activeYear->id;

        // Preserve existing data and merge new data
        $existingData = $classroomSubjectTeacher->data ?? [];
        if (is_string($existingData)) {
            $existingData = json_decode($existingData, true) ?? [];
        }
        $validated['data'] = array_merge($existingData, ['updated_at' => now()->toDateTimeString()]);

        $classroomSubjectTeacher->update($validated);

        return redirect()->back()->with('success', 'Record updated successfully');
    }

    public function destroy(ClassroomSubjectTeacher $classroomSubjectTeacher)
    {
        $classroomSubjectTeacher->delete();
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


}
