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
        // Rename the table
        Schema::rename('presentation_categories', 'cr_presentation_categories');
        
        // Update foreign key references in presentations table if it exists
        if (Schema::hasTable('presentations')) {
            Schema::table('presentations', function (Blueprint $table) {
                // Drop existing foreign key if it exists
                $table->dropForeign(['category_id']);
                
                // Add new foreign key constraint
                $table->foreign('category_id')->references('id')->on('cr_presentation_categories')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the rename
        Schema::rename('cr_presentation_categories', 'presentation_categories');
        
        // Restore original foreign key references in presentations table if it exists
        if (Schema::hasTable('presentations')) {
            Schema::table('presentations', function (Blueprint $table) {
                // Drop current foreign key
                $table->dropForeign(['category_id']);
                
                // Restore original foreign key constraint
                $table->foreign('category_id')->references('id')->on('presentation_categories')->onDelete('set null');
            });
        }
    }
};
