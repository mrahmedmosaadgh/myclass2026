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
            if (!Schema::hasColumn('weekly_plans', 'day_number')) {
                $table->unsignedTinyInteger('day_number')->nullable()->after('week_number');
            }
            if (!Schema::hasColumn('weekly_plans', 'period_order')) {
                $table->unsignedTinyInteger('period_order')->nullable()->after('day_number');
            }
            if (!Schema::hasColumn('weekly_plans', 'classroom_id')) {
                $table->foreignId('classroom_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('weekly_plans', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('weekly_plans', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->constrained()->onDelete('cascade');
            }
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
