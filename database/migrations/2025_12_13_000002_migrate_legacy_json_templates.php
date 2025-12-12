<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Subject;
use App\Models\CourseManagement\LessonPlanTemplate;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Migrate all JSON templates from subjects.lesson_plan_templates 
     * to the lesson_plan_templates table.
     */
    public function up(): void
    {
        $subjects = Subject::whereNotNull('lesson_plan_templates')->get();

        $migratedCount = 0;
        $errorCount = 0;

        foreach ($subjects as $subject) {
            try {
                // Parse JSON templates
                $templates = is_string($subject->lesson_plan_templates)
                    ? json_decode($subject->lesson_plan_templates, true)
                    : $subject->lesson_plan_templates;

                if (!is_array($templates)) {
                    \Log::warning("Subject {$subject->id} has invalid template format");
                    continue;
                }

                // Create database records for each template
                foreach ($templates as $template) {
                    if (!isset($template['name']) || !isset($template['structure'])) {
                        \Log::warning("Subject {$subject->id} has template missing name or structure");
                        continue;
                    }

                    LessonPlanTemplate::create([
                        'name' => $template['name'],
                        'structure' => $template['structure'],
                        'subject_id' => $subject->id,
                        'is_active' => true,
                        'created_by' => null, // Legacy templates have no creator
                    ]);

                    $migratedCount++;
                }

                // Mark as migrated
                $subject->update(['templates_migrated_at' => now()]);

            } catch (\Exception $e) {
                \Log::error("Failed to migrate templates for subject {$subject->id}: " . $e->getMessage());
                $errorCount++;
            }
        }

        \Log::info("Template migration complete: {$migratedCount} templates migrated, {$errorCount} errors");
    }

    /**
     * Reverse the migrations.
     * 
     * This will NOT restore the JSON templates - they remain in the subjects table.
     * It only removes the migrated database records.
     */
    public function down(): void
    {
        // Delete all templates that were created from migration (have subject_id but no created_by)
        LessonPlanTemplate::whereNotNull('subject_id')
            ->whereNull('created_by')
            ->delete();

        // Clear migration timestamps
        Subject::whereNotNull('templates_migrated_at')
            ->update(['templates_migrated_at' => null]);
    }
};
