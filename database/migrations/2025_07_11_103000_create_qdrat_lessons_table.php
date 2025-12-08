<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQdratLessonsTable extends Migration
{
    public function up()
    {
        Schema::create('qdrat_lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();

            // ربط الدرس بفئة الدروس
            $table->foreignId('lesson_category_id')->nullable()->constrained('qdrat_lesson_categories')->nullOnDelete();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->integer('order')->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('qdrat_lessons');
    }
}
