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
        Schema::create('curriculum_lessons', function (Blueprint $table) {
$table->id();
    // Link to topic instead of duplicating topic strings
    $table->foreignId('topic_id')->constrained('curriculum_topics')->onDelete('cascade');
    
    // School ID might still be needed here if you want school-specific lesson overrides,
    // otherwise, it usually belongs to the Curriculum or Topic level.
    $table->foreignId('school_id')->constrained()->onDelete('cascade');

    // $table->tinyInteger('selected')->default(1);
    $table->string('lesson_number');
    $table->string('lesson_title');
    $table->integer('page_number')->nullable();
    
    // Educational Content
    $table->text('description')->nullable();
    $table->string('standard')->nullable();
    $table->string('strand')->nullable();
    $table->text('content')->nullable();
    $table->text('skill')->nullable();
    $table->text('activities')->nullable();
    $table->text('assignment')->nullable();
    $table->text('assessment')->nullable();
    $table->text('objective')->nullable();
    
    $table->json('data')->nullable();
    $table->enum('type', ['main', 'revision', 'quiz', 'project', 'extra'])->default('main');
    $table->timestamps();

  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_lessons');
    }
};
