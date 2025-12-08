<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('resume_answers', function (Blueprint $table) {
            $table->string('voice_note_path')->nullable() ;
            $table->integer('voice_note_duration')->nullable()->after('voice_note_path')->comment('Duration in seconds');
            $table->json('voice_note_metadata')->nullable()->after('voice_note_duration');
            $table->integer('views_count')->default(0)->after('voice_note_metadata');
            $table->decimal('average_rating', 3, 2)->default(0)->after('views_count');
            $table->integer('ratings_count')->default(0)->after('average_rating');
            $table->integer('likes_count')->default(0)->after('ratings_count');
            $table->integer('comments_count')->default(0)->after('likes_count');
            $table->boolean('is_featured')->default(false)->after('comments_count');
            $table->timestamp('featured_at')->nullable()->after('is_featured');
            
            // Add indexes for performance
            $table->index(['average_rating', 'ratings_count']);
            $table->index(['likes_count']);
            $table->index(['is_featured', 'featured_at']);
        });
    }

    public function down()
    {
        Schema::table('resume_answers', function (Blueprint $table) {
            $table->dropIndex(['average_rating', 'ratings_count']);
            $table->dropIndex(['likes_count']);
            $table->dropIndex(['is_featured', 'featured_at']);
            
            $table->dropColumn([
                'voice_note_path',
                'voice_note_duration',
                'voice_note_metadata',
                'views_count',
                'average_rating',
                'ratings_count',
                'likes_count',
                'comments_count',
                'is_featured',
                'featured_at'
            ]);
        });
    }
};
