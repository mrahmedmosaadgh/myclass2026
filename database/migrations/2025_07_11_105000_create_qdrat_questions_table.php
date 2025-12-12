<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQdratQuestionsTable extends Migration
{
    public function up()
    {
        Schema::create('qdrat_questions', function (Blueprint $table) {
            $table->id();

            $table->text('content'); // نص السؤال

            $table->foreignId('question_type_id')
                  ->constrained('qdrat_question_types')
                  ->default(1); // 1 يعني multiple_choice مثلاً

            $table->json('options')->nullable(); 
            // تخزين الخيارات كـ JSON، مثال:
            // [
            //   { "id": 101, "option_text": "15", "is_correct": true },
            //   { "id": 102, "option_text": "10", "is_correct": false }
            // ]

            $table->text('answer_text')->nullable(); 
            // للإجابة النصية في حالة الأسئلة المفتوحة

            $table->foreignId('difficulty_level_id')
                  ->nullable()
                  ->constrained('qdrat_question_difficulties')
                  ->nullOnDelete();

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('qdrat_questions');
    }
}
