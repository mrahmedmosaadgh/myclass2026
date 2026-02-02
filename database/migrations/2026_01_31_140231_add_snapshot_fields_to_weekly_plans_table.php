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
        Schema::table('weekly_plans', function (Blueprint $table) {
            $table->unsignedTinyInteger('day_number')->nullable()->after('week_number');
            $table->unsignedTinyInteger('period_order')->nullable()->after('day_number');
            
            $table->foreignId('classroom_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_plans', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['teacher_id']);
            
            $table->dropColumn([
                'day_number',
                'period_order',
                'classroom_id',
                'subject_id',
                'teacher_id'
            ]);
        });
    }
};
