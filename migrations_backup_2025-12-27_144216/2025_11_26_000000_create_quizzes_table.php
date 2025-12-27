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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['draft', 'active', 'archived'])->default('active');
            $table->integer('time_limit_minutes')->nullable();
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('allow_review')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['school_id', 'status']);
            $table->index('created_by_id');
        });

        // Pivot table for quiz questions
        Schema::create('quiz_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->integer('order_index')->default(0);
            $table->timestamps();
            
            $table->unique(['quiz_id', 'question_id']);
            $table->index('order_index');
        });
        
        // Add foreign key constraints to existing tables
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->foreign('quiz_id')->references('id')->on('quizzes')->nullOnDelete();
        });
        
        Schema::table('lesson_presentations', function (Blueprint $table) {
            $table->foreign('quiz_id')->references('id')->on('quizzes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign keys from existing tables
        Schema::table('lesson_presentations', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
        });
        
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
        });
        
        Schema::dropIfExists('quiz_question');
        Schema::dropIfExists('quizzes');
    }
};
