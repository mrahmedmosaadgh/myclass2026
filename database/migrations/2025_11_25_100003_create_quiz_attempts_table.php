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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('quiz_id')->nullable()->comment('Optional reference to a quiz collection');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_questions');
            $table->integer('correct_answers')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->json('metadata')->nullable()->comment('Additional quiz attempt metadata');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
