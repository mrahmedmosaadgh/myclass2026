<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates the main assessments table for BM2 Basic Math Platform.
     * Tracks overall assessment sessions, scores, and skill breakdowns.
     */
    public function up(): void
    {
        Schema::create('bm2_assessments', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to students (users table)
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            
            // Assessment metadata
            $table->string('title')->default('Basic Math Placement Test');
            $table->enum('type', ['placement', 'progress', 'final'])->default('placement');
            
            // Scoring
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->string('grade_level_equivalent', 10)->nullable(); // e.g., "K", "1", "2"
            $table->enum('performance_level', ['emerging', 'developing', 'proficient', 'advanced'])->nullable();
            
            // Detailed skill breakdown (JSON format)
            // Example: {"addition": 85, "subtraction": 72, "number_sense": 90}
            $table->json('skill_breakdown')->nullable();
            
            // Recommendations for learning path
            // Example: [{"module_id": 2, "priority": "high", "reason": "subtraction_needs_work"}]
            $table->json('recommended_modules')->nullable();
            
            // Session tracking
            $table->string('firebase_session_id', 100)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_time_seconds')->nullable();
            
            // System fields
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for performance
            $table->index('student_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bm2_assessments');
    }
};
