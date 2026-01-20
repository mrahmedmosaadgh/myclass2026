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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('h_r_id')->constrained('h_r_s')->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('section')->nullable();
            $table->string('section_ar')->nullable();
            $table->boolean('is_active')->default(true);
            // $table->string('address');
            $table->json('data')->nullable();
            $table->integer('active_academic_year_id')->nullable();
            $table->integer('active_semester_id')->nullable();
            // $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('set null');
            // $table->integer('schedule_copy_id')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->json('weekly_settings')->nullable();
            $table->json('weekly_plan_settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
