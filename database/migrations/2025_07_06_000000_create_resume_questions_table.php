<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resume_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Owner/creator
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('meta')->nullable(); // For extra config, tags, etc.
            $table->enum('type', ['text', 'audio', 'video', 'file'])->default('text');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('resume_questions');
    }
};
