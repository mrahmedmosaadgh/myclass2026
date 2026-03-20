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
        Schema::create('fg_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('task_id')->constrained('fg_tasks')->cascadeOnDelete();
            $table->string('intention', 255)->nullable();
            $table->enum('energy_level', ['high', 'medium', 'low'])->nullable();
            $table->enum('status', ['active', 'completed', 'drifted'])->default('active');
            $table->enum('check_in_answer', ['on_track', 'drifted', 'done'])->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->integer('version')->default(1);
            $table->enum('sync_status', ['synced', 'pending', 'conflict'])->default('synced');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
            $table->index('task_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fg_sessions');
    }
};
