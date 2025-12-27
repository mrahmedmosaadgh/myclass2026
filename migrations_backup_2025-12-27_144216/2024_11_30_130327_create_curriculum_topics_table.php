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
        Schema::create('curriculum_topics', function (Blueprint $table) {
          $table->id();
    $table->foreignId('curriculum_id')->constrained()->onDelete('cascade');
    $table->string('number'); // e.g., "Topic 1"
    $table->string('title');
    $table->text('description')->nullable();
    // $table->integer('sort_order')->default(0); 
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_topics');
    }
};
