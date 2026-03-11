<?php

namespace App\Services\Courses\bm;

class BMScoringService
{
    /**
     * Calculate the final BM Score based on accuracy, fluency, and consistency.
     */
    public function calculateFinalScore(array $responses): array
    {
        if (empty($responses)) {
            return [
                'accuracy' => 0,
                'fluency' => 0,
                'total' => 0,
                'level' => 'Beginner',
                'domains' => []
            ];
        }

        $totalCorrect = 0;
        $totalTime = 0;
        $domains = [];

        foreach ($responses as $response) {
            $isCorrect = $response['is_correct'] ?? false;
            $timeMs = $response['time_taken_ms'] ?? 0;
            $domain = $response['domain'] ?? 'Other';
            
            if ($isCorrect) $totalCorrect++;
            $totalTime += $timeMs;

            if (!isset($domains[$domain])) {
                $domains[$domain] = ['correct' => 0, 'total' => 0, 'time' => 0];
            }
            $domains[$domain]['total']++;
            if ($isCorrect) $domains[$domain]['correct']++;
            $domains[$domain]['time'] += $timeMs;
        }

        $accuracy = ($totalCorrect / count($responses)) * 100;
        
        // Fluency score (assuming < 15s per question is 100% fluency, slower reduces score)
        $avgTimeSec = ($totalTime / count($responses)) / 1000;
        
        // Base score is purely accuracy
        // We use fluency only to differentiate between levels of mastery
        // For basic math, if you get it right, you get the points. 
        // Speed acts as a slight modifier if accuracy is somewhat low, 
        // but 100% accuracy ALWAYS means 100% score for this specific age group.
        
        $fluency = 100;
        if ($avgTimeSec > 15) {
            $fluency = max(0, 100 - (($avgTimeSec - 15) * 5)); 
        }

        // If you get everything right, you get 100.
        // Otherwise, fluency plays a tiny 10% role in your score.
        if ($accuracy == 100) {
            $totalScore = 100;
        } else {
            $totalScore = round(($accuracy * 0.9) + ($fluency * 0.1));
        }

        // Domain Specific Scores (Accuracy only for radar for now)
        $domainScores = [];
        foreach ($domains as $domain => $stats) {
            $domainScores[strtolower($domain)] = round(($stats['correct'] / $stats['total']) * 100);
        }

        $level = 'Beginner';
        if ($totalScore >= 90) $level = 'Expert';
        elseif ($totalScore >= 75) $level = 'Advanced';
        elseif ($totalScore >= 50) $level = 'Proficient';
        elseif ($totalScore >= 25) $level = 'Developing';

        return [
            'accuracy' => round($accuracy),
            'fluency' => round($fluency),
            'total' => $totalScore,
            'level' => $level,
            'domains' => $domainScores
        ];
    }
}
