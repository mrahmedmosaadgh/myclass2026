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
            $table->integer('semester_number')->comment('1 or 2');
            $table->integer('week_number')->comment('1-18 or 1-36');
            $table->foreignId('cst_id')->constrained('classroom_subject_teachers');
            $table->timestamps();
            
            // Composite index for efficient querying
            $table->unique(['academic_year_id', 'semester_number', 'week_number', 'cst_id'], 'weekly_plans_unique_composite');
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