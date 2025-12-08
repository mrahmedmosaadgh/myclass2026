<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseManagement\Course;
use App\Models\CourseManagement\CourseLevel;
use App\Models\CourseManagement\CourseSection;
use App\Models\CourseManagement\CourseLesson;
use App\Models\User;

class CourseManagementSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first user or create one
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Create sample courses
        $mathCourse = Course::create([
            'name' => 'Grade 5 Mathematics',
            'description' => 'Comprehensive mathematics curriculum for grade 5 students covering fractions, decimals, geometry, and basic algebra.',
            'created_by' => $user->id,
        ]);

        $scienceCourse = Course::create([
            'name' => 'Elementary Science',
            'description' => 'Introduction to basic scientific concepts including life science, physical science, and earth science.',
            'created_by' => $user->id,
        ]);

        // Create levels for Math course
        $fractionsLevel = CourseLevel::create([
            'title' => 'Fractions',
            'order' => 1,
            'course_id' => $mathCourse->id,
            'created_by' => $user->id,
        ]);

        $decimalsLevel = CourseLevel::create([
            'title' => 'Decimals',
            'order' => 2,
            'course_id' => $mathCourse->id,
            'created_by' => $user->id,
        ]);

        $geometryLevel = CourseLevel::create([
            'title' => 'Geometry',
            'order' => 3,
            'course_id' => $mathCourse->id,
            'created_by' => $user->id,
        ]);

        // Create sections for Fractions level
        $basicFractionsSection = CourseSection::create([
            'title' => 'Understanding Fractions',
            'order' => 1,
            'course_level_id' => $fractionsLevel->id,
            'created_by' => $user->id,
        ]);

        $addingFractionsSection = CourseSection::create([
            'title' => 'Adding Fractions',
            'order' => 2,
            'course_level_id' => $fractionsLevel->id,
            'created_by' => $user->id,
        ]);

        // Create lessons for Understanding Fractions section
        CourseLesson::create([
            'title' => 'What is a Fraction?',
            'text' => 'Learn the basic concept of fractions as parts of a whole.',
            'data' => [
                'duration' => 30,
                'difficulty' => 'beginner',
                'objectives' => ['Understand fraction notation', 'Identify numerator and denominator']
            ],
            'order' => 1,
            'course_section_id' => $basicFractionsSection->id,
            'created_by' => $user->id,
        ]);

        CourseLesson::create([
            'title' => 'Equivalent Fractions',
            'text' => 'Discover how different fractions can represent the same value.',
            'data' => [
                'duration' => 45,
                'difficulty' => 'beginner',
                'objectives' => ['Identify equivalent fractions', 'Create equivalent fractions']
            ],
            'order' => 2,
            'course_section_id' => $basicFractionsSection->id,
            'created_by' => $user->id,
        ]);

        CourseLesson::create([
            'title' => 'Comparing Fractions',
            'text' => 'Learn how to compare fractions with different denominators.',
            'data' => [
                'duration' => 40,
                'difficulty' => 'intermediate',
                'objectives' => ['Compare fractions', 'Order fractions from least to greatest']
            ],
            'order' => 3,
            'course_section_id' => $basicFractionsSection->id,
            'created_by' => $user->id,
        ]);

        // Create lessons for Adding Fractions section
        CourseLesson::create([
            'title' => 'Adding Like Fractions',
            'text' => 'Learn to add fractions with the same denominator.',
            'data' => [
                'duration' => 35,
                'difficulty' => 'beginner',
                'objectives' => ['Add fractions with like denominators', 'Simplify results']
            ],
            'order' => 1,
            'course_section_id' => $addingFractionsSection->id,
            'created_by' => $user->id,
        ]);

        CourseLesson::create([
            'title' => 'Adding Unlike Fractions',
            'text' => 'Master adding fractions with different denominators.',
            'data' => [
                'duration' => 50,
                'difficulty' => 'intermediate',
                'objectives' => ['Find common denominators', 'Add unlike fractions']
            ],
            'order' => 2,
            'course_section_id' => $addingFractionsSection->id,
            'created_by' => $user->id,
        ]);

        // Create levels for Science course
        $lifeScienceLevel = CourseLevel::create([
            'title' => 'Life Science',
            'order' => 1,
            'course_id' => $scienceCourse->id,
            'created_by' => $user->id,
        ]);

        // Create section for Life Science
        $plantsSection = CourseSection::create([
            'title' => 'Plants and Their Parts',
            'order' => 1,
            'course_level_id' => $lifeScienceLevel->id,
            'created_by' => $user->id,
        ]);

        // Create lessons for Plants section
        CourseLesson::create([
            'title' => 'Parts of a Plant',
            'text' => 'Identify and understand the function of different plant parts.',
            'data' => [
                'duration' => 40,
                'difficulty' => 'beginner',
                'objectives' => ['Identify plant parts', 'Understand plant functions']
            ],
            'order' => 1,
            'course_section_id' => $plantsSection->id,
            'created_by' => $user->id,
        ]);

        CourseLesson::create([
            'title' => 'How Plants Make Food',
            'text' => 'Learn about photosynthesis and how plants create their own food.',
            'data' => [
                'duration' => 45,
                'difficulty' => 'intermediate',
                'objectives' => ['Understand photosynthesis', 'Identify what plants need to grow']
            ],
            'order' => 2,
            'course_section_id' => $plantsSection->id,
            'created_by' => $user->id,
        ]);
    }
}