<?php

namespace App\Http\Controllers\Api\Cr;

use Illuminate\Support\Facades\Auth;

use App\Helpers\PeriodCodeGenerator;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\CrCategoryMapping;
use App\Models\CrScore;
use App\Models\CrSession;
use App\Models\CrStudentPeriod;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Exceptions\UnauthorizedException;

class CrSessionController extends Controller
{
    /**
     * Initialize a classroom records session.
     * Creates or retrieves the session, student periods, and default scores.
     */
    public function initSession(Request $request): JsonResponse
    {
        // Validate required inputs
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
            'period_code' => 'required|string|max:50',
            'day_number' => 'required|integer|min:1|max:7',
            'period_number' => 'required|integer|min:1',
        ]);

        // Get authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $schoolId = $user->currentSchoolId();
        $yearId = $user->currentAcademicYearId();

        // Determine if user is admin (read-only) or teacher
        $isAdmin = $user->hasAnyRole(['admin', 'school_admin', 'super_admin']);
        $isTeacher = $user->hasRole('teacher');

        // Authorization check
        if (!$isAdmin && !$isTeacher) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // If teacher, verify they're assigned to this classroom+subject
        if ($isTeacher && !$isAdmin) {
            // Use authenticated user's teacher record, not request input
            $teacherRecord = Teacher::where('user_id', $user->id)
                ->where('school_id', $schoolId)
                ->first();
            
            if (!$teacherRecord) {
                return response()->json(['error' => 'No teacher record found for your account'], 403);
            }

            // Verify assignment to this classroom+subject combination
            $assignment = DB::table('classroom_subject_teachers')
                ->where('classroom_id', $validated['classroom_id'])
                ->where('subject_id', $validated['subject_id'])
                ->where('teacher_id', $teacherRecord->id)
                ->where('school_id', $schoolId)
                ->first();

            if (!$assignment) {
                return response()->json([
                    'error' => 'You are not assigned to teach this classroom-subject combination'
                ], 403);
            }
            
            // Override teacher_id with authenticated user's teacher ID to prevent spoofing
            $validated['teacher_id'] = $teacherRecord->id;
        }

        // try {
            $result = DB::transaction(function () use ($validated, $schoolId, $yearId, $isAdmin) {
                // Upsert session
                $session = CrSession::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'year_id' => $yearId,
                        'classroom_id' => $validated['classroom_id'],
                        'subject_id' => $validated['subject_id'],
                        'teacher_id' => $validated['teacher_id'],
                        'date' => $validated['date'],
                        'period_code' => $validated['period_code'],
                    ],
                    [
                        'day_number' => $validated['day_number'],
                        'period_number' => $validated['period_number'],
                        'status' => 'active',
                    ]
                );

                // Fetch classroom roster (students assigned to this classroom)
                $students = Student::where('classroom_id', $validated['classroom_id'])
                    ->whereNull('deleted_at')
                    ->with(['parent'])
                    ->get();

                // Get active category mappings for this school
                $activeMappings = CrCategoryMapping::where('school_id', $schoolId)
                    ->whereNull('deleted_at')
                    ->orderBy('sort_order')
                    ->get();

                // Prepare student data with periods and scores
                $studentsData = [];

                foreach ($students as $student) {
                    // Check if student period already exists
                    $existingStudentPeriod = CrStudentPeriod::where([
                        'school_id' => $schoolId,
                        'year_id' => $yearId,
                        'student_id' => $student->id,
                        'date' => $validated['date'],
                        'period_code' => $validated['period_code'],
                    ])->first();

                    // Only create defaults if record doesn't exist (idempotency)
                    if ($existingStudentPeriod) {
                        // Record exists - preserve existing state (don't overwrite)
                        $studentPeriod = $existingStudentPeriod;
                        
                        // Load existing scores
                        $scoresData = [];
                        foreach ($activeMappings as $mapping) {
                            $score = CrScore::where('student_period_id', $studentPeriod->id)
                                ->where('mapping_id', $mapping->id)
                                ->first();

                            if ($score) {
                                $scoresData[] = [
                                    'mapping_id' => $mapping->id,
                                    'mapping_key' => $mapping->key,
                                    'label' => $mapping->label,
                                    'numeric_value' => (float) $score->numeric_value,
                                    'max_value' => $mapping->max_value,
                                ];
                            }
                        }

                        $studentsData[] = [
                            'id' => $student->id,
                            'name' => $student->name,
                            'student_period_id' => $studentPeriod->id,
                            'period' => [
                                'attendance_status' => $studentPeriod->attendance_status,
                                'attendance_score' => $studentPeriod->attendance_score,
                                'attendance_note' => $studentPeriod->attendance_note,
                                'total_score' => $studentPeriod->total_score,
                                'locked' => $studentPeriod->locked,
                            ],
                            'scores' => $scoresData,
                        ];
                        
                        continue; // Skip to next student
                    }

                    // Create new student period record (only for new records)
                    $studentPeriod = CrStudentPeriod::create([
                        'school_id' => $schoolId,
                        'year_id' => $yearId,
                        'student_id' => $student->id,
                        'session_id' => $session->id,
                        'date' => $validated['date'],
                        'period_code' => $validated['period_code'],
                        'attendance_status' => 'present',
                        'attendance_score' => 5,
                        'total_score' => 0, // Will be calculated after scores are created
                        'locked' => false,
                    ]);

                    // Create default scores for each active mapping
                    $scoresData = [];
                    foreach ($activeMappings as $mapping) {
                        $score = CrScore::create([
                            'student_period_id' => $studentPeriod->id,
                            'mapping_id' => $mapping->id,
                            'numeric_value' => $mapping->default_value,
                        ]);

                        $scoresData[] = [
                            'mapping_id' => $mapping->id,
                            'mapping_key' => $mapping->key,
                            'label' => $mapping->label,
                            'numeric_value' => (float) $score->numeric_value,
                            'max_value' => $mapping->max_value,
                        ];
                    }

                    // Calculate total score: attendance_score + sum of all category scores
                    $totalScore = $studentPeriod->attendance_score + collect($scoresData)->sum('numeric_value');
                    $studentPeriod->update(['total_score' => $totalScore]);

                    $studentsData[] = [
                        'id' => $student->id,
                        'name' => $student->name,
                        'student_period_id' => $studentPeriod->id,
                        'period' => [
                            'attendance_status' => $studentPeriod->attendance_status,
                            'attendance_score' => $studentPeriod->attendance_score,
                            'attendance_note' => $studentPeriod->attendance_note,
                            'total_score' => $studentPeriod->total_score,
                            'locked' => $studentPeriod->locked,
                        ],
                        'scores' => $scoresData,
                    ];
                }

                return [
                    'session' => [
                        'id' => $session->id,
                        'classroom_id' => $session->classroom_id,
                        'subject_id' => $session->subject_id,
                        'teacher_id' => $session->teacher_id,
                        'date' => $session->date->toDateString(),
                        'day_number' => $session->day_number,
                        'period_number' => $session->period_number,
                        'period_code' => $session->period_code,
                        'status' => $session->status,
                        'is_read_only' => $isAdmin,
                    ],
                    'students' => $studentsData,
                    'mappings' => $activeMappings->map(fn($m) => [
                        'id' => $m->id,
                        'key' => $m->key,
                        'label' => $m->label,
                        'max_value' => $m->max_value,
                        'default_value' => $m->default_value,
                    ]),
                ];
            });

            return response()->json($result);

        // } catch (\Exception $e) {
        //     report($e);
        //     return response()->json(['error' => 'Failed to initialize session: ' . $e->getMessage()], 500);
        // }
    }

    /**
     * Batch update student periods and scores.
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        // Get authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $schoolId = $user->currentSchoolId();
        $yearId = $user->currentAcademicYearId();

        // Admins are read-only - cannot use batch update
        $isAdmin = $user->hasAnyRole(['admin', 'school_admin', 'super_admin']);
        if ($isAdmin) {
            return response()->json(['error' => 'Admin access is read-only. Cannot update records.'], 403);
        }

        // Validate required inputs
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.student_period_id' => 'required|exists:cr_student_periods,id',
            'updates.*.attendance_status' => 'nullable|in:present,absent,late,left_early',
            'updates.*.attendance_score' => 'nullable|integer|min:0|max:5',
            'updates.*.attendance_note' => 'nullable|string|max:255',
            'updates.*.total_score' => 'nullable|integer|min:0|max:20',
            'updates.*.scores' => 'nullable|array',
            'updates.*.scores.*.mapping_id' => 'required_with:updates.*.scores|exists:cr_category_mappings,id',
            'updates.*.scores.*.numeric_value' => 'required_with:updates.*.scores|numeric|min:0',
        ]);

        // Get teacher record for authenticated user
        $teacherRecord = Teacher::where('user_id', $user->id)
            ->where('school_id', $schoolId)
            ->first();

        if (!$teacherRecord) {
            return response()->json(['error' => 'No teacher record found'], 403);
        }

        $updated = [];
        $errors = [];

        foreach ($validated['updates'] as $updateItem) {
            try {
                DB::transaction(function () use ($updateItem, $schoolId, $yearId, $teacherRecord) {
                    $studentPeriod = CrStudentPeriod::findOrFail($updateItem['student_period_id']);

                    // Authorization: verify student_period belongs to current school/year
                    if ($studentPeriod->school_id !== $schoolId || $studentPeriod->year_id !== $yearId) {
                        throw new \Exception('Unauthorized access to student period record');
                    }

                    // Verify teacher is assigned to this session's classroom/subject
                    $session = CrSession::find($studentPeriod->session_id);
                    if ($session) {
                        $assignment = DB::table('classroom_subject_teachers')
                            ->where('classroom_id', $session->classroom_id)
                            ->where('subject_id', $session->subject_id)
                            ->where('teacher_id', $teacherRecord->id)
                            ->where('school_id', $schoolId)
                            ->first();

                        if (!$assignment) {
                            throw new \Exception('You are not assigned to teach this session');
                        }
                    }

                    // Check if locked (absent) - but allow changing away from absent
                    if ($studentPeriod->locked && $studentPeriod->attendance_status === 'absent') {
                        // Allow changing FROM absent TO another status
                        if (!isset($updateItem['attendance_status']) || $updateItem['attendance_status'] === 'absent') {
                            // Still absent, reject other changes
                            throw new \Exception('Cannot modify scores for absent student (locked). Change attendance status first.');
                        }
                    }

                    // Update attendance fields if provided
                    if (isset($updateItem['attendance_status'])) {
                        $wasAbsent = ($studentPeriod->attendance_status === 'absent');
                        $studentPeriod->attendance_status = $updateItem['attendance_status'];
                        
                        // Apply absent lock server-side
                        if ($updateItem['attendance_status'] === 'absent') {
                            $studentPeriod->attendance_score = 0;
                            $studentPeriod->locked = true;
                            
                            // Zero out all scores for this student
                            $studentPeriod->scores()->update(['numeric_value' => 0]);
                            $studentPeriod->total_score = 0;
                        } elseif ($wasAbsent && $studentPeriod->locked) {
                            // Changing away from absent - unlock and reset to defaults
                            $studentPeriod->locked = false;
                            
                            // Reset all category scores to defaults
                            $activeMappings = CrCategoryMapping::where('school_id', $studentPeriod->school_id)
                                ->whereNull('deleted_at')
                                ->get();
                            
                            foreach ($activeMappings as $mapping) {
                                $studentPeriod->scores()
                                    ->where('mapping_id', $mapping->id)
                                    ->update(['numeric_value' => $mapping->default_value]);
                            }
                        }
                    }

                    if (isset($updateItem['attendance_score'])) {
                        $studentPeriod->attendance_score = $updateItem['attendance_score'];
                    }

                    if (isset($updateItem['attendance_note'])) {
                        $studentPeriod->attendance_note = $updateItem['attendance_note'];
                    }

                    // Update individual category scores if provided
                    if (isset($updateItem['scores']) && is_array($updateItem['scores'])) {
                        foreach ($updateItem['scores'] as $scoreUpdate) {
                            $score = CrScore::where('student_period_id', $studentPeriod->id)
                                ->where('mapping_id', $scoreUpdate['mapping_id'])
                                ->first();

                            if ($score) {
                                // Validate against max value
                                $mapping = CrCategoryMapping::find($scoreUpdate['mapping_id']);
                                $maxValue = $mapping ? $mapping->max_value : 5;
                                
                                if ($scoreUpdate['numeric_value'] > $maxValue) {
                                    throw new \Exception("Score exceeds maximum value of {$maxValue}");
                                }

                                $score->numeric_value = $scoreUpdate['numeric_value'];
                                $score->save();
                            }
                        }
                    }

                    // Recalculate total score using flexible formula:
                    // total_score = attendance_score + sum(all active category scores)
                    $categoryScoresSum = $studentPeriod->scores()->sum('numeric_value');
                    $totalScore = $studentPeriod->attendance_score + $categoryScoresSum;
                    $studentPeriod->total_score = $totalScore;

                    $studentPeriod->save();

                    return true;
                });

                $updated[] = $updateItem['student_period_id'];

            } catch (\Exception $e) {
                $errors[] = [
                    'student_period_id' => $updateItem['student_period_id'],
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'updated' => $updated,
            'errors' => $errors,
        ]);
    }
}
