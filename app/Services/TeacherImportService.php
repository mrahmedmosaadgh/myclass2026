<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\ClassroomSubjectTeacher;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Stage;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TeacherImportService
{
    /**
     * Validate import data structure and content
     *
     * @param array $data Excel data rows
     * @param int $schoolId Selected school ID
     * @param int $academicYearId Selected academic year ID
     * @return array Validation results with errors
     */
    public function validateImportData(array $data, int $schoolId, int $academicYearId): array
    {
        $errors = [];
        $requiredColumns = ['classroom', 'subject', 'teacher_name', 'periods_per_week'];
        
        // Validate required columns exist
        if (empty($data)) {
            $errors[] = 'No data found in Excel file';
            return ['valid' => false, 'errors' => $errors];
        }
        
        $firstRow = $data[0] ?? [];
        foreach ($requiredColumns as $column) {
            if (!array_key_exists($column, $firstRow)) {
                $errors[] = "Required column '{$column}' is missing";
            }
        }
        
        if (!empty($errors)) {
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Validate each row
        foreach ($data as $index => $row) {
            $rowNumber = $index + 1;
            
            // Check required fields
            if (empty(trim($row['classroom'] ?? ''))) {
                $errors[] = "Row {$rowNumber}: Classroom is required";
            }
            
            if (empty(trim($row['subject'] ?? ''))) {
                $errors[] = "Row {$rowNumber}: Subject is required";
            }
            
            if (empty(trim($row['teacher_name'] ?? ''))) {
                $errors[] = "Row {$rowNumber}: Teacher Name is required";
            }
            
            // Validate periods_per_week (requirement 5.6)
            $periods = $row['periods_per_week'] ?? '';
            if (!is_numeric($periods) || $periods <= 0) {
                $errors[] = "Row {$rowNumber}: Periods_per_Week must be a positive number";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'row_count' => count($data)
        ];
    }
    
    /**
     * Process the import with transaction management and chunk processing
     *
     * @param array $data Excel data rows
     * @param int $schoolId Selected school ID
     * @param int $academicYearId Selected academic year ID
     * @param string $syncMode 'update_existing' or 'full_sync'
     * @return array Import results
     */
    public function processImport(array $data, int $schoolId, int $academicYearId, string $syncMode): array
    {
        $results = [
            'success' => false,
            'teachers_created' => 0,
            'assignments_created' => 0,
            'assignments_updated' => 0,
            'errors' => [],
            'processed_rows' => 0,
            'total_rows' => count($data),
            'chunks_processed' => 0
        ];
        
        try {
            DB::beginTransaction();
            
            // Requirement 5.3: Full Sync mode - replace all assignments for school and academic year
            if ($syncMode === 'full_sync') {
                ClassroomSubjectTeacher::where('school_id', $schoolId)
                    ->where('academic_year_id', $academicYearId)
                    ->delete();
                Log::info('Full sync: Removed existing assignments', [
                    'school_id' => $schoolId, 
                    'academic_year_id' => $academicYearId
                ]);
            }
            
            // Requirement 7.1: Process large files using chunk reading for files larger than 1000 rows
            $chunkSize = 1000;
            $totalRows = count($data);
            
            if ($totalRows > $chunkSize) {
                Log::info('Processing large file with chunking', [
                    'total_rows' => $totalRows,
                    'chunk_size' => $chunkSize,
                    'school_id' => $schoolId,
                    'academic_year_id' => $academicYearId
                ]);
                
                // Process data in chunks
                $chunks = array_chunk($data, $chunkSize, true);
                foreach ($chunks as $chunkIndex => $chunk) {
                    $this->processChunk($chunk, $schoolId, $academicYearId, $results, $chunkIndex);
                    $results['chunks_processed']++;
                    
                    // Log progress for large imports
                    Log::info('Chunk processed', [
                        'chunk' => $chunkIndex + 1,
                        'total_chunks' => count($chunks),
                        'rows_processed' => $results['processed_rows']
                    ]);
                }
            } else {
                // Process all data at once for smaller files
                $this->processChunk($data, $schoolId, $academicYearId, $results, 0);
                $results['chunks_processed'] = 1;
            }
            
            DB::commit();
            $results['success'] = true;
            
            Log::info('Teacher import completed successfully', [
                'sync_mode' => $syncMode,
                'school_id' => $schoolId,
                'academic_year_id' => $academicYearId,
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            // Requirement 7.4: Rollback all changes if critical error occurs
            DB::rollBack();
            $results['errors'][] = 'Critical import failure: ' . $e->getMessage();
            Log::error('Teacher import failed with rollback', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString(),
                'sync_mode' => $syncMode,
                'school_id' => $schoolId,
                'academic_year_id' => $academicYearId,
                'processed_rows' => $results['processed_rows']
            ]);
        }
        
        return $results;
    }
    
    /**
     * Process a chunk of data rows with comprehensive error handling
     *
     * @param array $chunk Chunk of data rows
     * @param int $schoolId School ID
     * @param int $academicYearId Academic year ID
     * @param array &$results Results array to update
     * @param int $chunkIndex Current chunk index for logging
     */
    protected function processChunk(array $chunk, int $schoolId, int $academicYearId, array &$results, int $chunkIndex): void
    {
        foreach ($chunk as $originalIndex => $row) {
            try {
                // Requirement 6.5: Continue processing valid rows and collect all errors
                $this->processRow($row, $schoolId, $academicYearId, $results);
                $results['processed_rows']++;
                
            } catch (\Exception $e) {
                // Collect detailed error information with row context
                $rowNumber = is_int($originalIndex) ? $originalIndex + 1 : $results['processed_rows'] + 1;
                $errorDetails = [
                    'row' => $rowNumber,
                    'chunk' => $chunkIndex + 1,
                    'error' => $e->getMessage(),
                    'data' => $this->sanitizeRowDataForLogging($row)
                ];
                
                $results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                
                Log::warning('Row processing failed, continuing with next row', $errorDetails);
                
                // Continue processing despite individual row failures
                continue;
            }
        }
    }
    
    /**
     * Process a single row of import data
     *
     * @param array $row Single row data
     * @param int $schoolId School ID
     * @param int $academicYearId Academic year ID
     * @param array &$results Results array to update
     */
    protected function processRow(array $row, int $schoolId, int $academicYearId, array &$results): void
    {
        // Create or find teacher
        $teacher = $this->createOrUpdateTeacher([
            'name' => trim($row['teacher_name']),
            'email' => trim($row['teacher_email'] ?? ''),
            'phone_number' => trim($row['phone'] ?? ''),
            'national_id' => trim($row['national_id'] ?? ''),
            'gender' => trim($row['gender'] ?? ''),
            'date_of_birth' => $row['date_of_birth'] ?? null,
        ], $schoolId);
        
        if ($teacher->wasRecentlyCreated) {
            $results['teachers_created']++;
        }
        
        // Create or find classroom
        $classroom = $this->createOrUpdateClassroom(trim($row['classroom']), $schoolId);
        
        // Create or find subject
        $subject = $this->createOrUpdateSubject(trim($row['subject']), $schoolId);
        
        // Create or update assignment
        $assignmentData = [
            'school_id' => $schoolId,
            'academic_year_id' => $academicYearId,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'classes_per_week' => (int) $row['periods_per_week']
        ];
        
        $assignment = $this->createOrUpdateAssignment($assignmentData);
        
        if ($assignment->wasRecentlyCreated) {
            $results['assignments_created']++;
        } else {
            $results['assignments_updated']++;
        }
    }
    
    /**
     * Create or update teacher with user account and advanced status management
     *
     * @param array $teacherData Teacher data
     * @param int $schoolId School ID
     * @return Teacher
     */
    public function createOrUpdateTeacher(array $teacherData, int $schoolId): Teacher
    {
        // Normalize empty values to null to prevent unique constraint violations
        if (isset($teacherData['national_id']) && empty($teacherData['national_id'])) {
            $teacherData['national_id'] = null;
        }
        
        if (isset($teacherData['email']) && empty($teacherData['email'])) {
            $teacherData['email'] = null;
        }
        
        if (isset($teacherData['phone_number']) && empty($teacherData['phone_number'])) {
            $teacherData['phone_number'] = null;
        }
        
        if (isset($teacherData['whatsapp_number']) && empty($teacherData['whatsapp_number'])) {
            $teacherData['whatsapp_number'] = null;
        }

        // Try to find existing teacher by name and school (requirement 3.6)
        $teacher = Teacher::where('name', $teacherData['name'])
            ->where('school_id', $schoolId)
            ->first();
        
        if ($teacher) {
            // Update existing teacher with new data if provided
            $updateData = array_filter($teacherData, function($value) {
                return !empty($value) || $value === 0 || $value === '0'; // Allow 0 values but not empty strings
            });
            
            if (!empty($updateData)) {
                $teacher->update($updateData);
                
                // Perform comprehensive status synchronization (Requirements 8.1, 8.3)
                $syncResult = $teacher->performComprehensiveStatusSync();
                if (!$syncResult['success']) {
                    Log::warning('Teacher status sync issues found during update', [
                        'teacher_id' => $teacher->id,
                        'issues' => $syncResult['issues_found']
                    ]);
                }
            }
            
            return $teacher;
        }
        
        // Create new teacher - the boot method will handle:
        // - Unique t_id generation (requirement 3.2)
        // - User account creation with role 'teacher' (requirement 3.3)
        // - Default password '12345678' (requirement 3.5)
        // - Email defaulting to t_id if not provided (requirement 3.4)
        // - School association (requirement 3.7)
        $teacher = Teacher::create(array_merge($teacherData, [
            'school_id' => $schoolId
        ]));
        
        // Verify comprehensive status synchronization for new teacher (Requirements 8.1, 8.3)
        $syncResult = $teacher->performComprehensiveStatusSync();
        if (!$syncResult['success']) {
            Log::warning('New teacher created with status sync issues', [
                'teacher_id' => $teacher->id,
                'issues' => $syncResult['issues_found']
            ]);
        }
        
        return $teacher;
    }
    
    /**
     * Create or update classroom
     *
     * @param string $name Classroom name
     * @param int $schoolId School ID
     * @return Classroom
     */
    public function createOrUpdateClassroom(string $name, int $schoolId): Classroom
    {
        // Ensure there is a default stage and grade for the school
        $stage = Stage::where('school_id', $schoolId)->first();
        if (!$stage) {
            $stage = Stage::create([
                'name' => 'Default Stage',
                'school_id' => $schoolId,
            ]);
        }

        $grade = Grade::where('school_id', $schoolId)->first();
        if (!$grade) {
            $grade = Grade::create([
                'name' => 'Default Grade',
                'school_id' => $schoolId,
                'stage_id' => $stage->id,
            ]);
        }

        // Create or get classroom with required fields
        return Classroom::firstOrCreate(
            ['name' => $name, 'school_id' => $schoolId],
            [
                'name' => $name,
                'school_id' => $schoolId,
                'capacity' => 30,
                'stage_id' => $stage->id,
                'grade_id' => $grade->id,
            ]
        );
    }
    
    /**
     * Create or update subject
     *
     * @param string $name Subject name
     * @param int $schoolId School ID
     * @return Subject
     */
    public function createOrUpdateSubject(string $name, int $schoolId): Subject
    {
        // Requirement 4.2: Create subject if not exists, 4.3: Associate with school
        // Requirement 4.4: Use existing record if already exists for the school
        return Subject::firstOrCreate(
            ['name' => $name, 'school_id' => $schoolId],
            ['name' => $name, 'school_id' => $schoolId]
        );
    }
    
    /**
     * Create or update assignment with comprehensive validation and integrity checks
     *
     * @param array $assignmentData Assignment data
     * @return ClassroomSubjectTeacher
     * @throws \InvalidArgumentException
     */
    public function createOrUpdateAssignment(array $assignmentData): ClassroomSubjectTeacher
    {
        // Requirement 5.6: Validate that periods_per_week is a positive number
        $periodsPerWeek = $assignmentData['classes_per_week'] ?? 0;
        if (!is_numeric($periodsPerWeek) || $periodsPerWeek <= 0) {
            throw new \InvalidArgumentException('Periods per week must be a positive number');
        }
        
        // Enhanced validation: Check if teacher can be assigned (Requirement 8.4)
        if (isset($assignmentData['teacher_id'])) {
            $teacher = Teacher::find($assignmentData['teacher_id']);
            if ($teacher && !$teacher->canBeAssignedToClassroom()) {
                $reason = $teacher->getAssignmentPreventionReason();
                throw new \InvalidArgumentException("Cannot create assignment: {$reason}");
            }
        }
        
        // Requirement 5.5: Update existing assignments with new periods_per_week value
        $existing = ClassroomSubjectTeacher::where([
            'school_id' => $assignmentData['school_id'],
            'academic_year_id' => $assignmentData['academic_year_id'],
            'classroom_id' => $assignmentData['classroom_id'],
            'subject_id' => $assignmentData['subject_id'],
            'teacher_id' => $assignmentData['teacher_id']
        ])->first();
        
        if ($existing) {
            // Validate assignment integrity before updating (Requirement 8.5)
            $integrityIssues = $existing->validateAssignmentIntegrity();
            if (!empty($integrityIssues)) {
                Log::warning('Assignment integrity issues found during update', [
                    'assignment_id' => $existing->id,
                    'issues' => $integrityIssues
                ]);
            }
            
            // Update existing assignment with new periods_per_week
            $existing->update(['classes_per_week' => (int) $periodsPerWeek]);
            
            // Verify historical data integrity is maintained (Requirement 8.2)
            if (!$existing->maintainsHistoricalIntegrity()) {
                Log::error('Historical data integrity compromised during assignment update', [
                    'assignment_id' => $existing->id
                ]);
            }
            
            return $existing;
        }
        
        // Try to create the new assignment, but handle duplicate key constraint
        try {
            $assignment = ClassroomSubjectTeacher::create($assignmentData);
            
            // Validate new assignment integrity (Requirement 8.5)
            $integrityIssues = $assignment->validateAssignmentIntegrity();
            if (!empty($integrityIssues)) {
                Log::warning('New assignment created with integrity issues', [
                    'assignment_id' => $assignment->id,
                    'issues' => $integrityIssues
                ]);
            }
            
            return $assignment;
        } catch (\Illuminate\Database\QueryException $e) {
            // Check if the error is a duplicate entry error
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'unique_assignment_idx')) {
                // The record was created between our check and the create attempt
                // Fetch the existing record and return it
                $existing = ClassroomSubjectTeacher::where([
                    'school_id' => $assignmentData['school_id'],
                    'academic_year_id' => $assignmentData['academic_year_id'],
                    'classroom_id' => $assignmentData['classroom_id'],
                    'subject_id' => $assignmentData['subject_id'],
                    'teacher_id' => $assignmentData['teacher_id']
                ])->first();
                
                if ($existing) {
                    // Update with the current periods per week value
                    $existing->update(['classes_per_week' => (int) $periodsPerWeek]);
                    return $existing;
                } else {
                    // This shouldn't happen, but if it does, re-throw the exception
                    throw $e;
                }
            } else {
                // If it's not a duplicate entry error, re-throw the exception
                throw $e;
            }
        }
    }
    
    /**
     * Generate comprehensive import report with detailed error information
     *
     * @param array $results Import results
     * @return array Formatted report
     */
    public function generateImportReport(array $results): array
    {
        $errorSummary = $this->categorizeErrors($results['errors']);
        
        return [
            'summary' => [
                'total_rows' => $results['total_rows'],
                'processed_rows' => $results['processed_rows'],
                'chunks_processed' => $results['chunks_processed'],
                'teachers_created' => $results['teachers_created'],
                'assignments_created' => $results['assignments_created'],
                'assignments_updated' => $results['assignments_updated'],
                'errors_count' => count($results['errors']),
                'success_rate' => $results['total_rows'] > 0 ? 
                    round(($results['processed_rows'] / $results['total_rows']) * 100, 2) : 0
            ],
            'errors' => $results['errors'],
            'error_summary' => $errorSummary,
            'success' => $results['success'],
            'recommendations' => $this->generateRecommendations($errorSummary)
        ];
    }
    
    /**
     * Categorize errors for better reporting
     *
     * @param array $errors List of error messages
     * @return array Categorized errors
     */
    protected function categorizeErrors(array $errors): array
    {
        $categories = [
            'validation' => [],
            'data_integrity' => [],
            'system' => [],
            'other' => []
        ];
        
        foreach ($errors as $error) {
            if (strpos($error, 'required') !== false || strpos($error, 'positive number') !== false) {
                $categories['validation'][] = $error;
            } elseif (strpos($error, 'duplicate') !== false || strpos($error, 'constraint') !== false) {
                $categories['data_integrity'][] = $error;
            } elseif (strpos($error, 'Critical import failure') !== false) {
                $categories['system'][] = $error;
            } else {
                $categories['other'][] = $error;
            }
        }
        
        return array_filter($categories, function($category) {
            return !empty($category);
        });
    }
    
    /**
     * Generate recommendations based on error patterns
     *
     * @param array $errorSummary Categorized errors
     * @return array Recommendations
     */
    protected function generateRecommendations(array $errorSummary): array
    {
        $recommendations = [];
        
        if (!empty($errorSummary['validation'])) {
            $recommendations[] = 'Review Excel file format and ensure all required columns are present with valid data';
        }
        
        if (!empty($errorSummary['data_integrity'])) {
            $recommendations[] = 'Check for duplicate entries or conflicting data in the Excel file';
        }
        
        if (!empty($errorSummary['system'])) {
            $recommendations[] = 'Contact system administrator - there may be database or system configuration issues';
        }
        
        return $recommendations;
    }
    
    /**
     * Sanitize row data for safe logging (remove sensitive information)
     *
     * @param array $row Row data
     * @return array Sanitized row data
     */
    protected function sanitizeRowDataForLogging(array $row): array
    {
        $sanitized = $row;
        
        // Remove or mask sensitive fields
        if (isset($sanitized['teacher_email'])) {
            $sanitized['teacher_email'] = $this->maskEmail($sanitized['teacher_email']);
        }
        
        if (isset($sanitized['phone'])) {
            $sanitized['phone'] = $this->maskPhone($sanitized['phone']);
        }
        
        if (isset($sanitized['national_id'])) {
            $sanitized['national_id'] = '***masked***';
        }
        
        return $sanitized;
    }
    
    /**
     * Mask email for logging
     *
     * @param string $email Email address
     * @return string Masked email
     */
    protected function maskEmail(string $email): string
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }
        
        $username = $parts[0];
        $domain = $parts[1];
        
        $maskedUsername = substr($username, 0, 2) . str_repeat('*', max(0, strlen($username) - 2));
        
        return $maskedUsername . '@' . $domain;
    }
    
    /**
     * Mask phone number for logging
     *
     * @param string $phone Phone number
     * @return string Masked phone
     */
    protected function maskPhone(string $phone): string
    {
        if (empty($phone)) {
            return $phone;
        }
        
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleaned) < 4) {
            return '***masked***';
        }
        
        return substr($cleaned, 0, 2) . str_repeat('*', strlen($cleaned) - 4) . substr($cleaned, -2);
    }

    /**
     * Perform comprehensive system integrity check for a school (Requirements 8.2, 8.5)
     * 
     * @param int $schoolId School ID to check
     * @return array Integrity check results
     */
    public function performSchoolIntegrityCheck(int $schoolId): array
    {
        $results = [
            'school_id' => $schoolId,
            'teachers_checked' => 0,
            'assignments_checked' => 0,
            'integrity_issues' => [],
            'status_sync_issues' => [],
            'historical_data_issues' => [],
            'recommendations' => []
        ];

        try {
            // Check all teachers for the school
            $teachers = Teacher::withTrashed()->where('school_id', $schoolId)->with('user')->get();
            $results['teachers_checked'] = $teachers->count();

            foreach ($teachers as $teacher) {
                // Check teacher-user status synchronization (Requirements 8.1, 8.3)
                $syncResult = $teacher->performComprehensiveStatusSync();
                if (!$syncResult['success']) {
                    $results['status_sync_issues'][] = [
                        'teacher_id' => $teacher->id,
                        'teacher_name' => $teacher->name,
                        'issues' => $syncResult['issues_found']
                    ];
                }

                // Check referential integrity (Requirement 8.5)
                $integrityIssues = $teacher->validateComprehensiveReferentialIntegrity();
                if (!empty($integrityIssues)) {
                    $results['integrity_issues'][] = [
                        'teacher_id' => $teacher->id,
                        'teacher_name' => $teacher->name,
                        'issues' => $integrityIssues
                    ];
                }

                // Check historical data preservation (Requirement 8.2)
                if (!$teacher->preserveHistoricalData()) {
                    $results['historical_data_issues'][] = [
                        'teacher_id' => $teacher->id,
                        'teacher_name' => $teacher->name,
                        'issue' => 'Historical data preservation check failed'
                    ];
                }
            }

            // Check all assignments for the school
            $assignments = ClassroomSubjectTeacher::withTrashed()
                ->where('school_id', $schoolId)
                ->with(['teacher', 'classroom', 'subject'])
                ->get();
            $results['assignments_checked'] = $assignments->count();

            foreach ($assignments as $assignment) {
                // Check assignment integrity (Requirement 8.5)
                $assignmentIssues = $assignment->validateAssignmentIntegrity();
                if (!empty($assignmentIssues)) {
                    $results['integrity_issues'][] = [
                        'assignment_id' => $assignment->id,
                        'teacher_name' => $assignment->teacher?->name ?? 'Unknown',
                        'classroom_name' => $assignment->classroom?->name ?? 'Unknown',
                        'subject_name' => $assignment->subject?->name ?? 'Unknown',
                        'issues' => $assignmentIssues
                    ];
                }

                // Check historical integrity (Requirement 8.2)
                if (!$assignment->maintainsHistoricalIntegrity()) {
                    $results['historical_data_issues'][] = [
                        'assignment_id' => $assignment->id,
                        'issue' => 'Assignment historical integrity compromised'
                    ];
                }
            }

            // Generate recommendations based on findings
            $results['recommendations'] = $this->generateIntegrityRecommendations($results);

        } catch (\Exception $e) {
            $results['integrity_issues'][] = [
                'system_error' => true,
                'message' => 'Exception during integrity check: ' . $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * Generate recommendations based on integrity check results
     * 
     * @param array $results Integrity check results
     * @return array Recommendations
     */
    protected function generateIntegrityRecommendations(array $results): array
    {
        $recommendations = [];

        if (!empty($results['status_sync_issues'])) {
            $recommendations[] = 'Run teacher-user status synchronization to fix status mismatches';
        }

        if (!empty($results['integrity_issues'])) {
            $recommendations[] = 'Review and fix referential integrity issues in teacher and assignment data';
        }

        if (!empty($results['historical_data_issues'])) {
            $recommendations[] = 'Investigate historical data preservation issues and restore from backups if necessary';
        }

        if (empty($results['status_sync_issues']) && empty($results['integrity_issues']) && empty($results['historical_data_issues'])) {
            $recommendations[] = 'All integrity checks passed - system is in good state';
        }

        return $recommendations;
    }

    /**
     * Perform bulk teacher status synchronization for a school (Requirements 8.1, 8.3)
     * 
     * @param int $schoolId School ID
     * @return array Synchronization results
     */
    public function performBulkTeacherStatusSync(int $schoolId): array
    {
        return Teacher::performBulkStatusSync($schoolId);
    }

    /**
     * Validate that all teachers in import data can be assigned (Requirement 8.4)
     * 
     * @param array $data Import data
     * @param int $schoolId School ID
     * @return array Validation results
     */
    public function validateTeacherAssignmentCapability(array $data, int $schoolId): array
    {
        $results = [
            'valid' => true,
            'unassignable_teachers' => [],
            'warnings' => []
        ];

        $teacherNames = array_unique(array_column($data, 'teacher_name'));

        foreach ($teacherNames as $teacherName) {
            if (empty(trim($teacherName))) {
                continue;
            }

            $teacher = Teacher::where('name', trim($teacherName))
                ->where('school_id', $schoolId)
                ->first();

            if ($teacher && !$teacher->canBeAssignedToClassroom()) {
                $reason = $teacher->getAssignmentPreventionReason();
                $results['unassignable_teachers'][] = [
                    'name' => $teacherName,
                    'reason' => $reason
                ];
                $results['valid'] = false;
            }
        }

        if (!empty($results['unassignable_teachers'])) {
            $results['warnings'][] = 'Some teachers in the import data cannot be assigned to classrooms';
        }

        return $results;
    }
}