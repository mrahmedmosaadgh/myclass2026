<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates badges table for BM2 gamification system.
     */
    public function up(): void
    {
        Schema::create('bm2_badges', function (Blueprint $table) {
            $table->id();
            
            // Badge details
            $table->string('name');
            $table->text('description');
            $table->string('icon_url')->nullable();
            $table->enum('category', ['achievement', 'milestone', 'skill_mastery', 'speed', 'consistency']);
            
            // Earning criteria (JSON format)
            // Example: {"type": "score_threshold", "value": 90, "context": "single_assessment"}
            // Example: {"type": "streak", "value": 7, "context": "consecutive_days"}
            $table->json('earning_criteria');
            
            // Points value
            $table->integer('points_value')->default(10);
            
            // Rarity
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary'])->default('common');
            
            // Display order
            $table->integer('display_order')->default(0);
            
            // Status
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index('category');
            $table->index('rarity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bm2_badges');
    }
};
