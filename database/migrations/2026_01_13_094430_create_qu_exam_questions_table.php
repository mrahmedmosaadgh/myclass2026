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
        Schema::create('qu_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qu_exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('qu_question_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->unique(['qu_exam_id', 'qu_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qu_exam_questions');
    }
};
