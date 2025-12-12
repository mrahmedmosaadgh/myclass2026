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
        Schema::create('lesson_presentation_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_presentation_id')->constrained('lesson_presentations')->onDelete('cascade');
            $table->string('section');
            $table->string('slide_type');
            $table->json('slide_content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_presentation_slides');
    }
};
