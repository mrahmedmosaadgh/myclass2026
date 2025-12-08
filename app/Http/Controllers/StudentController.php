<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\School;
use App\Models\Stage;
use App\Models\Grade;
use App\Models\Classroom;
use App\Traits\SchoolAccessTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    use SchoolAccessTrait;

    private $studentFieldMappings = [
        'ID' => 's_id',
        'Name' => 'name',
        'Arabic Name' => 'name_ar',
        'School ID' => 'school_id',
        'Grade ID' => 'grade_id',
        'Classroom ID' => 'classroom_id',
        'Stage ID' => 'stage_id'
    ];

    private $uniqueFields = [
        's_id' => 'ID',
        'name' => 'Name',
        'school_id' => 'School ID',
        'classroom_id' => 'Classroom ID'
    ];

    private $requiredFields = [
        'Name',
        'School ID',
        'Grade ID',
        'Classroom ID',
        'Stage ID'
    ];

    public function index()
    {
        $accessData = $this->getUserSchoolAccess();
        $students = Student::with(['school', 'stage', 'grade', 'classroom', 'parent'])
            ->orderBy('name')
            ->paginate(40);

        return Inertia::render('my_class/admin/Students/Index', [
            'records' => $students,
            'schools' => $accessData['schools'] ?? [], // Ensure schools is always an array
            'userRoles' => $accessData['userRoles'] ?? [],
            'permissions' => $accessData['permissions'] ?? []
        ]);
    }
    public function get_school_students($school_id)
    {
        $accessData = $this->getUserSchoolAccess();

        $students = Student::where('school_id', $school_id)
            ->with(['school', 'parent', 'classroom', 'grade', 'stage'])
            ->orderBy('name')->paginate(50);

        return response()->json([
            'records' => $students,
            'schools' => $accessData['schools'],
            'userRoles' => $accessData['userRoles'],
            'permissions' => $accessData['permissions']
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'name_ar' => 'nullable|string|max:255',
                'name_cute' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'school_id' => 'required|exists:schools,id',
                'stage_id' => 'required|exists:stages,id',
                'grade_id' => 'required|exists:grades,id',
                'classroom_id' => 'required|exists:classrooms,id',
            ]);

            $student = Student::create($validated);

            return response()->json([
                'message' => 'Student created successfully',
                'records' => Student::all() // Or use your pagination/filtering logic
            ]);
        } catch (\Exception $e) {
            \Log::error('Student creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create student',
                'errors' => ['error' => [$e->getMessage()]]
            ], 500);
        }
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            's_id' => 'required|unique:students,s_id,' . $student->id,
            'name' => 'required',
            'name_ar' => 'nullable',
            'name_cute' => 'nullable',
            'order_1' => 'nullable',
            'order_2' => 'nullable',
            'notes' => 'nullable',
            'parent_id' => 'nullable|exists:student_parents,id',
            'school_section_id' => 'nullable|exists:school_sections,id',
            'school_id' => 'required|exists:schools,id',
            'stage_id' => 'required|exists:stages,id',
            'grade_id' => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $student->update($validated);

        return redirect()->back()->with('success', 'Student updated successfully.');
    }

    /**
     * Upload or update student avatar
     */
    public function uploadAvatar(Request $request, Student $student)
    {
        $request->validate([
            'avatar' => 'required|file|image|max:5120', // max 5MB
        ]);

        try {
            $file = $request->file('avatar');
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $filename = 'student_' . $student->id . '_' . time() . '.' . $ext;
            $path = $file->storeAs('avatars', $filename, 'public');

            // Save public URL to student
            $student->avatar = '/storage/' . $path;
            $student->save();

            return response()->json(['avatar' => $student->avatar]);
        } catch (\Exception $e) {
            \Log::error('Avatar upload failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to upload avatar'], 500);
        }
    }

    /**
     * Remove student avatar
     */
    public function deleteAvatar(Student $student)
    {
        try {
            if ($student->avatar) {
                // Optional: Delete file from storage if needed
                // Storage::disk('public')->delete(str_replace('/storage/', '', $student->avatar));
                
                $student->avatar = null;
                $student->save();
            }

            return response()->json(['message' => 'Avatar removed successfully']);
        } catch (\Exception $e) {
            \Log::error('Avatar removal failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to remove avatar'], 500);
        }
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->back()->with('success', 'Student deleted successfully.');
    }

    public function validateImport(Request $request)
    {
        $statuses = [];

        // Find the ID column (column with is_id = true)
        $idColumn = collect($request->columns)
            ->where('is_id', true)
            ->first();

        // Get visible required columns
        $requiredColumns = collect($request->columns)
            ->where('required', true)
            ->where('hidden', false)
            ->pluck('key')
            ->toArray();

        foreach ($request->data as $row) {
            $status = 'new';

            // Check if record exists by ID field
            if ($idColumn && !empty($row[$idColumn['key']])) {
                $student = Student::where('s_id', $row[$idColumn['key']])->first();
                if ($student) {
                    $status = 'update';
                    $statuses[] = $status;
                    continue;
                }
            }

            // Check for required fields (only visible ones)
            $missingFields = false;
            foreach ($requiredColumns as $field) {
                if (empty($row[$field])) {
                    $missingFields = true;
                    break;
                }
            }

            if ($missingFields) {
                $status = 'invalid';
                $statuses[] = $status;
                continue;
            }

            // Check if school exists (if school column is visible and required)
            if (in_array('school', $requiredColumns)) {
                $school = School::where('name', $row['school'])->first();
                if (!$school) {
                    $status = 'invalid';
                    $statuses[] = $status;
                    continue;
                }

                // Check for duplicate student in same school and classroom
                // Only if these fields are visible and required
                if (in_array('name', $requiredColumns) && in_array('classroom', $requiredColumns)) {
                    $existingStudent = Student::where('name', $row['name'])
                        ->whereHas('school', function($query) use ($row) {
                            $query->where('name', $row['school']);
                        })
                        ->whereHas('classroom', function($query) use ($row) {
                            $query->where('name', $row['classroom']);
                        })
                        ->first();

                    if ($existingStudent) {
                        $status = 'duplicate';
                    }
                }
            }

            $statuses[] = $status;
        }

        return response()->json(['statuses' => $statuses]);
    }
    public function import(Request $request)
    {
        $results = [
            'success' => [],
            'errors' => []
        ];

        try {
            DB::beginTransaction();
            $importId = Str::uuid();
            $affectedStudents = [];

            // Find the ID column
            $idColumn = collect($request->columns)
                ->where('is_id', true)
                ->first();

            // Get visible columns
            $visibleColumns = collect($request->columns)
                ->where('hidden', false)
                ->pluck('key')
                ->toArray();

            foreach ($request->data as $row) {
                try {
                    // Find or create student
                    $student = null;

                    // Check for existing student by ID if provided
                    if ($idColumn && !empty($row[$idColumn['key']])) {
                        $student = Student::where($idColumn['key'], $row[$idColumn['key']])->first();
                    }

                    // Only process visible columns
                    $studentData = [];
                    foreach ($visibleColumns as $column) {
                        if (isset($row[$column])) {
                            // Handle special cases for related models
                            switch ($column) {
                                case 'school':
                                    $school = School::where('name', $row[$column])->first();
                                    if ($school) {
                                        $studentData['school_id'] = $school->id;
                                    }
                                    break;
                                case 'classroom':
                                    $classroom = Classroom::where('name', $row[$column])->first();
                                    if ($classroom) {
                                        $studentData['classroom_id'] = $classroom->id;
                                    }
                                    break;
                                case 'grade':
                                    $grade = Grade::where('name', $row[$column])->first();
                                    if ($grade) {
                                        $studentData['grade_id'] = $grade->id;
                                    }
                                    break;
                                case 'stage':
                                    $stage = Stage::where('name', $row[$column])->first();
                                    if ($stage) {
                                        $studentData['stage_id'] = $stage->id;
                                    }
                                    break;
                                default:
                                    $studentData[$column] = $row[$column];
                            }
                        }
                    }

                    if ($student) {
                        // Store original data for undo
                        $affectedStudents[] = [
                            'id' => $student->id,
                            'original_data' => $student->toArray()
                        ];

                        $student->update($studentData);
                        $results['success'][] = "Updated student: {$row['name']}";
                    } else {
                        $student = Student::create($studentData);
                        $affectedStudents[] = [
                            'id' => $student->id,
                            'original_data' => null
                        ];
                        $results['success'][] = "Created student: {$row['name']}";
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Error processing student {$row['name']}: " . $e->getMessage();
                }
            }

            Cache::put("student_import_{$importId}", $affectedStudents, now()->addHours(24));
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

    /**
     * Delegate import to CourseManagement\StudentImportController to reuse import logic.
     * This wrapper allows calling the existing import implementation from the StudentController.
     */
    public function importFromCourseManagement(Request $request)
    {
        try {
            $importController = new \App\Http\Controllers\CourseManagement\StudentImportController();
            // Reuse the import method. It returns a JSON response.
            return $importController->import($request);
        } catch (\Exception $e) {
            \Log::error('Delegated import failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Delegated import failed: ' . $e->getMessage()
            ], 500);
        }
    }



    public function undoImport($importId)
    {
        try {
            DB::beginTransaction();

            $affectedStudents = Cache::get("student_import_{$importId}");
            if (!$affectedStudents) {
                throw new \Exception('Import data not found or expired');
            }

            foreach ($affectedStudents as $studentData) {
                $student = Student::find($studentData['id']);
                if (!$student) continue;

                if ($studentData['original_data'] === null) {
                    // This was a new record - delete it
                    $student->delete();
                } else {
                    // This was an update - restore original data
                    $student->update($studentData['original_data']);
                }
            }

            Cache::forget("student_import_{$importId}");
            DB::commit();

            return response()->json(['message' => 'Import successfully undone']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function findSchoolId($schoolName)
    {
        return \App\Models\School::where('name', $schoolName)->value('id');
    }

    private function findStageId($stageName)
    {
        return \App\Models\Stage::where('name', $stageName)->value('id');
    }

    private function findGradeId($gradeName)
    {
        return \App\Models\Grade::where('name', $gradeName)->value('id');
    }

    private function findClassroomId($classroomName)
    {
        return \App\Models\Classroom::where('name', $classroomName)->value('id');
    }

    public function getFiltered(Request $request)
    {
        try {
            $query = Student::with(['school', 'stage', 'grade', 'classroom'])
                ->when($request->school_id, function ($query, $schoolId) {
                    return $query->where('school_id', $schoolId);
                })
                ->when($request->stage_id, function ($query, $stageId) {
                    return $query->where('stage_id', $stageId);
                })
                ->when($request->grade_id, function ($query, $gradeId) {
                    return $query->where('grade_id', $gradeId);
                })
                ->when($request->classroom_id, function ($query, $classroomId) {
                    return $query->where('classroom_id', $classroomId);
                });

            $records = $query->orderBy('name')->paginate(40);

            return response()->json([
                'records' => $records
            ]);

        } catch (\Exception $e) {
            Log::error('Error filtering students: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error filtering students',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}























