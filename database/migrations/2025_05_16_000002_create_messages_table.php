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
        // Drop the foreign key constraint first
        if (Schema::hasTable('message_recipients') && Schema::hasColumn('message_recipients', 'message_id')) {
            Schema::table('message_recipients', function (Blueprint $table) {
                $table->dropForeign(['message_id']);
            });
        }

        // Drop the existing messages table
        Schema::dropIfExists('messages');

        // Create the new messages table
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('body');
            $table->string('type')->default('text'); // text, image, file, etc.
            $table->string('attachment_url')->nullable(); // For files or images
            $table->boolean('is_seen')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
