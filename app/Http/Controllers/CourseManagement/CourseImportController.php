<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Models\CourseManagement\Course;
use App\Models\CourseManagement\CourseLevel;
use App\Models\CourseManagement\CourseSection;
use App\Models\CourseManagement\CourseLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Inertia\Inertia;

class CourseImportController extends Controller
{
    public function index()
    {
        return Inertia::render('CourseManagement/Import/Index');
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
            'data.*.Course' => 'required|string|max:255',
            'data.*.Level' => 'required|string|max:255',
            'data.*.Section' => 'nullable|string|max:255',
            'data.*.Lesson' => 'required|string|max:255',
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
                $requiredFields = ['Course', 'Level', 'Lesson'];
                foreach ($requiredFields as $field) {
                    if (empty(trim($row[$field] ?? ''))) {
                        $errors[] = "Row {$rowNumber}: Missing required field '{$field}'";
                        $isValid = false;
                    }
                }

                // Validate field lengths
                $allFields = ['Course', 'Level', 'Section', 'Lesson'];
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
                            'row' => $rowNumber,
                            'course' => trim($row['Course'] ?? ''),
                            'level' => trim($row['Level'] ?? ''),
                            'section' => trim($row['Section'] ?? ''),
                            'lesson' => trim($row['Lesson'] ?? ''),
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

            foreach ($data as $row) {
                $courseName = trim($row['Course'] ?? '');
                $levelTitle = trim($row['Level'] ?? '');
                $sectionTitle = trim($row['Section'] ?? '');
                $lessonTitle = trim($row['Lesson'] ?? '');

                // Skip empty rows
                if (empty($courseName) || empty($levelTitle) || empty($lessonTitle)) {
                    continue;
                }
                
                // Use default section name if empty
                if (empty($sectionTitle)) {
                    $sectionTitle = 'General';  // Default section name
                }

                // Find or create Course
                $course = Course::where('name', $courseName)->first();
                if (!$course) {
                    $course = Course::create([
                        'name' => $courseName,
                        'description' => "Imported course: {$courseName}",
                        'created_by' => Auth::id(),
                    ]);
                    $stats['courses_created']++;
                } else {
                    $stats['courses_found']++;
                }

                // Find or create Level
                $level = CourseLevel::where('course_id', $course->id)
                    ->where('title', $levelTitle)
                    ->first();
                
                if (!$level) {
                    $maxOrder = CourseLevel::where('course_id', $course->id)->max('order') ?? 0;
                    $level = CourseLevel::create([
                        'title' => $levelTitle,
                        'order' => $maxOrder + 1,
                        'course_id' => $course->id,
                        'created_by' => Auth::id(),
                    ]);
                    $stats['levels_created']++;
                } else {
                    $stats['levels_found']++;
                }

                // Find or create Section
                $section = CourseSection::where('course_level_id', $level->id)
                    ->where('title', $sectionTitle)
                    ->first();
                
                if (!$section) {
                    $maxOrder = CourseSection::where('course_level_id', $level->id)->max('order') ?? 0;
                    $section = CourseSection::create([
                        'title' => $sectionTitle,
                        'order' => $maxOrder + 1,
                        'course_level_id' => $level->id,
                        'created_by' => Auth::id(),
                    ]);
                    $stats['sections_created']++;
                } else {
                    $stats['sections_found']++;
                }

                // Create Lesson (avoid duplicates)
                $existingLesson = CourseLesson::where('course_section_id', $section->id)
                    ->where('title', $lessonTitle)
                    ->first();
                
                if (!$existingLesson) {
                    $maxOrder = CourseLesson::where('course_section_id', $section->id)->max('order') ?? 0;
                    CourseLesson::create([
                        'title' => $lessonTitle,
                        'text' => "Imported lesson: {$lessonTitle}",
                        'data' => [],
                        'order' => $maxOrder + 1,
                        'course_section_id' => $section->id,
                        'created_by' => Auth::id(),
                    ]);
                    $stats['lessons_created']++;
                } else {
                    $stats['lessons_skipped']++;
                }

                $stats['total_processed']++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully!',
                'stats' => $stats,
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