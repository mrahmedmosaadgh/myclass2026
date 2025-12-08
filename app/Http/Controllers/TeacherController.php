<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Models\ClassroomSubjectTeacher;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    public function index()
    {
        // return 'okk';
        $teachers = Teacher::with(['school', 'user'])
            ->orderBy('name')
            ->paginate(10);

        $schools = School::select('id', 'name')->get();
// resources/js/Pages/my_class/admin/Teacher/TeacherHome.vue
// resources\js\Pages/Teachers/TeacherManagement.vue
        return Inertia::render('Teachers/TeacherManagement', [
        // return Inertia::render('my_class/admin/Teacher/TeacherHome', [
            'records' => $teachers,
            'schools' => $schools,
        ]);
    }


    public function lesson_presentation()
    {
        return Inertia::render('my_class/teacher/lesson_presentation/lesson_presentation_main');
        // D:/my_projects\2025\laravel12\myclass4\resources\js\Pages\my_class\teacher\lesson_presentation\lesson_presentation_main.vue
    }

    public function home()
    {
        $teachers = Teacher::with(['school', 'user'])
            ->orderBy('name')
            ->paginate(10);

        $schools = School::select('id', 'name')->get();
        $teacher = Auth::user();
        $teacher = Teacher::where('user_id', $teacher->id)->first();

        $assignments = ClassroomSubjectTeacher::where('teacher_id', $teacher->id)
            ->with(['classroom', 'subject'])
            ->get();


        $classrooms = array() ;
        $subjects = array() ;
        foreach ($assignments as $key => $value) {
            array_push($classrooms, $value->classroom);
            array_push($subjects, $value->subject);
        }


        return Inertia::render('Teacher/TeacherHome', [
            'records' => $teachers,
            'schools' => $schools,
            'assignments' => $assignments,
            'classrooms' => $classrooms,
            'subjects' => $subjects,
        ]);
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'email' => 'nullable|email|unique:teachers,email',
            'phone_number' => 'nullable|string|unique:teachers,phone_number',
            'whatsapp_number' => 'nullable|string|unique:teachers,whatsapp_number',
            'gender' => 'nullable|string|in:male,female',
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $teacher = Teacher::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Teacher created successfully',
                'data' => $teacher
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create teacher',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'email' => 'nullable|email|unique:teachers,email,' . $teacher->id,
            'phone_number' => 'nullable|string|unique:teachers,phone_number,' . $teacher->id,
            'whatsapp_number' => 'nullable|string|unique:teachers,whatsapp_number,' . $teacher->id,
            'gender' => 'nullable|string|in:male,female',
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $teacher->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Teacher updated successfully',
                'data' => $teacher
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update teacher',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        return redirect()->back()->with('success', 'Teacher deleted successfully.');
    }

    public function export()
    {
        $teachers = Teacher::with(['school', 'user'])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['ID', 'Name', 'Arabic Name', 'School', 'Email', 'Phone', 'Gender', 'Nationality'];
        foreach (range('A', 'H') as $index => $column) {
            $cell = $column . '1';
            $sheet->setCellValue($cell, $headers[$index]);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Data
        $row = 2;
        foreach ($teachers as $teacher) {
            $sheet->setCellValue('A' . $row, $teacher->t_id);
            $sheet->setCellValue('B' . $row, $teacher->name);
            $sheet->setCellValue('C' . $row, $teacher->name_ar);
            $sheet->setCellValue('D' . $row, $teacher->school->name);
            $sheet->setCellValue('E' . $row, $teacher->email);
            $sheet->setCellValue('F' . $row, $teacher->phone_number);
            $sheet->setCellValue('G' . $row, $teacher->gender);
            $sheet->setCellValue('H' . $row, $teacher->nationality);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'teachers_' . date('Y-m-d_H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    public function validateImport2(Request $request)
    {
        $statuses = [];
        $statuses[] = 'new';
        foreach ($request->data as $row) {
            $teacher = null;
            if (isset($row['ID']) && !empty($row['ID'])) {
                $teacher = Teacher::where('t_id', $row['ID'])->first();
                if ($teacher) {
                    $statuses[] = 'update';
                }
            }
            // $teacher = Teacher::where('t_id', $row['ID'])
                // ->orWhere('email', $row['email'])
                // ->orWhere('phone_number', $row['phone_number'])
                // ->first();


        }

        return response()->json(['statuses' => $statuses]);
    }
    public function validateImport(Request $request)
    {
        $statuses = [];
        foreach ($request->data as $row) {
            $teacher = null;

            // Check by ID if provided
            if (!empty($row['ID'])) {
                $teacher = Teacher::where('t_id', $row['ID'])->first();
            }

            // Optional: Check by email or phone if not found by ID
            // if (!$teacher) {
            //     $teacher = Teacher::where('email', $row['email'])
            //         ->orWhere('phone_number', $row['phone_number'])
            //         ->first();
            // }

            $statuses[] = $teacher ? 'update' : 'new';
        }

        return response()->json(['statuses' => $statuses]);
    }
    public function import(Request $request)
    {
        $results = [
            'success' => [],
            'errors' => []
        ];

        // Define field mappings
        $fieldMappings = [
            'Name' => 'name',
            // 'Arabic Name' => 'name_ar',
            // 'Email' => 'email',
            // 'Phone' => 'phone_number',
            // 'Nationality' => 'nationality',
        ];

        try {
            DB::beginTransaction();
            $importId = Str::uuid();
            $affectedTeachers = [];

            foreach ($request->data as $row) {

                $teacher = null;
                if (isset($row['ID']) && !empty($row['ID'])) {
                    $teacher = Teacher::where('t_id', $row['ID'])->first();
                }
                if (isset($row['name']) && !empty($row['name'])) {
                    $teacher = Teacher::where('name', $row['name'])->first();
                }
                if (isset($row['Name']) && !empty($row['Name'])) {
                    $teacher = Teacher::where('name', $row['name'])->first();
                }


                if ($teacher) {
                    $affectedTeachers[] = [
                        'id' => $teacher->id,
                        'original_data' => $teacher->toArray()
                    ];

                    $updateData = [];

                    // Process standard fields
                    foreach ($fieldMappings as $excelColumn => $dbColumn) {
                        if (!empty($row[$excelColumn])) {
                            $updateData[$dbColumn] = $row[$excelColumn];
                        }
                    }

                    // Handle special cases
                    if (!empty($row['Gender'])) {
                        $updateData['gender'] = strtolower($row['Gender']);
                    }

                    if (!empty($row['School'])) {
                        $schoolId = $this->findSchoolId($row['School']);
                        if ($schoolId) {
                            $updateData['school_id'] = $schoolId;
                        } else {
                            $results['errors'][] = "School not found for teacher {$row['Name']}: {$row['School']}";
                            continue;
                        }
                    }

                    if (!empty($updateData)) {
                        $teacher->update($updateData);
                        $results['success'][] = "Updated teacher: {$row['Name']}";
                    }
                } else {
                    if (empty($row['name'])) {
                        $results['errors'][] = "Name is required for new teacher record";
                        continue;
                    }
                    if (empty($row['school'])) {
                        $results['errors'][] = "School is required for teacher: {$row['Name']}";
                        continue;
                    }

                    $schoolId = $this->findSchoolId($row['school']);
                    if (!$schoolId) {
                        $results['errors'][] = "School not found for teacher {$row['Name']}: {$row['School']}";
                        continue;
                    }

                    $newTeacherData = ['t_id' => $row['ID'] ?? null];

                    // Process standard fields
                    foreach ($fieldMappings as $excelColumn => $dbColumn) {
                        if (!empty($row[$excelColumn])) {
                            $newTeacherData[$dbColumn] = $row[$excelColumn];
                        }
                    }

                    // Handle special cases
                    if (!empty($row['Gender'])) {
                        $newTeacherData['gender'] = strtolower($row['Gender']);
                    }
                    $newTeacherData['school_id'] = $schoolId;
                    $newTeacherData['name'] = $row['name'];

                    $newTeacher = Teacher::create($newTeacherData);

                    $affectedTeachers[] = [
                        'id' => $newTeacher->id,
                        'original_data' => null
                    ];
                    $results['success'][] = "Created teacher: {$row['name']}";
                }
            }

            Cache::put("teacher_import_{$importId}", $affectedTeachers, now()->addHours(24));
            DB::commit();

            return response()->json([
                'results' => $results,
                'importId' => $importId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => [],
                'errors' => ['An error occurred while processing the data: ' . $e->getMessage()]
            ], 500);
        }
    }

    public function undoImport($importId)
    {
        try {
            DB::beginTransaction();

            $affectedTeachers = Cache::get("teacher_import_{$importId}");

            if (!$affectedTeachers) {
                throw new \Exception('Import data not found or expired');
            }

            foreach ($affectedTeachers as $teacherData) {
                if ($teacherData['original_data'] === null) {
                    // This was a new record - delete it
                    Teacher::destroy($teacherData['id']);
                } else {
                    // This was an update - restore original data
                    Teacher::where('id', $teacherData['id'])
                        ->update($teacherData['original_data']);
                }
            }

            // Remove the cached data
            Cache::forget("teacher_import_{$importId}");

            DB::commit();
            return response()->json(['message' => 'Import successfully undone']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function findSchoolId($schoolName)
    {
        $school = School::where('name', $schoolName)->first();
        return $school ? $school->id : null;
    }

    public function getTeacherClasses()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        if (!$teacher) {
            return response()->json(['error' => 'No teacher record found for this user'], 403);
        }

        $assignments = ClassroomSubjectTeacher::where('teacher_id', $teacher->id)
            ->with(['classroom', 'subject'])
            ->get();

        $classrooms = $assignments->pluck('classroom')->unique();
        $subjects = $assignments->map(function ($assignment) {
            return [
                'id' => $assignment->subject_id,
                'name' => $assignment->subject->name,
                'classroom_id' => $assignment->classroom_id
            ];
        });

        return response()->json([
            'classrooms' => $classrooms,
            'subjects' => $subjects
        ]);
    }

    public function getStudents(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id'
        ]);

        $students = Student::where('classroom_id', $request->classroom_id)
            ->select('id', 'name', 'name_ar', 'student_id')
            ->get();

        return response()->json([
            'students' => $students
        ]);
    }

    public function students(Request $request)
    {
        $students = Student::where('classroom_id', $request->classroom_id)
            ->with([
                'user',
                'school:id,name',  // Note the :id,name to select specific fields
                'stage:id,name',
                'grade:id,name',
                'classroom:id,name',
                'parent:id,name'
            ])
            ->select('id', 's_id', 'user_id', 'name', 'name_ar', 'grade_id', 'school_id','classroom_id','stage_id') // Make sure to include school_id
            ->get();

        return response()->json([
            'students' => $students
        ]);
    }

    public function classes()
    {
        return Inertia::render('Teacher/Classes');
    }

    public function attendance()
    {
        return Inertia::render('Teacher/Attendance');
    }

    public function grades()
    {
        return Inertia::render('Teacher/Grades');
    }
}





