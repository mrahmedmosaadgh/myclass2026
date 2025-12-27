<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('period_activities')) {
            Schema::create('period_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
                $table->foreignId('calendar_id')->constrained('calendars');
                $table->foreignId('teacher_id')->constrained('teachers');
                $table->foreignId('teacher_substitute_id')->nullable()->constrained('teachers');
                $table->boolean('teacher_present')->default(true);
                $table->json('teacher_plan')->nullable();
                // $table->boolean('teacher_co_present')->default(false);
                $table->string('period_status', 20)->default('completed')
                    ->comment('completed, cancelled, modified, event_affected');
               
                // Student tracking
                // $table->json('student_attendance')->nullable();
                // $table->json('student_behavior')->nullable();
                // $table->json('student_participation')->nullable();
                // $table->json('homework_records')->nullable();

                // Academic tracking
                $table->text('lesson_notes')->nullable();
                $table->string('lesson_code', 20)
                ->nullable()
                ->comment('Links to weekly_plans.code if exists, format like 12.1.1.1');
                $table->text('improvement_notes')->nullable();

                // Duty tracking
                $table->boolean('was_duty_period')->default(false);
                $table->text('duty_notes')->nullable();

                // System fields
                $table->foreignId('created_by')->constrained('teachers');
                $table->foreignId('updated_by')->nullable()->constrained('teachers');
                $table->timestamps();
                $table->softDeletes();

                // Indexes for better performance
                $table->index(['schedule_id', 'calendar_id']);
                $table->index(['teacher_id', 'calendar_id']);
                $table->index('period_status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('period_activities');
    }
};
