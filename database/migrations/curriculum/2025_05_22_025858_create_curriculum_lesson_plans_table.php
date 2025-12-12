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
        Schema::create('curriculum_lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('curriculum_lesson_id')->nullable()->constrained('curriculum_lessons')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->json('co_teacher_ids')->nullable()->comment('Array of teacher IDs for co-teachers');
            $table->string('title');
            $table->integer('page_number')->nullable();
            $table->text('cw')->nullable()->comment('Class work');
            $table->text('hw')->nullable()->comment('Home work');
            $table->text('objectives')->nullable();
            $table->json('materials')->nullable()->comment('Teaching materials and resources');
            $table->json('plan')->nullable()->comment('Detailed lesson plan structure');
            $table->tinyInteger('status')->default(0)->comment('0=draft, 1=active, 2=completed');
            $table->date('planned_date')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['school_id', 'teacher_id']);
            $table->index(['school_id', 'classroom_id']);
            $table->index(['school_id', 'status']);
            $table->index(['planned_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_lesson_plans');
    }
};
