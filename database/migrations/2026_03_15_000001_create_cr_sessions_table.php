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
        Schema::create('cr_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('day_number'); // 1-7 (Sunday-Saturday)
            $table->unsignedTinyInteger('period_number'); // 1,2,3...
            $table->string('period_code', 50); // Format: Y2026-S1-W12-D2-P3
            $table->enum('status', ['draft', 'active', 'locked'])->default('draft');
            $table->timestamps();

            // Unique constraint to prevent duplicate sessions
            $table->unique([
                'school_id', 
                'year_id', 
                'classroom_id', 
                'subject_id', 
                'teacher_id', 
                'date', 
                'period_code'
            ], 'unique_cr_session');

            // Indexes for performance
            $table->index(['school_id', 'year_id', 'date']);
            $table->index(['teacher_id', 'date']);
            $table->index('period_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cr_sessions');
    }
};
