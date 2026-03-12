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
        Schema::create('curriculum_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_id')->constrained('curricula')->onDelete('cascade');
            $table->string('title'); // e.g., "2025/2026 Edition"
            $table->string('academic_year')->nullable(); // Optional string to map to years
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->integer('version_number')->default(1);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['curriculum_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_versions');
    }
};
