<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates the learning paths table for BM2.
     * Stores personalized learning recommendations based on assessment results.
     */
    public function up(): void
    {
        Schema::create('bm2_learning_paths', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to student
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            
            // Reference to the assessment that generated this path
            $table->foreignId('assessment_id')->nullable()->constrained('bm2_assessments')->nullOnDelete();
            
            // Learning path title and description
            $table->string('title')->default('Personalized Math Learning Path');
            $table->text('description')->nullable();
            
            // Recommended modules (JSON format)
            // Example: [
            //   {"module_id": 2, "lesson_ids": [1, 2, 3], "priority": "high", "reason": "addition_mastery"},
            //   {"module_id": 3, "lesson_ids": [1, 2], "priority": "medium", "reason": "subtraction_practice"}
            // ]
            $table->json('recommended_modules');
            
            // Progress tracking
            $table->integer('total_lessons')->default(0);
            $table->integer('completed_lessons')->default(0);
            $table->decimal('completion_percentage', 5, 2)->default(0);
            
            // Estimated completion time
            $table->integer('estimated_minutes')->default(60);
            
            // Status
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            
            // Dates
            $table->date('target_completion_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // System fields
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('student_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bm2_learning_paths');
    }
};
