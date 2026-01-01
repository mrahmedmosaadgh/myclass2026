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
        Schema::create('classroom_subject_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->onDelete('cascade');
            $table->integer('classes_per_week');
            $table->string('color_custom',22)->nullable();

            $table->string('color_custom_text', 22)
                ->nullable()
                ->comment('Custom color for UI display (hex format: #RRGGBB)');
            $table->json('data')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            // Performance indexes
            // Unique composite index for assignment constraint
            $table->unique([
                'school_id', 
                'academic_year_id', 
                'classroom_id', 
                'subject_id', 
                'teacher_id'
            ], 'unique_assignment_idx');
            
            // Index for school and academic year queries (for import operations)
            $table->index(['school_id', 'academic_year_id'], 'school_academic_year_idx');
            
            // Index for teacher queries
            $table->index(['teacher_id', 'school_id'], 'teacher_school_idx');
            
            // Index for classroom queries
            $table->index(['classroom_id', 'subject_id'], 'classroom_subject_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_subject_teachers');
    }
};
