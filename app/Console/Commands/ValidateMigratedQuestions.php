<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Command to validate migrated questions from lesson presentations
 * 
 * This command checks:
 * 1. All migrated questions have valid question types
 * 2. All questions have at least one correct option
 * 3. Single choice questions have exactly one correct option
 * 4. Multi-select questions have at least one correct option
 * 5. All questions have at least 2 options
 */
class ValidateMigratedQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quiz:validate-migration
                          {--fix : Attempt to fix validation errors}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate migrated questions from lesson presentations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting validation of migrated questions...');
        $this->newLine();

        $errors = [];
        $warnings = [];
        $fixed = 0;

        // Check 1: Valid question types
        $this->info('Checking question types...');
        $invalidTypes = DB::table('questions')
            ->leftJoin('question_types', 'questions.question_type_id', '=', 'question_types.id')
            ->whereNull('question_types.id')
            ->select('questions.id', 'questions.question_text')
            ->get();

        if ($invalidTypes->count() > 0) {
            $errors[] = "Found {$invalidTypes->count()} questions with invalid question types";
            foreach ($invalidTypes as $question) {
                $this->error("  - Question ID {$question->id}: Invalid question type");
            }
        } else {
            $this->info('  ✓ All questions have valid question types');
        }

        // Check 2: Questions have options
        $this->info('Checking question options...');
        $questionsWithoutOptions = DB::table('questions')
            ->leftJoin('question_options', 'questions.id', '=', 'question_options.question_id')
            ->join('question_types', 'questions.question_type_id', '=', 'question_types.id')
            ->where('question_types.has_options', true)
            ->whereNull('question_options.id')
            ->select('questions.id', 'questions.question_text')
            ->distinct()
            ->get();

        if ($questionsWithoutOptions->count() > 0) {
            $errors[] = "Found {$questionsWithoutOptions->count()} questions without options";
            foreach ($questionsWithoutOptions as $question) {
                $this->error("  - Question ID {$question->id}: No options found");
            }
        } else {
            $this->info('  ✓ All option-based questions have options');
        }

        // Check 3: Questions have at least one correct option
        $this->info('Checking correct options...');
        $questionsWithoutCorrect = DB::table('questions')
            ->leftJoin('question_options', function($join) {
                $join->on('questions.id', '=', 'question_options.question_id')
                     ->where('question_options.is_correct', true);
            })
            ->join('question_types', 'questions.question_type_id', '=', 'question_types.id')
            ->where('question_types.has_options', true)
            ->whereNull('question_options.id')
            ->select('questions.id', 'questions.question_text')
            ->distinct()
            ->get();

        if ($questionsWithoutCorrect->count() > 0) {
            $errors[] = "Found {$questionsWithoutCorrect->count()} questions without correct options";
            foreach ($questionsWithoutCorrect as $question) {
                $this->error("  - Question ID {$question->id}: No correct option");
            }
        } else {
            $this->info('  ✓ All questions have at least one correct option');
        }

        // Check 4: Single choice questions have exactly one correct option
        $this->info('Checking single choice questions...');
        $singleChoiceQuestions = DB::table('questions')
            ->join('question_types', 'questions.question_type_id', '=', 'question_types.id')
            ->where('question_types.slug', 'single_choice')
            ->select('questions.id', 'questions.question_text')
            ->get();

        foreach ($singleChoiceQuestions as $question) {
            $correctCount = DB::table('question_options')
                ->where('question_id', $question->id)
                ->where('is_correct', true)
                ->count();

            if ($correctCount !== 1) {
                $errors[] = "Question ID {$question->id} has {$correctCount} correct options (should be 1)";
                $this->error("  - Question ID {$question->id}: {$correctCount} correct options (expected 1)");
            }
        }

        if (count($errors) === 0 || !str_contains(end($errors), 'correct options')) {
            $this->info('  ✓ All single choice questions have exactly one correct option');
        }

        // Check 5: Questions have at least 2 options
        $this->info('Checking minimum option count...');
        $questionsWithFewOptions = DB::table('questions')
            ->join('question_types', 'questions.question_type_id', '=', 'question_types.id')
            ->where('question_types.has_options', true)
            ->select('questions.id', 'questions.question_text')
            ->get()
            ->filter(function($question) {
                $optionCount = DB::table('question_options')
                    ->where('question_id', $question->id)
                    ->count();
                return $optionCount < 2;
            });

        if ($questionsWithFewOptions->count() > 0) {
            $warnings[] = "Found {$questionsWithFewOptions->count()} questions with fewer than 2 options";
            foreach ($questionsWithFewOptions as $question) {
                $this->warn("  - Question ID {$question->id}: Less than 2 options");
            }
        } else {
            $this->info('  ✓ All questions have at least 2 options');
        }

        // Summary
        $this->newLine();
        $this->info('Validation Summary:');
        $this->info('==================');
        
        $totalQuestions = DB::table('questions')->count();
        $this->info("Total questions: {$totalQuestions}");
        
        if (count($errors) === 0 && count($warnings) === 0) {
            $this->info('✓ All validations passed!');
            return 0;
        }

        if (count($errors) > 0) {
            $this->error("\nFound " . count($errors) . " error(s):");
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
        }

        if (count($warnings) > 0) {
            $this->warn("\nFound " . count($warnings) . " warning(s):");
            foreach ($warnings as $warning) {
                $this->warn("  - {$warning}");
            }
        }

        return count($errors) > 0 ? 1 : 0;
    }
}
