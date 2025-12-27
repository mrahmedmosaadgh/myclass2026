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
        Schema::create('pomodoro_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['work', 'break'])->default('work');
            $table->integer('duration')->default(25)->comment('Duration in minutes');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->text('notes')->nullable()->comment('What was accomplished');
            $table->string('status')->default('completed')->comment('completed, interrupted, extended');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pomodoro_sessions');
    }
};
