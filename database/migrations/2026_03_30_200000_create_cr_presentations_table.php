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
        if (Schema::hasTable('cr_presentations')) {
            return;
        }

        Schema::create('cr_presentations', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();

            $table->unsignedBigInteger('cr_presentation_category_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('classroom_id')->nullable();

            $table->json('slides')->nullable();
            $table->integer('current_slide_index')->default(0);
            $table->boolean('use_phases')->default(false);
            $table->boolean('has_initialized_phases')->default(false);

            $table->json('metadata')->nullable();

            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_public')->default(false);
            $table->boolean('is_template')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cr_presentation_category_id')
                ->references('id')
                ->on('cr_presentation_categories')
                ->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('classroom_id')->references('id')->on('classrooms')->onDelete('set null');

            $table->index(['user_id', 'status']);
            $table->index(['cr_presentation_category_id']);
            $table->index(['school_id']);
            $table->index(['classroom_id']);
            $table->index(['title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cr_presentations');
    }
};
