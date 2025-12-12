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
        Schema::create('behavior_incidents', function (Blueprint $table) {
            // Primary & external identifiers
            $table->id();
            $table->uuid('uuid')->unique()->index();

            // Core relationships (never null for existing records)
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->onDelete('set null');

            // Staff involved
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('reported_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');

            // Snapshot data (critical for historical accuracy)
            $table->string('student_name', 100)->index(); // indexed for fast search
            $table->unsignedTinyInteger('grade')->nullable()->index(); // 0–12 more than enough
            $table->string('student_grade_snapshot', 30)->nullable();  // e.g., "Grade 10", "Year 11"
            $table->string('student_section_snapshot', 50)->nullable(); // "10A", "Blue House"

            // When it happened — single source of truth
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->string('period_code', 20)->nullable()->index(); // "P1", "Homeroom", "Lunch"

            // === Structured + translatable fields (keep JSON for multilingual support) ===
            $table->json('incident_type');      // {"en": "Tardy", "ar": "تأخير", "code": "TARDY"}
            $table->json('location');           // {"en": "Classroom", "ar": "فصل دراسي", "code": "CLASSROOM"}
            $table->json('behavior');           // array of behaviors with codes
            $table->json('description')->nullable();     // structured + free text
            $table->json('motivation')->nullable();
            $table->json('others_involved')->nullable(); // [{"student_id": 123, "role": "victim"}]
            $table->json('teacher_action')->nullable();
            $table->json('admin_action')->nullable();

            // Optional: keep primary codes as separate columns for ultra-fast filtering
            $table->string('primary_behavior_code', 50)->nullable()->index(); // e.g., "DISRUPTION"
            $table->string('primary_location_code', 50)->nullable()->index(); // e.g., "PLAYGROUND"

            // Severity & workflow
            $table->enum('severity', ['minor', 'moderate', 'major'])->default('minor')->index();
            $table->enum('status', ['open', 'in_review', 'resolved', 'closed'])->default('open')->index();
            $table->boolean('follow_up_needed')->default(false)->index();

            // Gamification / points system
            $table->smallInteger('points_deducted')->default(0); // negative or positive
            $table->smallInteger('points_awarded')->default(0);  // for positive behavior

            // Parent communication (very important in 2025+ systems)
            $table->boolean('visible_to_parent')->default(true);
            $table->timestamp('parent_viewed_at')->nullable();
            $table->timestamp('parent_notified_at')->nullable();
            $table->foreignId('parent_notified_by')->nullable()->constrained('users')->onDelete('set null');

            // Critical escalations
            $table->boolean('critical_alert')->default(false)->index();
            $table->timestamp('escalated_at')->nullable();

            // Attachments & evidence
            $table->json('attachments')->nullable(); // [{"type": "image", "url": "...", "name": "..."}]

            // Audit trail
            $table->string('submitted_via', 30)->nullable(); // 'web', 'mobile-app', 'kiosk', 'import'
            $table->ipAddress('device_ip')->nullable();

            // Academic context (highly recommended)
            $table->foreignId('school_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
            // $table->foreignId('term_id')->nullable()->constrained('terms')->onDelete('set null'); // Uncomment if you have terms table

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // ==================================================================
            // OPTIMIZED INDEXING STRATEGY (covers 99% of real-world queries)
            // ==================================================================
            // Single column indexes (already defined above with ->index())
            // Additional composite indexes for performance
            $table->index(['school_id', 'occurred_at']);
            $table->index(['student_id', 'occurred_at']);
            $table->index(['classroom_id', 'occurred_at']);
            $table->index(['school_id', 'severity', 'occurred_at']);
            $table->index(['school_id', 'status', 'occurred_at']);
            $table->index(['grade', 'occurred_at']);
            $table->index(['school_year_id', 'occurred_at']);
            $table->index(['school_id', 'critical_alert', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('behavior_incidents');
    }
};
