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
        Schema::create('chatbot_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('virtual_id')->nullable();
            $table->string('email')->nullable();
            $table->enum('type', ['bug', 'idea', 'question'])->default('question');
            $table->enum('status', ['new', 'replied', 'closed', 'waiting'])->default('new');
            $table->enum('mode', ['manual', 'hybrid', 'ai'])->default('manual');
            $table->string('url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_conversations');
    }
};
