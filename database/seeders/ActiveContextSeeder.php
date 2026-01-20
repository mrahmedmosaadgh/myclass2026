<?php

namespace Database\Seeders;

use App\Models\ActiveContext;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\ScheduleCopy;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActiveContextSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Example of how to use the updateOrCreate pattern as mentioned in the requirement
        $schools = School::all();
        
        foreach ($schools as $school) {
            // Get the active academic year for this school
            $academicYear = $school->academic_years()->where('active', true)->first();
            
            if ($academicYear) {
                // Get the active semester for this academic year
                $semester = $academicYear->semesters()->where('active', true)->first();
                
                if ($semester) {
                    // Create or update the active context for this school
                    ActiveContext::updateOrCreate(
                        ['school_id' => $school->id],
                        [
                            'academic_year_id' => $academicYear->id,
                            'semester_id' => $semester->id,
                            'week_number' => 1, // Default week number
                            // 'schedule_copy_id' => null, // Initially no schedule copy
                            'resolved_by' => null,
                            'resolved_at' => null,
                            'locked' => false,
                        ]
                    );
                }
            }
        }
    }
}