<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('calendar_events')) {
            Schema::create('calendar_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('calendar_id')->constrained('calendars')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('type')->comment('party, meeting, exam, holiday, etc.');
                $table->boolean('is_full_day')->default(false);
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->string('location')->nullable();
                $table->json('affected_schedules')->nullable()->comment('List of schedule IDs that are affected');
                $table->boolean('affects_all_schedules')->default(false);
                $table->string('status')->default('active')->comment('active, cancelled, completed');
                $table->foreignId('created_by')->constrained('teachers');
                $table->foreignId('updated_by')->nullable()->constrained('teachers');
                $table->timestamps();
                $table->softDeletes();

                // Indexes for better performance
                $table->index(['calendar_id', 'type']);
                $table->index('status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('calendar_events');
    }
};
