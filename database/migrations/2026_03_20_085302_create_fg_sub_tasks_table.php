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
        Schema::create('fg_sub_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('task_id')->constrained('fg_tasks')->cascadeOnDelete();
            $table->string('title', 255);
            $table->boolean('is_done')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('version')->default(1);
            $table->enum('sync_status', ['synced', 'pending', 'conflict'])->default('synced');
            $table->softDeletes();
            $table->timestamps();

            $table->index('task_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fg_sub_tasks');
    }
};
