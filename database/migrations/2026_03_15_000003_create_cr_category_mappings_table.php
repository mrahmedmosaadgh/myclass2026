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
        Schema::create('cr_category_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            // Optional scoping by grade / subject
            $table->foreignId('grade_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->string('key', 100); // e.g., 'book_participation', 'homework', 'behavior'
            $table->string('label'); // Display label
             
                  $table->string('icon')->default('📊') ;
            $table->string('color')->default('blue') ;
            $table->string('type')->default('numeric'); // numeric, text, json
            $table->unsignedTinyInteger('max_value')->default(5); // Maximum possible score
            $table->unsignedTinyInteger('passing_value')->nullable(); // Optional passing threshold
            $table->unsignedTinyInteger('default_value')->default(5); // Default value when initialized
            $table->unsignedTinyInteger('sort_order')->default(0); // Display order
            $table->boolean('active')->default(true); // Can be deactivated without deleting
            $table->timestamps();

            // Unique constraint per school + scope (school / grade / subject)
            $table->unique(
                ['school_id', 'key', 'grade_id', 'subject_id'],
                'unique_cr_category_mapping_scope'
            );

            // Index for active lookups
            $table->index(['school_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cr_category_mappings');
    }
};
