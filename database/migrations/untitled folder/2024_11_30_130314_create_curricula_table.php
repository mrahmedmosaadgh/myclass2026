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
        Schema::create('curricula', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column with index

            $table->date('edit_lock_date')->nullable()->comment('After this date, lesson plans become read-only');

            // Indexes for better performance
            $table->index(['school_id', 'grade_id', 'subject_id']);

            // Unique constraint to prevent duplicate curricula
            $table->unique(['school_id', 'grade_id', 'subject_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curricula');
    }
};
