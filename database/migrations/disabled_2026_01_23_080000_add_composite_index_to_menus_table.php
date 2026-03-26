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
        $existingIndexes = collect(DB::select("SHOW INDEX FROM menus"))->pluck('Key_name');

        if (!$existingIndexes->contains('menus_v2_composite_index')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->index(['v2_enabled', 'is_active', 'role_specific', 'parent_id', 'order'], 'menus_v2_composite_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex('menus_v2_composite_index');
        });
    }
};
