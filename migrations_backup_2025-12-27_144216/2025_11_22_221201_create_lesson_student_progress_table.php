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
        Schema::create('lesson_student_progress', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('lesson_presentation_id')->constrained('lesson_presentations')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('opened_by_teacher_id')->nullable()->constrained('teachers')->onDelete('set null');
            
            // Status Fields (indexed for fast queries)
            $table->enum('status', [
                'locked', 
                'opened', 
                'learning', 
                'practice_pending', 
                'practice_submitted', 
                'quiz_unlocked', 
                'completed', 
                'failed'
            ])->default('locked')->index();
            
            $table->enum('color_status', [
                'gray', 
                'light_blue', 
                'blue', 
                'purple', 
                'green', 
                'yellow', 
                'dark_yellow', 
                'orange', 
                'red'
            ])->default('gray')->index();
            
            // Learn Stage
            $table->timestamp('learn_completed_at')->nullable();
            
            // Practice Stage (core fields for queries)
            $table->integer('practice_score')->nullable()->index(); // 0-10
            $table->timestamp('practice_submitted_at')->nullable();
            $table->timestamp('practice_graded_at')->nullable();
            
            // Quiz Stage (core fields for queries)
            $table->integer('quiz_attempts')->default(0);
            $table->integer('quiz_best_score')->nullable(); // 0-100
            $table->boolean('quiz_passed')->default(false)->index();
            
            // Teacher Controls
            $table->boolean('force_passed')->default(false);
            $table->timestamp('opened_at')->nullable();
            
            // JSON Fields (extensible data)
            $table->json('practice_data')->nullable(); // submission_type, file_path, drawing_data, teacher_feedback, etc.
            $table->json('quiz_data')->nullable(); // attempts_detail, extra_attempts_granted, quiz_versions_used
            $table->json('metadata')->nullable(); // time_spent, teacher_notes, flags, etc.
            
            $table->timestamps();
            
            // Composite indexes for common queries
            $table->index(['lesson_presentation_id', 'student_id']);
            $table->index(['student_id', 'status']);
            $table->index(['lesson_presentation_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_student_progress');
    }
};
