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
        Schema::create('lesson_presentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
            $table->integer('order')->default(0); // for lesson sequencing
            $table->unsignedBigInteger('quiz_id')->nullable(); // link to quiz system (future)
            $table->boolean('is_active')->default(true); // for lesson sequencing
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('sections')->nullable();
            $table->timestamps();
            
            // Index for ordering lessons
            $table->index(['grade_id', 'subject_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_presentations');
    }
};