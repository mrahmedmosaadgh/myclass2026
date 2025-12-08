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
        Schema::create('schedule_dailies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('teacher_substitute_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('schedule_copy_id')->constrained('schedule_copies')->onDelete('cascade');
            $table->unsignedTinyInteger('day')->comment('1-5: Sunday to Thursday');
            $table->unsignedTinyInteger('week')->comment('1-52: Week number in year');
            $table->unsignedTinyInteger('semester')->comment('1-2: First or Second semester');
            $table->date('date');

            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_dailies');
    }
};


