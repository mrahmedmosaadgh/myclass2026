<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resume_answer_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('answer_id')->constrained('resume_answers')->cascadeOnDelete();
            $table->string('report_type'); // spam, inappropriate, copyright, other
            $table->text('reason');
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Add indexes for performance
            $table->index(['status', 'created_at']);
            $table->index(['answer_id']);
            $table->index(['user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('resume_answer_reports');
    }
};
