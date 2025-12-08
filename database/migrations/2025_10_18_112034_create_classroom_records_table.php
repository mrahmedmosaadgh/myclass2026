<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('period_code',20); // e.g. 1.7.1.2 ( semester.week.day.period_order)
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            // $table->foreignId('behavior_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('attend')->nullable(); 
            $table->tinyInteger('book')->nullable(); 
            $table->tinyInteger('homework')->nullable(); 
            $table->tinyInteger('out_classroom')->nullable(); 
            $table->string('out_classroom_notes')->nullable(); 

            $table->tinyInteger('turn1')->nullable(); 
            $table->tinyInteger('turn2')->nullable(); 
            $table->tinyInteger('turn3')->nullable(); 
            $table->tinyInteger('plus')->nullable(); 
            $table->tinyInteger('minus')->nullable(); 
            $table->tinyInteger('total')->nullable(); 

            $table->date('date');
            $table->time('time')->nullable();
 
            $table->text('points_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_records');
    }
};
