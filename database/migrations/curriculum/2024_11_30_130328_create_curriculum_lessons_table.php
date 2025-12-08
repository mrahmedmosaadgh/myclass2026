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
        Schema::create('curriculum_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('curriculum_id')->constrained('curricula')->onDelete('cascade');
            $table->tinyInteger('selected')->default(1)->comment('0=not selected, 1=selected');
            $table->string('topic_number');
            $table->string('topic_title');
            $table->string('lesson_number');
            $table->string('lesson_title');
            $table->integer('page_number')->nullable();
            $table->text('description')->nullable();
            $table->string('standard')->nullable();
            $table->string('strand')->nullable();
            $table->text('content')->nullable();
            $table->text('skill')->nullable();
            $table->text('activities')->nullable();
            $table->text('assignment')->nullable();
            $table->text('assessment')->nullable();
            $table->text('notes_admin')->nullable();
            $table->text('notes_teacher')->nullable();
            $table->text('objective')->nullable();
            $table->json('data')->nullable();
            $table->enum('type', ['main', 'revision', 'quiz', 'project', 'extra'])->default('main');
            $table->timestamps();

            // Indexes for better performance
            $table->index(['school_id', 'curriculum_id']);
            $table->index(['school_id', 'selected']);
            $table->index(['school_id', 'type']);

            // Unique constraint for lesson ordering
            $table->unique(['curriculum_id', 'topic_number', 'lesson_number'], 'curriculum_lessons_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_lessons');
    }
};
