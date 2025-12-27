<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Calendar;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        // Validate that user has a school assigned
        if (!$schoolId) {
            return back()->withErrors(['school' => 'You must be assigned to a school before creating academic years. Please contact your administrator.']);
        }

        $startDate = Carbon::parse($request->start_date);
        $name = $request->name ?: $startDate->year . '-' . ($startDate->year + 1);

        // Check if name already exists
        if (AcademicYear::where('name', $name)->where('school_id', $schoolId)->exists()) {
            return back()->withErrors(['name' => 'The academic year name has already been taken.']);
        }

        AcademicYear::create([
            'name' => $name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'school_id' => $schoolId,
            'active' => true,
        ]);

        return redirect()->back()->with('success', 'Academic Year and 4 Semesters created successfully.');
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
                $dayNumber = $dayOfWeek + 1;

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

    public function getCalendarData(AcademicYear $year)
    {
        $calendars = Calendar::with('semester')
            ->where('academic_year_id', $year->id)
            ->orderBy('date')
            ->get();

        return response()->json($calendars);
    }
}
