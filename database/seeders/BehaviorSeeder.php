<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Behavior;

class BehaviorSeeder extends Seeder
{
    public function run(): void
    {
        $schoolId = 1; // you can make this dynamic later
        $yearId = 2;

        $behaviors = [
            ['name' => 'Helping Others', 'points' => 1, 'category' => 'Social'],
            ['name' => 'On Task', 'points' => 1, 'category' => 'Academic'],
            ['name' => 'Participating', 'points' => 2, 'category' => 'Academic'],
            ['name' => 'Persistence', 'points' => 1, 'category' => 'Social-Emotional'],
            ['name' => 'Teamwork', 'points' => 2, 'category' => 'Social'],
            ['name' => 'Clean Workspace', 'points' => 1, 'category' => 'Organizational'],
            ['name' => 'Creative Thinking', 'points' => 3, 'category' => 'Academic'],
            ['name' => 'Prayer Etiquette', 'points' => 1, 'category' => 'Character'],
        ];

        $schools = School::with('academic_years')->get();

        foreach ($schools as $school) {
            foreach ($school->academic_years as $academicYear) {
                foreach ($behaviors as $behavior) {
                    Behavior::updateOrCreate(
                        [
                            'school_id' => $school->id,
                            'year_id' => $academicYear->id,
                            'name' => $behavior['name'],
                        ],
                        [
                            'type' => 'positive', // All provided points are positive
                            'points' => $behavior['points'],
                            'is_active' => true,
                            'description' => $behavior['category'], // Storing category in description as requested
                        ]
                    );
                }
            }
        }
    }
}
