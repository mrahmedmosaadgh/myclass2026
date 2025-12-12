<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Calendar;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::with('school')
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('academic-years.index', compact('academicYears'));
    }

    public function create()
    {
        return view('academic-years.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'active' => 'boolean'
        ]);

        $validated['school_id'] = auth()->user()->school_id;

        AcademicYear::create($validated);

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic year created successfully.');
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'active' => 'boolean'
        ]);

        $academicYear->update($validated);

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic year updated successfully.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic year deleted successfully.');
    }

    public function export()
    {
        return response()->download(storage_path('app/academic_years.xlsx'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        // Import logic here

        return redirect()->back()->with('success', 'Academic Years imported successfully');
    }

    /**
     * Get academic years for API (used by Weekly Plans)
     */
    public function apiIndex()
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        $teacher = Teacher::where('user_id', $user->id)->first();
        
        if (!$teacher) {
            return response()->json(['error' => 'User is not a teacher'], 400);
        }
        
        if (!$teacher->school_id) {
            return response()->json(['error' => 'Teacher has no school assigned'], 400);
        }

        $academicYears = AcademicYear::where('school_id', $teacher->school_id)
            ->orderBy('start_date', 'desc')
            ->get(['id', 'name', 'start_date', 'end_date', 'active']);

        return response()->json($academicYears);
    }

    public function generateCalendar(AcademicYear $academicYear)
    {
        try {
            // Get the school's calendar settings
            $school = $academicYear->school;

            // Set default dates if not provided
            if (!$academicYear->start_date || !$academicYear->end_date) {
                $currentYear = now()->year;
                $academicYear->start_date = Carbon::create($currentYear, 7, 1);
                $academicYear->end_date = Carbon::create($currentYear + 1, 7, 1);
                $academicYear->save();
            }

            // Generate calendar entries for the full year
            $startDate = Carbon::parse($academicYear->start_date);
            $endDate = Carbon::parse($academicYear->end_date);

            $calendars = [];
            $currentDate = $startDate->copy();
            $weekNumber = 1;
            $semesterNumber = 1;
            $skipped = 0;
            $created = 0;

            while ($currentDate <= $endDate) {
                // Check if entry already exists
                $exists = Calendar::where('date', $currentDate->format('Y-m-d'))
                    ->where('school_id', $school->id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $currentDate->addDay();
                    continue;
                }

                // Set vacation status for Friday (5) and Saturday (6)
                $isVacation = in_array($currentDate->dayOfWeek, [5, 6]);

                $calendars[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'week' => $weekNumber,
                    'day' => $currentDate->format('l'),
                    'day_number' => $currentDate->dayOfWeek === 0 ? 1 : $currentDate->dayOfWeek + 1, // Convert to 1-7 format where 1 is Sunday
                    'week_number' => $weekNumber,
                    'semester_number' => $semesterNumber,
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'status' => $isVacation ? 0 : 1, // 0 for vacation days, 1 for active days
                    'vacation' => $isVacation
                ];

                $created++;

                // Update counters
                if ($currentDate->dayOfWeek === 5) { // If Friday
                    $weekNumber++;
                    // Optional: Update semester number based on your rules
                    if ($weekNumber > 20) {
                        $semesterNumber = 2;
                    }
                }

                $currentDate->addDay();
            }

            if (!empty($calendars)) {
                Calendar::insert($calendars);
            }

            return response()->json([
                'message' => 'Calendar generated successfully',
                'statistics' => [
                    'created' => $created,
                    'skipped' => $skipped,
                    'total' => $created + $skipped
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate calendar: ' . $e->getMessage()
            ], 500);
        }
    }
}






