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
            // Drop valid individual indexes if they exist to avoid redundancy
            // We verify existence by try/catch or just attempting drop (Laravel handles check if explicit?) 
            // Better to just add the new one. Use a custom name.
            
            // Composite index for efficient retrieval of menus by role and hierarchy
            // Access pattern: WHERE v2_enabled=1 AND is_active=1 AND role_specific=? AND parent_id=? ORDER BY order
            $table->index(['v2_enabled', 'is_active', 'role_specific', 'parent_id', 'order'], 'menus_v2_composite_index');
        });
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
