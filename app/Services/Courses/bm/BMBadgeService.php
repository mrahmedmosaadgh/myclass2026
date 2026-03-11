<?php

namespace App\Services\Courses\bm;

use App\Models\Courses\bm\BMBadge;

class BMBadgeService
{
    /**
     * Analyze assessment results and award badges.
     */
    public function awardBadges(int $userId, int $assessmentId, array $responses, array $scoreData): array
    {
        $newBadges = [];
        $awarded = [];

        // Badge 1: Perfect Score (100% Accuracy)
        if ($scoreData['accuracy'] == 100) {
            $newBadges[] = [
                'type' => 'perfect_score',
                'name' => 'Flawless Victory',
                'description' => 'You answered every question correctly!',
                'icon' => 'star',
                'color' => 'warning'
            ];
        }

        // Badge 2: Speed Demon (High Fluency, good Accuracy)
        if ($scoreData['fluency'] >= 90 && $scoreData['accuracy'] >= 80) {
            $newBadges[] = [
                'type' => 'speed_demon',
                'name' => 'Speed Demon',
                'description' => 'Lightning fast answers with high accuracy.',
                'icon' => 'bolt',
                'color' => 'info'
            ];
        }

        // Badge 3: Domain Master (100% in a specific domain)
        foreach ($scoreData['domains'] as $domain => $domainScore) {
            if ($domainScore == 100) {
                $newBadges[] = [
                    'type' => "master_{$domain}",
                    'name' => ucfirst($domain) . ' Master',
                    'description' => "Perfect score in {$domain}.",
                    'icon' => 'school',
                    'color' => 'positive'
                ];
            }
        }

        // Insert badges into database
        foreach ($newBadges as $badgeDef) {
            // Check if user already has this badge (optional: maybe they can earn it multiple times, 
            // but usually badges are unique per user or at least unique per assessment)
            $exists = BMBadge::where('user_id', $userId)
                ->where('badge_type', $badgeDef['type'])
                ->exists();

            if (!$exists) {
                BMBadge::create([
                    'user_id' => $userId,
                    'badge_type' => $badgeDef['type'],
                    'bm_assessment_id' => $assessmentId,
                    'earned_at' => now(),
                ]);
                
                $awarded[] = $badgeDef;
            }
        }

        return $awarded;
    }
}
