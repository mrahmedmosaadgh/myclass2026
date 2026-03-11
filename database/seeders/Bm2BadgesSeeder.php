<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Bm2BadgesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            // Achievement Badges
            [
                'name' => 'First Steps',
                'description' => 'Complete your first assessment',
                'category' => 'achievement',
                'earning_criteria' => json_encode(['type' => 'assessment_complete', 'value' => 1]),
                'points_value' => 10,
                'rarity' => 'common',
                'display_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Math Wizard',
                'description' => 'Score 100% on any assessment',
                'category' => 'achievement',
                'earning_criteria' => json_encode(['type' => 'score_threshold', 'value' => 100, 'context' => 'single_assessment']),
                'points_value' => 50,
                'rarity' => 'epic',
                'display_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Milestone Badges
            [
                'name' => 'Dedicated Learner',
                'description' => 'Complete 5 assessments',
                'category' => 'milestone',
                'earning_criteria' => json_encode(['type' => 'assessment_count', 'value' => 5]),
                'points_value' => 25,
                'rarity' => 'uncommon',
                'display_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Century Club',
                'description' => 'Complete 100 assessments',
                'category' => 'milestone',
                'earning_criteria' => json_encode(['type' => 'assessment_count', 'value' => 100]),
                'points_value' => 100,
                'rarity' => 'legendary',
                'display_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Skill Mastery Badges
            [
                'name' => 'Addition Ace',
                'description' => 'Master addition with 90%+ accuracy',
                'category' => 'skill_mastery',
                'earning_criteria' => json_encode(['type' => 'skill_score', 'skill' => 'addition', 'value' => 90]),
                'points_value' => 30,
                'rarity' => 'rare',
                'display_order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Subtraction Star',
                'description' => 'Master subtraction with 90%+ accuracy',
                'category' => 'skill_mastery',
                'earning_criteria' => json_encode(['type' => 'skill_score', 'skill' => 'subtraction', 'value' => 90]),
                'points_value' => 30,
                'rarity' => 'rare',
                'display_order' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Number Sense Ninja',
                'description' => 'Master number sense with 90%+ accuracy',
                'category' => 'skill_mastery',
                'earning_criteria' => json_encode(['type' => 'skill_score', 'skill' => 'number_sense', 'value' => 90]),
                'points_value' => 30,
                'rarity' => 'rare',
                'display_order' => 7,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Speed Badges
            [
                'name' => 'Speed Demon',
                'description' => 'Complete an assessment in under 10 minutes with 85%+ accuracy',
                'category' => 'speed',
                'earning_criteria' => json_encode(['type' => 'speed_completion', 'time_seconds' => 600, 'min_accuracy' => 85]),
                'points_value' => 40,
                'rarity' => 'epic',
                'display_order' => 8,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Consistency Badges
            [
                'name' => 'On Fire!',
                'description' => 'Practice for 7 days in a row',
                'category' => 'consistency',
                'earning_criteria' => json_encode(['type' => 'streak', 'value' => 7, 'context' => 'consecutive_days']),
                'points_value' => 35,
                'rarity' => 'rare',
                'display_order' => 9,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Unstoppable',
                'description' => 'Practice for 30 days in a row',
                'category' => 'consistency',
                'earning_criteria' => json_encode(['type' => 'streak', 'value' => 30, 'context' => 'consecutive_days']),
                'points_value' => 75,
                'rarity' => 'legendary',
                'display_order' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('bm2_badges')->insert($badges);
    }
}
