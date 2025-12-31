<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeacherImportController extends Controller
{
    /**
     * Display the teacher import page with school and academic year selection
     */
    public function index(): Response
    {
        return Inertia::render('my_class/admin/TeacherImport', [
            'schools' => $this->getUserSchools(),
        ]);
    }

    /**
     * Get available schools for the current user
     */
    public function getSchools(): JsonResponse
    {
        $schools = $this->getUserSchools();
        
        return response()->json([
            'success' => true,
            'schools' => $schools
        ]);
    }

    /**
     * Get the active academic year for a specific school
     */
    public function getActiveAcademicYear(int $schoolId): JsonResponse
    {
        // Validate that the user has access to this school
        $userSchools = $this->getUserSchools();
        $hasAccess = $userSchools->contains('id', $schoolId);
        
        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied to this school'
            ], 403);
        }

        // Find the active academic year for this school
        $activeAcademicYear = AcademicYear::where('school_id', $schoolId)
            ->where('active', true)
            ->first();

        if (!$activeAcademicYear) {
            return response()->json([
                'success' => false,
                'message' => 'No active academic year found for this school. Please create an active academic year first.',
                'requires_academic_year' => true
            ], 422);
        }

        return response()->json([
            'success' => true,
            'academic_year' => [
                'id' => $activeAcademicYear->id,
                'name' => $activeAcademicYear->name,
                'start_date' => $activeAcademicYear->start_date->format('Y-m-d'),
                'end_date' => $activeAcademicYear->end_date->format('Y-m-d'),
                'active' => $activeAcademicYear->active
            ]
        ]);
    }

    /**
     * Validate uploaded Excel file before processing
     */
    public function validateFile(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $fileValidation = $this->validateExcelFile($request->file('file'));
        
        if (!$fileValidation['success']) {
            return response()->json($fileValidation, 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'File validation passed',
            'file_info' => [
                'name' => $request->file('file')->getClientOriginalName(),
                'size' => $request->file('file')->getSize(),
                'extension' => $request->file('file')->getClientOriginalExtension()
            ]
        ]);
    }

    /**
     * Validate import data before processing
     */
    public function validateImport(Request $request): JsonResponse
    {
        // First validate the file if it's uploaded
        if ($request->hasFile('file')) {
            $fileValidation = $this->validateExcelFile($request->file('file'));
            if (!$fileValidation['success']) {
                return response()->json($fileValidation, 422);
            }
        }

        // Validate the basic request structure
        $validated = $request->validate([
            'data' => 'required|array|min:1',
            'school_id' => 'sometimes|integer|exists:schools,id',
            'academic_year_id' => 'sometimes|integer|exists:academic_years,id',
        ]);

        // If school_id and academic_year_id are provided, validate them
        if (isset($validated['school_id'])) {
            // Validate user access to school
            $userSchools = $this->getUserSchools();
            if (!$userSchools->contains('id', $validated['school_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to this school'
                ], 403);
            }

            // Validate academic year belongs to school and is active
            if (isset($validated['academic_year_id'])) {
                $academicYear = AcademicYear::where('id', $validated['academic_year_id'])
                    ->where('school_id', $validated['school_id'])
                    ->where('active', true)
                    ->first();

                if (!$academicYear) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or inactive academic year for the selected school'
                    ], 422);
                }
            }
        }

        // Validate column structure
        $columnValidation = $this->validateRequiredColumns($validated['data']);
        if (!$columnValidation['success']) {
            return response()->json($columnValidation, 422);
        }

        // Validate each row of data
        $dataValidation = $this->validateImportData($validated['data']);
        
        return response()->json([
            'success' => $dataValidation['success'],
            'message' => $dataValidation['message'],
            'summary' => $dataValidation['summary'],
            'fileData' => $validated['data'] // Include the data for later use
        ], $dataValidation['success'] ? 200 : 422);
    }

    /**
     * Validate Excel file format and size
     */
    private function validateExcelFile($file): array
    {
        // Check file size (10MB limit)
        $maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if ($file->getSize() > $maxSize) {
            return [
                'success' => false,
                'message' => 'File size exceeds the maximum limit of 10MB',
                'error_type' => 'file_size'
            ];
        }

        // Check file format
        $allowedExtensions = ['xlsx', 'xls'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'message' => 'Invalid file format. Only .xlsx and .xls files are allowed',
                'error_type' => 'file_format',
                'allowed_formats' => $allowedExtensions
            ];
        }

        // Check MIME type for additional security
        $allowedMimeTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-excel', // .xls
            'application/excel',
            'application/x-excel',
            'application/x-msexcel'
        ];
        
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return [
                'success' => false,
                'message' => 'Invalid file type. The file does not appear to be a valid Excel file',
                'error_type' => 'mime_type'
            ];
        }

        return [
            'success' => true,
            'message' => 'File validation passed'
        ];
    }

    /**
     * Validate that required columns are present in the data
     */
    private function validateRequiredColumns(array $data): array
    {
        if (empty($data)) {
            return [
                'success' => false,
                'message' => 'No data rows found in the file',
                'summary' => [
                    'total_rows' => 0,
                    'valid_rows' => 0,
                    'invalid_rows' => 0,
                    'errors' => ['No data rows found in the file']
                ]
            ];
        }

        $requiredColumns = ['classroom', 'subject', 'teacher_name', 'periods_per_week'];
        $optionalColumns = ['teacher_email', 'phone', 'national_id', 'gender', 'date_of_birth'];
        
        // Check the first row to determine available columns
        $firstRow = $data[0];
        $availableColumns = array_keys($firstRow);
        
        // Check for required columns
        $missingColumns = [];
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $availableColumns)) {
                $missingColumns[] = "Required column '{$column}' is missing";
            }
        }

        if (!empty($missingColumns)) {
            return [
                'success' => false,
                'message' => 'Missing required columns',
                'summary' => [
                    'total_rows' => count($data),
                    'valid_rows' => 0,
                    'invalid_rows' => count($data),
                    'errors' => $missingColumns
                ]
            ];
        }

        return [
            'success' => true,
            'message' => 'Column validation passed',
            'available_columns' => $availableColumns,
            'required_columns' => $requiredColumns,
            'optional_columns' => $optionalColumns
        ];
    }

    /**
     * Validate the import data rows
     */
    private function validateImportData(array $data): array
    {
        $errors = [];
        $validRows = 0;
        $invalidRows = 0;

        foreach ($data as $index => $row) {
            $rowNumber = $index + 1;
            $rowErrors = [];

            // Validate required fields
            if (empty(trim($row['classroom'] ?? ''))) {
                $rowErrors[] = 'Classroom is required';
            }

            if (empty(trim($row['subject'] ?? ''))) {
                $rowErrors[] = 'Subject is required';
            }

            if (empty(trim($row['teacher_name'] ?? ''))) {
                $rowErrors[] = 'Teacher Name is required';
            }

            // Validate periods_per_week
            $periodsPerWeek = $row['periods_per_week'] ?? null;
            if (empty($periodsPerWeek) || !is_numeric($periodsPerWeek) || $periodsPerWeek < 1) {
                $rowErrors[] = 'Periods per Week must be a positive number';
            }

            // Validate optional fields if provided
            if (!empty($row['teacher_email']) && !filter_var($row['teacher_email'], FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = 'Invalid email format';
            }

            if (!empty($row['gender']) && !in_array($row['gender'], ['Male', 'Female'])) {
                $rowErrors[] = 'Gender must be either Male or Female';
            }

            if (!empty($row['date_of_birth'])) {
                $date = \DateTime::createFromFormat('Y-m-d', $row['date_of_birth']);
                if (!$date || $date->format('Y-m-d') !== $row['date_of_birth'] || $date >= new \DateTime()) {
                    $rowErrors[] = 'Date of Birth must be a valid date in YYYY-MM-DD format and in the past';
                }
            }

            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'errors' => $rowErrors,
                    'data' => $row
                ];
                $invalidRows++;
            } else {
                $validRows++;
            }
        }

        $success = empty($errors);
        
        return [
            'success' => $success,
            'message' => $success ? 'All rows are valid' : "Found {$invalidRows} invalid rows out of " . count($data) . " total rows",
            'summary' => [
                'total_rows' => count($data),
                'valid_rows' => $validRows,
                'invalid_rows' => $invalidRows,
                'errors' => $errors
            ]
        ];
    }

    /**
     * Process the teacher import with comprehensive error handling and reporting
     */
    public function processImport(Request $request): JsonResponse
    {
        // Validate the request
        $validated = $request->validate([
            'school_id' => 'required|integer|exists:schools,id',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'sync_mode' => 'required|string|in:update_existing,full_sync',
            'data' => 'required|array|min:1',
        ]);

        // Validate user access to school
        $userSchools = $this->getUserSchools();
        if (!$userSchools->contains('id', $validated['school_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied to this school'
            ], 403);
        }

        // Validate academic year belongs to school and is active
        $academicYear = AcademicYear::where('id', $validated['academic_year_id'])
            ->where('school_id', $validated['school_id'])
            ->where('active', true)
            ->first();

        if (!$academicYear) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive academic year for the selected school'
            ], 422);
        }

        try {
            // Use the TeacherImportService to process the import
            $importService = app(\App\Services\TeacherImportService::class);
            
            // First validate the data structure
            $validation = $importService->validateImportData(
                $validated['data'], 
                $validated['school_id'], 
                $validated['academic_year_id']
            );
            
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data validation failed',
                    'errors' => $validation['errors']
                ], 422);
            }
            
            // Process the import with enhanced error handling
            $results = $importService->processImport(
                $validated['data'],
                $validated['school_id'],
                $validated['academic_year_id'],
                $validated['sync_mode']
            );
            
            // Generate comprehensive report
            $report = $importService->generateImportReport($results);
            
            // Determine response status based on results
            $httpStatus = 200;
            if (!$results['success']) {
                $httpStatus = 500; // Server error for critical failures
            } elseif (!empty($results['errors'])) {
                $httpStatus = 207; // Multi-status for partial success
            }
            
            return response()->json([
                'success' => $results['success'],
                'message' => $this->generateImportMessage($report),
                'report' => $report,
                'import_id' => uniqid('import_', true), // For tracking purposes
                'timestamp' => now()->toISOString()
            ], $httpStatus);
            
        } catch (\Exception $e) {
            \Log::error('Teacher import controller error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => [
                    'school_id' => $validated['school_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'sync_mode' => $validated['sync_mode'],
                    'data_count' => count($validated['data'])
                ]
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred during import processing',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }
    
    /**
     * Generate a user-friendly import message based on the report
     *
     * @param array $report Import report
     * @return string User-friendly message
     */
    private function generateImportMessage(array $report): string
    {
        $summary = $report['summary'];
        
        if (!$report['success']) {
            return 'Import failed due to critical errors. Please check the error details and try again.';
        }
        
        if ($summary['errors_count'] === 0) {
            return "Import completed successfully! Processed {$summary['processed_rows']} rows, created {$summary['teachers_created']} teachers, and managed {$summary['assignments_created']} new assignments with {$summary['assignments_updated']} updates.";
        }
        
        return "Import completed with {$summary['errors_count']} errors. Successfully processed {$summary['processed_rows']} out of {$summary['total_rows']} rows. Created {$summary['teachers_created']} teachers and managed {$summary['assignments_created']} new assignments with {$summary['assignments_updated']} updates.";
    }

    /**
     * Get schools accessible to the current user based on their role
     */
    private function getUserSchools()
    {
        $user = auth()->user();
        
        switch ($user->role) {
            case 'hr_admin':
                // HR admin can access all schools
                return School::select('id', 'name')->get();
                
            case 'admin':
                // School admin can access their assigned school
                $schoolId = $user->schoolId();
                if ($schoolId) {
                    return School::where('id', $schoolId)->select('id', 'name')->get();
                }
                return collect();
                
            case 'teacher':
                // Teachers can access their school
                $schoolId = $user->teacher?->school_id;
                if ($schoolId) {
                    return School::where('id', $schoolId)->select('id', 'name')->get();
                }
                return collect();
                
            default:
                return collect();
        }
    }
}