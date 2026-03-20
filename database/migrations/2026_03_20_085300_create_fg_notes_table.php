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
        Schema::create('fg_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('domain_id')->nullable()->constrained('fg_domains')->nullOnDelete();
            $table->text('body');
            $table->enum('source', ['manual', 'ai_vent', 'quick_capture'])->default('manual');
            $table->json('tags')->nullable();
            $table->integer('version')->default(1);
            $table->enum('sync_status', ['synced', 'pending', 'conflict'])->default('synced');
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
            $table->index('domain_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fg_notes');
    }
};
