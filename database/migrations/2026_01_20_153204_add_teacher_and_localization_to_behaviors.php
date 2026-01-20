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
        Schema::table('behaviors', function (Blueprint $table) {
            // Add teacher_id column (nullable = school default, set = teacher custom)
            $table->foreignId('teacher_id')
                ->nullable()
                ->after('school_id')
                ->constrained('teachers')
                ->cascadeOnDelete()
                ->comment('Null = School Default, Set = Teacher Specific');
            
            // Add Arabic name column
            $table->string('name_ar')->nullable()->after('name')->comment('Arabic Name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('behaviors', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn(['teacher_id', 'name_ar']);
        });
    }
};
