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
            // Positive Behaviors
            ['name' => 'Brought the book', 'type' => 'positive', 'points' => 5],
            ['name' => 'Did the homework', 'type' => 'positive', 'points' => 5],
            ['name' => 'Helped a classmate', 'type' => 'positive', 'points' => 3],
            ['name' => 'Answered actively', 'type' => 'positive', 'points' => 4],
            ['name' => 'Participated in discussion', 'type' => 'positive', 'points' => 3],

            // Negative Behaviors
            ['name' => 'Sneaked information', 'type' => 'negative', 'points' => -3],
            ['name' => 'Disturbed the class', 'type' => 'negative', 'points' => -4],
            ['name' => 'Forgot the book', 'type' => 'negative', 'points' => -3],
            ['name' => 'Did not do homework', 'type' => 'negative', 'points' => -3],
            ['name' => 'Speak with no permission', 'type' => 'negative', 'points' => -3],
            ['name' => 'Eat with no permission', 'type' => 'negative', 'points' => -3],
            ['name' => 'Sleep', 'type' => 'negative', 'points' => -3],
        ];

        foreach ($behaviors as $behavior) {
            Behavior::updateOrCreate(
                [
                    'school_id' => $schoolId,
                    'year_id' => $yearId,
                    'name' => $behavior['name'],
                ],
                [
                    'type' => $behavior['type'],
                    'points' => $behavior['points'],
                    'is_active' => true,
                ]
            );
        }
    }
}
