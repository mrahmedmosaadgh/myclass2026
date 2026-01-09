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
            // V2 specific fields
            $table->string('v2_component')->nullable()->after('meta')
                ->comment('Specific V2 component to render');
            
            $table->boolean('requires_context')->default(false)->after('v2_component')
                ->comment('Requires school/tenant context');
            
            $table->string('role_specific')->nullable()->after('requires_context')
                ->comment('Specific role this menu belongs to: SuperSystem, SystemAdmin, SchoolAdmin, Teacher, Student, Parent');
            
            $table->boolean('v2_enabled')->default(false)->after('role_specific')
                ->comment('Enable this menu in V2 system');
            
            // Add indexes for performance
            $table->index('role_specific');
            $table->index('v2_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex(['role_specific']);
            $table->dropIndex(['v2_enabled']);
            $table->dropColumn([
                'v2_component',
                'requires_context',
                'role_specific',
                'v2_enabled'
            ]);
        });
    }
};
