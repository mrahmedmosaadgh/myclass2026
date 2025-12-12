<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQdratSkillsTable extends Migration
{
    public function up()
    {
        Schema::create('qdrat_skills', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            // ربط المهارة بمستوى المهارة
            $table->foreignId('skill_level_id')->nullable()->constrained('qdrat_skill_levels')->nullOnDelete();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->integer('order')->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('qdrat_skills');
    }
}
