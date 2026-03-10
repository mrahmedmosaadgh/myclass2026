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
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('page_name')->index(); // Name of the page being tracked
            $table->string('ip_address')->nullable(); // IP address of visitor
            $table->string('user_agent')->nullable(); // Browser/user agent info
            $table->string('referrer')->nullable(); // Referring page
            $table->json('metadata')->nullable(); // Additional tracking data
            $table->timestamps();
            
            // Index for faster queries by page name
            $table->index(['page_name', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};