<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates the assessment questions table for BM2.
     * Tracks individual question responses during assessments.
     */
    public function up(): void
    {
        Schema::create('bm2_assessment_questions', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to assessment
            $table->foreignId('assessment_id')->constrained('bm2_assessments')->onDelete('cascade');
            
            // Question details
            $table->foreignId('question_bank_id')->nullable()->constrained('bm2_questions_bank')->nullOnDelete();
            $table->text('question_text');
            
            // Question classification
            $table->enum('subject', ['math'])->default('math');
            $table->enum('grade_level', ['K', '1', '2']);
            $table->enum('question_type', ['addition', 'subtraction', 'number_sense', 'fractions', 'word_problem']);
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            
            // Student response
            $table->text('student_answer')->nullable();
            $table->text('correct_answer');
            $table->boolean('is_correct')->default(false);
            
            // Performance metrics
            $table->integer('time_taken_seconds')->default(0);
            $table->integer('hints_used')->default(0);
            $table->integer('points_earned')->default(0);
            $table->integer('possible_points')->default(10);
            
            // Adaptive testing tracking
            $table->integer('question_order')->default(0); // Sequence in assessment
            $table->boolean('was_adaptive')->default(false); // Was this question adaptively selected?
            
            // Timestamps
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('assessment_id');
            $table->index('question_type');
            $table->index('difficulty');
            $table->index('is_correct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bm2_assessment_questions');
    }
};
