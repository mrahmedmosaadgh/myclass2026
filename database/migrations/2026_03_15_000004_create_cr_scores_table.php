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
        Schema::create('cr_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_period_id')->constrained('cr_student_periods')->cascadeOnDelete();
            $table->foreignId('mapping_id')->constrained('cr_category_mappings')->cascadeOnDelete();
            $table->decimal('numeric_value', 8, 2)->nullable(); // For numeric scores
            $table->text('text_value')->nullable(); // For text-based feedback
            $table->json('json_value')->nullable(); // For complex data structures
            $table->timestamps();

            // Unique constraint to prevent duplicate scores for same student-period × category
            $table->unique(['student_period_id', 'mapping_id'], 'unique_cr_score');

            // Index for category-based reporting
            $table->index('mapping_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cr_scores');
    }
};
