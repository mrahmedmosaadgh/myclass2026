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
        Schema::create('semester_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('semester_number');
        
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->json('data')->nullable();
            // ALTER TABLE `semesters` ADD `active` TINYINT NOT NULL DEFAULT '1' AFTER `data`;
            $table->tinyInteger('active')->default(1);

            $table->softDeletes(); // Add this line for soft deletes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semester_tests');
    }
};
