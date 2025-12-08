# Period Code Deduplication Logic

## Overview

The reward system uses two-level period code deduplication to prevent duplicate behavior records while maintaining comprehensive audit trails.

## Period Code Structure

### 1. `period_code_main` (Teaching Context)
**Format:** `classroom_id.subject_id.teacher_id`

**Purpose:** Uniquely identifies the teaching context (which class, subject, and teacher combination)

**Examples:**
- `5.3.12` = Classroom 5, Subject (Math) 3, Teacher 12
- `2.7.8` = Classroom 2, Subject (English) 7, Teacher 8

**Why This:** Ensures that behaviors are tied to the specific class and subject being taught, not just the student.

---

### 2. `period_code` (Time Period)
**Format:** `year_id.semester.week.day.period`

**Purpose:** Uniquely identifies the specific time period when the behavior occurred

**Examples:**
- `1.1.1.1` = Academic Year 1, Semester 1, Week 1, Day 1, Period 1 (Saturday morning, period 1)
- `1.2.5.3.4` = Academic Year 1, Semester 2, Week 5, Day 3 (Monday), Period 4

**Components:**
- `year_id`: Academic year identifier (auto-filled from active year)
- `semester`: 1-4 (school has 4 semesters per year)
- `week`: 1-16 (each semester has 16 weeks)
- `day`: 1-7 (Monday=1, Tuesday=2, ..., Sunday=7)
  - Or Saturday=1, Sunday=2, etc. (depends on school calendar)
- `period`: 1-8 (each day has 8 periods max)

**Why This:** Captures the exact moment in time when the behavior occurred, allowing for detailed tracking and reporting by specific periods.

---

## Deduplication Logic

### When Creating a New Behavior Record

The system checks if a `StudentBehaviorsMain` record **already exists** before creating a new one.

**Deduplication Query:**
```sql
SELECT * FROM student_behaviors_mains 
WHERE school_id = ?
  AND year_id = ?
  AND student_id = ?
  AND period_code_main = ?
  AND date = ?
  AND (period_code = ? OR period_code IS NULL)
```

**Conditions for Reuse (Existing Record Found):**
- Same school
- Same academic year
- Same student
- Same `period_code_main` (same classroom/subject/teacher)
- Same date (same calendar day)
- Same or empty `period_code`

**If Found:** Use existing `StudentBehaviorsMain` record ID
**If Not Found:** Create new `StudentBehaviorsMain` record

---

## Implementation Details

### Backend: PeriodCodeService

Located in: `app/Services/PeriodCodeService.php`

**Key Methods:**

#### `generateMainCode($classroomId, $subjectId, $teacherId): string`
Generates the teaching context code.
```php
$mainCode = PeriodCodeService::generateMainCode(5, 3, 12);
// Returns: "5.3.12"
```

#### `generatePeriodCode($yearId, $semester, $week, $day, $period): string`
Generates the time period code.
```php
$periodCode = PeriodCodeService::generatePeriodCode(1, 2, 5, 3, 4);
// Returns: "1.2.5.3.4"
```

#### `parsePeriodCode($periodCode): ?array`
Parses period code into components.
```php
$parsed = PeriodCodeService::parsePeriodCode("1.2.5.3.4");
// Returns: ['year_id' => 1, 'semester' => 2, 'week' => 5, 'day' => 3, 'period' => 4]
```

#### `validatePeriodCode($periodCode, $maxSemester=4, $maxWeek=16, $maxDay=7, $maxPeriod=8): bool`
Validates period code format and ranges.
```php
$isValid = PeriodCodeService::validatePeriodCode("1.2.5.3.4");
// Returns: true
```

#### `comparePeriods($code1, $code2): ?int`
Compares two period codes chronologically.
```php
$result = PeriodCodeService::comparePeriods("1.1.1.1", "1.2.5.3.4");
// Returns: -1 (first period is earlier)
```

---

### Backend: StudentBehaviorController

**quickCreate() Method Flow:**

1. **Validate Input**
   - Required: `student_id`, `behavior_id`, `date`
   - Optional: `period_code`, `notes`

2. **Resolve Context**
   - Get authenticated teacher
   - Get school from teacher
   - Get active academic year
   - Get behavior details
   - Derive classroom and subject from teacher assignments

3. **Generate Codes**
   ```php
   $periodCodeMain = PeriodCodeService::generateMainCode($classroomId, $subjectId, $teacherId);
   // Example: "5.3.12"
   
   $periodCode = $request->period_code ?? '';
   // Example: "1.2.5.3.4"
   ```

4. **Check for Existing Record (Deduplication)**
   ```php
   $existing = StudentBehaviorsMain::where('school_id', $schoolId)
       ->where('year_id', $yearId)
       ->where('student_id', $studentId)
       ->where('period_code_main', $periodCodeMain)
       ->where('date', $date)
       ->where('period_code', $periodCode)
       ->first();
   ```

5. **Reuse or Create**
   - If exists: Use existing record ID
   - If not: Create new record with all codes

6. **Create StudentBehavior**
   - Links to `StudentBehaviorsMain` record
   - Records points and attendance

---

## Frontend: Period Code Generation

In `reward_sys.vue`:

```javascript
const periodCode = computed(() => {
  return `${selectedSemester.value}.${selectedWeek.value}.${selectedDay.value}.${selectedPeriodNumber.value}`
})
// Note: year_id is added by backend
```

The frontend generates: `semester.week.day.period`
The backend adds: `year_id` prefix

