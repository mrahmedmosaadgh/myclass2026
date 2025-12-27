<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Models\Semester;
use App\Models\Calendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AcademicCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->school = School::factory()->create();
        $this->user = User::factory()->create([
            'school_id' => $this->school->id,
        ]);
        $this->user->assignRole('admin');
    }

    public function test_creating_academic_year_auto_generates_4_semesters()
    {
        $response = $this->actingAs($this->user)->post(route('admin.academic_calendar.year.store'), [
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'name' => '2025-2026',
        ]);

        $year = AcademicYear::first();
        $this->assertEquals(4, $year->semesters()->count());
        $this->assertEquals(1, $year->semesters()->where('active', true)->count());
    }

    public function test_semester_date_update_and_active_enforcement()
    {
        $year = AcademicYear::factory()->create(['school_id' => $this->school->id]);
        $semesters = $year->semesters;
        $sem2 = $semesters->where('semester_number', 2)->first();

        $this->actingAs($this->user)->post(route('admin.academic_calendar.semester.update', $sem2->id), [
            'name' => 'Spring Semester',
            'start_date' => '2026-01-15',
            'total_weeks' => 12,
            'active' => true,
        ]);

        $sem2->refresh();
        $this->assertEquals('2026-01-15', $sem2->start_date->format('Y-m-d'));
        // 12 weeks from Jan 15 is April 8, minus 1 day is April 7
        $this->assertEquals('2026-04-07', $sem2->end_date->format('Y-m-d'));
        $this->assertTrue($sem2->active);
        
        // Ensure others are inactive
        $this->assertEquals(1, $year->semesters()->where('active', true)->count());
    }

    public function test_calendar_generation()
    {
        $year = AcademicYear::factory()->create(['school_id' => $this->school->id]);
        $sem1 = $year->semesters->first();
        $sem1->update([
            'start_date' => '2025-09-01', // Monday
            'end_date' => '2025-09-14', // Sunday (2 weeks)
        ]);

        $this->actingAs($this->user)->post(route('admin.academic_calendar.semester.generate', $sem1->id));

        $this->assertEquals(14, Calendar::where('semester_id', $sem1->id)->count());
        
        // Check regeneration
        $this->actingAs($this->user)->post(route('admin.academic_calendar.semester.generate', $sem1->id));
        $this->assertEquals(14, Calendar::where('semester_id', $sem1->id)->count());
    }

    public function test_only_one_active_year_per_school()
    {
        // Create first active year
        $year1 = AcademicYear::create([
            'name' => 'Year 1',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'school_id' => $this->school->id,
            'active' => true,
        ]);

        $this->assertTrue($year1->active);

        // Create second active year - should deactivate year1
        $year2 = AcademicYear::create([
            'name' => 'Year 2',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'school_id' => $this->school->id,
            'active' => true,
        ]);

        $year1->refresh();
        $this->assertFalse($year1->active);
        $this->assertTrue($year2->active);

        // Update year1 back to active - should deactivate year2
        $year1->update(['active' => true]);
        $year2->refresh();
        $this->assertTrue($year1->active);
        $this->assertFalse($year2->active);
    }
}
