<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQdratQuestionTypesTable extends Migration
{
    public function up()
    {
        Schema::create('qdrat_question_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();         // اسم النوع البرمجي (مثال: multiple_choice)
            $table->string('display_name');            // اسم العرض للمستخدم (مثال: اختيار من متعدد)
            $table->text('description')->nullable();  // وصف اختياري لنوع السؤال
            $table->timestamps();
        });

        // يمكن إدخال أنواع أساسية بشكل افتراضي هنا (اختياري)
        DB::table('qdrat_question_types')->insert([
            ['name' => 'multiple_choice', 'display_name' => 'اختيار من متعدد', 'description' => 'سؤال يحتوي على خيارات متعددة.'],
            ['name' => 'open_ended', 'display_name' => 'سؤال مفتوح', 'description' => 'سؤال يجيب عليه الطالب كتابة نص.'],
            ['name' => 'true_false', 'display_name' => 'صح أو خطأ', 'description' => 'سؤال به خياران: صح أو خطأ.'],
            // أضف أنواع أخرى حسب الحاجة
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('qdrat_question_types');
    }
}
