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
        Schema::create('skill_questions', function (Blueprint $table) {
            $table->id(); // Add the id column
            $table->unsignedBigInteger('skill_id');
            $table->unsignedBigInteger('qu_question_id');
            $table->integer('difficulty_level')->default(5);
            $table->text('explanation')->nullable();
            $table->timestamps();

            $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade');
            $table->foreign('qu_question_id')->references('id')->on('qu_questions')->onDelete('cascade');
            
            // Add unique constraint to prevent duplicate associations
            $table->unique(['skill_id', 'qu_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_questions');
    }
};
