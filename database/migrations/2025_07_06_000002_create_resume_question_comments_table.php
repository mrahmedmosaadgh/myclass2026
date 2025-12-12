<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resume_question_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('resume_questions')->cascadeOnDelete();
            $table->foreignId('answer_id')->nullable()->constrained('resume_answers')->cascadeOnDelete();
            $table->text('comment')->nullable();
            $table->string('media_type')->nullable();
            $table->string('media_path')->nullable();
            $table->boolean('is_public')->default(true);
            $table->foreignId('parent_id')->nullable()->constrained('resume_question_comments')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('resume_question_comments');
    }
};
