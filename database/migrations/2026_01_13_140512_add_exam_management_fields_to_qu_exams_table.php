<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('qu_exams', function (Blueprint $table) {
            // Exam categorization
            $table->enum('exam_type', ['practice', 'quiz', 'midterm', 'final', 'survey'])
                ->default('quiz')
                ->after('description');
            $table->string('custom_group', 100)->nullable()->after('exam_type');
            
            // Attempt tracking
            $table->integer('max_attempts')->nullable()->after('custom_group')
                ->comment('NULL = unlimited attempts');
            $table->enum('mark_calculation_method', ['last', 'best', 'average'])
                ->default('last')
                ->after('max_attempts');
            
            // Grading
            $table->decimal('passing_score', 5, 2)->nullable()->after('mark_calculation_method')
                ->comment('Minimum score to pass, NULL = no passing requirement');
            
            // Scheduling
            $table->dateTime('start_date')->nullable()->after('is_published')
                ->comment('When exam becomes available');
            $table->dateTime('end_date')->nullable()->after('start_date')
                ->comment('Submission deadline');
            $table->enum('publish_results_timing', ['immediate', 'after_end', 'manual'])
                ->default('immediate')
                ->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qu_exams', function (Blueprint $table) {
            $table->dropColumn([
                'exam_type',
                'custom_group',
                'max_attempts',
                'mark_calculation_method',
                'passing_score',
                'start_date',
                'end_date',
                'publish_results_timing',
            ]);
        });
    }
};
