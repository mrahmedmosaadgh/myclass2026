<?php

namespace App\Http\Controllers;

use App\Models\Bm2Assessment;
use App\Models\Bm2Badge;
use App\Models\Bm2LearningPath;
use App\Models\User;
use App\Services\Bm2GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Bm2TeacherController
 * 
 * Handles teacher dashboard and student monitoring features.
 */
class Bm2TeacherController extends Controller
{
    /**
     * Get teacher dashboard overview.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $teacher = $request->user();
        
        // Get teacher's classrooms/students (simplified - assuming all students for now)
        $students = User::role('student')->with(['bm2Badges' => function($query) {
            $query->withPivot('earned_at');
        }])->get();

        $studentCount = $students->count();
        
        // Overall class statistics
        $totalAssessments = Bm2Assessment::whereIn('student_id', $students->pluck('id'))
            ->whereNotNull('completed_at')
            ->count();
        
        $classAverageScore = Bm2Assessment::whereIn('student_id', $students->pluck('id'))
            ->whereNotNull('completed_at')
            ->avg('overall_score') ?? 0;
        
        $totalBadgesEarned = DB::table('bm2_student_badges')
            ->whereIn('student_id', $students->pluck('id'))
            ->count();
        
        $activeLearningPaths = Bm2LearningPath::whereIn('student_id', $students->pluck('id'))
            ->where('status', 'active')
            ->count();

        // Recent class activity
        $recentActivity = Bm2Assessment::whereIn('student_id', $students->pluck('id'))
            ->whereNotNull('completed_at')
            ->with('student')
            ->latest('completed_at')
            ->limit(10)
            ->get()
            ->map(fn($assessment) => [
                'id' => $assessment->id,
                'student_name' => $assessment->student->name,
                'student_id' => $assessment->student_id,
                'type' => $assessment->type,
                'score' => $assessment->overall_score,
                'completed_at' => $assessment->completed_at,
            ]);

        // Top performers (by total points)
        $gamificationService = new Bm2GamificationService();
        $topPerformers = $students->map(function($student) use ($gamificationService) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'total_points' => $gamificationService->getTotalPoints($student),
                'badges_count' => $student->bm2Badges->count(),
                'assessments_completed' => Bm2Assessment::where('student_id', $student->id)
                    ->whereNotNull('completed_at')
                    ->count(),
                'average_score' => round(Bm2Assessment::where('student_id', $student->id)
                    ->whereNotNull('completed_at')
                    ->avg('overall_score') ?? 0, 2),
            ];
        })
        ->sortByDesc('total_points')
        ->take(5)
        ->values();

        // Students needing attention (low scores or inactive)
        $strugglingStudents = $students->filter(function($student) {
            $assessments = Bm2Assessment::where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->get();
            
            if ($assessments->isEmpty()) {
                return true; // No assessments yet
            }
            
            $avgScore = $assessments->avg('overall_score');
            return $avgScore < 60; // Below 60% average
        })
        ->map(fn($student) => [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'assessments_completed' => Bm2Assessment::where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->count(),
            'average_score' => round(Bm2Assessment::where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->avg('overall_score') ?? 0, 2),
            'last_active' => Bm2Assessment::where('student_id', $student->id)
                ->latest('created_at')
                ->first()?->created_at,
        ])
        ->take(5)
        ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_students' => $studentCount,
                    'total_assessments' => $totalAssessments,
                    'class_average_score' => round($classAverageScore, 2),
                    'total_badges_earned' => $totalBadgesEarned,
                    'active_learning_paths' => $activeLearningPaths,
                ],
                'recent_activity' => $recentActivity,
                'top_performers' => $topPerformers,
                'struggling_students' => $strugglingStudents,
            ],
        ]);
    }

    /**
     * Get detailed student progress.
     */
    public function studentProgress(int $studentId): JsonResponse
    {
        $student = User::findOrFail($studentId);
        
        // Verify student belongs to teacher (simplified for now)
        
        $gamificationService = new Bm2GamificationService();
        
        // Student overview
        $assessments = Bm2Assessment::where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->get();
        
        $totalAssessments = $assessments->count();
        $averageScore = $assessments->avg('overall_score') ?? 0;
        $highestScore = $assessments->max('overall_score') ?? 0;
        $lowestScore = $assessments->min('overall_score') ?? 0;
        
        // Skill breakdown from latest assessment
        $latestAssessment = $assessments->first();
        $skillBreakdown = $latestAssessment?->skill_breakdown ?? [];
        
        // Assessment history
        $assessmentHistory = $assessments->map(fn($a) => [
            'id' => $a->id,
            'title' => $a->title,
            'type' => $a->type,
            'score' => $a->overall_score,
            'performance_level' => $a->performance_level,
            'completed_at' => $a->completed_at,
            'time_taken_minutes' => round($a->total_time_seconds / 60, 1),
        ]);
        
        // Badges
        $badges = $student->bm2Badges()
            ->withPivot('earned_at', 'points_awarded')
            ->orderByPivot('earned_at', 'desc')
            ->get()
            ->map(fn($badge) => [
                'id' => $badge->id,
                'name' => $badge->name,
                'icon_url' => $badge->icon_url,
                'category' => $badge->category,
                'rarity' => $badge->rarity,
                'earned_at' => $badge->pivot->earned_at,
                'points' => $badge->pivot->points_awarded,
            ]);
        
        // Learning path
        $learningPath = Bm2LearningPath::where('student_id', $student->id)
            ->inProgress()
            ->active()
            ->first();
        
        // Streak
        $currentStreak = $gamificationService->getCurrentStreak($student);
        $totalPoints = $gamificationService->getTotalPoints($student);
        
        // Performance trend (last 10 assessments)
        $performanceTrend = $assessments->take(10)->reverse()->map(fn($a) => [
            'assessment_id' => $a->id,
            'date' => $a->completed_at->format('Y-m-d'),
            'score' => $a->overall_score,
            'type' => $a->type,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                ],
                'overview' => [
                    'total_assessments' => $totalAssessments,
                    'average_score' => round($averageScore, 2),
                    'highest_score' => round($highestScore, 2),
                    'lowest_score' => round($lowestScore, 2),
                    'current_streak' => $currentStreak,
                    'total_points' => $totalPoints,
                ],
                'skill_breakdown' => $skillBreakdown,
                'assessment_history' => $assessmentHistory,
                'badges' => $badges,
                'learning_path' => $learningPath,
                'performance_trend' => $performanceTrend,
            ],
        ]);
    }

    /**
     * Get class-wide skill analysis.
     */
    public function classSkillAnalysis(): JsonResponse
    {
        $students = User::role('student')->get();
        $studentIds = $students->pluck('id');
        
        // Get all completed assessments
        $assessments = Bm2Assessment::whereIn('student_id', $studentIds)
            ->whereNotNull('completed_at')
            ->whereNotNull('skill_breakdown')
            ->get();
        
        // Aggregate skill data
        $skillData = [];
        foreach ($assessments as $assessment) {
            $skills = $assessment->skill_breakdown ?? [];
            foreach ($skills as $skill => $data) {
                if (!isset($skillData[$skill])) {
                    $skillData[$skill] = [
                        'total' => 0,
                        'count' => 0,
                        'students' => [],
                    ];
                }
                $skillData[$skill]['total'] += $data['percentage'];
                $skillData[$skill]['count']++;
                $skillData[$skill]['students'][] = [
                    'student_id' => $assessment->student_id,
                    'student_name' => $assessment->student->name ?? 'Unknown',
                    'score' => $data['percentage'],
                ];
            }
        }
        
        // Calculate averages
        $skillAverages = [];
        foreach ($skillData as $skill => $data) {
            $skillAverages[$skill] = [
                'average' => round($data['total'] / max($data['count'], 1), 2),
                'student_count' => count(array_unique(array_column($data['students'], 'student_id'))),
                'assessment_count' => $data['count'],
                'students' => $data['students'],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'skill_averages' => $skillAverages,
                'total_assessments_analyzed' => $assessments->count(),
                'total_students' => $students->count(),
            ],
        ]);
    }

    /**
     * Get badge leaderboard.
     */
    public function badgeLeaderboard(): JsonResponse
    {
        $students = User::role('student')
            ->with(['bm2Badges' => function($query) {
                $query->withPivot('earned_at', 'points_awarded');
            }])
            ->get();
        
        $leaderboard = $students->map(function($student) {
            $badges = $student->bm2Badges;
            $totalPoints = $badges->sum('pivot.points_awarded');
            $badgeCount = $badges->count();
            
            // Group by category
            $byCategory = $badges->groupBy('category')->map->count();
            
            return [
                'rank' => 0, // Will be set after sorting
                'student_id' => $student->id,
                'student_name' => $student->name,
                'email' => $student->email,
                'total_points' => $totalPoints,
                'total_badges' => $badgeCount,
                'badges_by_category' => $byCategory,
                'recent_badges' => $badges->take(3)->map(fn($b) => [
                    'name' => $b->name,
                    'category' => $b->category,
                    'earned_at' => $b->pivot->earned_at,
                ]),
            ];
        })
        ->sortByDesc('total_points')
        ->values()
        ->map(function($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'leaderboard' => $leaderboard,
                'total_students' => $students->count(),
            ],
        ]);
    }

    /**
     * Get student list with quick stats.
     */
    public function studentList(): JsonResponse
    {
        $students = User::role('student')->get();
        
        $studentList = $students->map(function($student) {
            $gamificationService = new Bm2GamificationService();
            
            $assessments = Bm2Assessment::where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->get();
            
            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'assessments_completed' => $assessments->count(),
                'average_score' => round($assessments->avg('overall_score') ?? 0, 2),
                'badges_count' => $student->bm2Badges()->count(),
                'total_points' => $gamificationService->getTotalPoints($student),
                'current_streak' => $gamificationService->getCurrentStreak($student),
                'has_active_path' => Bm2LearningPath::where('student_id', $student->id)
                    ->inProgress()
                    ->active()
                    ->exists(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'students' => $studentList,
                'total' => $studentList->count(),
            ],
        ]);
    }
}
