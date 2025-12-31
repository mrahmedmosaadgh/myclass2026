<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TeacherImportService;
use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TeacherImportController extends Controller
{
    protected TeacherImportService $teacherImportService;

    public function __construct(TeacherImportService $teacherImportService)
    {
        $this->teacherImportService = $teacherImportService;
    }

    /**
     * Display the teacher import page
     */
    public function index()
    {
        return Inertia::render('Admin/Teachers/Import', [
            'schools' => $this->getSchools(),
        ]);
    }

    /**
     * Get available schools for current user
     */
    public function getSchools()
    {
        // For now, return all schools - this can be filtered based on user permissions
        return School::select('id', 'name')->orderBy('name')->get();
    }

    /**
     * Get active academic year for selected school
     */
    public function getActiveAcademicYear(Request $request, $schoolId)
    {
        $academicYear = AcademicYear::where('school_id', $schoolId)
            ->where('active', true)
            ->first();

        if (!$academicYear) {
            return response()->json([
                'error' => 'No active academic year found for this school. Please create an active academic year first.'
            ], 404);
        }

        return response()->json([
            'academic_year' => $academicYear
        ]);
    }

    /**
     * Validate Excel import data
     */
    public function validateImport(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'data' => 'required|array',
            'data.*' => 'array'
        ]);

        $validation = $this->teacherImportService->validateImportData(
            $request->input('data'),
            $request->input('school_id'),
            $request->input('academic_year_id')
        );

        return response()->json($validation);
    }

    /**
     * Process the teacher import
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'sync_mode' => 'required|in:update_existing,full_sync',
            'data' => 'required|array',
            'data.*' => 'array'
        ]);

        // Validate data first
        $validation = $this->teacherImportService->validateImportData(
            $request->input('data'),
            $request->input('school_id'),
            $request->input('academic_year_id')
        );

        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'errors' => $validation['errors']
            ], 422);
        }

        // Process the import
        $results = $this->teacherImportService->processImport(
            $request->input('data'),
            $request->input('school_id'),
            $request->input('academic_year_id'),
            $request->input('sync_mode')
        );

        return response()->json($this->teacherImportService->generateImportReport($results));
    }
}