<?php

namespace App\Services;

use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\ScheduleCopy;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserContextService
{
    /**
     * Resolve the active context for the current user
     */
    public function resolveContext(?User $user = null): array
    {
        $user = $user ?: Auth::user();
        
        if (!$user) {
            return [];
        }

        // Resolve school context
        $school = $this->resolveSchool($user);
        if (!$school) {
            return [];
        }

        // Resolve academic year context
        $academicYear = $this->resolveAcademicYear($school);
        if (!$academicYear) {
            return ['school_id' => $school->id];
        }

        // Resolve semester context
        $semester = $this->resolveSemester($academicYear);
        if (!$semester) {
            return [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id
            ];
        }

        // Resolve schedule copy context
        $scheduleCopy = $this->resolveScheduleCopy($school, $academicYear, $semester);

        return [
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'schedule_copy_id' => $scheduleCopy?->id,
        ];
    }

    /**
     * Update or create the active context for all schools
     */
    public function updateAllSchoolsContext(): array
    {
        $schools = School::all();
        $results = [];

        foreach ($schools as $school) {
            $context = $this->resolveContextForSchool($school);
            $results[$school->id] = $context;
        }

        return $results;
    }

    /**
     * Resolve context for a specific school
     */
    private function resolveContextForSchool(School $school): array
    {
        // Resolve academic year context
        $academicYear = $this->resolveAcademicYear($school);
        if (!$academicYear) {
            return ['school_id' => $school->id];
        }

        // Resolve semester context
        $semester = $this->resolveSemester($academicYear);
        if (!$semester) {
            return [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id
            ];
        }

        // Resolve schedule copy context
        $scheduleCopy = $this->resolveScheduleCopy($school, $academicYear, $semester);

        return [
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'schedule_copy_id' => $scheduleCopy?->id,
        ];
    }

    /**
     * Resolve school context for a user
     */
    private function resolveSchool(User $user): ?School
    {
        // If user has a specific school assigned, use that
        if ($user->school_id) {
            return School::find($user->school_id);
        }

        // If user has only one school, use that
        $schools = $user->schools; // Assuming a relationship exists
        if ($schools && $schools->count() === 1) {
            return $schools->first();
        }

        // Fallback to first school if any
        return School::first();
    }

    /**
     * Resolve academic year for a school
     */
    private function resolveAcademicYear(School $school): ?AcademicYear
    {
        // Prefer active academic year
        $academicYear = $school->academic_years()->where('active', true)->first();
        if ($academicYear) {
            return $academicYear;
        }

        // Fallback to most recent
        return $school->academic_years()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Resolve semester for an academic year
     */
    private function resolveSemester(AcademicYear $academicYear): ?Semester
    {
        // Prefer active semester
        $semester = $academicYear->semesters()->where('active', true)->first();
        if ($semester) {
            return $semester;
        }

        // Fallback to semester 1
        $semester = $academicYear->semesters()->where('semester_number', 1)->first();
        if ($semester) {
            return $semester;
        }

        // Fallback to most recent
        return $academicYear->semesters()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Resolve schedule copy for a school, academic year, and semester
     */
    private function resolveScheduleCopy(School $school, AcademicYear $academicYear, Semester $semester): ?ScheduleCopy
    {
        // Prefer active schedule copy
        $scheduleCopy = $school->scheduleCopies()
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_id', $semester->id)
            ->where('active', true)
            ->where('status', 'active')
            ->first();
            
        if ($scheduleCopy) {
            return $scheduleCopy;
        }

        // Fallback to most recent schedule copy for this school/year/semester
        return $school->scheduleCopies()
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_id', $semester->id)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}