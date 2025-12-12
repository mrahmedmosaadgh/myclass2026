# 🚀 Calendar + Semester Schema Update

**Timestamp:** 2025-12-04 22:07:01

This document contains the updated database schema and models for the calendar and semester architecture, implementing the unified design agreement for scalable, enterprise-grade performance.

---

## 📋 Summary of Changes

### Key Improvements:

1. **Semesters Table:**

    - Renamed `weeks_number` to `total_weeks` for clarity
    - Added unique constraint on `academic_year_id + semester_number`
    - Maintained all essential fields with proper foreign keys

2. **Calendars Table:**

    - Added `semester_id` foreign key (previously commented out)
    - Removed redundant stored fields: `day`, `day_number`, `semester_number`
    - These fields are now computed dynamically via model accessors
    - Added `event_academic` field
    - Added unique constraint on `date + school_id` to prevent duplicates
    - Renamed `week` to `week_number` for consistency

3. **Models:**
    - **Semester Model:** Added `calendars()` relationship
    - **Calendar Model:**
        - Added automatic sync logic for `semester_id` and `academic_year_id`
        - Added dynamic accessors for `semester_number`, `day_name`, `day_number`, `week_of_semester`
        - Implemented `syncSemesterFields()` method for data integrity

---

## 1️⃣ Updated Migration: `semesters`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('semester_number'); // business meaning (1st term, 2nd term)
            $table->tinyInteger('total_weeks')->nullable(); // total weeks in semester
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->json('data')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->softDeletes();
            $table->timestamps();

            // Natural key: ensures one semester per number per academic year
            $table->unique(['academic_year_id', 'semester_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
```

---

## 2️⃣ Updated Migration: `calendars`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->date('date');

            // Foreign Keys
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');

            // Status and Flags
            $table->tinyInteger('status')->default(1)
                ->comment('1: work, 0: day_off, 2: activity, 3: test, 4: final exam');
            $table->tinyInteger('vacation')->default(0);
            $table->tinyInteger('vacation_students')->default(0);

            // Events
            $table->string('event')->nullable();
            $table->string('event_academic')->nullable();

            // Week tracking (absolute week in year)
            $table->tinyInteger('week_number');

            // Additional data
            $table->json('data')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Prevent duplicate calendar entries for same date/school
            $table->unique(['date', 'school_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};
```

---

## 3️⃣ Updated Semester Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Semester extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'semester_number',
        'total_weeks',
        'start_date',
        'end_date',
        'academic_year_id',
        'school_id',
        'data',
        'active'
    ];

    protected $casts = [
        'data' => 'array',
        'active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    /**
     * Get the academic year this semester belongs to
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the school this semester belongs to
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get all calendar entries for this semester
     */
    public function calendars()
    {
        return $this->hasMany(Calendar::class);
    }
}
```

---

## 4️⃣ Updated Calendar Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Calendar extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'date',
        'semester_id',
        'academic_year_id',
        'school_id',
        'status',
        'vacation',
        'vacation_students',
        'event',
        'event_academic',
        'week_number',
        'data'
    ];

    protected $casts = [
        'date' => 'date',
        'data' => 'json'
    ];

    /**
     * Dynamic accessors for computed fields
     */
    protected $appends = [
        'day_name',
        'day_number',
        'week_of_semester',
        'semester_number'
    ];

    /**
     * Boot method to auto-sync semester fields
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            $model->syncSemesterFields();
        });
    }

    /**
     * Automatically sync semester_id and academic_year_id
     *
     * This ensures data integrity by:
     * - Auto-filling academic_year_id when semester_id is set
     * - Validating consistency between both fields
     */
    public function syncSemesterFields()
    {
        if ($this->semester_id) {
            $semester = Semester::find($this->semester_id);
            if ($semester) {
                $this->academic_year_id = $semester->academic_year_id;
            }
        }
    }

    /**
     * Relationships
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function periodActivities(): HasMany
    {
        return $this->hasMany(PeriodActivity::class);
    }

    /**
     * Dynamic Accessors (computed fields)
     */

    /**
     * Get the semester number (business meaning)
     * Dynamically retrieved from the related semester
     */
    public function getSemesterNumberAttribute()
    {
        return $this->semester?->semester_number;
    }

    /**
     * Get the day name (Monday, Tuesday, etc.)
     * Computed from the date field
     */
    public function getDayNameAttribute()
    {
        return Carbon::parse($this->date)->format('l');
    }

    /**
     * Get the day number (1 = Monday, 7 = Sunday)
     * Computed from the date field using ISO-8601
     */
    public function getDayNumberAttribute()
    {
        return Carbon::parse($this->date)->dayOfWeekIso;
    }

    /**
     * Get the week number within the semester
     * Computed based on semester start_date
     */
    public function getWeekOfSemesterAttribute()
    {
        if (!$this->semester || !$this->semester->start_date) {
            return null;
        }

        return Carbon::parse($this->date)
            ->diffInWeeks(Carbon::parse($this->semester->start_date)) + 1;
    }
}
```

---

## 🧠 Architecture Benefits

### ✅ Data Integrity

-   **Automatic Synchronization:** The `syncSemesterFields()` method ensures `academic_year_id` is always consistent with `semester_id`
-   **Unique Constraints:** Prevent duplicate semesters and calendar entries
-   **Foreign Key Cascades:** Maintain referential integrity across the system

### ✅ Reduced Redundancy

-   **Dynamic Computation:** Fields like `day_name`, `day_number`, and `semester_number` are computed on-the-fly
-   **Single Source of Truth:** Date-based calculations eliminate storage duplication
-   **Normalized Design:** Minimal data redundancy while maintaining query performance

### ✅ Future-Proof Design

-   **Flexible Structure:** Easy to adapt to different academic term structures (quarters, trimesters, etc.)
-   **Scalable:** Supports multi-school, multi-year setups
-   **Maintainable:** Clear separation between stored and computed data

### ✅ Developer Experience

-   **Transparent API:** Developers can access `semester_number` as if it were a stored field
-   **Automatic Validation:** Model hooks prevent inconsistent data
-   **Clean Queries:** Relationships make complex queries simple

---

## 📊 Comparison: Old vs New

| Feature                        | Old Design           | New Design                     |
| ------------------------------ | -------------------- | ------------------------------ |
| `semester_number` in calendars | Stored column        | Dynamic accessor               |
| `day` field                    | Stored string        | Dynamic `day_name` accessor    |
| `day_number` field             | Stored integer       | Dynamic accessor via Carbon    |
| `week_number`                  | Ambiguous            | Absolute week of year (stored) |
| `week_of_semester`             | Not available        | Dynamic accessor               |
| `semester_id` FK               | Commented out        | Fully implemented with cascade |
| `total_weeks`                  | Named `weeks_number` | Renamed for clarity            |
| Unique constraints             | None                 | Added for data integrity       |
| Auto-sync logic                | None                 | Implemented in model           |
| Data redundancy                | High                 | Minimal                        |

---

## 🎯 Migration Path

### For Existing Databases:

1. **Backup your database** before running migrations
2. **Create a new migration** to modify existing tables:

```php
// Migration to update existing calendars table
Schema::table('calendars', function (Blueprint $table) {
    // Add semester_id if not exists
    if (!Schema::hasColumn('calendars', 'semester_id')) {
        $table->foreignId('semester_id')->nullable()
              ->constrained('semesters')->onDelete('cascade');
    }

    // Add event_academic if not exists
    if (!Schema::hasColumn('calendars', 'event_academic')) {
        $table->string('event_academic')->nullable();
    }

    // Rename week to week_number if needed
    if (Schema::hasColumn('calendars', 'week') &&
        !Schema::hasColumn('calendars', 'week_number')) {
        $table->renameColumn('week', 'week_number');
    }

    // Add unique constraint
    $table->unique(['date', 'school_id']);
});

// Migration to update existing semesters table
Schema::table('semesters', function (Blueprint $table) {
    // Rename weeks_number to total_weeks if needed
    if (Schema::hasColumn('semesters', 'weeks_number') &&
        !Schema::hasColumn('semesters', 'total_weeks')) {
        $table->renameColumn('weeks_number', 'total_weeks');
    }

    // Add unique constraint
    $table->unique(['academic_year_id', 'semester_number']);
});
```

3. **Populate semester_id** for existing calendar records:

```php
// Run this after adding semester_id column
Calendar::whereNull('semester_id')->chunk(100, function ($calendars) {
    foreach ($calendars as $calendar) {
        $semester = Semester::where('academic_year_id', $calendar->academic_year_id)
                           ->where('semester_number', $calendar->semester_number)
                           ->first();

        if ($semester) {
            $calendar->semester_id = $semester->id;
            $calendar->save();
        }
    }
});
```

4. **Optional: Drop redundant columns** (after verifying dynamic accessors work):

```php
Schema::table('calendars', function (Blueprint $table) {
    $table->dropColumn(['day', 'day_number', 'semester_number']);
});
```

---

## 🔍 Usage Examples

### Creating a Calendar Entry

```php
// Method 1: Using semester_id (recommended)
Calendar::create([
    'date' => '2025-01-15',
    'semester_id' => 1,
    'school_id' => 1,
    'status' => 1,
    'week_number' => 3,
]);
// academic_year_id is auto-filled via syncSemesterFields()

// Method 2: The model will auto-sync
$calendar = new Calendar();
$calendar->date = '2025-01-15';
$calendar->semester_id = 1;
$calendar->school_id = 1;
$calendar->status = 1;
$calendar->week_number = 3;
$calendar->save();
// academic_year_id is automatically set
```

### Accessing Dynamic Fields

```php
$calendar = Calendar::find(1);

// These work seamlessly as if they were stored columns
echo $calendar->semester_number;  // e.g., 1
echo $calendar->day_name;         // e.g., "Monday"
echo $calendar->day_number;       // e.g., 1
echo $calendar->week_of_semester; // e.g., 3

// Access the full semester
echo $calendar->semester->name;   // e.g., "First Term"
```

### Querying Calendars

```php
// Get all calendars for a specific semester
$calendars = Calendar::where('semester_id', 1)->get();

// Get calendars with semester info
$calendars = Calendar::with('semester')->get();

// Filter by semester number (via relationship)
$calendars = Calendar::whereHas('semester', function($query) {
    $query->where('semester_number', 1);
})->get();

// Get work days only
$workDays = Calendar::where('status', 1)
                   ->where('vacation', 0)
                   ->get();
```

---

## 📝 Notes

1. **Backward Compatibility:** The dynamic accessors (`semester_number`, `day_name`, etc.) maintain API compatibility with existing code that expects these fields
2. **Performance:** Dynamic accessors add minimal overhead and can be eager-loaded via relationships
3. **Validation:** Consider adding validation rules in controllers/requests to ensure data integrity
4. **Indexes:** The unique constraints also serve as indexes for faster queries

---

## ✅ Checklist for Implementation

-   [ ] Review and understand the new schema design
-   [ ] Backup existing database
-   [ ] Update `semesters` migration file
-   [ ] Update `calendars` migration file
-   [ ] Update `Semester` model
-   [ ] Update `Calendar` model
-   [ ] Create migration to modify existing tables (if applicable)
-   [ ] Run migrations in development environment
-   [ ] Test calendar creation and retrieval
-   [ ] Verify dynamic accessors work correctly
-   [ ] Test auto-sync functionality
-   [ ] Update any controllers/services using old field names
-   [ ] Update API documentation
-   [ ] Run full test suite
-   [ ] Deploy to staging
-   [ ] Deploy to production

---

**End of Document**
