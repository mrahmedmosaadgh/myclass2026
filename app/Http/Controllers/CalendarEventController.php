<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CalendarEventController extends Controller
{
    /**
     * Display the calendar page
     */
    public function index()
    {
        // Get user's school for filtering
        $user = Auth::user();
        $schoolId = null;

        if ($user->hasRole('admin')) {
            // Admin can see all schools, but we'll default to first school if available
            // For admin, we'll get the first school from the database
            $schoolId = \App\Models\School::first()?->id;
        } elseif ($user->teacher) {
            $schoolId = $user->teacher->school_id;
        }

        // Get events for the school
        $events = [];
        if ($schoolId) {
            $events = $this->getEventsForSchool($schoolId);
        }

        return Inertia::render('my_class/teacher/Calendar/Index', [
            'events' => $events,
            'schoolId' => $schoolId
        ]);
    }

    /**
     * Get events for a specific school
     */
    private function getEventsForSchool($schoolId)
    {
        $events = CalendarEvent::with('calendar')
            ->whereHas('calendar', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // If no events exist, return sample data for testing
        if ($events->isEmpty()) {
            return $this->getSampleEvents();
        }

        return $events;
    }

    /**
     * Get sample events for testing
     */
    private function getSampleEvents()
    {
        return [
            [
                'id' => 1,
                'title' => 'Sample Exam',
                'type' => 'exam',
                'description' => 'This is a sample exam event',
                'date' => date('Y-m-d'),
                'is_full_day' => false,
                'start_time' => '09:00',
                'end_time' => '11:00',
                'location' => 'Room 101',
                'affects_all_schedules' => false,
                'status' => 'active',
                'calendar' => [
                    'date' => date('Y-m-d')
                ]
            ],
            [
                'id' => 2,
                'title' => 'School Holiday',
                'type' => 'holiday',
                'description' => 'School closed for holiday',
                'date' => date('Y-m-d', strtotime('+2 days')),
                'is_full_day' => true,
                'start_time' => null,
                'end_time' => null,
                'location' => null,
                'affects_all_schedules' => true,
                'status' => 'active',
                'calendar' => [
                    'date' => date('Y-m-d', strtotime('+2 days'))
                ]
            ],
            [
                'id' => 3,
                'title' => 'Staff Meeting',
                'type' => 'meeting',
                'description' => 'Weekly staff meeting',
                'date' => date('Y-m-d', strtotime('+1 week')),
                'is_full_day' => false,
                'start_time' => '14:00',
                'end_time' => '15:30',
                'location' => 'Conference Room',
                'affects_all_schedules' => false,
                'status' => 'active',
                'calendar' => [
                    'date' => date('Y-m-d', strtotime('+1 week'))
                ]
            ]
        ];
    }

    /**
     * Get events for a school (API endpoint)
     */
    public function getEvents(Request $request)
    {
        $user = Auth::user();
        $schoolId = $request->input('school_id');

        // If no school_id provided, try to get from user
        if (!$schoolId) {
            if ($user->hasRole('admin')) {
                // For admin, get the first school from the database
                $schoolId = \App\Models\School::first()?->id;
            } elseif ($user->teacher) {
                $schoolId = $user->teacher->school_id;
            }
        }

        // Validate the user has access to this school
        if (!$user->hasRole('admin') && $user->teacher && $user->teacher->school_id != $schoolId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$schoolId) {
            return response()->json(['message' => 'School not found'], 404);
        }

        // Get events for the school
        $events = $this->getEventsForSchool($schoolId);

        return response()->json($events);
    }

    /**
     * Export calendar events to Excel
     */
    public function exportEvents(Request $request)
    {
        $schoolId = $request->input('school_id');

        // Validate the user has access to this school
        $user = Auth::user();
        if (!$user->hasRole('admin') && $user->teacher && $user->teacher->school_id != $schoolId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get events for the school
        $events = CalendarEvent::with('calendar')
            ->whereHas('calendar', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Create a new spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Title');
        $sheet->setCellValue('B1', 'Type');
        $sheet->setCellValue('C1', 'Description');
        $sheet->setCellValue('D1', 'Date');
        $sheet->setCellValue('E1', 'All Day');
        $sheet->setCellValue('F1', 'Start Time');
        $sheet->setCellValue('G1', 'End Time');
        $sheet->setCellValue('H1', 'Location');
        $sheet->setCellValue('I1', 'Affects All Schedules');
        $sheet->setCellValue('J1', 'Status');

        // Add data
        $row = 2;
        foreach ($events as $event) {
            $sheet->setCellValue('A' . $row, $event->title);
            $sheet->setCellValue('B' . $row, $event->type);
            $sheet->setCellValue('C' . $row, $event->description);
            $sheet->setCellValue('D' . $row, $event->calendar->date);
            $sheet->setCellValue('E' . $row, $event->is_full_day ? 'Yes' : 'No');
            $sheet->setCellValue('F' . $row, $event->start_time);
            $sheet->setCellValue('G' . $row, $event->end_time);
            $sheet->setCellValue('H' . $row, $event->location);
            $sheet->setCellValue('I' . $row, $event->affects_all_schedules ? 'Yes' : 'No');
            $sheet->setCellValue('J' . $row, $event->status);
            $row++;
        }

        // Create writer and save to output
        $writer = new Xlsx($spreadsheet);
        $filename = 'calendar_events_' . date('Y-m-d') . '.xlsx';

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Display the form to create a new event
     */
    public function create()
    {
        $calendars = Calendar::where('school_id', auth()->user()->school_id)
            ->orderBy('date')
            ->get();

        return view('calendar-events.create', compact('calendars'));
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:255',
            'is_full_day' => 'boolean',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:255',
            'affected_schedules' => 'nullable|array',
            'affects_all_schedules' => 'boolean',
            'status' => 'required|string|in:active,cancelled,completed',
            'date' => 'required|date'
        ]);

        // Get the teacher's school ID and teacher ID
        $user = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return response()->json(['message' => 'Teacher record not found for this user'], 400);
        }

        $schoolId = $teacher->school_id;

        if (!$schoolId) {
            return response()->json(['message' => 'School ID not found for this teacher'], 400);
        }

        // Find or create calendar entry for the given date
        $date = $request->input('date');
        $calendar = Calendar::firstOrCreate(
            ['date' => $date, 'school_id' => $schoolId],
            [
                'day_name' => date('l', strtotime($date)),
                'week_number' => 1, // Default value
                'semester_number' => 1, // Default value
                'year_id' => 1, // Default value - you might need to determine this dynamically
                'is_holiday' => false,
                'is_weekend' => in_array(date('N', strtotime($date)), [6, 7]) // 6=Saturday, 7=Sunday
            ]
        );

        // Add calendar_id to validated data
        $validated['calendar_id'] = $calendar->id;
        $validated['created_by'] = $teacher->id; // Use teacher ID instead of user ID
        $validated['updated_by'] = $teacher->id; // Use teacher ID instead of user ID

        $event = CalendarEvent::create($validated);

        return response()->json($event);
    }

    /**
     * Display the form to edit an event
     */
    public function edit(CalendarEvent $calendarEvent)
    {
        $calendars = Calendar::where('school_id', auth()->user()->school_id)
            ->orderBy('date')
            ->get();

        return view('calendar-events.edit', compact('calendarEvent', 'calendars'));
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:255',
            'is_full_day' => 'boolean',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:255',
            'affected_schedules' => 'nullable|array',
            'affects_all_schedules' => 'boolean',
            'status' => 'required|string|in:active,cancelled,completed',
            'date' => 'required|date'
        ]);

        // If date is changing, we need to find or create a new calendar entry
        if ($request->has('date')) {
            // Get the teacher's school ID
            $user = Auth::user();
            $teacher = $user->teacher;

            if (!$teacher) {
                return response()->json(['message' => 'Teacher record not found for this user'], 400);
            }

            $schoolId = $teacher->school_id;

            if (!$schoolId) {
                return response()->json(['message' => 'School ID not found for this teacher'], 400);
            }

            // Find or create calendar entry for the given date
            $date = $request->input('date');
            $calendar = Calendar::firstOrCreate(
                ['date' => $date, 'school_id' => $schoolId],
                [
                    'day_name' => date('l', strtotime($date)),
                    'week_number' => 1, // Default value
                    'semester_number' => 1, // Default value
                    'year_id' => 1, // Default value - you might need to determine this dynamically
                    'is_holiday' => false,
                    'is_weekend' => in_array(date('N', strtotime($date)), [6, 7]) // 6=Saturday, 7=Sunday
                ]
            );

            // Add calendar_id to validated data
            $validated['calendar_id'] = $calendar->id;
        }

        // Use teacher ID instead of user ID
        $user = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return response()->json(['message' => 'Teacher record not found for this user'], 400);
        }

        $validated['updated_by'] = $teacher->id;

        $calendarEvent->update($validated);

        return response()->json($calendarEvent->fresh());
    }

    /**
     * Remove the specified event
     */
    public function destroy(CalendarEvent $calendarEvent)
    {
        // Check if the user has permission to delete this event
        $user = Auth::user();
        $teacher = $user->teacher;

        if (!$user->hasRole('admin') && $teacher && $calendarEvent->created_by != $teacher->id) {
            return response()->json(['message' => 'You do not have permission to delete this event'], 403);
        }

        $calendarEvent->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }

    /**
     * Get event types for dropdown
     */
    public function getEventTypes()
    {
        $types = [
            ['value' => 'holiday', 'label' => 'Holiday'],
            ['value' => 'meeting', 'label' => 'Meeting'],
            ['value' => 'exam', 'label' => 'Exam'],
            ['value' => 'activity', 'label' => 'Activity'],
            ['value' => 'other', 'label' => 'Other']
        ];

        return response()->json($types);
    }



    /**
     * Get teacher's school ID by user ID
     */
    private function getTeacherSchoolId($userId)
    {
        $teacher = Teacher::where('user_id', $userId)->first();
        if (!$teacher) {
            return null;
        }
        return $teacher->school_id;
    }

    /**
     * Import calendar events from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.title' => 'required|string|max:255',
            'data.*.type' => 'required|string|in:holiday,meeting,exam,activity,other',
            'data.*.date' => 'required|date',
        ]);

        $user = Auth::user();
        $schoolId = null;

        if ($user->hasRole('admin')) {
            // For admin, get the first school from the database
            $schoolId = \App\Models\School::first()?->id;
        } elseif ($user->teacher) {
            $schoolId = $user->teacher->school_id;
        }

        if (!$schoolId) {
            return response()->json(['message' => 'School not found'], 404);
        }

        $results = ['success' => [], 'errors' => []];

        foreach ($request->data as $index => $eventData) {
            try {
                // Find or create calendar entry for the date
                $calendar = Calendar::firstOrCreate(
                    ['date' => $eventData['date'], 'school_id' => $schoolId],
                    [
                        'day_name' => date('l', strtotime($eventData['date'])),
                        'week_number' => 1,
                        'semester_number' => 1,
                        'year_id' => 1,
                        'is_holiday' => false,
                        'is_weekend' => in_array(date('N', strtotime($eventData['date'])), [6, 7])
                    ]
                );

                // Create the event
                $event = CalendarEvent::create([
                    'title' => $eventData['title'],
                    'description' => $eventData['description'] ?? null,
                    'type' => $eventData['type'],
                    'is_full_day' => $eventData['is_full_day'] ?? false,
                    'start_time' => $eventData['start_time'] ?? null,
                    'end_time' => $eventData['end_time'] ?? null,
                    'location' => $eventData['location'] ?? null,
                    'status' => $eventData['status'] ?? 'active',
                    'calendar_id' => $calendar->id,
                    'created_by' => $user->teacher?->id ?? $user->id,
                    'updated_by' => $user->teacher?->id ?? $user->id,
                ]);

                $results['success'][] = "Row " . ($index + 1) . ": Event '{$event->title}' created successfully";

            } catch (\Exception $e) {
                $results['errors'][] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        return response()->json([
            'results' => $results,
            'message' => count($results['success']) . ' events imported successfully, ' . count($results['errors']) . ' errors'
        ]);
    }

    /**
     * Validate import data
     */
    public function validateImport(Request $request)
    {
        $request->validate([
            'data' => 'required|array'
        ]);

        $errors = [];
        $validData = [];

        foreach ($request->data as $index => $row) {
            $rowErrors = [];

            if (empty($row['title'])) {
                $rowErrors[] = 'Title is required';
            }

            if (empty($row['type']) || !in_array($row['type'], ['holiday', 'meeting', 'exam', 'activity', 'other'])) {
                $rowErrors[] = 'Valid event type is required';
            }

            if (empty($row['date']) || !strtotime($row['date'])) {
                $rowErrors[] = 'Valid date is required';
            }

            if (!empty($rowErrors)) {
                $errors["Row " . ($index + 1)] = $rowErrors;
            } else {
                $validData[] = $row;
            }
        }

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
            'validData' => $validData
        ]);
    }
}









