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
        Schema::table('cr_category_mappings', function (Blueprint $table) {
            if (!Schema::hasColumn('cr_category_mappings', 'icon')) {
                $table->string('icon')->default('📊')->after('label');
            }
            if (!Schema::hasColumn('cr_category_mappings', 'color')) {
                $table->string('color')->default('blue')->after('icon');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cr_category_mappings', function (Blueprint $table) {
            $table->dropColumn(['icon', 'color']);
        });
    }
};
