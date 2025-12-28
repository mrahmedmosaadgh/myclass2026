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
        Schema::create('weekly_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->tinyInteger('semester_number')->comment('1 or 2');
            $table->tinyInteger('week_number')->comment('1-18 or 1-36');
            // $table->tinyInteger('day_number')->comment('1-5');
            // $table->tinyInteger('period_number')->comment('1-10');
            // Foreign keys with better constraints
            $table->foreignId('copy_id')
                ->constrained('schedule_copies')
                ->onDelete('cascade');

            $table->foreignId('schedule_id')
                ->constrained('schedules')
                ->onDelete('cascade');


            $table->text('cw')->nullable();
            $table->text('hw')->nullable();
            $table->text('notes')->nullable();
            $table->text('comments')->nullable();

            $table->timestamps();
            
            // Composite index for efficient querying
            // $table->unique(['academic_year_id', 'semester_number', 'week_number'], 'weekly_plans_unique_composite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_plans');
    }
};