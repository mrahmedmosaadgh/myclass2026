<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resume_answer_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('answer_id')->constrained('resume_answers')->cascadeOnDelete();
            $table->string('bookmark_type')->default('favorite'); // favorite, important, reference
            $table->text('notes')->nullable();
            $table->timestamps();

            // Ensure one bookmark per user per answer per type
            $table->unique(['user_id', 'answer_id', 'bookmark_type']);
            
            // Add index for performance
            $table->index(['user_id', 'bookmark_type']);
            $table->index(['answer_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('resume_answer_bookmarks');
    }
};
