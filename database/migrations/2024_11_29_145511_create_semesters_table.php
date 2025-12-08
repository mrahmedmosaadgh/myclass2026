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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('semester_number'); // business meaning (1st term, 2nd term)
            $table->tinyInteger('total_weeks')->nullable(); // total weeks in semester

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->json('data')->nullable();
            $table->tinyInteger('active')->default(1);

            $table->softDeletes();
            $table->timestamps();

            // Natural key: ensures one semester per number per academic year
            $table->unique(['academic_year_id', 'semester_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};

