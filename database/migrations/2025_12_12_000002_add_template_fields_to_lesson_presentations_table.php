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
            $table->foreignId('lesson_plan_template_id')->nullable()->constrained('lesson_plan_templates')->onDelete('set null');
            $table->json('template_snapshot')->nullable();
            $table->boolean('is_template_applied')->default(false);
            $table->index('lesson_plan_template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_presentations', function (Blueprint $table) {
            if (Schema::hasColumn('lesson_presentations', 'lesson_plan_template_id')) {
                $table->dropConstrainedForeignId('lesson_plan_template_id');
            }
            if (Schema::hasColumn('lesson_presentations', 'template_snapshot')) {
                $table->dropColumn('template_snapshot');
            }
            if (Schema::hasColumn('lesson_presentations', 'is_template_applied')) {
                $table->dropColumn('is_template_applied');
            }
        });
    }
};
