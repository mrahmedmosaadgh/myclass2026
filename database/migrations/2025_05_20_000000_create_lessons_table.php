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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_id')->constrained('curricula')->onDelete('cascade');
            $table->integer('lesson_number');
            $table->string('title');
            $table->integer('page_number')->nullable();
            $table->integer('position')->default(0);
            $table->text('description')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            
            // Create a unique index on curriculum_id and lesson_number
            // to ensure lesson numbers are unique within a curriculum
            $table->unique(['curriculum_id', 'lesson_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
