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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nour_name')->nullable();
            $table->string('nour_id')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->string('notes')->nullable();
            $table->json('lesson_plan_templates')->nullable();
 
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('color_bg',22)->nullable();
            $table->string('color_text',22)->nullable();
            // ALTER TABLE `subjects` ADD `color_bg` VARCHAR(20) NULL AFTER `notes`, ADD `color_text` VARCHAR(20) NULL AFTER `color_bg`;
            $table->softDeletes(); // Add this line for soft deletes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
