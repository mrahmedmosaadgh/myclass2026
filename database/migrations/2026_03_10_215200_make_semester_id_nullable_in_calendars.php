<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign key and unique constraints, then re-add with nullable
        DB::statement('ALTER TABLE calendars DROP FOREIGN KEY calendars_semester_id_foreign');
        DB::statement('ALTER TABLE calendars MODIFY COLUMN semester_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE calendars ADD CONSTRAINT calendars_semester_id_foreign FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE calendars DROP FOREIGN KEY calendars_semester_id_foreign');
        DB::statement('ALTER TABLE calendars MODIFY COLUMN semester_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE calendars ADD CONSTRAINT calendars_semester_id_foreign FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE');
    }
};
