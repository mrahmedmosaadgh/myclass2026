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
        Schema::table('menus', function (Blueprint $table) {
            $table->boolean('is_feature_flag')->default(false)->after('is_active');
            $table->string('feature_flag_key')->nullable()->after('is_feature_flag');
            $table->json('meta')->nullable()->after('feature_flag_key')->comment('Additional metadata like badges, descriptions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['is_feature_flag', 'feature_flag_key', 'meta']);
        });
    }
};
