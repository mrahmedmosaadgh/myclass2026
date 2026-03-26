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
        if (!Schema::hasTable('skillables')) {
            Schema::create('skillables', function (Blueprint $table) {
                $table->id();
                $table->foreignId('skill_id')->constrained('skills')->onDelete('cascade');
                $table->morphs('skillable');
                $table->timestamps();

                // Prevent duplicate skill assignments
                $table->unique(['skill_id', 'skillable_id', 'skillable_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skillables');
    }
};
