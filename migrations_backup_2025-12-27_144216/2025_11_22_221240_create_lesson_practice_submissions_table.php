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
        Schema::create('lesson_practice_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_student_progress_id')->constrained('lesson_student_progress')->onDelete('cascade');
            
            $table->enum('submission_type', ['upload', 'drawing']);
            $table->string('file_path')->nullable(); // for uploads
            $table->text('drawing_data')->nullable(); // for drawing pad (base64 or JSON)
            $table->timestamp('submitted_at');
            
            $table->timestamps();
            
            // Index for querying submissions by progress
            $table->index('lesson_student_progress_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_practice_submissions');
    }
};
