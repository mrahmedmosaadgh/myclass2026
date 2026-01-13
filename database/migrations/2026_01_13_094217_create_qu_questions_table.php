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
        Schema::create('qu_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('topic_id')->nullable()->constrained('curriculum_topics')->onDelete('set null');
            $table->text('question_text');
            $table->enum('question_type', ['mcq', 'true_false', 'short', 'long']);
            $table->json('options')->nullable(); // {"A": "Text", "B": "Text"}
            $table->json('correct_answer')->nullable(); // ["A"] or ["A", "C"]
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->enum('bloom_level', [
                'remember', 'understand', 'apply', 'analyze', 'evaluate', 'create'
            ])->nullable();
            $table->integer('marks')->default(1);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['subject_id', 'topic_id', 'difficulty', 'bloom_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qu_questions');
    }
};
