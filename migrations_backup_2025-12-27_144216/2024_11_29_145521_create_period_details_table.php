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
        Schema::create('period_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            // Basic information
            $table->tinyInteger('code');
            $table->tinyInteger('sequence');
            $table->string('name')->nullable();
            $table->tinyInteger('main')->default(1);
            // Copy details
            $table->tinyInteger('time_before')->nullable();
            $table->time('from')->nullable();
            $table->time('to')->nullable();
            $table->text('notes')->nullable();
                         // Timestamps and soft delete
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['school_id']);
            $table->index(['code']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('period_details');
    }
};
