<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Models\CourseManagement\Course;
use App\Models\CourseManagement\CourseLevel;
use App\Models\CourseManagement\CourseSection;
use App\Models\CourseManagement\CourseLesson;
use App\Models\Student;
use App\Models\User;
use App\Models\Classroom;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Inertia\Inertia;

class StudentImportController extends Controller
{
    public function index()
    {
        return Inertia::render('CourseManagement/Import/import_students/Index');
    // }resources\js\Pages\CourseManagement\Import\import_students\Index.vue
    }
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['number', 'classroom', 'full_name'];
        $sheet->fromArray($headers, null, 'A1');

        // Add sample data
        $sampleData = [
    ['number', 'classroom', 'full_name'],
    ['number', 'classroom', 'full_name'],
    ['number', 'classroom', 'full_name']
        ];

        $sheet->fromArray($sampleData, null, 'A2');

        // Style the header row
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

        // Auto-size columns
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders to data
        $dataRange = 'A1:C' . (count($sampleData) + 1);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        // Create writer and download
        $writer = new Xlsx($spreadsheet);
        $filename = 'course_management_template_' . date('Y-m-d') . '.xlsx';

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function validateFile(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*' => 'required|array',
            'data.*.classroom' => 'required|string|max:255',
            'data.*.full_name' => 'required|string|max:255',
        
        ]);

        try {
            $data = $request->input('data');
            $preview = [];
            $errors = [];
            $validRows = 0;
            $invalidRows = 0;

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 because of header row and 0-based index
                $isValid = true;

                // Validate required fields
                $requiredFields =  ['number', 'classroom', 'full_name'];
                foreach ($requiredFields as $field) {
                    if (empty(trim($row[$field] ?? ''))) {
                        $errors[] = "Row {$rowNumber}: Missing required field '{$field}'";
                        $isValid = false;
                    }
                }

                // Validate field lengths
                $allFields =  ['number', 'classroom', 'full_name'];
                foreach ($allFields as $field) {
                    if (!empty($row[$field]) && strlen(trim($row[$field])) > 255) {
                        $errors[] = "Row {$rowNumber}: {$field} too long (max 255 characters)";
                        $isValid = false;
                    }
                }

                if ($isValid) {
                    $validRows++;
                    if (count($preview) < 10) { // Show first 10 valid rows as preview
                        $preview[] = [
                            'number' => $rowNumber,
                            'classroom' => trim($row['classroom'] ?? ''),
                            'full_name' => trim($row['full_name'] ?? ''),
                    
                        ];
                    }
                } else {
                    $invalidRows++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'File validation successful',
                'preview' => $preview,
                'fileData' => $data, // Include full file data for preview
                'stats' => [
                    'total_rows' => count($data),
                    'valid_rows' => $validRows,
                    'invalid_rows' => $invalidRows,
                ],
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'File validation failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->input('data');
            $stats = [
                'courses_created' => 0,
                'courses_found' => 0,
                'levels_created' => 0,
                'levels_found' => 0,
                'sections_created' => 0,
                'sections_found' => 0,
                'lessons_created' => 0,
                'lessons_skipped' => 0,
                'total_processed' => 0,
            ];

            $errors = [];
            foreach ($data as $index => $row) {
                $rowNumber = $index + 2;
                $number = trim($row['number'] ?? '');
                $classroom = trim($row['classroom'] ?? '');
                $fullName = trim($row['full_name'] ?? '');

                // Skip empty rows (require full_name and classroom at minimum)
                if (empty($fullName) || empty($classroom)) {
                    continue;
                }
                // Generate a short random string (letters + digits).
                // Str::random does not accept a character set, so use str_shuffle on literal strings.
                $letters = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 3);
                $numbers = substr(str_shuffle('0123456789'), 0, 3);
                // Concatenate and shuffle to mix letters and digits, length 6
                $randomString = substr(str_shuffle($letters . $numbers), 0, 6);


                // Resolve classroom string to Classroom model
                $classroomRecord = null;
                if (!empty($classroom)) {
                    $normalized = trim($classroom);
                    // 1) exact match
                    $classroomRecord = Classroom::where('name', $normalized)->first();
                    // 2) case-insensitive match
                    if (!$classroomRecord) {
                        $classroomRecord = Classroom::whereRaw('LOWER(name) = ?', [mb_strtolower($normalized)])->first();
                    }
                    // 3) prefix match (e.g., "6A" matches "6A - Section")
                    if (!$classroomRecord) {
                        $classroomRecord = Classroom::where('name', 'like', $normalized . '%')->first();
                    }
                }

                if (!$classroomRecord) {
                    // Don't create student if classroom cannot be resolved because DB requires classroom_id
                    $errors[] = "Row {$rowNumber}: Classroom '{$classroom}' not found";
                    // still create/find user (optional), but skip student creation
                }

                // Create or find User by name/email fallback
                // We'll create a unique email using slug + random to avoid clashes if email not provided
                $emailCandidate = Str::slug($fullName) .$randomString . '@msc';
                $existingUser = User::where('email', $emailCandidate)->orWhere('name', $fullName)->first();

                if (!$existingUser) {
                    $user = User::create([
                        'name' => $fullName,
                        'email' => $emailCandidate,
                        'password' => bcrypt('changeme123'),
                    ]);
                    $stats['users_created'] = ($stats['users_created'] ?? 0) + 1;
                } else {
                    $user = $existingUser;
                    $stats['users_found'] = ($stats['users_found'] ?? 0) + 1;
                }

                // Create or find Student linked to user (only if classroom resolved)
                if ($classroomRecord) {
                    // Ensure classroom has required stage_id (DB may require it non-null)
                    if (empty($classroomRecord->stage_id)) {
                        $errors[] = "Row {$rowNumber}: Classroom '{$classroom}' does not have a stage_id configured";
                        // Skip creating this student
                        continue;
                    }
                    $student = Student::where('user_id', $user->id)->orWhere('name', $fullName)->first();
                    if (!$student) {
                        $student = Student::create([
                            'name' => $fullName,
                            'user_id' => $user->id,
                            'classroom_id' => $classroomRecord->id,
                            'school_id' => $classroomRecord->school_id ?? null,
                            'stage_id' => $classroomRecord->stage_id ?? null,
                            'grade_id' => $classroomRecord->grade_id ?? null,
                            'data' => [
                                'import_number' => $number,
                                'import_classroom' => $classroom
                            ]
                        ]);
                        $stats['students_created'] = ($stats['students_created'] ?? 0) + 1;
                    } else {
                        $stats['students_found'] = ($stats['students_found'] ?? 0) + 1;
                    }
                    $stats['total_processed']++;
                } else {
                    // Skip creating student due to missing classroom; already logged error
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully!',
                'stats' => $stats,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 422);
        }
    }
}