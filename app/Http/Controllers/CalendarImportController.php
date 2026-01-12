<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Calendar;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CalendarImportController extends Controller
{
    /**
     * Import calendar data from JSON (received from ExcelManager component)
     */
    public function importCalendarData(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.date' => 'required|date',
            'data.*.semester' => 'required',
            'data.*.status' => 'required|integer|between:0,4',
            'data.*.week_number' => 'nullable|integer|min:1',
            'data.*.event' => 'nullable|string|max:255',
            'data.*.notes' => 'nullable|string',
        ]);

        $user = auth()->user();
        $schoolId = $user->schoolId();

        if (!$schoolId) {
            return response()->json([
                'success' => false,
                'message' => 'You must be assigned to a school.',
            ], 403);
        }

        $results = [
            'success' => [],
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($request->data as $index => $row) {
                $rowNumber = $index + 1;

                try {
                    // Find semester by name or number
                    $semester = $this->findSemester($row['semester'], $schoolId);

                    if (!$semester) {
                        $results['errors'][] = "Row {$rowNumber}: Semester '{$row['semester']}' not found.";
                        continue;
                    }

                    // Check if date is within semester range
                    $date = Carbon::parse($row['date']);
                    if ($semester->start_date && $semester->end_date) {
                        $semesterStart = Carbon::parse($semester->start_date);
                        $semesterEnd = Carbon::parse($semester->end_date);

                        if ($date->lt($semesterStart) || $date->gt($semesterEnd)) {
                            $results['errors'][] = "Row {$rowNumber}: Date {$row['date']} is outside semester range ({$semester->start_date} to {$semester->end_date}).";
                            continue;
                        }
                    }

                    // Calculate week number if not provided
                    $weekNumber = $row['week_number'] ?? null;
                    if (!$weekNumber && $semester->start_date) {
                        $weekNumber = $date->diffInWeeks(Carbon::parse($semester->start_date)) + 1;
                    }

                    // Calculate day number (1-7, where 1 is Monday)
                    $dayNumber = $date->dayOfWeekIso;

                    // Check for existing record
                    $existing = Calendar::where('date', $date->format('Y-m-d'))
                        ->where('school_id', $schoolId)
                        ->first();

                    $calendarData = [
                        'semester_id' => $semester->id,
                        'academic_year_id' => $semester->academic_year_id,
                        'status' => $row['status'],
                        'week_number' => $weekNumber,
                        'day_number' => $dayNumber,
                        'event' => $row['event'] ?? null,
                        'data' => isset($row['notes']) ? ['notes' => $row['notes']] : null,
                    ];

                    if ($existing) {
                        $existing->update($calendarData);
                        $results['success'][] = "Row {$rowNumber}: Updated calendar for {$row['date']}.";
                    } else {
                        Calendar::create(array_merge($calendarData, [
                            'date' => $date->format('Y-m-d'),
                            'school_id' => $schoolId,
                        ]));
                        $results['success'][] = "Row {$rowNumber}: Created calendar for {$row['date']}.";
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Row {$rowNumber}: {$e->getMessage()}";
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Import completed.',
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'results' => $results,
            ], 500);
        }
    }

    /**
     * Export calendar data for a specific academic year
     */
    public function exportCalendarData(AcademicYear $year)
    {
        $user = auth()->user();
        $schoolId = $user->schoolId();

        // Ensure year belongs to user's school
        if ($year->school_id !== $schoolId) {
            return response()->json([
                'error' => 'Unauthorized access to this academic year.',
            ], 403);
        }

        $calendars = Calendar::with('semester')
            ->where('academic_year_id', $year->id)
            ->orderBy('date')
            ->get()
            ->map(function ($calendar) {
                return [
                    'date' => $calendar->date->format('Y-m-d'),
                    'semester' => $calendar->semester ? $calendar->semester->name : '',
                    'status' => $calendar->status,
                    'status_label' => $this->getStatusLabel($calendar->status),
                    'week_number' => $calendar->week_number,
                    'event' => $calendar->event,
                    'notes' => $calendar->data['notes'] ?? '',
                ];
            });

        return response()->json($calendars);
    }

    /**
     * Get export template with sample data
     */
    public function getExportTemplate()
    {
        $sampleData = [
            [
                'date' => '2025-09-01',
                'semester' => 1,
                'status' => 1,
                'status_label' => 'Work Day',
                'week_number' => 1,
                'event' => '',
                'notes' => '',
            ],
            [
                'date' => '2025-09-02',
                'semester' => 1,
                'status' => 1,
                'status_label' => 'Work Day',
                'week_number' => 1,
                'event' => '',
                'notes' => '',
            ],
            [
                'date' => '2025-09-03',
                'semester' => 1,
                'status' => 0,
                'status_label' => 'Day Off',
                'week_number' => 1,
                'event' => 'Weekend',
                'notes' => '',
            ],
            [
                'date' => '2025-09-04',
                'semester' => 1,
                'status' => 0,
                'status_label' => 'Day Off',
                'week_number' => 1,
                'event' => 'Weekend',
                'notes' => '',
            ],
            [
                'date' => '2025-09-05',
                'semester' => 1,
                'status' => 1,
                'status_label' => 'Work Day',
                'week_number' => 2,
                'event' => '',
                'notes' => '',
            ],
        ];

        return response()->json($sampleData);
    }

    /**
     * Find semester by name or number
     */
    private function findSemester($identifier, $schoolId)
    {
        // If it's numeric, try to find by semester number first
        if (is_numeric($identifier)) {
            $semester = Semester::whereHas('academicYear', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId)
                    ->where('active', true); // Only search in active year
            })
                ->where('semester_number', $identifier)
                ->first();

            if ($semester) {
                return $semester;
            }
        }

        // Try to find by name
        $semester = Semester::whereHas('academicYear', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })
            ->where('name', $identifier)
            ->first();

        return $semester;
    }

    /**
     * Get human-readable status label
     */
    private function getStatusLabel($status)
    {
        return match ($status) {
            0 => 'Day Off',
            1 => 'Work Day',
            2 => 'Activity',
            3 => 'Test',
            4 => 'Final Exam',
            default => 'Unknown',
        };
    }
}
