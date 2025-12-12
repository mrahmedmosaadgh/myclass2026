<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('behaviors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('name'); // e.g. "Brought the book"
            $table->enum('type', ['positive', 'negative']);
            $table->integer('points');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            // ✅ Enforce uniqueness at the database level
            $table->unique(['school_id', 'year_id', 'name'], 'unique_behavior_per_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behaviors');
    }
};
