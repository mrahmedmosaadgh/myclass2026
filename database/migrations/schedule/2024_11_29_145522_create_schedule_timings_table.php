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
        Schema::create('schedule_timings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');

             
            // $table->string('name',100);
        
 
            $table->json('options')->nullable();
            $table->json('timing')->nullable();
              
                 $table->string('notes')->nullable();

                         // Timestamps and soft delete
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['school_id']);
       

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_timings');
    }
};
