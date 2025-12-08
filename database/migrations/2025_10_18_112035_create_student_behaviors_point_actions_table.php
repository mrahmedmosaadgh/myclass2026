<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_behaviors_point_actions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_behaviors_id')
        ->constrained('student_behaviors')
        ->cascadeOnDelete();

    $table->foreignId('reason_id')->nullable()->constrained('behaviors');
    $table->integer('value'); // +1 +3 +5 -1 -3 -5
    $table->string('action_type'); // plus / minus / cancel / revoke
    $table->text('note')->nullable();

    // Cancellation tracking
    $table->boolean('canceled')->default(false);
    $table->foreignId('canceled_by')->nullable()->constrained('users');
    $table->timestamp('canceled_at')->nullable();
    $table->text('cancel_reason')->nullable();

    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_behaviors_point_actions');
    }
};
