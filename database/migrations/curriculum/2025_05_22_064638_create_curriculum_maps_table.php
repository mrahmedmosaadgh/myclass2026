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
        Schema::create('curriculum_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('curriculum_id')->nullable()->constrained('curricula')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->json('weekly_plan')->nullable()->comment('JSON structure: {week_number: {lessons: [], objectives: [], assessments: []}}');
            $table->tinyInteger('status')->default(0)->comment('0=draft, 1=active, 2=completed');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['school_id', 'academic_year_id']);
            $table->index(['school_id', 'teacher_id']);
            $table->index(['school_id', 'status']);

            // Unique constraint to prevent duplicate maps
            $table->unique(['school_id', 'academic_year_id', 'subject_id', 'grade_id', 'teacher_id'], 'curriculum_maps_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_maps');
    }
};
