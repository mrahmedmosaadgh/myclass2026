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
        // First check if the table exists
        if (!Schema::hasTable('push_subscriptions')) {
            // Create the table if it doesn't exist
            Schema::create('push_subscriptions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->morphs('subscribable');
                $table->string('endpoint', 500)->unique();
                $table->string('public_key')->nullable();
                $table->string('auth_token')->nullable();
                $table->string('content_encoding')->nullable();
                $table->timestamps();
            });
            return;
        }

        // If the table exists, make sure it has the correct structure
        Schema::table('push_subscriptions', function (Blueprint $table) {
            // Check if subscribable columns don't exist
            if (!Schema::hasColumn('push_subscriptions', 'subscribable_type')) {
                $table->morphs('subscribable');
            }

            // Make sure endpoint column exists and has the right length
            if (!Schema::hasColumn('push_subscriptions', 'endpoint')) {
                $table->string('endpoint', 500)->unique();
            }

            // Make sure public_key column exists
            if (!Schema::hasColumn('push_subscriptions', 'public_key')) {
                $table->string('public_key')->nullable();
            }

            // Make sure auth_token column exists
            if (!Schema::hasColumn('push_subscriptions', 'auth_token')) {
                $table->string('auth_token')->nullable();
            }

            // Make sure content_encoding column exists
            if (!Schema::hasColumn('push_subscriptions', 'content_encoding')) {
                $table->string('content_encoding')->nullable();
            }

            // Drop user_id if it exists (we're using polymorphic relationship)
            if (Schema::hasColumn('push_subscriptions', 'user_id')) {
                // Check if there's a foreign key constraint
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                $table->dropColumn('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to do anything in down method
    }
};
