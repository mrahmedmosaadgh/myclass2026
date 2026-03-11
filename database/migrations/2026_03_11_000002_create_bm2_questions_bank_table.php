<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates the question bank table for BM2.
     * Stores all available questions for assessments and practice.
     */
    public function up(): void
    {
        Schema::create('bm2_questions_bank', function (Blueprint $table) {
            $table->id();
            
            // Question content
            $table->text('question_text');
            $table->text('context_description')->nullable(); // For word problems
            
            // Classification
            $table->enum('subject', ['math'])->default('math');
            $table->enum('grade_level', ['K', '1', '2']);
            $table->enum('topic', ['addition', 'subtraction', 'number_sense', 'fractions', 'patterns', 'measurement']);
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            
            // Question format
            $table->enum('question_format', [
                'multiple_choice', 
                'true_false', 
                'fill_in_blank', 
                'short_answer',
                'matching',
                'drag_drop'
            ]);
            
            // Options and answers (JSON format for flexibility)
            // Multiple choice: ["option1", "option2", "option3", "option4"]
            // True/False: [true, false]
            $table->json('options')->nullable();
            
            // Correct answer(s) - can be string or array for matching
            $table->text('correct_answer');
            
            // Answer explanation (shown after student answers)
            $table->text('explanation')->nullable();
            
            // Visual aids
            $table->string('image_url')->nullable();
            $table->json('visual_properties')->nullable(); // For interactive elements
            
            // Metadata
            $table->integer('estimated_time_seconds')->default(30);
            $table->integer('points_default')->default(10);
            $table->boolean('allows_calculator')->default(false);
            $table->boolean('has_hint')->default(false);
            $table->json('hints')->nullable(); // Array of hint strings
            
            // Usage tracking
            $table->integer('times_used')->default(0);
            $table->decimal('success_rate', 5, 2)->nullable(); // Percentage
            $table->decimal('discrimination_index', 5, 2)->nullable(); // How well it differentiates students
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false); // Reviewed by teacher/expert
            
            // Authoring
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Indexes for efficient filtering
            $table->index('grade_level');
            $table->index('topic');
            $table->index('difficulty');
            $table->index('question_format');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bm2_questions_bank');
    }
};
