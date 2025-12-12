<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->boolean('has_options')->default(false);
            $table->boolean('supports_hints')->default(true);
            $table->boolean('supports_explanation')->default(true);
            $table->timestamps();
            
            // Add index on slug for fast lookups
            $table->index('slug');
        });

        // Seed initial question types
        DB::table('question_types')->insert([
            [
                'slug' => 'multiple_choice',
                'name' => 'Multiple Choice',
                'has_options' => true,
                'supports_hints' => true,
                'supports_explanation' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'multi_select',
                'name' => 'Multi Select',
                'has_options' => true,
                'supports_hints' => true,
                'supports_explanation' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'true_false',
                'name' => 'True/False',
                'has_options' => true,
                'supports_hints' => true,
                'supports_explanation' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'fill_blank',
                'name' => 'Fill in the Blank',
                'has_options' => false,
                'supports_hints' => true,
                'supports_explanation' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'short_answer',
                'name' => 'Short Answer',
                'has_options' => false,
                'supports_hints' => true,
                'supports_explanation' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'essay',
                'name' => 'Essay',
                'has_options' => false,
                'supports_hints' => true,
                'supports_explanation' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_types');
    }
};
