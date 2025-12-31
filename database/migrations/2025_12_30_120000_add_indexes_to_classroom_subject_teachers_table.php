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
        Schema::table('classroom_subject_teachers', function (Blueprint $table) {
            // Add composite index for unique assignment constraint
            $table->unique([
                'school_id', 
                'academic_year_id', 
                'classroom_id', 
                'subject_id', 
                'teacher_id'
            ], 'unique_assignment_idx');
            
            // Add index for school and academic year queries (for import operations)
            $table->index(['school_id', 'academic_year_id'], 'school_academic_year_idx');
            
            // Add index for teacher queries
            $table->index(['teacher_id', 'school_id'], 'teacher_school_idx');
            
            // Add index for classroom queries
            $table->index(['classroom_id', 'subject_id'], 'classroom_subject_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classroom_subject_teachers', function (Blueprint $table) {
            $table->dropUnique('unique_assignment_idx');
            $table->dropIndex('school_academic_year_idx');
            $table->dropIndex('teacher_school_idx');
            $table->dropIndex('classroom_subject_idx');
        });
    }
};