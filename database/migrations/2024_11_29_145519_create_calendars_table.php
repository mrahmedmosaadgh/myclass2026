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
            $table->date('date');
            
            // Foreign Keys
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            
            // Status and Flags
            $table->tinyInteger('status')->default(1)
                ->comment('1: work, 0: day_off, 2: activity, 3: test, 4: final exam');
            $table->tinyInteger('vacation')->default(0);
            $table->tinyInteger('vacation_students')->default(0);
            
            // Events
            $table->string('event')->nullable();
            $table->string('event_academic')->nullable();
            
            // Week tracking (absolute week in year)
            $table->tinyInteger('week_number');
            
            // Additional data
            $table->json('data')->nullable();
            
            $table->softDeletes();
            $table->timestamps();

            // Prevent duplicate calendar entries for same date/school
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

