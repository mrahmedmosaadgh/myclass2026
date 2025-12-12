<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('student_period_records')) {
            Schema::create('student_period_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('period_activity_id')->constrained('period_activities')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('students');

                // Attendance
                $table->string('attendance_status', 15)->default('present')
                    ->comment('present, absent, late, excused');
                $table->integer('late_minutes')->nullable();

                // Academic
                $table->boolean('homework_completed')->default(false);
                $table->decimal('homework_score', 5, 2)->nullable();

                // Behavior
                $table->integer('behavior_plus_marks')->default(0);
                $table->integer('behavior_minus_marks')->default(0);
                $table->text('behavior_notes')->nullable();

                // Participation
                $table->integer('participation_score')->nullable();
                $table->text('participation_notes')->nullable();

                $table->timestamps();

                // Indexes
                $table->unique(['period_activity_id', 'student_id']);
                $table->index('student_id');
                $table->index('attendance_status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('student_period_records');
    }
};
