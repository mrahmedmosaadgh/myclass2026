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
    $table->foreignId('curriculum_version_id')->constrained('curriculum_versions')->onDelete('cascade');
    // Link to topic (nullable to allow flat structure)
    $table->foreignId('topic_id')->nullable()->constrained('curriculum_topics')->onDelete('cascade');

    // $table->tinyInteger('selected')->default(1);
    $table->string('lesson_number');
    $table->string('lesson_title');
    $table->integer('page_number')->nullable();
    
    // Educational Content
    $table->text('description')->nullable();
    $table->string('standard')->nullable();
    $table->string('strand')->nullable();
    $table->text('content')->nullable();
    $table->text('activities')->nullable();
    $table->text('assignment')->nullable();
    $table->text('assessment')->nullable();
    
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
