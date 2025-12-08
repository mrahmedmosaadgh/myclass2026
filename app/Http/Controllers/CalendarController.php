<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Traits\ImportValidationTrait;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    use ImportValidationTrait;

    public function index(Request $request)
    {
        $query = Calendar::with(['school', 'academicYear'])
            ->where('school_id', auth()->user()->school_id);

        if ($request->has('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        if ($request->has('semester_number')) {
            $query->where('semester_number', $request->semester_number);
        }

        $calendars = $query->orderBy('date')->get();
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'desc')
            ->get();
return Inertia::render('my_class/admin/Calendars/Index', [
    'calendars' => $calendars,
    'academicYears' => $academicYears,
]);
// resources/js/Pages/my_class/admin/Calendars/Index.vue
        // return view('calendars.index', compact('calendars', 'academicYears'));
    }

    public function create()
    {
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('calendars.create', compact('academicYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year_id' => 'required|exists:academic_years,id',
            'semester_number' => 'required|integer|in:1,2',
            'week_number' => 'required|integer|min:1',
            'date' => 'required|date',
            'is_holiday' => 'boolean',
            'is_weekend' => 'boolean',
            'day_name' => 'required|string|max:255'
        ]);

        $validated['school_id'] = auth()->user()->school_id;

        Calendar::create($validated);

        return redirect()->route('calendars.index')
            ->with('success', 'Calendar entry created successfully.');
    }

    public function edit(Calendar $calendar)
    {
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('calendars.edit', compact('calendar', 'academicYears'));
    }

    public function update(Request $request, Calendar $calendar)
    {
        $validated = $request->validate([
            'year_id' => 'required|exists:academic_years,id',
            'semester_number' => 'required|integer|in:1,2',
            'week_number' => 'required|integer|min:1',
            'date' => 'required|date',
            'is_holiday' => 'boolean',
            'is_weekend' => 'boolean',
            'day_name' => 'required|string|max:255'
        ]);

        $calendar->update($validated);

        return redirect()->route('calendars.index')
            ->with('success', 'Calendar entry updated successfully.');
    }

    public function destroy(Calendar $calendar)
    {
        $calendar->delete();

        return redirect()->route('calendars.index')
            ->with('success', 'Calendar entry deleted successfully.');
    }

    public function validateImport(Request $request)
    {
        $data = $request->validate([
            'data' => 'required|array',
            'required_fields' => 'required|array',
            'columns' => 'required|array'
        ]);

        $statuses = [];
        foreach ($data['data'] as $row) {
            // Convert school name to school_id
            $schoolId = null;
            if (isset($row['school'])) {
                $schoolId = School::where('name', $row['school'])->value('id');
            }

            $validationData = [
                'col_db' => 'date',
                'col_file' => 'date',
                'row' => array_merge($row, ['school_id' => $schoolId]), // Merge school_id into row data
                'model' => Calendar::class,
                'uniqueFields' => [
                    'date' => 'date',
                    'school_id' => 'school_id'
                ],
                'requiredFields' => $data['required_fields']
            ];

            $status = $this->validateImportRecord($validationData);
            $statuses[] = $status;
        }

        return response()->json([
            'statuses' => $statuses
        ]);
    }

    public function import(Request $request)
    {
        \Log::info('Import data:', ['data' => $request->all()]); // Add this line for debugging
        $data = $request->validate([
            'data' => 'required|array',
            'columns' => 'required|array'
        ]);

        $results = [
            'success' => [],
            'errors' => []
        ];

        DB::beginTransaction();
        try {
            foreach ($data['data'] as $row) {
                try {
                    // Validate required fields
                    if (!isset($row['date']) || !isset($row['school']) || !isset($row['academic_year'])) {
                        throw new \Exception("Missing required fields: date, school, and academic_year are required");
                    }

                    // Handle school identification (accept both ID and name)
                    $schoolId = $this->resolveSchoolId($row['school']);
                    if (!$schoolId) {
                        throw new \Exception("School not found: {$row['school']}");
                    }

                    // Handle academic year (accept both ID and name)
                    $academicYearId = $this->resolveAcademicYearId($row['academic_year']);
                    if (!$academicYearId) {
                        throw new \Exception("Academic year not found: {$row['academic_year']}");
                    }

                    // Convert status from number to string if needed
                    $status = $this->convertStatus($row['status'] ?? 1);

                    // Sanitize numeric values
                    $calendarData = [
                        'date' => $row['date'],
                        'week' => $this->sanitizeNumericValue($row['week'] ?? 1),
                        'day' => $row['day'] ?? '',
                        'day_number' => $this->sanitizeNumericValue($row['day_number'] ?? 1),
                        'week_number' => $this->sanitizeNumericValue($row['week_number'] ?? 1),
                        'semester_number' => $this->sanitizeNumericValue($row['semester_number'] ?? 1),
                        'school_id' => $schoolId,
                        'academic_year_id' => $academicYearId,
                        'status' => $status,
                        'event' => $row['event'] ?? null,
                        'event_academic' => $row['event_academic'] ?? null,
                        'vacation' => isset($row['vacation']) ? (bool)$row['vacation'] : false,
                        'user_id' => Auth::id()
                    ];

                    // Check for existing record
                    $existingCalendar = Calendar::where('date', $row['date'])
                        ->where('school_id', $schoolId)
                        ->first();

                    if ($existingCalendar) {
                        $existingCalendar->update($calendarData);
                        $results['success'][] = "Updated calendar entry for date: {$row['date']}";
                    } else {
                        Calendar::create($calendarData);
                        $results['success'][] = "Created calendar entry for date: {$row['date']}";
                    }

                } catch (\Exception $e) {
                    $results['errors'][] = "Error processing record for date {$row['date']}: " . $e->getMessage();
                }
            }

            if (empty($results['errors'])) {
                DB::commit();
                return response()->json([
                    'results' => $results,
                    'importId' => uniqid('imp_')
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'results' => $results,
                    'importId' => uniqid('imp_')
                ], 422);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => [],
                'errors' => ['An error occurred while processing the data: ' . $e->getMessage()]
            ], 500);
        }
    }

    private function resolveSchoolId($school)
    {
        if (is_numeric($school)) {
            return School::where('id', $school)->exists() ? $school : null;
        }
        return School::where('name', $school)->value('id');
    }

    private function resolveAcademicYearId($academicYear)
    {
        if (is_numeric($academicYear)) {
            return AcademicYear::where('id', $academicYear)->exists() ? $academicYear : null;
        }
        return AcademicYear::where('name', $academicYear)->value('id');
    }

    private function convertStatus($status)
    {
        // Convert string status to integer
        if (is_string($status)) {
            return match (strtolower($status)) {
                'active' => 1,
                'inactive', 'day_off' => 0,
                'activity' => 2,
                'test' => 3,
                'final exam' => 4,
                default => 1,
            };
        }

        // If already numeric, ensure it's within valid range
        if (is_numeric($status)) {
            $status = (int)$status;
            return in_array($status, [0, 1, 2, 3, 4]) ? $status : 1;
        }

        return 1; // default status
    }

    private function sanitizeNumericValue($value, $default = 1)
    {
        if (is_numeric($value)) {
            return (int)$value;
        }
        return $default;
    }
}



