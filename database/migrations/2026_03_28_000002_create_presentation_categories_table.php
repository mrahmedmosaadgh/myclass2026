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
        Schema::create('presentation_categories', function (Blueprint $table) {
            $table->id();
            
            // Basic info
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->default('#6b7280'); // Category color
            $table->string('icon')->nullable(); // Icon identifier
            
            // Hierarchy support
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('presentation_categories')->onDelete('set null');
            $table->integer('sort_order')->default(0);
            
            // School/Teacher context
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            // System vs custom
            $table->boolean('is_system')->default(false); // Predefined system categories
            $table->boolean('is_active')->default(true);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['school_id']);
            $table->index(['parent_id']);
            $table->index(['is_system', 'is_active']);
        });

        // Insert default system categories
        DB::table('presentation_categories')->insert([
            [
                'name' => 'Mathematics',
                'slug' => 'mathematics',
                'description' => 'Math lessons and presentations',
                'color' => '#3b82f6',
                'icon' => 'calculator',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Science',
                'slug' => 'science',
                'description' => 'Science presentations and experiments',
                'color' => '#10b981',
                'icon' => 'flask',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Language Arts',
                'slug' => 'language-arts',
                'description' => 'Reading, writing, and literature presentations',
                'color' => '#f59e0b',
                'icon' => 'book',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Social Studies',
                'slug' => 'social-studies',
                'description' => 'History, geography, and social science presentations',
                'color' => '#8b5cf6',
                'icon' => 'globe',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'General purpose presentations',
                'color' => '#6b7280',
                'icon' => 'presentation',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Add FK from presentations to presentation_categories now that the table exists
        Schema::table('presentations', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('presentation_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentation_categories');
    }
};
