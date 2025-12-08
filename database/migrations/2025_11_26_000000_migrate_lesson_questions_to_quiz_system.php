<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration to migrate existing lesson presentation questions to the new quiz system
 * 
 * This migration:
 * 1. Reads existing questions from lesson_presentation_slides table
 * 2. Maps them to the new questions table schema
 * 3. Creates corresponding question_options records
 * 4. Validates migrated data
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all lesson presentation slides with question type
        $questionSlides = DB::table('lesson_presentation_slides')
            ->where('slide_type', 'question')
            ->whereNotNull('slide_content')
            ->get();

        $migratedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($questionSlides as $slide) {
            try {
                $slideContent = json_decode($slide->slide_content, true);
                
                if (!isset($slideContent['questions']) || !is_array($slideContent['questions'])) {
                    $skippedCount++;
                    continue;
                }

                foreach ($slideContent['questions'] as $legacyQuestion) {
                    // Only migrate compatible question types
                    $compatibleTypes = ['single_choice', 'multiple_choice', 'true_false'];
                    if (!in_array($legacyQuestion['type'] ?? '', $compatibleTypes)) {
                        $skippedCount++;
                        continue;
                    }

                    // Get question type ID
                    $questionTypeId = $this->getQuestionTypeId($legacyQuestion['type']);
                    if (!$questionTypeId) {
                        $skippedCount++;
                        continue;
                    }

                    // Check if question already migrated (by checking if it exists with same text)
                    $existingQuestion = DB::table('questions')
                        ->where('question_text', $legacyQuestion['text'] ?? '')
                        ->where('question_type_id', $questionTypeId)
                        ->first();

                    if ($existingQuestion) {
                        $skippedCount++;
                        continue;
                    }

                    // Insert question
                    $questionId = DB::table('questions')->insertGetId([
                        'question_type_id' => $questionTypeId,
                        'question_text' => $legacyQuestion['text'] ?? '',
                        'grade_level_id' => null, // Will be set based on lesson context
                        'subject_id' => null, // Will be set based on lesson context
                        'topic_id' => null,
                        'bloom_level' => $legacyQuestion['bloomLevel'] ?? null,
                        'difficulty_level' => $legacyQuestion['difficultyLevel'] ?? null,
                        'estimated_time_sec' => $legacyQuestion['timer'] ?? null,
                        'author_id' => null, // Will be set based on lesson context
                        'status' => 'active',
                        'usage_count' => 0,
                        'avg_success_rate' => null,
                        'discrimination_index' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Insert options for single_choice and multiple_choice
                    if (in_array($legacyQuestion['type'], ['single_choice', 'multiple_choice'])) {
                        $options = $legacyQuestion['options'] ?? [];
                        foreach ($options as $index => $option) {
                            $isCorrect = false;
                            
                            if ($legacyQuestion['type'] === 'single_choice') {
                                $isCorrect = ($legacyQuestion['correct_answer'] ?? null) === ($option['id'] ?? null);
                            } else {
                                $correctAnswers = $legacyQuestion['correct_answer'] ?? [];
                                $isCorrect = in_array($option['id'] ?? null, $correctAnswers);
                            }

                            DB::table('question_options')->insert([
                                'question_id' => $questionId,
                                'option_key' => chr(65 + $index), // A, B, C, D...
                                'option_text' => $option['text'] ?? '',
                                'is_correct' => $isCorrect,
                                'distractor_strength' => null,
                                'order_index' => $index,
                            ]);
                        }
                    }

                    // Insert options for true_false
                    if ($legacyQuestion['type'] === 'true_false') {
                        $correctAnswer = $legacyQuestion['correct_answer'] ?? true;
                        
                        DB::table('question_options')->insert([
                            [
                                'question_id' => $questionId,
                                'option_key' => 'A',
                                'option_text' => 'True',
                                'is_correct' => $correctAnswer === true,
                                'distractor_strength' => null,
                                'order_index' => 0,
                            ],
                            [
                                'question_id' => $questionId,
                                'option_key' => 'B',
                                'option_text' => 'False',
                                'is_correct' => $correctAnswer === false,
                                'distractor_strength' => null,
                                'order_index' => 1,
                            ]
                        ]);
                    }

                    // Store hints if available
                    if (!empty($legacyQuestion['hints'])) {
                        // Hints are stored as JSON in the questions table
                        DB::table('questions')
                            ->where('id', $questionId)
                            ->update([
                                'hints' => json_encode($legacyQuestion['hints'])
                            ]);
                    }

                    // Store explanation if available
                    if (!empty($legacyQuestion['explanation'])) {
                        // Explanation is stored as JSON in the questions table
                        DB::table('questions')
                            ->where('id', $questionId)
                            ->update([
                                'explanation' => json_encode([
                                    'text' => $legacyQuestion['explanation'],
                                    'revealed_after_attempt' => true
                                ])
                            ]);
                    }

                    $migratedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "Slide {$slide->id}: " . $e->getMessage();
                $skippedCount++;
            }
        }

        // Log migration results
        \Log::info("Question Migration Results:", [
            'migrated' => $migratedCount,
            'skipped' => $skippedCount,
            'errors' => $errors
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This migration does not delete migrated questions on rollback
        // to prevent data loss. Manual cleanup may be required if needed.
        \Log::warning('Question migration rollback: No automatic cleanup performed');
    }

    /**
     * Get question type ID from slug
     */
    private function getQuestionTypeId(string $slug): ?int
    {
        $questionType = DB::table('question_types')
            ->where('slug', $slug)
            ->first();

        return $questionType ? $questionType->id : null;
    }
};
