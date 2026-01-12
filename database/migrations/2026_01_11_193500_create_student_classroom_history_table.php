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
        Schema::create('student_classroom_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('from_classroom_id')->nullable()->constrained('classrooms')->onDelete('set null');
            $table->foreignId('to_classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignId('from_grade_id')->nullable()->constrained('grades')->onDelete('set null');
            $table->foreignId('to_grade_id')->constrained('grades')->onDelete('cascade');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('set null');
            $table->foreignId('changed_by_user_id')->constrained('users')->onDelete('cascade');
            $table->string('change_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better query performance
            $table->index('student_id');
            $table->index('academic_year_id');
            $table->index('changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_classroom_history');
    }
};
