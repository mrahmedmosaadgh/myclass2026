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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('s_id')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_cute')->nullable();
            $table->string('order_1')->nullable();
            $table->string('order_2')->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('student_parents');
            $table->foreignId('school_section_id')->nullable()->constrained('school_sections');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->json('data')->nullable();
            $table->foreignId('stage_id')->constrained('stages')->onDelete('cascade');
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->json('classroom_history')->nullable();
            // $table->json('grade_history')->nullable();

            $table->softDeletes(); // Add this line for soft deletes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
