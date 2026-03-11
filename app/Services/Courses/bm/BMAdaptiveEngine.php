<?php

namespace App\Services\Courses\bm;

class BMAdaptiveEngine
{
    /**
     * Compute the next question's difficulty level using the CAT algorithm.
     */
    public function getNextDifficulty(int $currentDifficulty, bool $wasCorrect, int $timeTakenMs): int
    {
        // Basic CAT logic incorporating speed (fluency)
        if ($wasCorrect) {
            if ($timeTakenMs < 5000) {
                // Fast and correct -> jump ahead 2
                return min(10, $currentDifficulty + 2);
            } elseif ($timeTakenMs < 15000) {
                // Moderate speed and correct -> jump ahead 1
                return min(10, $currentDifficulty + 1);
            } else {
                // Slow and correct -> stay at same difficulty to build fluency
                return $currentDifficulty;
            }
        } else {
            if ($timeTakenMs < 3000) {
                // Very fast and wrong -> guessing, drop by 2
                return max(1, $currentDifficulty - 2);
            }
            // Normal wrong answer
            return max(1, $currentDifficulty - 1);
        }
    }
}
