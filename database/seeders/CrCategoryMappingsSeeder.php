<?php

namespace Database\Seeders;

use App\Models\CrCategoryMapping;
use Illuminate\Database\Seeder;

class CrCategoryMappingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default category mappings for classroom records
        // These are school-scoped, so we only seed for specific schools
        
        $defaultCategories = [
            [
                'key' => 'book_participation',
                'label' => 'Book & Participation',
                'type' => 'numeric',
                'max_value' => 5,
                'passing_value' => 3,
                'default_value' => 5,
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'key' => 'homework',
                'label' => 'Homework',
                'type' => 'numeric',
                'max_value' => 5,
                'passing_value' => 3,
                'default_value' => 5,
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'key' => 'behavior',
                'label' => 'Behavior',
                'type' => 'numeric',
                'max_value' => 5,
                'passing_value' => 3,
                'default_value' => 5,
                'sort_order' => 3,
                'active' => true,
            ],
        ];

        // Get the dev/test school (you can modify this to match your setup)
        // For now, we'll seed for school_id = 1 if it exists
        $schoolId = 1; // Change this to your dev school ID
        
        foreach ($defaultCategories as $category) {
            CrCategoryMapping::updateOrCreate(
                [
                    'school_id' => $schoolId,
                    'key' => $category['key'],
                ],
                $category
            );
        }

        $this->command->info("Seeded {$schoolId} with " . count($defaultCategories) . ' default CR category mappings.');
    }
}
