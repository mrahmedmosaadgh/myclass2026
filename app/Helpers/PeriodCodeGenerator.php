<?php

namespace App\Helpers;

use Carbon\Carbon;

class PeriodCodeGenerator
{
    /**
     * Generate a standardized period code.
     * Format: Y{year_id}-S{semester}-W{iso_week}-D{day}-P{period}
     * Example: Y2026-S1-W12-D2-P3
     *
     * @param int $yearId Academic year ID
     * @param int $semester Semester number (1 or 2)
     * @param string|Carbon $date Date of the session
     * @param int $dayNumber Day number (1-7, Sunday-Saturday)
     * @param int $periodNumber Period number in the day
     * @return string
     */
    public static function generate(
        int $yearId,
        int $semester,
        string|Carbon $date,
        int $dayNumber,
        int $periodNumber
    ): string {
        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        $isoWeek = $carbonDate->isoWeek();
        
        return sprintf(
            'Y%d-S%d-W%02d-D%d-P%d',
            $yearId,
            $semester,
            $isoWeek,
            $dayNumber,
            $periodNumber
        );
    }

    /**
     * Parse a period code into its components.
     *
     * @param string $periodCode
     * @return array{year_id: int, semester: int, iso_week: int, day_number: int, period_number: int}
     */
    public static function parse(string $periodCode): array
    {
        // Pattern: Y2026-S1-W12-D2-P3
        preg_match('/Y(\d+)-S(\d+)-W(\d+)-D(\d+)-P(\d+)/', $periodCode, $matches);
        
        if (count($matches) !== 6) {
            throw new \InvalidArgumentException("Invalid period code format: {$periodCode}");
        }

        return [
            'year_id' => (int) $matches[1],
            'semester' => (int) $matches[2],
            'iso_week' => (int) $matches[3],
            'day_number' => (int) $matches[4],
            'period_number' => (int) $matches[5],
        ];
    }
}
