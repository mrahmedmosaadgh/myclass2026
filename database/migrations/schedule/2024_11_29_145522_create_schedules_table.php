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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            // Foreign keys with better constraints
            $table->foreignId('copy_id')
                ->constrained('schedule_copies')
                ->onDelete('cascade');

                            // Location and display information
                            $table->foreignId('cst_id')
                            ->constrained('classroom_subject_teachers')
                            ->onDelete('cascade');

            $table->foreignId('school_id')
                ->constrained('schools')
                ->onDelete('cascade');

            // $table->foreignId('grade_id')
            //     ->constrained('grades')
            //     ->onDelete('cascade');

            // $table->foreignId('classroom_id')
            //     ->constrained('classrooms')
            //     ->onDelete('cascade');

            // $table->foreignId('subject_id')
            //     ->constrained('subjects')
            //     ->onDelete('cascade');

            // $table->foreignId('teacher_id')->nullable()
            //     ->constrained('teachers')
            //     ->onDelete('cascade');

                        $table->foreignId('teacher_substitute_id')->nullable()
                ->constrained('teachers')
                ->onDelete('cascade');
                        $table->foreignId('co_teacher_id')->nullable()
                ->constrained('teachers')
                ->onDelete('cascade');
                $table->foreignId('co_subject_id')->nullable()
                ->constrained('subjects')
                ->onDelete('cascade');
            // Schedule timing information
            // $table->unsignedTinyInteger('day')
            //     ->nullable();
            // $table->unsignedTinyInteger('week')
            //     ->nullable();
            //     $table->unsignedTinyInteger('semester')
            //     ->nullable();


                // $table->foreignId('period_detail_id')->nullable()
                // ->constrained('period_details')
                // ->onDelete('cascade');


            // $table->unsignedTinyInteger('period_code')
            //     ->nullable();

            $table->string('period_code',  20)
                ->nullable();

                // ->check('num > 0');
           

            $table->string('place', 120)
                ->nullable()
                ->comment('Physical location or classroom');

            // $table->string('color_custom', 22)
            //     ->nullable()
            //     ->comment('Custom color for UI display (hex format: #RRGGBB)');
            // $table->string('color_custom_text', 22)
            //     ->nullable()
            //     ->comment('Custom color for UI display (hex format: #RRGGBB)');
                // ->check("color_custom REGEXP '^#[0-9A-Fa-f]{6}$'");
                // ALTER TABLE `classroom_subject_teachers` ADD `color_custom_text` VARCHAR(22) NULL AFTER `color_custom`;
            // Status and metadata
            $table->boolean('active')
                ->default(true)
                ->comment('Whether this schedule is currently active');

            $table->text('notes')
                ->nullable()
                ->comment('Additional notes or comments');

            // Audit timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['school_id' ]);
            $table->index(['period_code']);
            $table->index(['copy_id', 'active']);
            // $table->index(['teacher_id', 'day']);

            // Unique constraints
            // $table->unique(['school_id' , 'day', 'period_number', 'copy_id'], 'unique_schedule_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};



