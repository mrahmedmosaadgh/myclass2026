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
        Schema::table('cr_student_periods', function (Blueprint $table) {
            // Change attendance_status to allow NULL and remove default
            DB::statement("ALTER TABLE cr_student_periods MODIFY COLUMN attendance_status ENUM('present', 'absent', 'late', 'left_early', '') NULL DEFAULT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cr_student_periods', function (Blueprint $table) {
            // Revert to NOT NULL with default
            DB::statement("ALTER TABLE cr_student_periods MODIFY COLUMN attendance_status ENUM('present', 'absent', 'late', 'left_early') NOT NULL DEFAULT 'present'");
        });
    }
};