Example flow:
1. Frontend selects: Semester 2, Week 5, Day 3, Period 4
2. Frontend sends: `period_code: "1.2.5.3.4"` (if year_id = 1)
3. Backend validates and stores

---

## Benefits of This Approach

### ✅ **Prevents Duplicate Entries**
- Same student, same class, same day, same period = ONE record
- Multiple behaviors in same period still reuse the same main record
- Creates multiple StudentBehavior entries (one per behavior) under same main

### ✅ **Maintains Audit Trail**
- Each behavior action is tracked separately
- StudentBehaviorsPointAction records track individual point changes
- Can see full history of what happened in each period

### ✅ **Efficient Data Storage**
- Reduces StudentBehaviorsMain records (grouped by period)
- Detailed StudentBehavior records for each action
- Granular StudentBehaviorsPointAction for audit trail

### ✅ **Supports Transaction Model**
- Multiple behaviors per period → multiple StudentBehavior records
- Multiple point actions per behavior → multiple StudentBehaviorsPointAction records
- Can calculate totals dynamically from point actions

---

## Example Scenarios

### Scenario 1: First Behavior in Period
**Teacher applies "Good Attendance" to Student 1 in Period 1.1.1.1**

1. Check: Does StudentBehaviorsMain exist for Student 1, Classroom 5, Subject 3, Teacher 12, Date 2025-11-15, Period 1.1.1.1?
   - **No** → Create new
   
2. Create StudentBehaviorsMain:
   - `period_code_main: "5.3.12"`
   - `period_code: "1.1.1.1"`
   - `date: 2025-11-15`

3. Create StudentBehavior linked to this record

**Database Result:**
- ✅ StudentBehaviorsMain created (ID: 42)
- ✅ StudentBehavior created (ID: 100)

---

### Scenario 2: Second Behavior in Same Period
**Teacher applies "Participation" to Same Student 1 in Same Period 1.1.1.1**

1. Check: Does StudentBehaviorsMain exist for Student 1, Classroom 5, Subject 3, Teacher 12, Date 2025-11-15, Period 1.1.1.1?
   - **Yes** → Record ID 42 found
   
2. **Reuse** StudentBehaviorsMain (ID: 42)

3. Create new StudentBehavior linked to **same** record (ID: 42)

**Database Result:**
- ✅ StudentBehaviorsMain (ID: 42) - REUSED
- ✅ StudentBehavior (ID: 101) - NEW (linked to same main)

**SQL Query:**
```sql
SELECT sb.*, sbm.period_code_main, sbm.period_code
FROM student_behaviors sb
JOIN student_behaviors_mains sbm ON sb.student_behaviors_mains_id = sbm.id
WHERE sbm.period_code_main = '5.3.12'
  AND sbm.period_code = '1.1.1.1'
  AND sbm.date = '2025-11-15'
  AND sb.student_id = 1;
-- Returns: 2 records (both behaviors in this period)
```

---

### Scenario 3: Same Behavior, Different Period
**Teacher applies "Good Attendance" to Student 1 again, but in Period 1.1.2.1 (Day 2)**

1. Check: Does StudentBehaviorsMain exist for Period 1.1.2.1?
   - **No** (different period code)
   
2. Create **new** StudentBehaviorsMain:
   - `period_code: "1.1.2.1"` (different)

3. Create new StudentBehavior

**Database Result:**
- ✅ StudentBehaviorsMain (ID: 43) - NEW
- ✅ StudentBehavior (ID: 102) - NEW

---

## API Endpoint: quickCreate

**POST** `/api/student-behaviors/quick-create`

**Request Payload:**
```json
{
  "student_id": 1,
  "behavior_id": 3,
  "date": "2025-11-15",
  "period_code": "1.1.1.1",
  "notes": "Good performance in math"
}
```

**Response (201 Created):**
```json
{
  "id": 100,
  "school_id": 1,
  "student_behaviors_mains_id": 42,
  "student_id": 1,
  "attend": true,
  "points_plus": 5,
  "points_minus": 0,
  "points_details": {
    "behavior_id": 3,
    "behavior_name": "Good Attendance",
    "behavior_type": "positive"
  },
  "notes": "Good performance in math",
  "created_at": "2025-11-15T14:30:00Z",
  "updated_at": "2025-11-15T14:30:00Z",
  "student": {...},
  "behaviorMain": {
    "id": 42,
    "period_code_main": "5.3.12",
    "period_code": "1.1.1.1",
    "date": "2025-11-15"
  }
}
```

---

## Migration (If Creating New Fields)

If you need to add indexes for faster deduplication queries:

```php
Schema::table('student_behaviors_mains', function (Blueprint $table) {
    $table->index(['school_id', 'year_id', 'student_id', 'period_code_main', 'date']);
    $table->index(['period_code_main', 'period_code', 'date']);
});
```

---

## Summary

| Aspect | Details |
|--------|---------|
| **Main Code Purpose** | Identifies teaching context (class/subject/teacher) |
| **Main Code Format** | `classroom_id.subject_id.teacher_id` |
| **Period Code Purpose** | Identifies specific time period |
| **Period Code Format** | `year_id.semester.week.day.period` |
| **Deduplication Check** | Looks for existing record matching: school, year, student, main_code, period_code, date |
| **When Records Reused** | Same student, same class, same day, same period |
| **When New Records Created** | Different student, class, day, or period |
| **Service Class** | `PeriodCodeService` for all code generation/parsing |
| **Controller Method** | `StudentBehaviorController::quickCreate()` implements logic |

