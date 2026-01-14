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
        // Modify qu_answers table
        Schema::table('qu_answers', function (Blueprint $table) {
            // Change marks_obtained to decimal for precision
            $table->decimal('marks_obtained', 5, 2)->nullable()->change();
            
            // Add grading fields
            $table->text('feedback')->nullable()->after('marks_obtained');
            $table->timestamp('graded_at')->nullable()->after('feedback');
            $table->foreignId('graded_by')->nullable()->constrained('users')->after('graded_at');
        });

        // Modify qu_attempts table
        Schema::table('qu_attempts', function (Blueprint $table) {
            // Change score to decimal for precision
            $table->decimal('score', 5, 2)->nullable()->change();
            
            // Add grading status
            $table->enum('grading_status', ['pending', 'partial', 'completed'])->default('pending')->after('score');
            $table->timestamp('graded_at')->nullable()->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qu_answers', function (Blueprint $table) {
            $table->dropForeign(['graded_by']);
            $table->dropColumn(['feedback', 'graded_at', 'graded_by']);
            $table->integer('marks_obtained')->nullable()->change();
        });

        Schema::table('qu_attempts', function (Blueprint $table) {
            $table->dropColumn(['grading_status', 'graded_at']);
            $table->integer('score')->nullable()->change();
        });
    }
};
