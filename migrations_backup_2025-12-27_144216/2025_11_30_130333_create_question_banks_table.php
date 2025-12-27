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
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
            $table->foreignId('curriculum_id')->nullable()->constrained('curricula')->onDelete('cascade');
            $table->foreignId('curriculum_lessons_id')->nullable()->constrained('curriculum_lessons')->onDelete('cascade');
            $table->string('title')->comment('Question head/title');
            $table->text('body')->comment('Question details/content');
            $table->json('options')->nullable()->comment('Multiple-choice options: {A: "option1", B: "option2", ...}');
            $table->string('correct_answer')->nullable()->comment('Correct answer key (A, B, C, etc.)');
            $table->json('resources')->nullable()->comment('Images, PDFs, attachments');
            $table->enum('type', ['mcq', 'true_false', 'fill_blank', 'essay', 'short_answer'])->default('mcq');
            $table->integer('score')->default(1);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->text('notes')->nullable();
            $table->string('tags')->nullable()->comment('Comma-separated tags');
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->string('author')->nullable();
            $table->string('source')->nullable();
            $table->json('metadata')->nullable()->comment('Additional question metadata');
            $table->text('notes_admin')->nullable();
            $table->text('notes_teacher')->nullable();
            $table->json('explanation')->nullable()->comment('Answer explanation and reasoning');
            $table->json('question_data')->nullable()->comment('Additional structured question data');
            $table->foreignId('created_by_id')->constrained('teachers')->onDelete('cascade');
            $table->timestamps();

            // Indexes for better performance
            $table->index(['school_id', 'subject_id']);
            $table->index(['school_id', 'type']);
            $table->index(['school_id', 'difficulty']);
            $table->index(['school_id', 'status']);
            $table->index(['created_by_id']);

            // Full-text search index for question content
            $table->fullText(['title', 'body', 'tags']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_banks');
    }
};
