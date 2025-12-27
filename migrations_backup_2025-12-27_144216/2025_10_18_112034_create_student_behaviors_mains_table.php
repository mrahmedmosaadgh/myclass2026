<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_behaviors_mains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('year_id')->constrained('academic_years')->cascadeOnDelete();
             $table->foreignId('teacher_id')->nullable()-> constrained('teachers')->nullOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();

            
            $table->string('period_code_main'); // e.g. 1.7.1.2 (classroom_id.subject_id.teacher_id)
            $table->string('period_code'); // e.g. 1.7.1.2 (year_id.semester.week.day.period)
            $table->date('date');
         
 
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_behaviors_mains');
    }
};
