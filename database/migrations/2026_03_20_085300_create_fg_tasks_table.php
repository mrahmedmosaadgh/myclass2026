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
        Schema::create('fg_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('domain_id')->nullable()->constrained('fg_domains')->nullOnDelete();
            $table->string('title', 255);
            $table->text('notes')->nullable();
            $table->tinyInteger('importance')->default(0);
            $table->enum('status', ['inbox', 'active', 'done', 'cancelled'])->default('inbox');
            $table->enum('source', ['manual', 'ai_vent', 'quick_capture'])->default('manual');
            $table->boolean('is_today')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('tags')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('version')->default(1);
            $table->enum('sync_status', ['synced', 'pending', 'conflict'])->default('synced');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'is_today']);
            $table->index('domain_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fg_tasks');
    }
};
