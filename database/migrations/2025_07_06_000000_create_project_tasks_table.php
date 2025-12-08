<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('path')->nullable();
            $table->string('description')->nullable();
            $table->string('priority')->nullable(); // low / medium / high
            $table->string('type'); // UI / Backend / Feature
            $table->string('status'); // todo / in_progress / done
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_tasks');
    }
};
