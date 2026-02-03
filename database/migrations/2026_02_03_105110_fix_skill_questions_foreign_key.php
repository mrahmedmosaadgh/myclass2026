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
        // Drop the skill_practice_answers table if it exists
        Schema::dropIfExists('skill_practice_answers');
        
        // Recreate the skill_practice_answers table with correct foreign key
        Schema::create('skill_practice_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('skill_question_id');
            $table->text('user_answer');
            $table->boolean('is_correct')->default(false);
            $table->integer('time_taken_ms')->default(0);
            $table->integer('difficulty_at_time')->default(0);
            $table->integer('score_change')->default(0);
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('skill_practice_sessions')->onDelete('cascade');
            $table->foreign('skill_question_id')->references('id')->on('skill_questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_practice_answers');
    }
};