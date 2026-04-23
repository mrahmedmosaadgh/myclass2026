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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->string('component_version')->nullable()->comment('Vue component version that created/last edited this exam');
            
            // JSON columns for flexible data - no schema updates needed
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();
            $table->json('page_options')->nullable();
            $table->json('header_options')->nullable();
            $table->json('footer_options')->nullable();
            $table->json('custom_fields')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for common queries
            $table->index('user_id');
            $table->index('slug');
            $table->index('component_version');
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            
            // Basic fields that are common to all question types
            $table->string('type')->nullable();
            $table->integer('marks')->default(1);
            $table->string('section')->nullable();
            
            // JSON columns for flexible question data - no schema updates needed
            $table->json('content')->nullable();
            $table->json('options')->nullable();
            $table->json('correct_answer')->nullable();
            $table->json('explanation')->nullable();
            $table->json('metadata')->nullable();
            $table->json('custom_fields')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for common queries
            $table->index('exam_id');
            $table->index('order');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exams');
    }
};
