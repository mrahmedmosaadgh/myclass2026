<?php

namespace App\Services;

/**
 * PeriodCodeService
 * 
 * Manages period code generation and parsing for the reward system.
 * 
 * Period Codes:
 * - period_code_main: classroom_id.subject_id.teacher_id (uniquely identifies a teaching context)
 * - period_code: year_id.semester.week.day.period (uniquely identifies a time period)
 */
class PeriodCodeService
{
    /**
     * Generate period_code_main from teaching context components
     * Format: classroom_id.subject_id.teacher_id
     * 
     * @param int $classroomId
     * @param int $subjectId
     * @param int $teacherId
     * @return string
     */
    public static function generateMainCode(int $classroomId, int $subjectId, int $teacherId): string
    {
        return "{$classroomId}.{$subjectId}.{$teacherId}";
    }

    /**
     * Generate period_code from time period components
     * Format: year_id.semester.week.day.period
     * 
     * @param int $yearId
     * @param int $semester (1-4)
     * @param int $week (1-16)
     * @param int $day (1-7)
     * @param int $period (1-8)
     * @return string
     */
    public static function generatePeriodCode(int $yearId, int $semester, int $week, int $day, int $period): string
    {
        return "{$yearId}.{$semester}.{$week}.{$day}.{$period}";
    }

    /**
     * Parse period_code_main into components
     * 
     * @param string $mainCode Format: classroom_id.subject_id.teacher_id
     * @return array|null Returns ['classroom_id', 'subject_id', 'teacher_id'] or null if invalid
     */
    public static function parseMainCode(string $mainCode): ?array
    {
        $parts = explode('.', $mainCode);
        
        if (count($parts) !== 3) {
            return null;
        }

        return [
            'classroom_id' => (int) $parts[0],
            'subject_id' => (int) $parts[1],
            'teacher_id' => (int) $parts[2],
        ];
    }

    /**
     * Parse period_code into components
     * 
     * @param string $periodCode Format: year_id.semester.week.day.period
     * @return array|null Returns ['year_id', 'semester', 'week', 'day', 'period'] or null if invalid
     */
    public static function parsePeriodCode(string $periodCode): ?array
    {
        $parts = explode('.', $periodCode);
        
        if (count($parts) !== 5) {
            return null;
        }

        return [
            'year_id' => (int) $parts[0],
            'semester' => (int) $parts[1],
            'week' => (int) $parts[2],
            'day' => (int) $parts[3],
            'period' => (int) $parts[4],
        ];
    }

    /**
     * Validate period_code format and values
     * 
     * @param string $periodCode
     * @param int $maxSemester Default 4
     * @param int $maxWeek Default 16
     * @param int $maxDay Default 7
     * @param int $maxPeriod Default 8
     * @return bool
     */
    public static function validatePeriodCode(
        string $periodCode,
        int $maxSemester = 4,
        int $maxWeek = 16,
        int $maxDay = 7,
        int $maxPeriod = 8
    ): bool
    {
        $parsed = self::parsePeriodCode($periodCode);
        
        if (!$parsed) {
            return false;
        }

        return $parsed['semester'] >= 1 && $parsed['semester'] <= $maxSemester
            && $parsed['week'] >= 1 && $parsed['week'] <= $maxWeek
            && $parsed['day'] >= 1 && $parsed['day'] <= $maxDay
            && $parsed['period'] >= 1 && $parsed['period'] <= $maxPeriod;
    }

    /**
     * Generate human-readable period description
     * 
     * @param string $periodCode Format: year_id.semester.week.day.period
     * @return string
     */
    public static function getPeriodDescription(string $periodCode): string
    {
        $parsed = self::parsePeriodCode($periodCode);
        
        if (!$parsed) {
            return $periodCode;
        }

        $dayNames = ['', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $dayName = $dayNames[$parsed['day']] ?? "Day {$parsed['day']}";
        
        return sprintf(
            'Year %d, Semester %d, Week %d, %s, Period %d',
            $parsed['year_id'],
            $parsed['semester'],
            $parsed['week'],
            $dayName,
            $parsed['period']
        );
    }

    /**
     * Compare two period codes
     * Returns: -1 if $code1 < $code2, 0 if equal, 1 if $code1 > $code2
     * 
     * @param string $code1
     * @param string $code2
     * @return int|null Returns null if either code is invalid
     */
    public static function comparePeriods(string $code1, string $code2): ?int
    {
        $parsed1 = self::parsePeriodCode($code1);
        $parsed2 = self::parsePeriodCode($code2);
        
        if (!$parsed1 || !$parsed2) {
            return null;
        }

        // Compare year first
        if ($parsed1['year_id'] !== $parsed2['year_id']) {
            return $parsed1['year_id'] < $parsed2['year_id'] ? -1 : 1;
        }

        // Then semester
        if ($parsed1['semester'] !== $parsed2['semester']) {
            return $parsed1['semester'] < $parsed2['semester'] ? -1 : 1;
        }

        // Then week
        if ($parsed1['week'] !== $parsed2['week']) {
            return $parsed1['week'] < $parsed2['week'] ? -1 : 1;
        }

        // Then day
        if ($parsed1['day'] !== $parsed2['day']) {
            return $parsed1['day'] < $parsed2['day'] ? -1 : 1;
        }

        // Finally period
        if ($parsed1['period'] !== $parsed2['period']) {
            return $parsed1['period'] < $parsed2['period'] ? -1 : 1;
        }

        return 0; // All equal
    }

    /**
     * Get the next period code chronologically
     * 
     * @param string $periodCode Current period code
     * @param int $maxSemester
     * @param int $maxWeek
     * @param int $maxDay
     * @param int $maxPeriod
     * @return string|null
     */
    public static function getNextPeriod(
        string $periodCode,
        int $maxSemester = 4,
        int $maxWeek = 16,
        int $maxDay = 7,
        int $maxPeriod = 8
    ): ?string
    {
        $parsed = self::parsePeriodCode($periodCode);
        
        if (!$parsed) {
            return null;
        }

        $yearId = $parsed['year_id'];
        $semester = $parsed['semester'];
        $week = $parsed['week'];
        $day = $parsed['day'];
        $period = $parsed['period'];

        // Increment period
        $period++;
        if ($period > $maxPeriod) {
            $period = 1;
            $day++;
            
            if ($day > $maxDay) {
                $day = 1;
                $week++;
                
                if ($week > $maxWeek) {
                    $week = 1;
                    $semester++;
                    
                    if ($semester > $maxSemester) {
                        $semester = 1;
                        $yearId++;
                    }
                }
            }
        }

        return self::generatePeriodCode($yearId, $semester, $week, $day, $period);
    }
}
