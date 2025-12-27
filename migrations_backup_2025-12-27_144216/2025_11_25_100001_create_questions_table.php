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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            
            // Question type and content
            $table->foreignId('question_type_id')->constrained('question_types')->onDelete('cascade');
            $table->text('question_text');
            
            // Curriculum alignment fields
            $table->foreignId('grade_id')->nullable()->constrained('grades')->onDelete('set null');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->unsignedBigInteger('topic_id')->nullable();
            
            // Cognitive model fields
            $table->tinyInteger('bloom_level')->nullable()->comment('Bloom taxonomy level 1-6');
            $table->tinyInteger('difficulty_level')->nullable()->comment('Difficulty level 1-5');
            $table->integer('estimated_time_sec')->nullable()->comment('Estimated time to complete in seconds');
            
            // Analytics fields
            $table->integer('usage_count')->default(0)->comment('Number of times question has been used');
            $table->decimal('avg_success_rate', 5, 2)->nullable()->comment('Average success rate percentage');
            $table->decimal('discrimination_index', 5, 2)->nullable()->comment('Statistical measure of question quality');
            
            // Metadata fields
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['draft', 'active', 'archived', 'review'])->default('draft');
            
            // JSON columns for hints and explanation
            $table->json('hints')->nullable()->comment('Array of hint strings');
            $table->json('explanation')->nullable()->comment('Object with text and revealed_after_attempt fields');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('question_type_id');
            $table->index(['grade_id', 'subject_id']);
            $table->index('status');
            $table->index('author_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
