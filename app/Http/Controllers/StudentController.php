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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        // Ensure schools array is never empty - fallback to all schools
        $schools = $accessData['schools'] ?? [];
        if (empty($schools)) {
            $schools = \App\Models\School::orderBy('name')->get(['id', 'name'])->toArray();
        }

        return Inertia::render('my_class/admin/Students/Index', [
            'records' => $students,
            'schools' => $schools,
            'grades' => \App\Models\Grade::orderBy('name')->get(['id', 'name']),
            'academicYears' => \App\Models\AcademicYear::orderBy('name')->get(['id', 'name']),
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
        // Validation will auto-throw ValidationException (422)
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

        try {
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
            
            // Save directly to public/uploads/avatars
            $destinationPath = public_path('uploads/avatars');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            $file->move($destinationPath, $filename);
            $relativePath = 'uploads/avatars/' . $filename;

            // Delete old avatar if exists
            if ($student->avatar) {
                 $oldPath = public_path($student->avatar); // Try absolute public path
                 if (file_exists($oldPath) && is_file($oldPath)) {
                     unlink($oldPath);
                 } elseif (strpos($student->avatar, '/storage/') === 0) {
                     // Fallback for legacy storage path
                     $storagePath = str_replace('/storage/', '', $student->avatar);
                     if (Storage::disk('public')->exists($storagePath)) {
                         Storage::disk('public')->delete($storagePath);
                     }
                 }
            }

            // Save public relative path to student
            $student->avatar = $relativePath;
            $student->save();

            return response()->json(['avatar' => $student->avatar_url]);
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
                 $path = public_path($student->avatar);
                 
                 if (file_exists($path) && is_file($path)) {
                     unlink($path);
                 } else {
                     // Legacy storage cleanup
                     $storagePath = str_replace('/storage/', '', $student->avatar);
                     if (Storage::disk('public')->exists($storagePath)) {
                         Storage::disk('public')->delete($storagePath);
                     }
                 }
                
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

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['name', 'name_ar', 'name_cute', 'notes'];
        $sheet->fromArray($headers, null, 'A1');

        // Add sample data
        $sampleData = [
            ['John Doe', 'جون دو', 'Johnny', 'Sample note'],
            ['Jane Smith', 'جين سميث', 'Janie', 'Another note'],
            ['Ahmed Ali', 'أحمد علي', 'Ahmed', 'Sample student']
        ];

        $sheet->fromArray($sampleData, null, 'A2');

        // Style the header row
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

        // Auto-size columns
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders to data
        $dataRange = 'A1:D' . (count($sampleData) + 1);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        // Create writer and download
        $writer = new Xlsx($spreadsheet);
        $filename = 'students_template_' . date('Y-m-d') . '.xlsx';

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function downloadTemplateWithClassroom()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['name', 'name_ar', 'name_cute', 'classroom', 'notes'];
        $sheet->fromArray($headers, null, 'A1');

        // Add sample data with various classroom formats
        $sampleData = [
            ['John Doe', 'جون دو', 'Johnny', '4A', 'Sample note'],
            ['Jane Smith', 'جين سميث', 'Janie', 'Grade 4 - A', 'Another note'],
            ['Ahmed Ali', 'أحمد علي', 'Ahmed', '4-A', 'Sample student']
        ];

        $sheet->fromArray($sampleData, null, 'A2');

        // Style the header row
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ];
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        // Auto-size columns
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders to data
        $dataRange = 'A1:E' . (count($sampleData) + 1);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        // Create writer and download
        $writer = new Xlsx($spreadsheet);
        $filename = 'students_template_with_classroom_' . date('Y-m-d') . '.xlsx';

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
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
     * Import students with classroom column - school-wide import
     * Processes ONE record at a time to avoid timeout
     * Supports two modes: 'skip' (default) or 'update'
     */
    public function importWithClassroom(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string',
            'classroom' => 'required|string',
            'name_ar' => 'nullable|string',
            'name_cute' => 'nullable|string',
            'notes' => 'nullable|string',
            'import_mode' => 'nullable|string|in:skip,update',
        ]);

        try {
            $schoolId = $request->school_id;
            $name = trim($request->name);
            $classroomName = trim($request->classroom);
            $importMode = $request->import_mode ?? 'skip'; // Default to skip mode

            // Smart classroom matching
            $classroom = $this->findClassroom($classroomName, $schoolId);

            if (!$classroom) {
                return response()->json([
                    'success' => false,
                    'status' => 'classroom_not_found',
                    'message' => "Classroom '{$classroomName}' not found in this school"
                ]);
            }

            // Validate classroom belongs to the selected school
            if ($classroom->school_id != $schoolId) {
                return response()->json([
                    'success' => false,
                    'status' => 'classroom_mismatch',
                    'message' => "Classroom '{$classroom->name}' belongs to a different school"
                ]);
            }

            // Validate classroom has required fields
            if (empty($classroom->stage_id) || empty($classroom->grade_id)) {
                return response()->json([
                    'success' => false,
                    'status' => 'classroom_invalid',
                    'message' => "Classroom '{$classroom->name}' is missing stage_id or grade_id. Please configure the classroom properly."
                ]);
            }

            // Check for duplicates using normalized name comparison
            $duplicateCheck = $this->checkDuplicateStudent($name, $schoolId, $classroom->id);

            if ($duplicateCheck) {
                // Handle soft-deleted students
                if ($duplicateCheck['status'] === 'soft_deleted') {
                    $softDeletedStudent = $duplicateCheck['student'];
                    $daysSinceDeletion = $duplicateCheck['days_since_deletion'];

                    // Time-based restoration logic
                    if ($daysSinceDeletion <= 7) {
                        // Auto-restore (likely accidental deletion)
                        $softDeletedStudent->restore();
                        $softDeletedStudent->update([
                            'classroom_id' => $classroom->id,
                            'grade_id' => $classroom->grade_id,
                            'stage_id' => $classroom->stage_id,
                            'name_ar' => trim($request->name_ar ?? $softDeletedStudent->name_ar ?? ''),
                            'name_cute' => trim($request->name_cute ?? $softDeletedStudent->name_cute ?? ''),
                            'notes' => trim($request->notes ?? $softDeletedStudent->notes ?? ''),
                        ]);

                        return response()->json([
                            'success' => true,
                            'status' => 'restored',
                            'message' => "Student '{$name}' was restored (deleted {$daysSinceDeletion} days ago)",
                            'student_id' => $softDeletedStudent->id
                        ]);
                    } elseif ($daysSinceDeletion <= 90) {
                        // Ask user (return warning status)
                        return response()->json([
                            'success' => true,
                            'status' => 'soft_deleted_warning',
                            'message' => "Student '{$name}' was deleted {$daysSinceDeletion} days ago. Creating new record.",
                            'days_since_deletion' => $daysSinceDeletion
                        ]);
                    }
                    // If > 90 days, proceed to create new student (fall through)
                }

                // Handle active duplicate students
                if ($duplicateCheck['status'] === 'duplicate') {
                    $existingStudent = $duplicateCheck['student'];

                    if ($importMode === 'update') {
                        // Update mode: Update existing student with non-empty fields
                        $updateData = [];
                        
                        if (!empty($request->name_ar)) {
                            $updateData['name_ar'] = trim($request->name_ar);
                        }
                        if (!empty($request->name_cute)) {
                            $updateData['name_cute'] = trim($request->name_cute);
                        }
                        if (!empty($request->notes)) {
                            $updateData['notes'] = trim($request->notes);
                        }
                        
                        // Always update classroom assignment
                        $updateData['classroom_id'] = $classroom->id;
                        $updateData['grade_id'] = $classroom->grade_id;
                        $updateData['stage_id'] = $classroom->stage_id;
                        $updateData['school_id'] = $classroom->school_id;

                        $existingStudent->update($updateData);

                        return response()->json([
                            'success' => true,
                            'status' => 'updated',
                            'message' => "Student '{$name}' updated successfully in {$classroom->name}",
                            'student_id' => $existingStudent->id,
                            'updated_fields' => array_keys($updateData)
                        ]);
                    } else {
                        // Skip mode: Return duplicate status
                        return response()->json([
                            'success' => true,
                            'status' => 'duplicate',
                            'message' => "Student '{$name}' already exists in {$classroom->name}",
                            'student_id' => $existingStudent->id
                        ]);
                    }
                }
            }

            // No duplicate found - create new student
            // User will be created automatically by Student model
            $student = Student::create([
                'name' => $name,
                'name_ar' => trim($request->name_ar ?? ''),
                'name_cute' => trim($request->name_cute ?? ''),
                'notes' => trim($request->notes ?? ''),
                'school_id' => $classroom->school_id,
                'stage_id' => $classroom->stage_id,
                'grade_id' => $classroom->grade_id,
                'classroom_id' => $classroom->id,
            ]);

            return response()->json([
                'success' => true,
                'status' => 'created',
                'message' => "Student '{$name}' created successfully in {$classroom->name}",
                'student_id' => $student->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Student import error: ' . $e->getMessage(), [
                'name' => $request->name ?? null,
                'classroom' => $request->classroom ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Failed to process student: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Smart classroom finder - tries multiple matching strategies
     */
    private function findClassroom($classroomName, $schoolId)
    {
        // 1. Exact match
        $classroom = Classroom::where('school_id', $schoolId)
            ->where('name', $classroomName)
            ->select('id', 'name', 'school_id', 'stage_id', 'grade_id')
            ->first();

        if ($classroom) {
            return $classroom;
        }

        // 2. Case-insensitive match
        $classroom = Classroom::where('school_id', $schoolId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($classroomName)])
            ->select('id', 'name', 'school_id', 'stage_id', 'grade_id')
            ->first();

        if ($classroom) {
            return $classroom;
        }

        // 3. Normalized match (using existing normalizeClassroomName method)
        $normalized = $this->normalizeClassroomName($classroomName);
        
        $classrooms = Classroom::where('school_id', $schoolId)
            ->select('id', 'name', 'school_id', 'stage_id', 'grade_id')
            ->get();
        
        foreach ($classrooms as $cls) {
            if ($this->normalizeClassroomName($cls->name) === $normalized) {
                return $cls;
            }
        }

        // 4. Partial match (contains)
        $classroom = Classroom::where('school_id', $schoolId)
            ->where('name', 'like', '%' . $classroomName . '%')
            ->select('id', 'name', 'school_id', 'stage_id', 'grade_id')
            ->first();

    }

    /**
     * Check for duplicate student using normalized name comparison
     * Returns null if no duplicate, or array with status and student data
     */
    private function checkDuplicateStudent($name, $schoolId, $classroomId = null)
    {
        $normalizedName = Student::normalizeName($name);

        // Check for active students with same normalized name
        $query = Student::where('school_id', $schoolId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($normalizedName)]);
        
        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }

        $existingStudent = $query->first();

        if ($existingStudent) {
            return [
                'status' => 'duplicate',
                'student' => $existingStudent
            ];
        }

        // Check for soft-deleted students with same normalized name
        $softDeletedQuery = Student::onlyTrashed()
            ->where('school_id', $schoolId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($normalizedName)]);
        
        if ($classroomId) {
            $softDeletedQuery->where('classroom_id', $classroomId);
        }

        $softDeletedStudent = $softDeletedQuery->first();

        if ($softDeletedStudent) {
            $daysSinceDeletion = now()->diffInDays($softDeletedStudent->deleted_at);
            
            return [
                'status' => 'soft_deleted',
                'student' => $softDeletedStudent,
                'days_since_deletion' => $daysSinceDeletion
            ];
        }

        return null; // No duplicate found
    }

    /**
     * Validate a batch of students before import
     * Returns status for each student and overall summary
     */
    public function validateImportBatch(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'students' => 'required|array',
            'students.*.name' => 'required|string',
            'students.*.classroom' => 'required|string',
            'students.*.name_ar' => 'nullable|string',
            'students.*.name_cute' => 'nullable|string',
            'students.*.notes' => 'nullable|string',
            'import_mode' => 'nullable|string|in:skip,update',
        ]);

        $schoolId = $request->school_id;
        $students = $request->students;
        $importMode = $request->import_mode ?? 'skip';

        $validations = [];
        $summary = [
            'total' => count($students),
            'will_create' => 0,
            'will_update' => 0,
            'will_skip' => 0,
            'will_restore' => 0,
            'errors' => 0,
        ];

        foreach ($students as $index => $studentData) {
            $name = trim($studentData['name']);
            $classroomName = trim($studentData['classroom']);

            // Find classroom
            $classroom = $this->findClassroom($classroomName, $schoolId);

            if (!$classroom) {
                $validations[] = [
                    'index' => $index,
                    'name' => $name,
                    'classroom' => $classroomName,
                    'status' => 'error',
                    'message' => "Classroom '{$classroomName}' not found",
                    'icon' => '❌',
                    'color' => 'negative'
                ];
                $summary['errors']++;
                continue;
            }

            // Validate classroom
            if ($classroom->school_id != $schoolId) {
                $validations[] = [
                    'index' => $index,
                    'name' => $name,
                    'classroom' => $classroomName,
                    'status' => 'error',
                    'message' => "Classroom belongs to different school",
                    'icon' => '❌',
                    'color' => 'negative'
                ];
                $summary['errors']++;
                continue;
            }

            if (empty($classroom->stage_id) || empty($classroom->grade_id)) {
                $validations[] = [
                    'index' => $index,
                    'name' => $name,
                    'classroom' => $classroomName,
                    'status' => 'error',
                    'message' => "Classroom missing stage_id or grade_id",
                    'icon' => '❌',
                    'color' => 'negative'
                ];
                $summary['errors']++;
                continue;
            }

            // Check for duplicates
            $duplicateCheck = $this->checkDuplicateStudent($name, $schoolId, $classroom->id);

            if ($duplicateCheck) {
                if ($duplicateCheck['status'] === 'soft_deleted') {
                    $daysSinceDeletion = $duplicateCheck['days_since_deletion'];
                    
                    if ($daysSinceDeletion <= 7) {
                        $validations[] = [
                            'index' => $index,
                            'name' => $name,
                            'classroom' => $classroomName,
                            'status' => 'will_restore',
                            'message' => "Will restore (deleted {$daysSinceDeletion} days ago)",
                            'icon' => '🔄',
                            'color' => 'info'
                        ];
                        $summary['will_restore']++;
                    } else {
                        $validations[] = [
                            'index' => $index,
                            'name' => $name,
                            'classroom' => $classroomName,
                            'status' => 'will_create',
                            'message' => "Will create new (old record deleted {$daysSinceDeletion} days ago)",
                            'icon' => '✅',
                            'color' => 'positive'
                        ];
                        $summary['will_create']++;
                    }
                } elseif ($duplicateCheck['status'] === 'duplicate') {
                    if ($importMode === 'update') {
                        $validations[] = [
                            'index' => $index,
                            'name' => $name,
                            'classroom' => $classroomName,
                            'status' => 'will_update',
                            'message' => 'Will update existing student',
                            'icon' => '📝',
                            'color' => 'primary'
                        ];
                        $summary['will_update']++;
                    } else {
                        $validations[] = [
                            'index' => $index,
                            'name' => $name,
                            'classroom' => $classroomName,
                            'status' => 'will_skip',
                            'message' => 'Already exists, will skip',
                            'icon' => '⏭️',
                            'color' => 'warning'
                        ];
                        $summary['will_skip']++;
                    }
                }
            } else {
                // No duplicate - will create
                $validations[] = [
                    'index' => $index,
                    'name' => $name,
                    'classroom' => $classroomName,
                    'status' => 'will_create',
                    'message' => 'Will create new student',
                    'icon' => '✅',
                    'color' => 'positive'
                ];
                $summary['will_create']++;
            }
        }

        return response()->json([
            'validations' => $validations,
            'summary' => $summary
        ]);
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
                })
                ->when($request->search, function ($query, $search) {
                    return $query->search($search);
                });

            // Sorting logic
            $sortBy = $request->input('sort_by', 'name');
            $sortDirection = $request->boolean('descending') ? 'desc' : 'asc';
            
            // Allow sorting by relation columns if needed, or just standard columns
            // For simplicity, we'll sort by the main table columns or use joins for relations later if requested
            // basic safety check to prevent SQL injection if column doesn't exist
            $sortableColumns = ['name', 'name_ar', 's_id', 'created_at'];
            if (in_array($sortBy, $sortableColumns)) {
                $query->orderBy($sortBy, $sortDirection);
            } else {
                $query->orderBy('name', 'asc'); // Default
            }

            $records = $query->paginate($request->input('rows_per_page', 40));

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

    /**
     * Student Promotion System
     */
    public function promoteStudents(Request $request)
    {
        $validated = $request->validate([
            'source_grade_id' => 'required|exists:grades,id',
            'target_grade_id' => 'required|exists:grades,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'classroom_mappings' => 'required|array',
            'classroom_mappings.*.from_classroom_id' => 'required|exists:classrooms,id',
            'classroom_mappings.*.to_classroom_id' => 'required|exists:classrooms,id',
            'promotion_reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $promoted = 0;
            $errors = [];

            foreach ($validated['classroom_mappings'] as $mapping) {
                // Get students in source classroom
                $students = Student::where('classroom_id', $mapping['from_classroom_id'])
                    ->where('grade_id', $validated['source_grade_id'])
                    ->get();

                foreach ($students as $student) {
                    try {
                        $student->promoteToNextGrade(
                            $validated['target_grade_id'],
                            $mapping['to_classroom_id'],
                            $validated['academic_year_id'],
                            $validated['promotion_reason']
                        );

                        // Update the last history record with notes if provided
                        if ($validated['notes']) {
                            $student->classroomHistories()->first()->update([
                                'notes' => $validated['notes']
                            ]);
                        }

                        $promoted++;
                    } catch (\Exception $e) {
                        $errors[] = "Failed to promote {$student->name}: " . $e->getMessage();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully promoted {$promoted} students",
                'promoted_count' => $promoted,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Student promotion failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Promotion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPromotionPreview(Request $request)
    {
        $validated = $request->validate([
            'source_grade_id' => 'required|exists:grades,id',
            'target_grade_id' => 'required|exists:grades,id',
            'classroom_mappings' => 'required|array',
        ]);

        $preview = [];
        $warnings = [];

        foreach ($validated['classroom_mappings'] as $mapping) {
            $fromClassroom = Classroom::with('grade')->find($mapping['from_classroom_id']);
            $toClassroom = Classroom::with('grade')->find($mapping['to_classroom_id']);

            $students = Student::where('classroom_id', $mapping['from_classroom_id'])
                ->where('grade_id', $validated['source_grade_id'])
                ->get(['id', 'name', 'name_ar']);

            // Check capacity
            $currentCount = Student::where('classroom_id', $mapping['to_classroom_id'])->count();
            $incomingCount = $students->count();
            $totalAfter = $currentCount + $incomingCount;

            // Assuming max capacity of 30 (you can make this configurable)
            $capacity = 30;
            if ($totalAfter > $capacity) {
                $warnings[] = [
                    'type' => 'overcapacity',
                    'classroom' => $toClassroom->name,
                    'message' => "Classroom {$toClassroom->name} will be over capacity ({$totalAfter}/{$capacity})"
                ];
            }

            $preview[] = [
                'from_classroom' => [
                    'id' => $fromClassroom->id,
                    'name' => $fromClassroom->name,
                    'grade' => $fromClassroom->grade->name
                ],
                'to_classroom' => [
                    'id' => $toClassroom->id,
                    'name' => $toClassroom->name,
                    'grade' => $toClassroom->grade->name,
                    'current_count' => $currentCount,
                    'capacity' => $capacity
                ],
                'students' => $students,
                'student_count' => $students->count()
            ];
        }

        return response()->json([
            'preview' => $preview,
            'warnings' => $warnings,
            'total_students' => collect($preview)->sum('student_count')
        ]);
    }

    public function getClassroomMappingSuggestions(Request $request)
    {
        $sourceGradeId = $request->input('source_grade_id');
        $targetGradeId = $request->input('target_grade_id');

        $sourceClassrooms = Classroom::where('grade_id', $sourceGradeId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $targetClassrooms = Classroom::where('grade_id', $targetGradeId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $suggestions = [];

        foreach ($sourceClassrooms as $sourceClassroom) {
            // Try to find matching classroom name
            $sourceName = $this->normalizeClassroomName($sourceClassroom->name);
            
            $bestMatch = null;
            $highestScore = 0;

            foreach ($targetClassrooms as $targetClassroom) {
                $targetName = $this->normalizeClassroomName($targetClassroom->name);
                $score = $this->calculateNameSimilarity($sourceName, $targetName);

                if ($score > $highestScore) {
                    $highestScore = $score;
                    $bestMatch = $targetClassroom;
                }
            }

            $suggestions[] = [
                'from_classroom_id' => $sourceClassroom->id,
                'from_classroom_name' => $sourceClassroom->name,
                'to_classroom_id' => $bestMatch?->id,
                'to_classroom_name' => $bestMatch?->name,
                'confidence' => $highestScore,
                'auto_suggested' => $highestScore > 70
            ];
        }

        return response()->json([
            'suggestions' => $suggestions,
            'unmapped_source' => $sourceClassrooms->whereNotIn('id', collect($suggestions)->pluck('from_classroom_id')),
            'available_target' => $targetClassrooms
        ]);
    }

    private function normalizeClassroomName($name)
    {
        // Remove common prefixes and suffixes
        $normalized = preg_replace('/^(grade|class|room|صف)\s*/i', '', $name);
        $normalized = preg_replace('/\s*(grade|class|room|صف)$/i', '', $normalized);
        
        // Extract letter/number pattern (e.g., "4A", "4-A", "4 A")
        if (preg_match('/(\d+)\s*[-\s]*([A-Z])/i', $normalized, $matches)) {
            return strtoupper($matches[2]); // Return just the letter
        }
        
        return strtolower(trim($normalized));
    }

    private function calculateNameSimilarity($str1, $str2)
    {
        // If both are single letters and match, 100% confidence
        if (strlen($str1) === 1 && strlen($str2) === 1) {
            return $str1 === $str2 ? 100 : 0;
        }

        // Use Levenshtein distance for longer strings
        $distance = levenshtein(strtolower($str1), strtolower($str2));
        $maxLength = max(strlen($str1), strlen($str2));
        
        return $maxLength > 0 ? (1 - ($distance / $maxLength)) * 100 : 0;
    }

    public function getClassroomHistory(Student $student)
    {
        $history = $student->classroomHistories()
            ->with(['fromClassroom', 'toClassroom', 'fromGrade', 'toGrade', 'academicYear', 'semester', 'changedBy'])
            ->get();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'current_classroom' => $student->classroom->name ?? null,
                'current_grade' => $student->grade->name ?? null
            ],
            'history' => $history
        ]);
    }
}























