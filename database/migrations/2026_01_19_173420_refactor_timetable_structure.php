<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add JSON columns to CST
        if (!Schema::hasColumn('classroom_subject_teachers', 'drafts')) {
            Schema::table('classroom_subject_teachers', function (Blueprint $table) {
                $table->json('drafts')->nullable()->comment('Stores draft schedules');
                $table->json('history')->nullable()->comment('Stores historical changes');
            });
        }

        // 2. Cleanup Schedules Table
        if (Schema::hasColumn('schedules', 'copy_id')) {
            // Prune non-active schedules first
            DB::table('schedules')
                ->join('schedule_copies', 'schedules.copy_id', '=', 'schedule_copies.id')
                ->where('schedule_copies.status', '!=', 'active')
                ->delete();
            
            // Also delete any schedules where 'active' is false (if mixed within a copy)
            if (Schema::hasColumn('schedules', 'active')) {
                DB::table('schedules')->where('active', false)->delete();
            }

            // 3. Drop Constraints and Columns from Schedules
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropForeign(['copy_id']);
                $table->dropColumn(['copy_id', 'active']);
            });
        }

        // 3.5 Drop Constraints from Dependent Tables
        if (Schema::hasColumn('weekly_plans', 'copy_id')) {
            Schema::table('weekly_plans', function (Blueprint $table) {
                // Check if index exists before dropping to avoid errors
                // We assume standard naming; simple dropColumn is usually safe if we don't strictly need to drop FK first individually 
                // but Laravel's dropColumn usually tries to drop FK constraint too if it knows about it.
                // However, safety first: explicit FK drop if possible, or try catch?
                // Standard Laravel convention:
                $table->dropForeign(['copy_id']); 
                $table->dropColumn('copy_id');
            });
        }

        if (Schema::hasColumn('schedule_dailies', 'schedule_copy_id')) {
            Schema::table('schedule_dailies', function (Blueprint $table) {
                $table->dropForeign(['schedule_copy_id']);
                $table->dropColumn('schedule_copy_id');
            });
        }

        // 4. Drop Schedule Copies Table
        Schema::dropIfExists('schedule_copies');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create schedule_copies table
        Schema::create('schedule_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('name', 50);
            $table->string('description')->nullable();
            $table->date('copy_date')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('cascade');
            $table->foreignId('week_number')->nullable();
            $table->enum('status', ['draft', 'pending', 'active', 'archived'])->default('draft');
            $table->timestamp('activated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('last_modified_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // Re-add columns to schedules
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('copy_id')->nullable()->constrained('schedule_copies')->onDelete('cascade');
            $table->boolean('active')->default(true);
        });

        // Remove columns from CST
        Schema::table('classroom_subject_teachers', function (Blueprint $table) {
            $table->dropColumn(['drafts', 'history']);
        });
    }
};
