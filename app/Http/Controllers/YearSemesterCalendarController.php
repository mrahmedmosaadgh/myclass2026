<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Calendar;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class YearSemesterCalendarController extends Controller
{
    public function index()
    {
    //   return  $schoolId =  auth()->user() ;
         $schoolId = auth()->user()->schoolId();
    //   return  $schoolId = auth()->user()->schoolIdRole();

        $academicYears = AcademicYear::with(['semesters'])
            ->where('school_id', $schoolId)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($year) {
                $year->semesters->map(function ($semester) {
                    $semester->calendar_count = Calendar::where('semester_id', $semester->id)->count();
                    $semester->calculated_days = $semester->start_date && $semester->end_date 
                        ? Carbon::parse($semester->start_date)->diffInDays(Carbon::parse($semester->end_date)) + 1 
                        : 0;
                    $semester->calculated_weeks = $semester->calculated_days > 0 
                        ? ceil($semester->calculated_days / 7) 
                        : 0;
                    return $semester;
                });
                return $year;
            });

        return Inertia::render('my_class/admin/year_semester_calendar/Index', [
            'academicYears' => $academicYears,
        ]);
    }

    public function storeYear(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'name' => 'nullable|string|max:255|unique:academic_years,name',
        ]);

        $user = auth()->user();
        $schoolId = $user->schoolId();

        if (!$schoolId) {
            return back()->withErrors(['school' => 'You must be assigned to a school before creating academic years. Please contact your administrator.']);
        }

        $startDate = Carbon::parse($request->start_date);
        $name = $request->name ?: $startDate->year . '-' . ($startDate->year + 1);

        if (AcademicYear::where('name', $name)->where('school_id', $schoolId)->exists()) {
            return back()->withErrors(['name' => 'The academic year name has already been taken.']);
        }

        AcademicYear::create([
            'name'       => $name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'school_id'  => $schoolId,
            'active'     => true,
        ]);

        return redirect()->back()->with('success',
            'Academic Year created! Now use AI Setup or add semesters manually to define your schedule.');
    }

    public function updateSemester(Request $request, Semester $semester)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'total_weeks' => 'nullable|integer|min:1',
            'active' => 'boolean',
        ]);

        if ($request->filled('total_weeks') && !$request->filled('end_date')) {
            $validated['end_date'] = Carbon::parse($request->start_date)
                ->addWeeks($request->total_weeks)
                ->subDay()
                ->format('Y-m-d');
        }

        if ($validated['active']) {
            // Deactivate other semesters for this academic year
            Semester::where('academic_year_id', $semester->academic_year_id)
                ->where('id', '!=', $semester->id)
                ->update(['active' => false]);
        }

        $semester->update($validated);

        return redirect()->back()->with('success', 'Semester updated successfully.');
    }

    public function generateCalendar(Semester $semester)
    {
        if (!$semester->start_date || !$semester->end_date) {
            return back()->withErrors(['calendar' => 'Semester start and end dates are required.']);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($semester, &$created, &$updated, &$skipped) {
            $currentDate = Carbon::parse($semester->start_date);
            $endDate = Carbon::parse($semester->end_date);
            $weekNumber = 1;

            while ($currentDate <= $endDate) {
                $dayOfWeek = $currentDate->dayOfWeek; // 0 (Sun) to 6 (Sat)
                $dayNumber = $currentDate->dayOfWeekIso; // 1 (Mon) to 7 (Sun)

                $calendarData = [
                    'semester_id' => $semester->id,
                    'academic_year_id' => $semester->academic_year_id,
                    'status' => in_array($dayOfWeek, [5, 6]) ? 0 : 1, // 0 for Friday/Saturday by default
                    'week_number' => $weekNumber,
                    'day_number' => $dayNumber,
                ];

                // Check if record exists
                $existing = Calendar::where('date', $currentDate->format('Y-m-d'))
                    ->where('school_id', $semester->school_id)
                    ->first();

                if ($existing) {
                    // Update if it's from a different semester or update needed
                    if ($existing->semester_id !== $semester->id) {
                        $existing->update($calendarData);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    // Create new record
                    Calendar::create(array_merge($calendarData, [
                        'date' => $currentDate->format('Y-m-d'),
                        'school_id' => $semester->school_id,
                    ]));
                    $created++;
                }

                if ($dayOfWeek == 6) { // End of week (Saturday)
                    $weekNumber++;
                }

                $currentDate->addDay();
            }
        });

        $message = "Calendar processed: {$created} created, {$updated} updated, {$skipped} skipped.";
        return redirect()->back()->with('success', $message);
    }

    public function getMissingDays(AcademicYear $year)
    {
        $yearStart = Carbon::parse($year->start_date);
        $yearEnd = Carbon::parse($year->end_date);
        
        $calendarDays = Calendar::where('academic_year_id', $year->id)
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $missingRanges = [];
        $currentDate = $yearStart->copy();
        $rangeStart = null;

        while ($currentDate <= $yearEnd) {
            $formatted = $currentDate->format('Y-m-d');
            if (!in_array($formatted, $calendarDays)) {
                if ($rangeStart === null) {
                    $rangeStart = $currentDate->copy();
                }
            } else {
                if ($rangeStart !== null) {
                    $missingRanges[] = $this->formatRange($rangeStart, $currentDate->copy()->subDay());
                    $rangeStart = null;
                }
            }
            $currentDate->addDay();
        }

        if ($rangeStart !== null) {
            $missingRanges[] = $this->formatRange($rangeStart, $yearEnd);
        }

        return response()->json($missingRanges);
    }

    private function formatRange($start, $end)
    {
        if ($start->equalTo($end)) {
            return $start->format('Y-m-d');
        }
        return $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d');
    }

    public function toggleYearActive(AcademicYear $year)
    {
        $user = auth()->user();
        $schoolId = $user->schoolId();

        // Ensure year belongs to user's school
        if ($year->school_id !== $schoolId) {
            return back()->withErrors(['authorization' => 'You can only modify years for your school.']);
        }

        // If setting to active, deactivate others
        if (!$year->active) {
            AcademicYear::where('school_id', $schoolId)
                ->where('id', '!=', $year->id)
                ->update(['active' => false]);
        }

        $year->update(['active' => !$year->active]);

        return redirect()->back()->with('success', 'Academic year status updated.');
    }

    public function getSemesterEvents(Semester $semester)
    {
        $schoolId = auth()->user()->schoolId();
        if ($semester->school_id !== $schoolId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $events = Calendar::where('semester_id', $semester->id)
            ->where('status', '!=', 1) // Exclude normal work days
            ->orderBy('date')
            ->get(['id', 'date', 'status', 'day_number', 'data'])
            ->map(function ($cal) {
                $date = $cal->date instanceof \Carbon\Carbon ? $cal->date : \Carbon\Carbon::parse($cal->date);
                return [
                    'id'          => $cal->id,
                    'date'        => $date->format('Y-m-d'),
                    'day_of_week' => $date->dayOfWeek, // 0=Sun, 5=Fri, 6=Sat
                    'status'      => $cal->status,
                    'label'       => $cal->data['ai_event_name'] ?? $cal->data['ai_vacation_name'] ?? null,
                ];
            });

        return response()->json($events);
    }

    public function setActiveSemester(AcademicYear $year, Semester $semester)
    {
        $schoolId = auth()->user()->schoolId();
        if ($year->school_id !== $schoolId || $semester->academic_year_id !== $year->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Deactivate all semesters for this year
        Semester::where('academic_year_id', $year->id)->update(['active' => false]);

        // Activate the selected one
        $semester->update(['active' => true]);

        return redirect()->back()->with('success', "{$semester->name} is now the active semester.");
    }

    public function getCalendarData(AcademicYear $year)
    {
        $calendars = Calendar::with('semester')
            ->where('academic_year_id', $year->id)
            ->orderBy('date')
            ->get();

        return response()->json($calendars);
    }

    public function bulkUpdateCalendar(Request $request)
    {
        $request->validate([
            'calendar_ids' => 'required|array',
            'calendar_ids.*' => 'exists:calendars,id',
            'status' => 'nullable|integer|between:0,4',
            'event' => 'nullable|string|max:255',
            'clear_events' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        $schoolId = $user->schoolId();

        $updateData = [];
        if ($request->filled('status')) {
            $updateData['status'] = $request->status;
        }
        if ($request->filled('event')) {
            $updateData['event'] = $request->event;
        }
        if ($request->clear_events) {
            $updateData['event'] = null;
        }

        $updated = Calendar::whereIn('id', $request->calendar_ids)
            ->where('school_id', $schoolId)
            ->update($updateData);

        return redirect()->back()->with('success', "Updated {$updated} calendar records.");
    }

    public function getCalendarStats(AcademicYear $year)
    {
        $user = auth()->user();
        $schoolId = $user->schoolId();

        // Ensure year belongs to user's school
        if ($year->school_id !== $schoolId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_days' => Calendar::where('academic_year_id', $year->id)->count(),
            'work_days' => Calendar::where('academic_year_id', $year->id)->where('status', 1)->count(),
            'holidays' => Calendar::where('academic_year_id', $year->id)->where('status', 0)->count(),
            'activity_days' => Calendar::where('academic_year_id', $year->id)->where('status', 2)->count(),
            'test_days' => Calendar::where('academic_year_id', $year->id)->where('status', 3)->count(),
            'final_exam_days' => Calendar::where('academic_year_id', $year->id)->where('status', 4)->count(),
            'events_count' => Calendar::where('academic_year_id', $year->id)->whereNotNull('event')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Auto-generate a full year of calendar records starting from July 1.
     * Returns ['skipped', existingCount] if records already exist, or ['generated', 0] on success.
     */
    private function autoGenerateFullCalendar(AcademicYear $year): array
    {
        $year->load('semesters');

        // Check for existing records — do NOT override
        $existingCount = Calendar::where('academic_year_id', $year->id)->count();
        if ($existingCount > 0) {
            return ['skipped', $existingCount];
        }

        // Reload semesters to ensure we have the 4 automatically created by the AcademicYear model event
        $year->load('semesters');
        $defaultSemester = $year->semesters->sortBy('semester_number')->first();

        $start      = Carbon::parse($year->start_date)->startOfDay();
        $end        = Carbon::parse($year->end_date)->endOfDay();
        $current    = $start->copy();
        $weekNumber = 1;
        $records    = [];
        $now        = now()->toDateTimeString();

        while ($current <= $end) {
            $dayOfWeek = $current->dayOfWeek;    // 0=Sun … 6=Sat
            $dayNumber = $current->dayOfWeekIso; // 1=Mon … 7=Sun
            $status    = in_array($dayOfWeek, [5, 6]) ? 0 : 1; // Fri/Sat = Day Off

            // Map to semester by date range
            $semester = $year->semesters->first(function ($s) use ($current) {
                if (!$s->start_date || !$s->end_date) return false;
                return $current->between(
                    Carbon::parse($s->start_date)->startOfDay(),
                    Carbon::parse($s->end_date)->endOfDay()
                );
            });

            // Fallback to the first automatically created semester
            $semesterId = $semester ? $semester->id : $defaultSemester?->id;

            $records[] = [
                'date'             => $current->format('Y-m-d'),
                'school_id'        => $year->school_id,
                'academic_year_id' => $year->id,
                'semester_id'      => $semesterId,
                'status'           => $status,
                'week_number'      => $weekNumber,
                'day_number'       => $dayNumber,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            if ($dayOfWeek === 6) { // End of Saturday = new week
                $weekNumber++;
            }
            $current->addDay();
        }

        // Bulk insert in chunks to avoid memory issues on large datasets
        collect($records)->chunk(500)->each(fn($chunk) => Calendar::insert($chunk->all()));

        return ['generated', 0];
    }

    /**
     * Apply AI Setup to Academic Year Semesters and Calendars.
     * Takes the AI JSON payload, updates semesters, vacations, and events.
     */
    public function applyAISemesterSetup(Request $request, AcademicYear $year)
    {
        $validated = $request->validate([
            'mode'     => 'nullable|in:update,replace',
            'semesters' => 'required|array',
            'semesters.*.number' => 'required|integer|min:1|max:4',
            'semesters.*.name' => 'required|string|max:255',
            'semesters.*.start_date' => 'required|date',
            'semesters.*.end_date' => 'required|date|after_or_equal:semesters.*.start_date',
            'semesters.*.vacations' => 'nullable|array',
            'semesters.*.vacations.*.name' => 'required_with:semesters.*.vacations|string|max:255',
            'semesters.*.vacations.*.start_date' => 'required_with:semesters.*.vacations|date',
            'semesters.*.vacations.*.end_date' => 'required_with:semesters.*.vacations|date|after_or_equal:semesters.*.vacations.*.start_date',
            'semesters.*.events' => 'nullable|array',
            'semesters.*.events.*.name' => 'required_with:semesters.*.events|string|max:255',
            'semesters.*.events.*.date' => 'required_with:semesters.*.events|date',
            'semesters.*.events.*.type' => 'required_with:semesters.*.events|string|in:activity,test,exam,holiday'
        ]);

        $mode = $request->input('mode', 'update');

        DB::beginTransaction();
        try {
            // REPLACE MODE: wipe all existing data first
            if ($mode === 'replace') {
                Calendar::withTrashed()->where('academic_year_id', $year->id)->forceDelete();
                Semester::withTrashed()->where('academic_year_id', $year->id)->forceDelete();
            }

            // ── PASS 1: Create / update semesters & collect event data ────────────
            $pendingVacations = [];
            $pendingEvents    = [];

            foreach ($validated['semesters'] as $semData) {
                $semester = Semester::withTrashed()->firstOrNew([
                    'academic_year_id' => $year->id,
                    'semester_number'  => $semData['number'],
                ]);
                if ($semester->trashed()) {
                    $semester->restore();
                }
                $semester->fill([
                    'name'       => $semData['name'],
                    'start_date' => $semData['start_date'],
                    'end_date'   => $semData['end_date'],
                    'school_id'  => $year->school_id,
                ])->save();

                // Collect vacations & events keyed by semester id (for later pass)
                if (!empty($semData['vacations'])) {
                    foreach ($semData['vacations'] as $v) {
                        $pendingVacations[] = [
                            'semester_id' => $semester->id,
                            'start_date'  => $v['start_date'],
                            'end_date'    => $v['end_date'],
                            'name'        => $v['name'],
                        ];
                    }
                }
                if (!empty($semData['events'])) {
                    foreach ($semData['events'] as $e) {
                        $pendingEvents[] = [
                            'semester_id' => $semester->id,
                            'date'        => $e['date'],
                            'type'        => $e['type'],
                            'name'        => $e['name'],
                        ];
                    }
                }
            }

            // ── PASS 2: Generate the FULL YEAR calendar ────────────────────────────
            $year->load('semesters');
            $semesters = $year->semesters->filter(fn($s) => $s->start_date && $s->end_date)->values();

            $yearStart  = Carbon::parse($year->start_date);
            $yearEnd    = Carbon::parse($year->end_date);
            $weekNumber = 1;
            $records    = [];
            $current    = $yearStart->copy();

            while ($current <= $yearEnd) {
                $dow   = $current->dayOfWeek; // 0=Sun … 6=Sat
                $semId = null;
                foreach ($semesters as $s) {
                    if ($current->between(Carbon::parse($s->start_date), Carbon::parse($s->end_date))) {
                        $semId = $s->id;
                        break;
                    }
                }

                $records[] = [
                    'date'             => $current->format('Y-m-d'),
                    'semester_id'      => $semId,
                    'academic_year_id' => $year->id,
                    'school_id'        => $year->school_id,
                    'status'           => in_array($dow, [5, 6]) ? 0 : 1, // Fri/Sat = day off
                    'week_number'      => $weekNumber,
                    'day_number'       => $current->dayOfWeekIso,
                    'data'             => null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];

                if ($dow === 6) $weekNumber++;
                $current->addDay();
            }

            if ($mode === 'replace') {
                collect($records)->chunk(500)->each(fn($chunk) => Calendar::insert($chunk->all()));
            } else {
                collect($records)->chunk(500)->each(function ($chunk) {
                    Calendar::upsert(
                        $chunk->toArray(),
                        ['date', 'school_id'],
                        ['semester_id', 'status', 'week_number', 'day_number', 'updated_at']
                    );
                });
            }

            // ── PASS 3: Apply vacations & events (AFTER calendar rows exist) ───────
            foreach ($pendingVacations as $v) {
                Calendar::where('academic_year_id', $year->id)
                    ->whereBetween('date', [$v['start_date'], $v['end_date']])
                    ->update([
                        'status'      => 0,
                        'semester_id' => $v['semester_id'],
                        'data'        => json_encode(['ai_vacation_name' => $v['name']]),
                        'updated_at'  => now(),
                    ]);
            }

            foreach ($pendingEvents as $e) {
                $statusId = match (strtolower($e['type'])) {
                    'activity' => 2,
                    'test'     => 3,
                    'exam'     => 4,
                    'holiday'  => 0,
                    default    => 1,
                };
                Calendar::where('academic_year_id', $year->id)
                    ->where('date', $e['date'])
                    ->update([
                        'status'      => $statusId,
                        'semester_id' => $e['semester_id'],
                        'data'        => json_encode(['ai_event_name' => $e['name'], 'event_type' => $e['type']]),
                        'updated_at'  => now(),
                    ]);
            }

            DB::commit();
            return response()->json(['message' => 'AI Configuration applied successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AI Semester Setup failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to apply AI configuration: ' . $e->getMessage()], 500);
        }
    }
}
