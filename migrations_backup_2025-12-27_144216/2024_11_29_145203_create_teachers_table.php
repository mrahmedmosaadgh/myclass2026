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
        Schema::create('teachers', function (Blueprint $table) {





            $table->id();
            $table->string('t_id')->unique();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->tinyInteger('schools_number')->default(1);
            $table->json('school_extra_ids')->nullable();
            // $table->foreignId('school_section_id')->nullable()->constrained('school_sections');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_cute')->nullable();
            $table->string('national_id')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('phone_number')->nullable()->unique();
            $table->string('whatsapp_number')->nullable()->unique();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('address')->nullable();
            $table->integer('order_1')->nullable();
            $table->integer('order_2')->nullable();
            $table->text('notes')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Add this line for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
