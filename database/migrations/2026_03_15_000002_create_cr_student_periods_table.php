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
        Schema::create('cr_student_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('cr_sessions')->nullOnDelete();
            $table->date('date');
            $table->string('period_code', 50);
            $table->enum('attendance_status', ['present', 'absent', 'late', 'left_early'])->default('present');
            $table->unsignedTinyInteger('attendance_score')->default(5); // 0-5
            $table->text('attendance_note')->nullable();
            $table->unsignedTinyInteger('total_score')->default(0); // 0-20 (sum of all categories)
            $table->boolean('locked')->default(false); // Locked when absent
            $table->timestamps();

            // Unique constraint to prevent duplicate student-period records
            $table->unique([
                'school_id', 
                'year_id', 
                'date', 
                'period_code', 
                'student_id'
            ], 'unique_cr_student_period');

            // Indexes for performance (critical for reports)
            $table->index(['school_id', 'year_id', 'date']); // Date-range queries
            $table->index(['student_id', 'school_id']); // Per-student dashboards
            $table->index('period_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cr_student_periods');
    }
};
