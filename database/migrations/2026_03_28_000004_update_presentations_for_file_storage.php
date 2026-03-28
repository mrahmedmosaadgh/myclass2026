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
        Schema::table('presentations', function (Blueprint $table) {
            // Remove the slides JSON column
            $table->dropColumn('slides');
            
            // Add file path for slides storage
            $table->string('slides_file_path')->nullable()->after('classroom_id');
            
            // Add file size tracking
            $table->unsignedBigInteger('file_size_bytes')->default(0)->after('slides_file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presentations', function (Blueprint $table) {
            // Restore slides JSON column
            $table->json('slides')->after('classroom_id');
            
            // Remove file-related columns
            $table->dropColumn(['slides_file_path', 'file_size_bytes']);
        });
    }
};
