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
        Schema::table('quiz_session_participants', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->change();
            $table->string('nickname')->nullable()->after('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_session_participants', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable(false)->change();
            $table->dropColumn('nickname');
        });
    }
};
