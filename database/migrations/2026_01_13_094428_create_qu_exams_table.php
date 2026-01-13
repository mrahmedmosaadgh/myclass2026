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
        Schema::create('qu_exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('exam_type', ['practice', 'quiz', 'midterm', 'final', 'survey'])
                ->default('quiz');
            $table->string('custom_group', 100)->nullable();
            $table->integer('max_attempts')->nullable()
                ->comment('NULL = unlimited attempts');
            $table->enum('mark_calculation_method', ['last', 'best', 'average'])
                ->default('last');
            $table->decimal('passing_score', 5, 2)->nullable()
                ->comment('Minimum score to pass, NULL = no passing requirement');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->integer('duration_minutes');
            $table->integer('total_marks');
            $table->json('bloom_distribution')->nullable(); // {"remember": 20, "understand": 30, ...}
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_published')->default(false);
            $table->dateTime('start_date')->nullable()
                ->comment('When exam becomes available');
            $table->dateTime('end_date')->nullable()
                ->comment('Submission deadline');
            $table->enum('publish_results_timing', ['immediate', 'after_end', 'manual'])
                ->default('immediate');
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qu_exams');
    }
};