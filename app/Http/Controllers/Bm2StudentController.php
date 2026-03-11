<?php

namespace App\Http\Controllers;

use App\Models\Bm2Assessment;
use App\Models\Bm2LearningPath;
use App\Models\Bm2StudentBadge;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Bm2StudentController
 * 
 * Handles student dashboard, progress, and gamification.
 */
class Bm2StudentController extends Controller
{
    /**
     * Get student dashboard overview.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $student = $request->user();

        // Get recent assessments
        $recentAssessments = Bm2Assessment::where('student_id', $student->id)
            ->with('learningPath')
            ->latest('created_at')
            ->limit(5)
            ->get();

        // Get active learning path
        $activeLearningPath = Bm2LearningPath::where('student_id', $student->id)
            ->inProgress()
            ->active()
            ->first();

        // Get badges count
        $badgesCount = $student->bm2Badges()->count();

        // Calculate average score
        $avgScore = Bm2Assessment::where('student_id', $student->id)
            ->completed()
            ->avg('overall_score') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'student' => $student,
                'recent_assessments' => $recentAssessments,
                'active_learning_path' => $activeLearningPath,
                'badges_count' => $badgesCount,
                'average_score' => round($avgScore, 2),
                'total_assessments' => Bm2Assessment::where('student_id', $student->id)->count(),
            ],
        ]);
    }

    /**
     * Get student's assessment history.
     */
    public function assessmentHistory(Request $request): JsonResponse
    {
        $student = $request->user();

        $assessments = Bm2Assessment::where('student_id', $student->id)
            ->with('learningPath')
            ->completed()
            ->latest('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'assessments' => $assessments->items(),
                'pagination' => [
                    'current_page' => $assessments->currentPage(),
                    'last_page' => $assessments->lastPage(),
                    'total' => $assessments->total(),
                ],
            ],
        ]);
    }

    /**
     * Get student's learning paths.
     */
    public function learningPaths(Request $request): JsonResponse
    {
        $student = $request->user();

        $paths = Bm2LearningPath::where('student_id', $student->id)
            ->active()
            ->latest('created_at')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'learning_paths' => $paths->items(),
                'pagination' => [
                    'current_page' => $paths->currentPage(),
                    'last_page' => $paths->lastPage(),
                    'total' => $paths->total(),
                ],
            ],
        ]);
    }

    /**
     * Get student's badges collection.
     */
    public function badges(Request $request): JsonResponse
    {
        $student = $request->user();

        $badges = $student->bm2Badges()
            ->withPivot('earned_at', 'points_awarded', 'earned_for')
            ->orderByPivot('earned_at', 'desc')
            ->get();

        // Group by category
        $grouped = $badges->groupBy(function($badge) {
            return $badge->category;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'badges' => $badges,
                'badges_by_category' => $grouped,
                'total_badges' => $badges->count(),
                'total_points' => $badges->sum('pivot.points_awarded'),
            ],
        ]);
    }

    /**
     * Get detailed assessment results.
     */
    public function assessmentResults(int $assessmentId): JsonResponse
    {
        $assessment = Bm2Assessment::with(['questions.questionBank', 'learningPath'])
            ->findOrFail($assessmentId);

        // Verify ownership
        if ($assessment->student_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'assessment' => $assessment,
                'skill_breakdown' => $assessment->skill_breakdown,
                'question_details' => $assessment->questions->map(fn($q) => [
                    'question_text' => $q->question_text,
                    'student_answer' => $q->student_answer,
                    'correct_answer' => $q->correct_answer,
                    'is_correct' => $q->is_correct,
                    'time_taken' => $q->time_taken_seconds,
                    'points_earned' => $q->points_earned,
                    'difficulty' => $q->difficulty,
                ]),
                'learning_path' => $assessment->learningPath,
            ],
        ]);
    }

    /**
     * Update learning path progress (mark lesson as complete).
     */
    public function updateLearningPathProgress(Request $request, int $pathId): JsonResponse
    {
        $path = Bm2LearningPath::findOrFail($pathId);

        // Verify ownership
        if ($path->student_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $path->incrementProgress();

        return response()->json([
            'success' => true,
            'data' => [
                'learning_path' => $path,
                'completion_percentage' => $path->completion_percentage,
            ],
            'message' => 'Progress updated successfully',
        ]);
    }

    /**
     * Get student statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $student = $request->user();

        // Get detailed stats
        $stats = DB::table('bm2_assessments')
            ->selectRaw('
                COUNT(*) as total_assessments,
                AVG(overall_score) as avg_score,
                MAX(overall_score) as highest_score,
                MIN(overall_score) as lowest_score,
                SUM(total_time_seconds) as total_time_seconds
            ')
            ->where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->first();

        // Get skill breakdown across all assessments
        $allAssessments = Bm2Assessment::where('student_id', $student->id)
            ->completed()
            ->get();

        $aggregateSkills = [];
        foreach ($allAssessments as $assessment) {
            $skills = $assessment->skill_breakdown ?? [];
            foreach ($skills as $skill => $data) {
                if (!isset($aggregateSkills[$skill])) {
                    $aggregateSkills[$skill] = ['total' => 0, 'count' => 0];
                }
                $aggregateSkills[$skill]['total'] += $data['percentage'];
                $aggregateSkills[$skill]['count']++;
            }
        }

        $averagedSkills = array_map(function($data) {
            return $data['count'] > 0 ? round($data['total'] / $data['count'], 2) : 0;
        }, $aggregateSkills);

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => $stats,
                'skill_averages' => $averagedSkills,
                'performance_trend' => $this->calculatePerformanceTrend($student->id),
            ],
        ]);
    }

    /**
     * Calculate performance trend over time.
     */
    private function calculatePerformanceTrend(int $studentId): array
    {
        $assessments = Bm2Assessment::where('student_id', $studentId)
            ->completed()
            ->orderBy('created_at')
            ->get();

        return $assessments->map(fn($a) => [
            'date' => $a->created_at->format('Y-m-d'),
            'score' => $a->overall_score,
            'type' => $a->type,
        ])->toArray();
    }
}
