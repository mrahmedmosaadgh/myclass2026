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
        DB::table('menus')->where('role_specific', 'SuperSystem')->update(['role_specific' => 'super_admin']);
        DB::table('menus')->where('role_specific', 'SystemAdmin')->update(['role_specific' => 'super_admin']);
        DB::table('menus')->where('role_specific', 'SchoolAdmin')->update(['role_specific' => 'admin']);
        DB::table('menus')->where('role_specific', 'Teacher')->update(['role_specific' => 'teacher']);
        DB::table('menus')->where('role_specific', 'Student')->update(['role_specific' => 'student']);
        DB::table('menus')->where('role_specific', 'Parent')->update(['role_specific' => 'parent']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional rollback logic, but usually we don't want to go back to mixed keys
    }
};
