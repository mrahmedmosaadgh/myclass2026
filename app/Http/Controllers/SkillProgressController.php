<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\UserSkillProgress;
use App\Models\SkillAward;
use App\Models\SkillPracticeSession;
use App\Services\SmartScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillProgressController extends Controller
{
    protected $smartScoreService;

    public function __construct()
    {
        $this->smartScoreService = new SmartScoreService();
    }

    /**
     * Display a listing of the user's progress across all skills.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $query = UserSkillProgress::with(['skill.category', 'skill.questions'])
            ->where('user_id', $user->id);

        // Filter by category if provided
        if ($request->has('category_id')) {
            $query->whereHas('skill', function($q) use ($request) {
                $q->where('category_id', $request->input('category_id'));
            });
        }

        // Filter by skill if provided
        if ($request->has('skill_id')) {
            $query->where('skill_id', $request->input('skill_id'));
        }

        // Filter by mastery level
        if ($request->has('mastery_level')) {
            $query->where('mastery_level', $request->input('mastery_level'));
        }

        $progress = $query->get();

        // Add calculated fields to each progress item
        $progressWithCalcs = $progress->map(function ($item) {
            $item->mastery_percentage = $this->getMasteryPercentage($item->smart_score, $item->mastery_level);
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $progressWithCalcs
        ]);
    }

    /**
     * Display the specified skill progress for the authenticated user.
     *
     * @param  int  $skillId
     * @return \Illuminate\Http\Response
     */
    public function show($skillId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $progress = UserSkillProgress::with([
            'skill.category',
            'skill.questions',
            'skill.practiceSessions' => function($q) use ($user) {
                $q->where('user_id', $user->id)->latest()->limit(10);
            }
        ])->where('user_id', $user->id)
        ->where('skill_id', $skillId)
        ->first();

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'No progress found for this skill'
            ], 404);
        }

        // Get additional analytics for this skill
        $analytics = $this->getSkillAnalytics($user->id, $skillId);

        return response()->json([
            'success' => true,
            'data' => $progress,
            'analytics' => $analytics
        ]);
    }

    /**
     * Get the user's awards/badges.
     *
     * @return \Illuminate\Http\Response
     */
    public function awards()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $awards = SkillAward::with('skill', 'skill.category')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $awards
        ]);
    }

    /**
     * Get analytics for a specific skill.
     *
     * @param int $userId
     * @param int $skillId
     * @return array
     */
    private function getSkillAnalytics($userId, $skillId)
    {
        $sessions = SkillPracticeSession::where('user_id', $userId)
            ->where('skill_id', $skillId)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSessions = $sessions->count();
        $avgAccuracy = $totalSessions > 0 
            ? $sessions->avg(function($session) {
                return $session->questions_attempted > 0 
                    ? ($session->questions_correct / $session->questions_attempted) * 100 
                    : 0;
            }) 
            : 0;
            
        $totalQuestionsAnswered = $sessions->sum('questions_attempted');
        $totalCorrectAnswers = $sessions->sum('questions_correct');
        $overallAccuracy = $totalQuestionsAnswered > 0 
            ? ($totalCorrectAnswers / $totalQuestionsAnswered) * 100 
            : 0;
            
        $totalTimeSpent = $sessions->sum('time_spent_seconds');
        
        // Calculate progress over time for visualization
        $progressOverTime = $sessions->sortBy('created_at')->map(function($session) {
            return [
                'date' => $session->created_at->format('Y-m-d'),
                'smart_score' => $session->end_score
            ];
        })->values();

        return [
            'total_sessions' => $totalSessions,
            'average_accuracy' => round($avgAccuracy, 2),
            'overall_accuracy' => round($overallAccuracy, 2),
            'total_questions_answered' => $totalQuestionsAnswered,
            'total_correct_answers' => $totalCorrectAnswers,
            'total_time_spent_seconds' => $totalTimeSpent,
            'hours_practiced' => round($totalTimeSpent / 3600, 2),
            'progress_over_time' => $progressOverTime,
        ];
    }

    /**
     * Get mastery percentage based on the current smart score and level.
     *
     * @param int $smartScore
     * @param string $masteryLevel
     * @return int
     */
    private function getMasteryPercentage($smartScore, $masteryLevel)
    {
        switch ($masteryLevel) {
            case 'beginner':
                return min(100, intval(($smartScore / 20) * 100));
            case 'developing':
                return min(100, intval((($smartScore - 20) / 30) * 100));
            case 'proficient':
                return min(100, intval((($smartScore - 50) / 30) * 100));
            case 'advanced':
                return min(100, intval((($smartScore - 80) / 20) * 100));
            case 'master':
                return min(100, intval(max(0, (($smartScore - 100) / 10) * 100)));
            default:
                return 0;
        }
    }
}