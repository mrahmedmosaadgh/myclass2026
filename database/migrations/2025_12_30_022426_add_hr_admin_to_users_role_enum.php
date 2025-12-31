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
        // Add 'hr_admin' to the ENUM list
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('SuperAdmin', 'admin', 'supervisor', 'teacher', 'student', 'parent', 'user', 'guest', 'hr_admin') NOT NULL DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original ENUM list (Warning: if any users have 'hr_admin' role, this might fail or truncate)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('SuperAdmin', 'admin', 'supervisor', 'teacher', 'student', 'parent', 'user', 'guest') NOT NULL DEFAULT 'user'");
    }
};
