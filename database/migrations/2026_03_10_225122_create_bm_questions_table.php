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
        Schema::create('bm_questions', function (Blueprint $table) {
            $table->id();
            $table->string('domain'); // Addition, Subtraction, Multiplication, Division, Fractions
            $table->string('sub_skill');
            $table->tinyInteger('difficulty')->comment('1 to 10');
            $table->text('template');
            $table->json('parameters_json');
            $table->string('correct_answer');
            $table->text('explanation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bm_questions');
    }
};
