<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add composite indexes for common query patterns to improve performance.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Composite index for filtering by status and grade
            $table->index(['status', 'grade_id'], 'idx_questions_status_grade');
            
            // Composite index for filtering by subject and difficulty
            $table->index(['subject_id', 'difficulty_level'], 'idx_questions_subject_difficulty');
            
            // Composite index for analytics queries
            $table->index(['usage_count', 'avg_success_rate'], 'idx_questions_analytics');
            
            // Index for Bloom level filtering
            $table->index('bloom_level', 'idx_questions_bloom_level');
            
            // Index for difficulty level filtering
            $table->index('difficulty_level', 'idx_questions_difficulty_level');
        });

        Schema::table('question_options', function (Blueprint $table) {
            // Composite index for finding correct options by question
            $table->index(['question_id', 'is_correct'], 'idx_options_question_correct');
            
            // Index for ordering options
            $table->index(['question_id', 'order_index'], 'idx_options_question_order');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            // Composite index for user's completed attempts
            $table->index(['user_id', 'completed_at'], 'idx_attempts_user_completed');
            
            // Composite index for recent attempts
            $table->index(['user_id', 'started_at'], 'idx_attempts_user_started');
            
            // Index for quiz_id lookups
            $table->index('quiz_id', 'idx_attempts_quiz');
        });

        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            // Composite index for attempt answers with correctness
            $table->index(['attempt_id', 'is_correct'], 'idx_answers_attempt_correct');
            
            // Composite index for question performance analytics
            $table->index(['question_id', 'is_correct'], 'idx_answers_question_correct');
            
            // Index for time-based analytics
            $table->index('answered_at', 'idx_answers_answered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('idx_questions_status_grade');
            $table->dropIndex('idx_questions_subject_difficulty');
            $table->dropIndex('idx_questions_analytics');
            $table->dropIndex('idx_questions_bloom_level');
            $table->dropIndex('idx_questions_difficulty_level');
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->dropIndex('idx_options_question_correct');
            $table->dropIndex('idx_options_question_order');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex('idx_attempts_user_completed');
            $table->dropIndex('idx_attempts_user_started');
            $table->dropIndex('idx_attempts_quiz');
        });

        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->dropIndex('idx_answers_attempt_correct');
            $table->dropIndex('idx_answers_question_correct');
            $table->dropIndex('idx_answers_answered_at');
        });
    }
};
