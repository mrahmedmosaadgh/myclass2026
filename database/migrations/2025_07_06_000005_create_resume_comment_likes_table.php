<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resume_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('comment_id')->constrained('resume_question_comments')->cascadeOnDelete();
            $table->timestamps();

            // Ensure one like per user per comment
            $table->unique(['user_id', 'comment_id']);
            
            // Add index for performance
            $table->index(['comment_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('resume_comment_likes');
    }
};
