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
            $table->integer('week');
            $table->string('day');
            $table->tinyInteger('day_number')->comment('1: Sunday, 2: Monday, 3: Tuesday, 4: Wednesday, 5: Thursday');
            $table->tinyInteger('week_number');
            $table->tinyInteger('semester_number');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            // $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->tinyInteger('status')->default(1)->comment('1: work, 0: day_off, 2: activity, 3: test,4:final exam');
            $table->tinyInteger('vacation')->default(0);
            $table->tinyInteger('vacation_students')->default(0);
            $table->string('event')->nullable();
 


            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
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

