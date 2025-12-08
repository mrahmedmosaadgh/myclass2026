<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resume_answer_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('answer_id')->constrained('resume_answers')->cascadeOnDelete();
            $table->timestamps();

            // Ensure one like per user per answer
            $table->unique(['user_id', 'answer_id']);
            
            // Add index for performance
            $table->index(['answer_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('resume_answer_likes');
    }
};
