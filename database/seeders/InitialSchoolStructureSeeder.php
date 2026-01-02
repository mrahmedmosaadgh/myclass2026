<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\Calendar;
use App\Models\School;
use App\Models\Stage;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\HR;

class InitialSchoolStructureSeeder extends Seeder
{
    /**
     * Create the initial school structure:
     * - School sections
     * - Stages (Primary, Intermediate, Secondary)
     * - Grades (1-12)
     * - Subjects
     * - Academic year
     * - Schedule timings
     */
    public function run()
    {
        $hr = HR::first(); // Get the first HR user to associate with the school
        
        if (!$hr) {
            $this->command->error('No HR user found. Please create an HR user first.');
            return;
        }

        // Create a sample school
        $school = School::create([
            'name' => 'Sample School',
            'name_ar' => 'مدرسة نموذجية',
            'h_r_id' => $hr->id,
            'section' => 'Main',
            'section_ar' => 'الرئيسي',
            'data' => ['type' => 'public', 'level' => 'primary'],
            'is_active' => true
        ]);

        $this->command->info('✓ Created sample school');

        // Create academic year
        $academicYear = AcademicYear::create([
            'name' => '2025-2026',
            // 'name_ar' => '2025-2026',
            'school_id' => $school->id,
            'active' => true
        ]);

        $this->command->info('✓ Created academic year 2025-2026');

        // Create calendar for the academic year
        // $calendar = Calendar::create([
        //     'name' => 'Academic Year Calendar',
        //     'name_ar' => 'تقويم السنة الدراسية',
        //     'academic_year_id' => $academicYear->id,
        //     'school_id' => $school->id,
        //     'data' => [
        //         'start_date' => '2025-09-01',
        //         'end_date' => '2026-06-15',
        //         'holidays' => [
        //             ['start' => '2025-11-15', 'end' => '2025-11-20', 'name' => 'Fall Break'],
        //             ['start' => '2025-12-20', 'end' => '2026-01-05', 'name' => 'Winter Break'],
        //             ['start' => '2026-03-15', 'end' => '2026-03-20', 'name' => 'Spring Break']
        //         ]
        //     ]
        // ]);

        // $this->command->info('✓ Created academic calendar');

        // Create stages and grades
        $this->createStagesAndGrades($school);
        
        // Create some sample subjects
        $this->createSubjects($school);

        $this->command->info('✅ School structure created successfully!');
    }


    private function createStagesAndGrades($school)
    {
        $stagesData = [
            [
                'name' => 'Primary',
                'name_ar' => 'الابتدائية',
                'description' => 'Grades 1-6',
                'grades' => [
                    ['name' => 'Grade 1', 'name_ar' => 'الصف الأول'],
                    ['name' => 'Grade 2', 'name_ar' => 'الصف الثاني'],
                    ['name' => 'Grade 3', 'name_ar' => 'الصف الثالث'],
                    ['name' => 'Grade 4', 'name_ar' => 'الصف الرابع'],
                    ['name' => 'Grade 5', 'name_ar' => 'الصف الخامس'],
                    ['name' => 'Grade 6', 'name_ar' => 'الصف السادس'],
                ]
            ],
            [
                'name' => 'Intermediate',
                'name_ar' => 'المتوسطة',
                'description' => 'Grades 7-9',
                'grades' => [
                    ['name' => 'Grade 7', 'name_ar' => 'الصف السابع'],
                    ['name' => 'Grade 8', 'name_ar' => 'الصف الثامن'],
                    ['name' => 'Grade 9', 'name_ar' => 'الصف التاسع'],
                ]
            ],
            [
                'name' => 'Secondary',
                'name_ar' => 'الثانوية',
                'description' => 'Grades 10-12',
                'grades' => [
                    ['name' => 'Grade 10', 'name_ar' => 'الصف العاشر'],
                    ['name' => 'Grade 11', 'name_ar' => 'الصف الحادي عشر'],
                    ['name' => 'Grade 12', 'name_ar' => 'الصف الثاني عشر'],
                ]
            ],
        ];

        $totalGrades = 0;

        foreach ($stagesData as $stageData) {
            $stage = Stage::create([
                'school_id' => $school->id,
                'name' => $stageData['name'],
                'name_ar' => $stageData['name_ar'],
                'description' => $stageData['description']
            ]);

            foreach ($stageData['grades'] as $gradeData) {
                Grade::create([
                    'school_id' => $school->id,
                    'stage_id' => $stage->id,
                    'name' => $gradeData['name'],
                    'name_ar' => $gradeData['name_ar'],
                    'subject_ids' => null // Will be assigned later
                ]);
                $totalGrades++;
            }
        }

        $this->command->info('   ✓ Created 3 stages and ' . $totalGrades . ' grades');
    }

