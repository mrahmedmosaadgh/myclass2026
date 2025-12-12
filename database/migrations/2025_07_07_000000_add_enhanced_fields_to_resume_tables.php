<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add new fields to resume_questions table
        Schema::table('resume_questions', function (Blueprint $table) {
            $table->json('category')->nullable()->after('title');
            $table->string('language', 10)->default('en')->after('category');
            $table->json('tags')->nullable()->after('language');
            $table->json('options')->nullable()->after('tags');
            $table->text('default_answer')->nullable()->after('options');
            $table->boolean('is_required')->default(false)->after('default_answer');
            $table->text('description')->nullable()->after('is_required');
        });

        // Add new fields to resume_answers table
        Schema::table('resume_answers', function (Blueprint $table) {
            $table->text('answer_text')->nullable()->after('question_id');
            $table->json('media_links')->nullable()->after('answer_text');
            $table->json('attachments')->nullable()->after('media_links');
            $table->enum('status', ['draft', 'published', 'review', 'archived'])->default('draft')->after('attachments');
            $table->text('notes')->nullable()->after('status');
            $table->boolean('is_public')->default(false)->after('notes');
        });
    }

    public function down()
    {
        Schema::table('resume_questions', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'language', 
                'tags',
                'options',
                'default_answer',
                'is_required',
                'description'
            ]);
        });

        Schema::table('resume_answers', function (Blueprint $table) {
            $table->dropColumn([
                'answer_text',
                'media_links',
                'attachments',
                'status',
                'notes',
                'is_public'
            ]);
        });
    }
};
