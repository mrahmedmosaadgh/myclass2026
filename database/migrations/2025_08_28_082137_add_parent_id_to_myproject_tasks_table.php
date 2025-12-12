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
        Schema::table('myproject_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->integer('sort_order')->default(0)->after('due_date');
            
            // Add foreign key constraint
            $table->foreign('parent_id')->references('id')->on('myproject_tasks')->onDelete('cascade');
            
            // Add index for better performance
            $table->index('parent_id');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('myproject_tasks', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['sort_order']);
            $table->dropColumn(['parent_id', 'sort_order']);
        });
    }
};