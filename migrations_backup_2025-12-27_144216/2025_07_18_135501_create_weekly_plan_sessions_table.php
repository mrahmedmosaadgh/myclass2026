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
        Schema::create('weekly_plan_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_plan_id')->constrained('weekly_plans')->onDelete('cascade');
            $table->integer('session_index')->comment('Order within week (1, 2, 3...)');
            $table->string('period_code')->comment('Format: academic_year.semester.week.day');
            $table->enum('type', ['lesson', 'quiz', 'exam', 'extra', 'note']);
            $table->string('title');
            $table->json('data')->nullable()->comment('Flexible storage for materials, zoom links, homework, skill tags');
            $table->timestamps();
            
            // Indexes for efficient querying
            $table->index(['weekly_plan_id', 'session_index']);
            $table->index('period_code');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_plan_sessions');
    }
};