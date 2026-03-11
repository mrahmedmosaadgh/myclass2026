<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates student_badges pivot table for BM2.
     * Tracks which badges students have earned.
     */
    public function up(): void
    {
        Schema::create('bm2_student_badges', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('badge_id')->constrained('bm2_badges')->onDelete('cascade');
            
            // Context of earning
            $table->foreignId('assessment_id')->nullable()->constrained('bm2_assessments')->nullOnDelete();
            $table->string('earned_for')->nullable(); // Description of what they did to earn it
            
            // Date earned
            $table->timestamp('earned_at');
            
            // Points awarded
            $table->integer('points_awarded')->default(0);
            
            // Display on profile
            $table->boolean('is_displayed')->default(true);
            
            $table->timestamps();
            
            // Ensure unique badge per student
            $table->unique(['student_id', 'badge_id']);
            
            // Indexes
            $table->index('student_id');
            $table->index('earned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bm2_student_badges');
    }
};
