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
            $table->dropColumn(['subscribable_type', 'subscribable_id']);
            $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->morphs('subscribable');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
