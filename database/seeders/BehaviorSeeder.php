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
            ['name' => 'Helping Others', 'name_ar' => 'مساعدة الآخرين', 'points' => 1, 'category' => 'Social'],
            ['name' => 'On Task', 'name_ar' => 'التركيز على المهمة', 'points' => 1, 'category' => 'Academic'],
            ['name' => 'Participating', 'name_ar' => 'المشاركة', 'points' => 2, 'category' => 'Academic'],
            ['name' => 'Persistence', 'name_ar' => 'المثابرة', 'points' => 1, 'category' => 'Social-Emotional'],
            ['name' => 'Teamwork', 'name_ar' => 'العمل الجماعي', 'points' => 2, 'category' => 'Social'],
            ['name' => 'Clean Workspace', 'name_ar' => 'مكان عمل نظيف', 'points' => 1, 'category' => 'Organizational'],
            ['name' => 'Creative Thinking', 'name_ar' => 'التفكير الإبداعي', 'points' => 3, 'category' => 'Academic'],
            ['name' => 'Prayer Etiquette', 'name_ar' => 'آداب الصلاة', 'points' => 1, 'category' => 'Character'],
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
                            'name_ar' => $behavior['name_ar'], // Arabic name
                        ]
                    );
                }
            }
        }
    }
}
