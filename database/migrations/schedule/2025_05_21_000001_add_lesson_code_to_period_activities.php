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
        Schema::table('period_activities', function (Blueprint $table) {
            $table->string('lesson_code', 20)
                ->nullable()
                ->comment('Links to weekly_plans.code if exists, format like 12.1.1.1')
                ->after('event_id');
            
            $table->index('lesson_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('period_activities', function (Blueprint $table) {
            $table->dropIndex(['lesson_code']);
            $table->dropColumn('lesson_code');
        });
    }
};
