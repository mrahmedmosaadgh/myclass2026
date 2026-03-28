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
        Schema::create('presentation_backups', function (Blueprint $table) {
            $table->id();
            
            // Reference to original presentation
            $table->unsignedBigInteger('presentation_id');
            $table->foreign('presentation_id')->references('id')->on('presentations')->onDelete('cascade');
            
            // Backup data
            $table->json('backup_data'); // Complete presentation snapshot
            $table->text('backup_reason')->nullable(); // Manual, auto, before_delete, etc.
            
            // Metadata
            $table->string('backup_type')->default('manual'); // manual, auto, scheduled
            $table->integer('size_bytes')->nullable();
            
            // Timestamps
            $table->timestamp('backed_up_at')->useCurrent();
            $table->timestamp('expires_at')->nullable(); // Auto-cleanup old backups
            
            // Indexes
            $table->index(['presentation_id', 'backed_up_at']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentation_backups');
    }
};
