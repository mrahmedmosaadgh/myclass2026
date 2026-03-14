<?php

namespace App\Services;

use App\Models\Bm2Assessment;
use App\Models\Bm2Badge;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Bm2GamificationService
 * 
 * Handles badge earning, streak tracking, and gamification logic.
 */
class Bm2GamificationService
{
    /**
     * Check and award badges after assessment completion.
     */
    public function checkAndAwardBadges(Bm2Assessment $assessment): array
    {
        $student = $assessment->student;
        $awardedBadges = [];

        // Get all active badges
        $badges = Bm2Badge::where('is_active', true)->get();

        foreach ($badges as $badge) {
            // Skip if student already has this badge
            if ($this->studentHasBadge($student, $badge)) {
                continue;
            }

            // Check if student meets criteria
            if ($this->meetsBadgeCriteria($student, $badge, $assessment)) {
                $this->awardBadge($student, $badge, $assessment);
                $awardedBadges[] = $badge;
            }
        }

        return $awardedBadges;
    }

    /**
     * Check if student already has a badge.
     */
    private function studentHasBadge(User $student, Bm2Badge $badge): bool
    {
        return $student->bm2Badges()->where('badge_id', $badge->id)->exists();
    }

    /**
     * Check if student meets badge earning criteria.
     */
    private function meetsBadgeCriteria(User $student, Bm2Badge $badge, ?Bm2Assessment $assessment = null): bool
    {
        $criteria = json_decode($badge->earning_criteria, true);

        if (!isset($criteria['type'])) {
            return false;
        }

        switch ($criteria['type']) {
            case 'assessment_complete':
                return $this->checkAssessmentComplete($student, $criteria);

            case 'score_threshold':
                return $this->checkScoreThreshold($student, $criteria, $assessment);

            case 'assessment_count':
                return $this->checkAssessmentCount($student, $criteria);

            case 'skill_score':
                return $this->checkSkillScore($student, $criteria);

            case 'speed_completion':
                return $this->checkSpeedCompletion($student, $criteria, $assessment);

            case 'streak':
                return $this->checkStreak($student, $criteria);

            default:
                return false;
        }
    }

    /**
     * Check assessment completion criteria.
     */
    private function checkAssessmentComplete(User $student, array $criteria): bool
    {
        $count = Bm2Assessment::where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->count();

        return $count >= $criteria['value'];
    }

    /**
     * Check score threshold criteria.
     */
    private function checkScoreThreshold(User $student, array $criteria, ?Bm2Assessment $assessment): bool
    {
        if ($assessment && $assessment->overall_score !== null) {
            return $assessment->overall_score >= $criteria['value'];
        }

        // Check if any assessment meets the threshold
        return Bm2Assessment::where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->where('overall_score', '>=', $criteria['value'])
            ->exists();
    }

    /**
     * Check assessment count criteria.
     */
    private function checkAssessmentCount(User $student, array $criteria): bool
    {
        $count = Bm2Assessment::where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->count();

        return $count >= $criteria['value'];
    }

    /**
     * Check skill score criteria.
     */
    private function checkSkillScore(User $student, array $criteria): bool
    {
        $skill = $criteria['skill'] ?? null;
        $minScore = $criteria['value'] ?? 0;

        if (!$skill) {
            return false;
        }

        // Get latest assessment with skill breakdown
        $latestAssessment = Bm2Assessment::where('student_id', $student->id)
            ->whereNotNull('skill_breakdown')
            ->latest('completed_at')
            ->first();

        if (!$latestAssessment) {
            return false;
        }

        $skillBreakdown = $latestAssessment->skill_breakdown;
        
        if (!isset($skillBreakdown[$skill])) {
            return false;
        }

        return ($skillBreakdown[$skill]['percentage'] ?? 0) >= $minScore;
    }

    /**
     * Check speed completion criteria.
     */
    private function checkSpeedCompletion(User $student, array $criteria, ?Bm2Assessment $assessment): bool
    {
        if (!$assessment || !$assessment->total_time_seconds) {
            return false;
        }

        $maxTime = $criteria['time_seconds'] ?? 600; // Default 10 minutes
        $minAccuracy = $criteria['min_accuracy'] ?? 85;

        return $assessment->total_time_seconds <= $maxTime
            && $assessment->overall_score >= $minAccuracy;
    }

    /**
     * Check streak criteria.
     */
    private function checkStreak(User $student, array $criteria): bool
    {
        $requiredStreak = $criteria['value'] ?? 0;
        $currentStreak = $this->getCurrentStreak($student);

        return $currentStreak >= $requiredStreak;
    }

    /**
     * Award a badge to a student.
     */
    public function awardBadge(User $student, Bm2Badge $badge, ?Bm2Assessment $assessment = null, string $reason = null): void
    {
        DB::table('bm2_student_badges')->insert([
            'student_id' => $student->id,
            'badge_id' => $badge->id,
            'earned_at' => now(),
            'points_awarded' => $badge->points_value,
            'earned_for' => $reason ?? $badge->description,
            'assessment_id' => $assessment?->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // You could trigger a notification here
        // Notification::send($student, new BadgeEarnedNotification($badge));
    }

    /**
     * Calculate current streak for a student.
     */
    public function getCurrentStreak(User $student): int
    {
        // Get all completed assessments grouped by date
        $assessments = Bm2Assessment::where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->get(['completed_at']);

        if ($assessments->isEmpty()) {
            return 0;
        }

        $streak = 1;
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();

        // Check if last assessment was today or yesterday
        $lastAssessmentDate = \Carbon\Carbon::parse($assessments->first()->completed_at)->startOfDay();

        if (!$lastAssessmentDate->eq($today) && !$lastAssessmentDate->eq($yesterday)) {
            return 0; // Streak broken
        }

        // Count consecutive days
        for ($i = 1; $i < $assessments->count(); $i++) {
            $currentDate = \Carbon\Carbon::parse($assessments[$i]->completed_at)->startOfDay();
            $previousDate = \Carbon\Carbon::parse($assessments[$i - 1]->completed_at)->startOfDay();

            if ($currentDate->diffInDays($previousDate) === 1) {
                $streak++;
            } elseif ($currentDate->diffInDays($previousDate) > 1) {
                break; // Streak broken
            }
        }

        return $streak;
    }

    /**
     * Update streak after assessment completion.
     */
    public function updateStreak(User $student, Bm2Assessment $assessment): void
    {
        $currentStreak = $this->getCurrentStreak($student);

        // Store streak in user profile or separate table if needed
        // For now, we calculate it on-the-fly
    }

    /**
     * Get student's total points from badges.
     */
    public function getTotalPoints(User $student): int
    {
        return DB::table('bm2_student_badges')
            ->where('student_id', $student->id)
            ->sum('points_awarded');
    }

    /**
     * Get student's badge collection summary.
     */
    public function getBadgeSummary(User $student): array
    {
        $badges = $student->bm2Badges()
            ->withPivot('earned_at', 'points_awarded')
            ->orderByPivot('earned_at', 'desc')
            ->get();

        return [
            'total_badges' => $badges->count(),
            'total_points' => $badges->sum('pivot.points_awarded'),
            'by_category' => $badges->groupBy(function($badge) {
                return $badge->category;
            })->map->count()->toArray(),
            'recent_badges' => $badges->take(5)->map(function($badge) {
                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'icon_url' => $badge->icon_url,
                    'category' => $badge->category,
                    'rarity' => $badge->rarity,
                    'earned_at' => $badge->pivot->earned_at,
                    'points' => $badge->pivot->points_awarded,
                ];
            }),
        ];
    }
}
