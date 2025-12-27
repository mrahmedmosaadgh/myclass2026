<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resume_answer_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('answer_id')->constrained('resume_answers')->cascadeOnDelete();
            $table->tinyInteger('rating')->unsigned()->comment('Rating from 1 to 5');
            $table->text('review_comment')->nullable();
            $table->timestamps();

            // Ensure one rating per user per answer
            $table->unique(['user_id', 'answer_id']);
            
            // Add index for performance
            $table->index(['answer_id', 'rating']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('resume_answer_ratings');
    }
};
