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
        Schema::table('lesson_presentations', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('grade_id'); // for lesson sequencing
            $table->unsignedBigInteger('quiz_id')->nullable()->after('order'); // link to quiz system (future)
            $table->boolean('is_active')->default(true)->after('quiz_id');
            
            // Index for ordering lessons
            $table->index(['grade_id', 'subject_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_presentations', function (Blueprint $table) {
            $table->dropIndex(['grade_id', 'subject_id', 'order']);
            $table->dropColumn(['order', 'quiz_id', 'is_active']);
        });
    }
};
