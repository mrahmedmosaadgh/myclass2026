<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Clean up duplicated menus (based on route)
        // Keep the one with the highest ID (newest) or v2_enabled
        $duplicates = DB::table('menus')
            ->select('route', DB::raw('count(*) as count'))
            ->whereNotNull('route')
            ->groupBy('route')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $keepId = DB::table('menus')
                ->where('route', $duplicate->route)
                ->orderByDesc('v2_enabled') // Prefer V2 enabled
                ->orderByDesc('id')       // Then newest
                ->value('id');

            DB::table('menus')
                ->where('route', $duplicate->route)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        // 2. Clean up duplicated menus (based on label + parent_id for non-routed items)
        $duplicatesLabels = DB::table('menus')
            ->select('label', 'parent_id', DB::raw('count(*) as count'))
            // ->whereNull('route') // Only for non-routed parents usually
            ->groupBy('label', 'parent_id')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicatesLabels as $duplicate) {
            $keepId = DB::table('menus')
                ->where('label', $duplicate->label)
                ->where('parent_id', $duplicate->parent_id)
                ->orderByDesc('v2_enabled')
                ->orderByDesc('id')
                ->value('id');

            DB::table('menus')
                ->where('label', $duplicate->label)
                ->where('parent_id', $duplicate->parent_id)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('menus', function (Blueprint $table) {
            // Add unique constraint on route (nullable, so multiple nulls allowed usually, but let's be strict for named routes)
            // Note: In some SQL dialects, multiple NULLs are allowed in UNIQUE.
            // We will add unique on route.
            $table->unique('route', 'menus_route_unique');

            // Add unique constraint on label + parent_id (for folder structure uniqueness)
            $table->unique(['label', 'parent_id'], 'menus_label_parent_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropUnique('menus_route_unique');
            $table->dropUnique('menus_label_parent_unique');
        });
    }
};
