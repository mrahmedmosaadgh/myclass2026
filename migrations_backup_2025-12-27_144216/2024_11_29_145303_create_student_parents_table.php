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
        Schema::create('student_parents', function (Blueprint $table) {
            $table->id();
            $table->string('t_id')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->json('data')->nullable();
            // $table->tinyInteger('active')->default(1);
            $table->tinyInteger('report')->default(1);

            // $table->string('email')->unique();
            // $table->string('phone');
            // $table->string('relation')->nullable();
            // $table->string('occupation')->nullable();
            $table->softDeletes(); // Add this line for soft deletes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_parents');
    }
};
