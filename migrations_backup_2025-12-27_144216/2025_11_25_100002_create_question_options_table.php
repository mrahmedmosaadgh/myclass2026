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
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->string('option_key')->comment('Option identifier: A, B, C, D, etc.');
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->decimal('distractor_strength', 5, 2)->nullable()->comment('Analytics metric for incorrect options');
            $table->integer('order_index')->default(0)->comment('Display order of option');
            $table->timestamps();
            
            // Index on question_id for fast lookups
            $table->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
