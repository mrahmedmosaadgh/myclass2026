<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SkillDemoSeeder extends Seeder
{
    public function run()
    {
        // Create demo skill categories
        $categories = [
            [
                'name' => 'Mathematics Fundamentals',
                'grade_id' => 1,
                'subject_id' => 1, // Assuming subject_id 1 is Mathematics
                'icon' => '🔢',
                'display_order' => 1,
            ],
            [
                'name' => 'Algebra Basics',
                'grade_id' => 2,
                'subject_id' => 1,
                'icon' => '📊',
                'display_order' => 2,
            ],
            [
                'name' => 'Geometry Concepts',
                'grade_id' => 2,
                'subject_id' => 1,
                'icon' => '📐',
                'display_order' => 3,
            ],
            [
                'name' => 'Science Principles',
                'grade_id' => 1,
                'subject_id' => 2, // Assuming subject_id 2 is Science
                'icon' => '🔬',
                'display_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            $categoryId = DB::table('skill_categories')->insertGetId($category);
            
            // Create skills for each category
            $this->createSkillsForCategory($categoryId, $category['name']);
        }
    }

    private function createSkillsForCategory($categoryId, $categoryName)
    {
        // Determine the type of skills based on category name
        if (strpos(strtolower($categoryName), 'math') !== false) {
            $this->createMathSkills($categoryId);
        } elseif (strpos(strtolower($categoryName), 'algebra') !== false) {
            $this->createAlgebraSkills($categoryId);
        } elseif (strpos(strtolower($categoryName), 'geometry') !== false) {
            $this->createGeometrySkills($categoryId);
        } elseif (strpos(strtolower($categoryName), 'science') !== false) {
            $this->createScienceSkills($categoryId);
        }
    }

    private function createMathSkills($categoryId)
    {
        $skills = [
            [
                'name' => 'Addition Fundamentals',
                'category_id' => $categoryId,
                'description' => 'Master basic addition concepts and techniques',
                'difficulty_min' => 1,
                'difficulty_max' => 3,
                'mastery_threshold' => 80,
                'estimated_time_minutes' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Subtraction Techniques',
                'category_id' => $categoryId,
                'description' => 'Learn subtraction with whole numbers and decimals',
                'difficulty_min' => 1,
                'difficulty_max' => 3,
                'mastery_threshold' => 80,
                'estimated_time_minutes' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Multiplication Tables',
                'category_id' => $categoryId,
                'description' => 'Memorize and apply multiplication facts',
                'difficulty_min' => 1,
                'difficulty_max' => 4,
                'mastery_threshold' => 80,
                'estimated_time_minutes' => 20,
                'is_active' => true,
            ],
        ];

        foreach ($skills as $skill) {
            $skillId = DB::table('skills')->insertGetId($skill);
            
            // Link to existing questions (we'll use sample IDs)
            $this->linkQuestionsToSkill($skillId);
        }
    }

    private function createAlgebraSkills($categoryId)
    {
        $skills = [
            [
                'name' => 'Linear Equations',
                'category_id' => $categoryId,
                'description' => 'Solve linear equations with one variable',
                'difficulty_min' => 3,
                'difficulty_max' => 6,
                'mastery_threshold' => 80,
                'estimated_time_minutes' => 25,
                'is_active' => true,
            ],
            [
                'name' => 'Factoring Polynomials',
                'category_id' => $categoryId,
                'description' => 'Factor quadratic and higher degree polynomials',
                'difficulty_min' => 5,
                'difficulty_max' => 8,
                'mastery_threshold' => 80,
                'estimated_time_minutes' => 30,
                'is_active' => true,
            ],
        ];

        foreach ($skills as $skill) {
            $skillId = DB::table('skills')->insertGetId($skill);
            $this->linkQuestionsToSkill($skillId);
        }
    }

    private function createGeometrySkills($categoryId)
    {
        $skills = [
            [
                'name' => 'Properties of Triangles',
                'category_id' => $categoryId,
                'description' => 'Understand triangle classifications and properties',
                'difficulty_min' => 4,
                'difficulty_max' => 7,
                'mastery_threshold' => 80,
                'estimated_time_minutes' => 25,
                'is_active' => true,
            ],
            [
                'name' => 'Area Calculations',
                'category_id' => $categoryId,
                'description' => 'Calculate areas of various geometric shapes',
                'difficulty_min' => 3,
                'difficulty_max' => 6,
                'mastery_threshold' => 80,
                'estimated_time_minutes' => 20,
                'is_active' => true,
            ],
        ];

        foreach ($skills as $skill) {
            $skillId = DB::table('skills')->insertGetId($skill);
            $this->linkQuestionsToSkill($skillId);
        }
    }

    private function createScienceSkills($categoryId)
    {
        $skills = [
            [
                'name' => 'Scientific Method',
                'category_id' => $categoryId,
                'description' => 'Apply the scientific method to experiments',
                'difficulty_min' => 2,
                'difficulty_max' => 5,
                'mastery_threshold' => 80,
                'estimated_time_minutes' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'States of Matter',
                'category_id' => $categoryId,
                'description' => 'Understand solid, liquid, and gas states',
                'difficulty_min' => 2,
                'difficulty_max' => 4,
                'mastery_threshold' => 80,
                'estimated_time_minutes' => 15,
                'is_active' => true,
            ],
        ];

        foreach ($skills as $skill) {
            $skillId = DB::table('skills')->insertGetId($skill);
            $this->linkQuestionsToSkill($skillId);
        }
    }

    private function linkQuestionsToSkill($skillId)
    {
        // Get some sample questions from the qu_questions table
        // We'll try to get at least 3 questions per skill
        $existingQuestions = DB::table('qu_questions')
            ->select('id')
            ->where('id', '<=', 20) // Limit to first 20 questions as samples
            ->inRandomOrder()
            ->limit(5)
            ->get();

        foreach ($existingQuestions as $index => $question) {
            // Assign different difficulty levels to different questions
            $difficulty = 1 + ($index % 5); // Will cycle through 1-5
            
            DB::table('skill_questions')->insert([
                'skill_id' => $skillId,
                'qu_question_id' => $question->id,
                'difficulty_level' => $difficulty,
                'explanation' => 'This question helps develop skills in this area.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}