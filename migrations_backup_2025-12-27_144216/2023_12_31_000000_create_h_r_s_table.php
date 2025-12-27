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
        Schema::create('h_r_s', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('name');
            $table->json('data')->nullable();
            $table->tinyInteger('active')->default(1);

             // Emergency contact information as JSON
            // $table->json('benefits')->nullable(); // Benefits information as JSON
            // $table->string('profile_photo')->nullable(); // Profile photo path
            // $table->text('notes')->nullable(); // Additional notes or comments
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_r_s');
    }
};