    private function createSubjects($school)
    {
        $subjects = [
            // Core Subjects
            ['name' => 'Mathematics', 'name_ar' => 'الرياضيات', 'color_bg' => '#3B82F6', 'color_text' => '#FFFFFF'],
            ['name' => 'Math-NAFS', 'name_ar' => 'الرياضيات - نافس', 'color_bg' => '#2563EB', 'color_text' => '#FFFFFF'],
            ['name' => 'Science', 'name_ar' => 'العلوم', 'color_bg' => '#10B981', 'color_text' => '#FFFFFF'],
            ['name' => 'Science (N)', 'name_ar' => 'العلوم (ن)', 'color_bg' => '#059669', 'color_text' => '#FFFFFF'],
            ['name' => 'English', 'name_ar' => 'اللغة الإنجليزية', 'color_bg' => '#F59E0B', 'color_text' => '#FFFFFF'],
            ['name' => 'English-NAFS', 'name_ar' => 'الإنجليزية - نافس', 'color_bg' => '#D97706', 'color_text' => '#FFFFFF'],
            ['name' => 'Arabic', 'name_ar' => 'اللغة العربية', 'color_bg' => '#EF4444', 'color_text' => '#FFFFFF'],
            
            // Sciences
            ['name' => 'Biology', 'name_ar' => 'الأحياء', 'color_bg' => '#22C55E', 'color_text' => '#FFFFFF'],
            ['name' => 'Chemistry', 'name_ar' => 'الكيمياء', 'color_bg' => '#14B8A6', 'color_text' => '#FFFFFF'],
            ['name' => 'Physics', 'name_ar' => 'الفيزياء', 'color_bg' => '#06B6D4', 'color_text' => '#FFFFFF'],
            
            // Social Studies
            ['name' => 'Geography', 'name_ar' => 'الجغرافيا', 'color_bg' => '#8B5CF6', 'color_text' => '#FFFFFF'],
            ['name' => 'US history', 'name_ar' => 'التاريخ الأمريكي', 'color_bg' => '#A855F7', 'color_text' => '#FFFFFF'],
            ['name' => 'SSA', 'name_ar' => 'الدراسات الاجتماعية أ', 'color_bg' => '#9333EA', 'color_text' => '#FFFFFF'],
            ['name' => 'SSE', 'name_ar' => 'الدراسات الاجتماعية ع', 'color_bg' => '#7C3AED', 'color_text' => '#FFFFFF'],
            
            // Religious & Cultural
            ['name' => 'Islamic', 'name_ar' => 'التربية الإسلامية', 'color_bg' => '#059669', 'color_text' => '#FFFFFF'],
            ['name' => 'Noor AlBian', 'name_ar' => 'نور البيان', 'color_bg' => '#047857', 'color_text' => '#FFFFFF'],
            
            // Languages
            ['name' => 'French', 'name_ar' => 'اللغة الفرنسية', 'color_bg' => '#DC2626', 'color_text' => '#FFFFFF'],
            
            // Technology & Innovation
            ['name' => 'ICT', 'name_ar' => 'تقنية المعلومات', 'color_bg' => '#6366F1', 'color_text' => '#FFFFFF'],
            ['name' => 'Robot', 'name_ar' => 'الروبوت', 'color_bg' => '#4F46E5', 'color_text' => '#FFFFFF'],
            
            // Physical & Arts
            ['name' => 'PE', 'name_ar' => 'التربية البدنية', 'color_bg' => '#0EA5E9', 'color_text' => '#FFFFFF'],
            ['name' => 'Art', 'name_ar' => 'الفنون', 'color_bg' => '#EC4899', 'color_text' => '#FFFFFF'],
            
            // Test Preparation
            ['name' => 'GAT', 'name_ar' => 'القدرات العامة', 'color_bg' => '#F97316', 'color_text' => '#FFFFFF'],
            ['name' => 'SAT', 'name_ar' => 'اختبار SAT', 'color_bg' => '#EA580C', 'color_text' => '#FFFFFF'],
            
            // Special Programs
            ['name' => 'Capstone', 'name_ar' => 'المشروع النهائي', 'color_bg' => '#84CC16', 'color_text' => '#FFFFFF'],
        ];

        foreach ($subjects as $subjectData) {
            Subject::create([
                'school_id' => $school->id,
                'name' => $subjectData['name'],
                'name_ar' => $subjectData['name_ar'],
                'color_bg' => $subjectData['color_bg'],
                'color_text' => $subjectData['color_text'],
                'active' => 1,
                'description' => null,
                'notes' => null,
                'nour_name' => null,
                'nour_id' => null,
                'lesson_plan_templates' => null
            ]);
        }

        $this->command->info('   ✓ Created ' . count($subjects) . ' subjects');
    }

}
