<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates student avatars table for BM2 gamification.
     */
    public function up(): void
    {
        Schema::create('bm2_student_avatars', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to student
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            
            // Avatar customization options (JSON format)
            // Example: {
            //   "base": "wizard",
            //   "hair_color": "brown",
            //   "shirt_color": "blue",
            //   "accessory": "glasses",
            //   "background": "stars"
            // }
            $table->json('avatar_config');
            
            // Unlock status
            $table->boolean('is_unlocked')->default(true);
            $table->string('unlocked_by')->nullable(); // How they unlocked it
            
            // Is this their active avatar?
            $table->boolean('is_active')->default(false);
            
            $table->timestamps();
            
            // Only one active avatar per student
            $table->unique(['student_id', 'is_active']);
            
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bm2_student_avatars');
    }
};
