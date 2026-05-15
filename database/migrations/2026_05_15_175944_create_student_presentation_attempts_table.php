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
        Schema::create('student_presentation_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presentation_id')->constrained('user_presentations')->onDelete('cascade');
            $table->string('student_identifier');
            $table->json('quiz_attempts');
            $table->integer('total_score')->default(0);
            $table->integer('total_questions')->default(0);
            $table->timestamp('completed_at')->useCurrent();

            $table->index('presentation_id');
            $table->index('student_identifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_presentation_attempts');
    }
};
