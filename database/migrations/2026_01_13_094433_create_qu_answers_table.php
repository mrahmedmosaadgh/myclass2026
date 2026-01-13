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
        Schema::create('qu_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qu_attempt_id')->constrained()->onDelete('cascade');
            $table->foreignId('qu_question_id')->constrained()->onDelete('cascade');
            $table->json('selected_options')->nullable(); // ["A", "C"]
            $table->text('answer_text')->nullable();
            $table->integer('marks_obtained')->nullable();
            $table->timestamps();
            
            $table->unique(['qu_attempt_id', 'qu_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qu_answers');
    }
};
