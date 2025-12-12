<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_behaviors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_behaviors_mains_id')->constrained('student_behaviors_mains')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('attend')->nullable();
            $table->integer('points_plus')->nullable();
            $table->integer('points_minus')->nullable();
            // $table->text('points_details');
            $table->text('notes')->nullable();
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_behaviors');
    }
};
