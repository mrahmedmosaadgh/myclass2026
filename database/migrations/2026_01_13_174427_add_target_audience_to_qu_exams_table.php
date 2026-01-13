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
        Schema::table('qu_exams', function (Blueprint $table) {
            //
            $table->json('target_audience')->nullable()->after('settings')
                ->comment('Defines who can see/take the exam. NULL = Public/Everyone.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qu_exams', function (Blueprint $table) {
            $table->dropColumn('target_audience');
        });
    }
};
