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
        if (Schema::hasTable('presentations')) {
            return;
        }

        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            
            // Basic presentation info
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            
            // Category for organization
            $table->unsignedBigInteger('category_id')->nullable();
            
            // User ownership
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // School/Classroom context
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->unsignedBigInteger('classroom_id')->nullable();
            $table->foreign('classroom_id')->references('id')->on('classrooms')->onDelete('set null');
            
            // Presentation data (JSON)
            $table->json('slides');
            $table->integer('current_slide_index')->default(0);
            $table->boolean('use_phases')->default(false);
            $table->boolean('has_initialized_phases')->default(false);
            
            // Metadata
            $table->json('metadata')->nullable(); // size, slide_count, version, etc.
            
            // Status and visibility
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_public')->default(false);
            $table->boolean('is_template')->default(false);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['category_id']);
            $table->index(['school_id']);
            $table->index(['classroom_id']);
            $table->index(['title']);
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentations');
    }
};
