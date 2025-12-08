<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LessonStudentProgress;
use App\Models\LessonPracticeSubmission;
use App\Models\free\LessonPresentation;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class LessonProgressController extends Controller
{
    /**
     * Get all progress for a student
     */
    public function getStudentProgress($studentId)
    {
        $progress = LessonStudentProgress::with(['lesson', 'openedByTeacher'])
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($progress);
    }

    /**
     * Get all student progress for a lesson
     */
    public function getLessonProgress($lessonId)
    {
        $progress = LessonStudentProgress::with(['student', 'openedByTeacher'])
            ->where('lesson_presentation_id', $lessonId)
            ->get();

        return response()->json($progress);
    }

    /**
     * Teacher opens lesson for student(s)
     */

    public function openLesson(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lesson_presentations,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        // Get teacher ID from authenticated user
        $user = $request->user();
        $teacherId = $user->teacher->id ?? \App\Models\Teacher::first()->id;


        $results = [];

        foreach ($validated['student_ids'] as $studentId) {
            $progress = LessonStudentProgress::updateOrCreate(
                [
                    'lesson_presentation_id' => $validated['lesson_id'],
                    'student_id' => $studentId,
                ],
                [
                    'status' => 'opened',
                    'color_status' => 'light_blue',
                    'opened_by_teacher_id' => $teacherId,
                    'opened_at' => now(),
                ]
            );

            $results[] = $progress;
        }

        return response()->json([
            'message' => 'Lesson opened successfully',
            'progress' => $results,
        ]);
    }

    /**
     * Teacher locks lesson
     */
    public function lockLesson(Request $request)
    {
        $validated = $request->validate([
            'progress_id' => 'required|exists:lesson_student_progress,id',
        ]);

        $progress = LessonStudentProgress::findOrFail($validated['progress_id']);
        $progress->update([
            'status' => 'locked',
            'color_status' => 'gray',
        ]);

        return response()->json([
            'message' => 'Lesson locked successfully',
            'progress' => $progress,
        ]);
    }

    /**
     * Teacher force passes student
     */
    public function forcePass(Request $request)
    {
        $validated = $request->validate([
            'progress_id' => 'required|exists:lesson_student_progress,id',
        ]);

        $progress = LessonStudentProgress::findOrFail($validated['progress_id']);
        $progress->update([
            'force_passed' => true,
            'status' => 'completed',
            'color_status' => 'green',
        ]);

        return response()->json([
            'message' => 'Student force passed successfully',
            'progress' => $progress,
        ]);
    }

    /**
     * Teacher grants extra quiz attempt
     */
    public function grantAttempt(Request $request)
    {
        $validated = $request->validate([
            'progress_id' => 'required|exists:lesson_student_progress,id',
        ]);

        $progress = LessonStudentProgress::findOrFail($validated['progress_id']);
        
        // Update quiz_data JSON to track extra attempts
        $quizData = $progress->quiz_data ?? [];
        $quizData['extra_attempts_granted'] = ($quizData['extra_attempts_granted'] ?? 0) + 1;
        
        $progress->update([
            'quiz_data' => $quizData,
        ]);

        return response()->json([
            'message' => 'Extra attempt granted successfully',
            'progress' => $progress,
        ]);
    }

    /**
     * Teacher resets progress
     */
    public function resetProgress(Request $request)
    {
        $validated = $request->validate([
            'progress_id' => 'required|exists:lesson_student_progress,id',
        ]);

        $progress = LessonStudentProgress::findOrFail($validated['progress_id']);
        
        // Delete practice submissions
        $progress->practiceSubmissions()->delete();
        
        // Reset to opened state
        $progress->update([
            'status' => 'opened',
            'color_status' => 'light_blue',
            'learn_completed_at' => null,
            'practice_score' => null,
            'practice_submitted_at' => null,
            'practice_graded_at' => null,
            'quiz_attempts' => 0,
            'quiz_best_score' => null,
            'quiz_passed' => false,
            'force_passed' => false,
            'practice_data' => null,
            'quiz_data' => null,
            'metadata' => null,
        ]);

        return response()->json([
            'message' => 'Progress reset successfully',
            'progress' => $progress,
        ]);
    }

    /**
     * Student completes learn stage
     */
    public function completeLearn(Request $request, $id)
    {
        $progress = LessonStudentProgress::findOrFail($id);

        if ($progress->status !== 'opened' && $progress->status !== 'learning') {
            return response()->json(['error' => 'Invalid status for completing learn'], 400);
        }

        $progress->update([
            'learn_completed_at' => now(),
            'status' => 'practice_pending',
            'color_status' => 'blue',
        ]);

        return response()->json([
            'message' => 'Learn stage completed',
            'progress' => $progress,
        ]);
    }

    /**
     * Student submits practice
     */
    public function submitPractice(Request $request, $id)
    {
        $validated = $request->validate([
            'submission_type' => 'required|in:upload,drawing',
            'file' => 'required_if:submission_type,upload|image|max:5120', // 5MB max
            'drawing_data' => 'required_if:submission_type,drawing|string',
        ]);

        $progress = LessonStudentProgress::findOrFail($id);

        if (!$progress->canAccessPractice()) {
            return response()->json(['error' => 'Must complete learn stage first'], 400);
        }

        DB::beginTransaction();
        try {
            $filePath = null;
            $drawingData = null;

            if ($validated['submission_type'] === 'upload') {
                // Handle file upload
                $file = $request->file('file');
                $filePath = $file->store(
                    "practice_submissions/{$progress->lesson_presentation_id}/{$progress->student_id}",
                    'public'
                );
            } else {
                // Handle drawing data
                $drawingData = $validated['drawing_data'];
            }

            // Create submission record
            $submission = LessonPracticeSubmission::create([
                'lesson_student_progress_id' => $progress->id,
                'submission_type' => $validated['submission_type'],
                'file_path' => $filePath,
                'drawing_data' => $drawingData,
                'submitted_at' => now(),
            ]);

            // Update progress
            $practiceData = [
                'submission_type' => $validated['submission_type'],
                'file_path' => $filePath,
                'drawing_data' => $drawingData ? substr($drawingData, 0, 100) . '...' : null, // Store truncated version
                'resubmission_count' => ($progress->practice_data['resubmission_count'] ?? 0) + 1,
            ];

            $progress->update([
                'status' => 'practice_submitted',
                'color_status' => 'purple',
                'practice_submitted_at' => now(),
                'practice_data' => $practiceData,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Practice submitted successfully',
                'progress' => $progress,
                'submission' => $submission,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to submit practice: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Teacher grades practice
     */
    public function gradePractice(Request $request, $id)
    {
        $validated = $request->validate([
            'score' => 'required|integer|min:0|max:10',
            'feedback' => 'nullable|string|max:500',
        ]);

        $progress = LessonStudentProgress::findOrFail($id);

        if ($progress->status !== 'practice_submitted') {
            return response()->json(['error' => 'No practice submission to grade'], 400);
        }

        // Update practice_data with feedback
        $practiceData = $progress->practice_data ?? [];
        $practiceData['teacher_feedback'] = $validated['feedback'] ?? null;

        $newStatus = $validated['score'] >= 6 ? 'quiz_unlocked' : 'practice_pending';
        $newColor = $validated['score'] >= 6 ? 'blue' : 'light_blue';

        $progress->update([
            'practice_score' => $validated['score'],
            'practice_graded_at' => now(),
            'status' => $newStatus,
            'color_status' => $newColor,
            'practice_data' => $practiceData,
        ]);

        return response()->json([
            'message' => 'Practice graded successfully',
            'progress' => $progress,
            'can_access_quiz' => $validated['score'] >= 6,
        ]);
    }

    /**
     * Record quiz attempt
     */
    public function recordQuizAttempt(Request $request, $id)
    {
        $validated = $request->validate([
            'score' => 'required|integer|min:0|max:100',
            'quiz_version' => 'required|integer|min:1|max:4',
        ]);

        $progress = LessonStudentProgress::findOrFail($id);

        if (!$progress->canAccessQuiz()) {
            return response()->json(['error' => 'Quiz not unlocked yet'], 400);
        }

        // Get extra attempts granted
        $extraAttempts = $progress->quiz_data['extra_attempts_granted'] ?? 0;
        $maxAttempts = 3 + $extraAttempts;

        if ($progress->quiz_attempts >= $maxAttempts && !$progress->quiz_passed) {
            return response()->json(['error' => 'No attempts remaining'], 400);
        }

        // Update quiz_data
        $quizData = $progress->quiz_data ?? [];
        $quizData['attempts_detail'] = $quizData['attempts_detail'] ?? [];
        $quizData['attempts_detail'][] = [
            'attempt' => $progress->quiz_attempts + 1,
            'score' => $validated['score'],
            'date' => now()->toDateTimeString(),
            'version' => $validated['quiz_version'],
        ];
        $quizData['quiz_version_used'] = $quizData['quiz_version_used'] ?? [];
        $quizData['quiz_version_used'][] = $validated['quiz_version'];

        // Increment attempts
        $newAttempts = $progress->quiz_attempts + 1;
        $newBestScore = max($progress->quiz_best_score ?? 0, $validated['score']);
        $passed = $validated['score'] >= 80;

        // Determine status and color
        if ($passed) {
            $status = 'completed';
            $progress->quiz_passed = true;
        } elseif ($newAttempts >= $maxAttempts) {
            $status = 'failed';
        } else {
            $status = 'quiz_unlocked'; // Can try again
        }

        $progress->update([
            'quiz_attempts' => $newAttempts,
            'quiz_best_score' => $newBestScore,
            'quiz_passed' => $passed,
            'status' => $status,
            'quiz_data' => $quizData,
        ]);

        // Update color status
        $progress->updateColorStatus();

        return response()->json([
            'message' => $passed ? 'Quiz passed!' : 'Quiz attempt recorded',
            'progress' => $progress,
            'passed' => $passed,
            'attempts_remaining' => $maxAttempts - $newAttempts,
        ]);
    }

    /**
     * Get practice submission for viewing
     */
    public function getPracticeSubmission($progressId)
    {
        $progress = LessonStudentProgress::with('practiceSubmissions')->findOrFail($progressId);
        
        $latestSubmission = $progress->practiceSubmissions()->latest()->first();

        return response()->json([
            'progress' => $progress,
            'submission' => $latestSubmission,
            'file_url' => $latestSubmission ? $latestSubmission->getFileUrl() : null,
        ]);
    }
}
