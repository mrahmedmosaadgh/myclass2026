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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('route')->nullable()->comment('Named route');
            $table->string('permission')->nullable()->comment('Spatie permission name');
            $table->string('module')->index()->comment('Feature grouping e.g. Academics');
            $table->foreignId('parent_id')->nullable()->constrained('menus')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_feature_flag')->default(false);
            $table->string('feature_flag_key')->nullable();
            $table->json('meta')->nullable()->comment('Additional metadata like badges, descriptions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
