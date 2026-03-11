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
        Schema::create('bm_assessment_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bm_assessment_id')->constrained('bm_assessments')->cascadeOnDelete();
            $table->foreignId('bm_question_id')->constrained('bm_questions')->cascadeOnDelete();
            $table->string('user_answer')->nullable();
            $table->string('correct_answer');
            $table->boolean('is_correct');
            $table->integer('time_taken_ms');
            $table->tinyInteger('difficulty_level');
            $table->string('domain');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bm_assessment_responses');
    }
};
