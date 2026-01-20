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
        // 1. Add JSON columns to classroom_subject_teachers
        Schema::table('classroom_subject_teachers', function (Blueprint $table) {
            if (!Schema::hasColumn('classroom_subject_teachers', 'drafts')) {
                $table->json('drafts')->nullable()->comment('Stores draft schedules')->after('data');
            }
            if (!Schema::hasColumn('classroom_subject_teachers', 'history')) {
                $table->json('history')->nullable()->comment('Stores historical changes')->after('drafts');
            }
        });

        // 2. Prune schedules table to only keep active rows
        // We assume 'active' = true means it's the live schedule. 
        // We delete everything else.
        if (Schema::hasColumn('schedules', 'active')) {
             DB::table('schedules')->where('active', false)->delete();
        }

        // 3. Drop Foreign Key and Column from schedules
        Schema::table('schedules', function (Blueprint $table) {
            // Drop foreign key first (name might vary, usually schedules_copy_id_foreign)
            // We'll check if the column exists first
            if (Schema::hasColumn('schedules', 'copy_id')) {
                // Determine foreign key name convention or use array syntax which Laravel resolves
                try {
                     $table->dropForeign(['copy_id']); 
                } catch (\Exception $e) {
                    // Ignore if FK doesn't exist
                }
                $table->dropColumn('copy_id');
            }
            
            // Drop active column as all remaining rows are active implies active=true
            if (Schema::hasColumn('schedules', 'active')) {
                $table->dropColumn('active');
            }
        });

        // 4. Drop schedule_copies table
        Schema::dropIfExists('schedule_copies');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-create schedule_copies table
        Schema::create('schedule_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('name', 50);
            $table->string('description')->nullable();
            $table->date('copy_date')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('cascade');
            $table->enum('status', ['draft', 'pending', 'active', 'archived'])->default('draft');
            $table->timestamp('activated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('last_modified_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Add columns back to schedules
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('copy_id')->nullable()->constrained('schedule_copies')->onDelete('cascade');
            $table->boolean('active')->default(true);
        });

        // 3. Drop JSON columns from classroom_subject_teachers
        Schema::table('classroom_subject_teachers', function (Blueprint $table) {
            $table->dropColumn(['drafts', 'history']);
        });
    }
};
