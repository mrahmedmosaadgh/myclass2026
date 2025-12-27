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
        Schema::create('schedule_copies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');

            // Basic information
            $table->string('name', 50);
            $table->string('description')->nullable();
            $table->tinyInteger('active')->default(1);

            // Copy details
            $table->date('copy_date')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('cascade');
            $table->tinyInteger('week_number')->nullable();

            // Status tracking
            $table->enum('status', ['draft', 'pending', 'active', 'archived'])->default('draft');
            $table->timestamp('activated_at')->nullable();
            

            // Additional information
            $table->json('metadata')->nullable()->comment('Additional configurable data');
            $table->text('notes')->nullable();

            // Audit fields
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('last_modified_by')->nullable()->constrained('users');

            // Timestamps and soft delete
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['school_id', 'academic_year_id', 'semester_id']);
            $table->index(['active', 'status']);
            $table->index('copy_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_copies');
    }
};
