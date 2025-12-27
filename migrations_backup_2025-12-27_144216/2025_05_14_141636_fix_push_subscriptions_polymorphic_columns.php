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
        Schema::table('push_subscriptions', function (Blueprint $table) {
            // Drop the user_id column if it exists
            if (Schema::hasColumn('push_subscriptions', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }

            // Add the polymorphic relationship columns if they don't exist
            if (!Schema::hasColumn('push_subscriptions', 'subscribable_id')) {
                $table->morphs('subscribable');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            // Remove the polymorphic columns if they exist
            if (Schema::hasColumn('push_subscriptions', 'subscribable_id')) {
                $table->dropMorphs('subscribable');
            }

            // Add back the user_id column
            $table->unsignedBigInteger('user_id')->nullable();
        });
    }
};
