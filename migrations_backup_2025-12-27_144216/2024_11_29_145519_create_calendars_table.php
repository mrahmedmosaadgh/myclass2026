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
        Schema::create('calendars', function (Blueprint $table) {
    $table->id();
            $table->date('date')->index();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();

            // Daily status - kept as columns for fast filtering
            $table->tinyInteger('status')
                  ->default(1)
                  ->comment('1: work, 0: day_off, 2: activity, 3: test, 4: final_exam ,5: holiday ,6: more ' )
                  ->index();

            $table->boolean('vacation_all')->default(false);
            $table->boolean('vacation_teachers')->nullable();
            $table->boolean('vacation_students')->nullable();
     
           $table->tinyInteger('day_number')->comment('1: Sunday, 2: Monday, 3: Tuesday, 4: Wednesday, 5: Thursday');
            $table->tinyInteger('week_number')->nullable();
  
            // All events and extra flexible data
            $table->json('data')->nullable();
            $table->json('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Prevent duplicate entries for the same date + school
            $table->unique(['date', 'school_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};

