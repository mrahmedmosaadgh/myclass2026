# Weekly Plans Integration Documentation

## Overview

This document explains the integration between `weekly_plans` and `period_activities` tables in the MyClass7 system. This integration allows teachers to plan their curriculum lessons for each week and period, and then link these plans to actual classroom activities.

## Database Schema

### Weekly Plans Table

The `weekly_plans` table holds curriculum lessons assigned to each week/period per subject:

```sql
CREATE TABLE weekly_plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    semester TINYINT NOT NULL,
    week_number TINYINT NOT NULL,
    period_number TINYINT NOT NULL,
    cst_id BIGINT UNSIGNED NOT NULL,
    curriculum_lesson_id BIGINT UNSIGNED NULL,
    code VARCHAR(20) NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE INDEX unique_weekly_plan_slot (academic_year_id, semester, week_number, period_number, cst_id),
    INDEX idx_code (code),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    FOREIGN KEY (cst_id) REFERENCES classroom_subject_teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (curriculum_lesson_id) REFERENCES curriculum_lessons(id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
);
```

Key fields:
- `academic_year_id`, `semester`, `week_number`, `period_number`: Define when the lesson is planned
- `cst_id`: Links to classroom_subject_teachers (combines classroom, subject, and teacher)
- `curriculum_lesson_id`: Optional link to a specific curriculum lesson
- `code`: Unique identifier in format "year.semester.week.period" (e.g., "12.1.1.1")
- `teacher_id`: The teacher who created this plan

### Period Activities Table Update

The `period_activities` table was updated to include a `lesson_code` field:

```sql
ALTER TABLE period_activities 
ADD COLUMN lesson_code VARCHAR(20) NULL COMMENT 'Links to weekly_plans.code if exists, format like 12.1.1.1' AFTER event_id,
ADD INDEX idx_lesson_code (lesson_code);
```

This `lesson_code` field creates a non-foreign key relationship to the `weekly_plans` table, allowing flexibility in creating period activities before or after weekly plans exist.

## Model Relationships

### WeeklyPlan Model

```php
class WeeklyPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_year_id',
        'semester',
        'week_number',
        'period_number',
        'cst_id',
        'curriculum_lesson_id',
        'code',
        'teacher_id',
        'notes',
    ];

    // Relationships
    public function academicYear() {
        return $this->belongsTo(AcademicYear::class);
    }

    public function cst() {
        return $this->belongsTo(ClassroomSubjectTeacher::class, 'cst_id');
    }

    public function curriculumLesson() {
        return $this->belongsTo(CurriculumLesson::class);
    }

    public function teacher() {
        return $this->belongsTo(Teacher::class);
    }

    // Helper method to generate unique code
    public static function generateCode($academicYearId, $semester, $weekNumber, $periodNumber) {
        return "{$academicYearId}.{$semester}.{$weekNumber}.{$periodNumber}";
    }
}
```

### PeriodActivity Model Update

The `PeriodActivity` model was updated to include the `lesson_code` field and a relationship to `WeeklyPlan`:

```php
class PeriodActivity extends Model
{
    // Added 'lesson_code' to fillable array
    protected $fillable = [
        // ... existing fields
        'lesson_code',
    ];

    // Added relationship to WeeklyPlan
    public function weeklyPlan() {
        return $this->belongsTo(WeeklyPlan::class, 'lesson_code', 'code');
    }
}
```

## Controllers

### WeeklyPlanController

The `WeeklyPlanController` provides CRUD operations for weekly plans:

```php
class WeeklyPlanController extends Controller
{
    // Standard resource methods (index, create, store, show, edit, update, destroy)
    
    // Additional API methods
    public function getByAcademicYear($academicYearId) { /* ... */ }
    public function getBySemester($academicYearId, $semester) { /* ... */ }
    public function getByWeek($academicYearId, $semester, $weekNumber) { /* ... */ }
    public function getByCst($cstId) { /* ... */ }
}
```

### PeriodActivityController Update

The `PeriodActivityController` was updated to handle the `lesson_code` field:

```php
// In validation rules for store and update methods:
'lesson_code' => 'nullable|string|max:20'
```

## Routes

### Web Routes

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // Resource routes for CRUD operations
    Route::resource('weekly-plans', WeeklyPlanController::class);
});
```

### API Routes

```php
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('weekly-plans/by-academic-year/{academicYearId}', 
        [WeeklyPlanController::class, 'getByAcademicYear']);
    Route::get('weekly-plans/by-semester/{academicYearId}/{semester}', 
        [WeeklyPlanController::class, 'getBySemester']);
    Route::get('weekly-plans/by-week/{academicYearId}/{semester}/{weekNumber}', 
        [WeeklyPlanController::class, 'getByWeek']);
    Route::get('weekly-plans/by-cst/{cstId}', 
        [WeeklyPlanController::class, 'getByCst']);
});
```

## Usage Examples

### Creating a Weekly Plan

```php
// In a controller or service
$weeklyPlan = WeeklyPlan::create([
    'academic_year_id' => $request->academic_year_id,
    'semester' => $request->semester,
    'week_number' => $request->week_number,
    'period_number' => $request->period_number,
    'cst_id' => $request->cst_id,
    'curriculum_lesson_id' => $request->curriculum_lesson_id,
    'code' => WeeklyPlan::generateCode(
        $request->academic_year_id,
        $request->semester,
        $request->week_number,
        $request->period_number
    ),
    'teacher_id' => Auth::user()->teacher->id,
    'notes' => $request->notes,
]);
```

### Linking a Period Activity to a Weekly Plan

```php
// In a controller or service
$periodActivity = PeriodActivity::create([
    'schedule_id' => $request->schedule_id,
    'calendar_id' => $request->calendar_id,
    'teacher_id' => $request->teacher_id,
    'lesson_code' => $request->lesson_code, // This links to a weekly plan
    // ... other fields
]);
```

### Finding a Weekly Plan from a Period Activity

```php
// Get the weekly plan associated with a period activity
$periodActivity = PeriodActivity::find($id);
$weeklyPlan = $periodActivity->weeklyPlan;

if ($weeklyPlan) {
    // Access weekly plan data
    $curriculumLesson = $weeklyPlan->curriculumLesson;
    $classroom = $weeklyPlan->cst->classroom;
    $subject = $weeklyPlan->cst->subject;
}
```

### Finding Period Activities for a Weekly Plan

```php
// Get all period activities linked to a weekly plan
$weeklyPlan = WeeklyPlan::find($id);
$periodActivities = PeriodActivity::where('lesson_code', $weeklyPlan->code)->get();
```

## Workflow

1. **Planning Phase**:
   - Teachers create weekly plans for their subjects, specifying which curriculum lessons will be taught in which weeks and periods.
   - Each weekly plan gets a unique code in the format "year.semester.week.period".

2. **Execution Phase**:
   - When teachers conduct actual classroom activities, they create period activity records.
   - They can link these activities to their weekly plans by setting the `lesson_code` field.
   - This creates a connection between the planned curriculum and the actual classroom activities.

3. **Reporting Phase**:
   - The system can generate reports showing how closely the actual classroom activities followed the weekly plans.
   - Teachers and administrators can see which curriculum lessons were actually taught and when.

## Benefits

- **Flexibility**: Period activities can be created before or after weekly plans exist.
- **Traceability**: Easy to track which curriculum lessons were actually taught.
- **Planning**: Teachers can plan their curriculum in advance for the entire semester.
- **Reporting**: Administrators can see how closely the actual teaching follows the planned curriculum.
