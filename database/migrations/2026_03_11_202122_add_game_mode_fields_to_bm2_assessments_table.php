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
        Schema::table('bm2_assessments', function (Blueprint $table) {
            $table->string('game_mode')->default('normal')->after('type');
            $table->json('game_settings')->nullable()->after('game_mode');
            $table->json('game_stats')->nullable()->after('game_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bm2_assessments', function (Blueprint $table) {
            $table->dropColumn(['game_mode', 'game_settings', 'game_stats']);
        });
    }
};
