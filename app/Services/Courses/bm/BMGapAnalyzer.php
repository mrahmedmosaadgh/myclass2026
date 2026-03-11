<?php

namespace App\Services\Courses\bm;

class BMGapAnalyzer
{
    /**
     * Map assessment responses and scores to specific curriculum lessons.
     */
    public function getRecommendedLessons(array $domainScores): array
    {
        $recommendations = [];

        foreach ($domainScores as $domain => $score) {
            if ($score < 50) {
                // Critical gap
                $recommendations[] = [
                    'domain' => ucfirst($domain),
                    'title' => 'Master ' . ucfirst($domain),
                    'description' => "Your $domain score is $score%. This is a core weakness pulling your overall level down.",
                    'color' => 'negative',
                    'icon' => 'warning'
                ];
            } elseif ($score < 75) {
                // Fluency gap
                $recommendations[] = [
                    'domain' => ucfirst($domain),
                    'title' => ucfirst($domain) . ' Speed Drills',
                    'description' => "You understand $domain, but let's improve your speed to reach the next level.",
                    'color' => 'warning',
                    'icon' => 'bolt'
                ];
            }
        }

        // If perfect score everywhere, return an advanced recommendation
        if (empty($recommendations)) {
            $recommendations[] = [
                'domain' => 'Advanced',
                'title' => 'Advanced Challenge',
                'description' => 'You crushed the basics! Try timing yourself against the clock on extreme mode.',
                'color' => 'positive',
                'icon' => 'emoji_events'
            ];
        }

        // Return top 3 recommendations
        return array_slice($recommendations, 0, 3);
    }
}
