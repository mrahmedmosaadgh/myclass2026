# Period Code Deduplication - Database Schema & Queries

## Database Schema

### student_behaviors_mains Table

```sql
CREATE TABLE student_behaviors_mains (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT NOT NULL,
    year_id BIGINT NOT NULL,
    student_id BIGINT NOT NULL,
    teacher_id BIGINT NOT NULL,
    subject_id BIGINT NOT NULL,
    classroom_id BIGINT NOT NULL,
    
    -- NEW: Deduplication codes
    period_code_main VARCHAR(100),  -- Format: classroom_id.subject_id.teacher_id
    period_code VARCHAR(100),       -- Format: year_id.semester.week.day.period
    
    date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    -- Recommended indexes for deduplication queries
    UNIQUE KEY uq_period_dedup (school_id, year_id, student_id, period_code_main, date, period_code),
    INDEX idx_period_main (period_code_main),
    INDEX idx_period_code (period_code),
    INDEX idx_date (date),
    INDEX idx_student_date (student_id, date)
);
```

### Recommended Indexes

For optimal performance with deduplication queries:

```sql
-- Primary deduplication index
ALTER TABLE student_behaviors_mains 
ADD UNIQUE INDEX uq_period_dedup (
    school_id, 
    year_id, 
    student_id, 
    period_code_main, 
    date,
    period_code
);

-- Secondary indexes for common queries
ALTER TABLE student_behaviors_mains 
ADD INDEX idx_main_code (period_code_main);

ALTER TABLE student_behaviors_mains 
ADD INDEX idx_period_code (period_code);

ALTER TABLE student_behaviors_mains 
ADD INDEX idx_student_date (student_id, date);

ALTER TABLE student_behaviors_mains 
ADD INDEX idx_teacher_date (teacher_id, date);

ALTER TABLE student_behaviors_mains 
ADD INDEX idx_classroom_subject_teacher (classroom_id, subject_id, teacher_id);
```

---

## Deduplication Queries

### 1. Check if Record Exists (Before Creating)

**Purpose:** Determine if we should create a new record or reuse an existing one

```sql
-- Query to find existing StudentBehaviorsMain
SELECT * FROM student_behaviors_mains 
WHERE school_id = ?
  AND year_id = ?
  AND student_id = ?
  AND period_code_main = ?
  AND date = ?
  AND (period_code = ? OR period_code = '' OR period_code IS NULL)
LIMIT 1;
```

**PHP Eloquent:**
```php
$existing = StudentBehaviorsMain::where('school_id', $schoolId)
    ->where('year_id', $yearId)
    ->where('student_id', $studentId)
    ->where('period_code_main', $periodCodeMain)
    ->where('date', $date)
    ->where(function ($query) use ($periodCode) {
        $query->where('period_code', $periodCode)
              ->orWhere('period_code', '')
              ->orWhereNull('period_code');
    })
    ->first();
```

**Parameters:**
- `school_id`: Integer (from teacher's school)
- `year_id`: Integer (from active academic year)
- `student_id`: Integer (from request)
- `period_code_main`: String format `classroom_id.subject_id.teacher_id`
  - Example: `5.3.12`
- `date`: Date string format `YYYY-MM-DD`
  - Example: `2025-11-15`
- `period_code`: String format `year_id.semester.week.day.period`
  - Example: `1.1.1.1`

---

### 2. Get All Behaviors in a Period

**Purpose:** Retrieve all behaviors that occurred in a specific period

```sql
SELECT sb.*, sbm.period_code_main, sbm.period_code, sbm.date
FROM student_behaviors sb
JOIN student_behaviors_mains sbm ON sb.student_behaviors_mains_id = sbm.id
WHERE sbm.period_code_main = '5.3.12'
  AND sbm.period_code = '1.1.1.1'
  AND sbm.date = '2025-11-15'
ORDER BY sb.created_at;
```

**PHP Eloquent:**
```php
$behaviors = StudentBehavior::whereHas('behaviorMain', function ($query) {
    $query->where('period_code_main', '5.3.12')
          ->where('period_code', '1.1.1.1')
          ->where('date', '2025-11-15');
})->with('behaviorMain')
  ->orderBy('created_at')
  ->get();
```

---

### 3. Get All Behaviors for Student Today

**Purpose:** Get all behaviors recorded for a student on a specific date

```sql
SELECT sb.*, sbm.*
FROM student_behaviors sb
JOIN student_behaviors_mains sbm ON sb.student_behaviors_mains_id = sbm.id
WHERE sbm.student_id = ?
  AND DATE(sbm.date) = ?
ORDER BY sbm.created_at;
```

**PHP Eloquent:**
```php
$todayBehaviors = StudentBehavior::whereHas('behaviorMain', function ($query) {
    $query->where('student_id', 1)
          ->whereDate('date', today());
})->with('behaviorMain')
  ->orderBy('created_at')
  ->get();
```

---

### 4. Get All Behaviors for Classroom in Period

**Purpose:** See what happened in a specific class during a specific period

```sql
SELECT sb.*, sbm.*, s.id AS student_id, s.name AS student_name
FROM student_behaviors sb
JOIN student_behaviors_mains sbm ON sb.student_behaviors_mains_id = sbm.id
JOIN students s ON sb.student_id = s.id
WHERE sbm.classroom_id = ?
  AND sbm.subject_id = ?
  AND sbm.teacher_id = ?
  AND sbm.period_code = '1.1.1.1'
  AND sbm.date = '2025-11-15'
ORDER BY sbm.student_id, sb.created_at;
```

**PHP Eloquent:**
```php
$periodBehaviors = StudentBehavior::whereHas('behaviorMain', function ($query) {
    $query->where('classroom_id', 5)
          ->where('subject_id', 3)
          ->where('teacher_id', 12)
          ->where('period_code', '1.1.1.1')
          ->where('date', '2025-11-15');
})
->with(['student', 'behaviorMain'])
->orderBy('student_id')
->get();
```

---

### 5. Aggregate Points by Student in Period

**Purpose:** Calculate total points for each student in a specific period

```sql
SELECT 
    sbm.student_id,
    s.name AS student_name,
    COUNT(sb.id) AS behavior_count,
    SUM(CASE WHEN sb.points_plus > 0 THEN sb.points_plus ELSE 0 END) AS total_positive,
    SUM(CASE WHEN sb.points_minus > 0 THEN sb.points_minus ELSE 0 END) AS total_negative,
    (
        SUM(CASE WHEN sb.points_plus > 0 THEN sb.points_plus ELSE 0 END) - 
        SUM(CASE WHEN sb.points_minus > 0 THEN sb.points_minus ELSE 0 END)
    ) AS net_points
FROM student_behaviors sb
JOIN student_behaviors_mains sbm ON sb.student_behaviors_mains_id = sbm.id
JOIN students s ON sbm.student_id = s.id
WHERE sbm.classroom_id = ?
  AND sbm.subject_id = ?
  AND sbm.teacher_id = ?
  AND sbm.period_code = '1.1.1.1'
  AND sbm.date = '2025-11-15'
GROUP BY sbm.student_id, s.name
ORDER BY net_points DESC;
```

**PHP (Manual Grouping):**
```php
$leaderboard = StudentBehavior::whereHas('behaviorMain', function ($query) {
    $query->where('classroom_id', 5)
          ->where('subject_id', 3)
          ->where('teacher_id', 12)
          ->where('period_code', '1.1.1.1')
          ->where('date', '2025-11-15');
})
->with(['student', 'behaviorMain'])
->get()
->groupBy('student_id')
->map(function ($behaviors) {
    $positive = $behaviors->sum('points_plus');
    $negative = $behaviors->sum('points_minus');
    return [
        'student' => $behaviors->first()->student,
        'behavior_count' => $behaviors->count(),
        'positive_points' => $positive,
        'negative_points' => $negative,
        'net_points' => $positive - $negative,
    ];
})
->sortByDesc('net_points');
```

---

### 6. Duplicate Check Before Creating

**Purpose:** The main query used in `quickCreate()` to prevent duplicates

```sql
SELECT * FROM student_behaviors_mains 
WHERE school_id = ?
  AND year_id = ?
  AND student_id = ?
  AND period_code_main = ?
  AND date = ?
  AND period_code = ?
LIMIT 1;
```

**Return Values:**
- **NULL/Empty:** No existing record → CREATE NEW
- **Record Object:** Existing record found → REUSE its ID

**Example Flow:**

```
Request arrives with:
- student_id: 1
- behavior_id: 3
- date: 2025-11-15
- period_code: 1.1.1.1

System generates:
- period_code_main: 5.3.12 (from classroom 5, subject 3, teacher 12)

Query executes:
SELECT * FROM student_behaviors_mains 
WHERE school_id = 1
  AND year_id = 1
  AND student_id = 1
  AND period_code_main = '5.3.12'
  AND date = '2025-11-15'
  AND period_code = '1.1.1.1'

Result:
✓ Found ID 42
  └─> REUSE StudentBehaviorsMain (ID: 42)
     └─> CREATE new StudentBehavior (linked to ID: 42)

OR

✗ Not found
  └─> CREATE StudentBehaviorsMain (gets new ID)
     └─> CREATE StudentBehavior (linked to new ID)
```

---

## Example Data

### StudentBehaviorsMain Table State

```
ID | student_id | period_code_main | period_code | date       | classroom_id | subject_id | teacher_id
---|------------|------------------|-------------|------------|------|---|---
42 | 1          | 5.3.12           | 1.1.1.1     | 2025-11-15 | 5    | 3 | 12
43 | 1          | 5.3.12           | 1.1.2.1     | 2025-11-15 | 5    | 3 | 12
44 | 2          | 5.3.12           | 1.1.1.1     | 2025-11-15 | 5    | 3 | 12
45 | 1          | 2.7.8            | 1.1.1.2     | 2025-11-15 | 2    | 7 | 8
```

### StudentBehaviors Table State

```
ID  | student_id | student_behaviors_mains_id | points_plus | points_minus
----|------------|---------------------------|-------------|-------------
100 | 1          | 42                        | 5           | 0
101 | 1          | 42                        | 0           | 3
102 | 1          | 43                        | 5           | 0
103 | 2          | 44                        | 5           | 0
104 | 1          | 45                        | 0           | 2
```

### Interpretation

**Student 1 in Period 1.1.1.1 (Classroom 5, Subject 3):**
- Behaviors: 100 (5 points), 101 (-3 points)
- Total: +2 net points
- Main record: ID 42 (shared)

**Student 1 in Period 1.1.2.1 (Same class, different time):**
- Behaviors: 102 (5 points)
- Total: +5 net points
- Main record: ID 43 (new, different period)

---

## Migration SQL

If you need to add these fields to an existing table:

```sql
ALTER TABLE student_behaviors_mains
ADD COLUMN period_code_main VARCHAR(100) NULL AFTER classroom_id,
ADD COLUMN period_code VARCHAR(100) NULL AFTER period_code_main;

-- Add indexes
ALTER TABLE student_behaviors_mains 
ADD UNIQUE INDEX uq_period_dedup (
    school_id, 
    year_id, 
    student_id, 
    period_code_main, 
    date,
    period_code
);

ALTER TABLE student_behaviors_mains 
ADD INDEX idx_period_main (period_code_main),
ADD INDEX idx_period_code (period_code);
```

---

## Performance Considerations

### Query Performance Tips

1. **Always use school_id + year_id + student_id + date filters first** (most restrictive)
2. **period_code_main should be indexed** (used in grouping queries)
3. **period_code should be indexed** (used for time-based filtering)
4. **Use LIMIT 1** when checking for existence (stops after first match)

### Typical Query Times

```
Deduplication check (indexed):     ~1-2ms
Get period behaviors (indexed):    ~5-10ms
Aggregate by student (grouped):    ~20-50ms
```

### Optimize if Needed

```sql
-- Monitor slow queries
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;

-- Analyze query performance
EXPLAIN SELECT * FROM student_behaviors_mains 
WHERE school_id = 1 AND year_id = 1 AND student_id = 1;
```

---

## Validation Queries

### Verify No Duplicates

```sql
-- Find any duplicate main records
SELECT period_code_main, period_code, date, student_id, COUNT(*) as count
FROM student_behaviors_mains
GROUP BY period_code_main, period_code, date, student_id
HAVING COUNT(*) > 1;

-- Expected result: Empty (0 rows)
```

### Verify Data Integrity

```sql
-- Check for orphaned StudentBehavior records
SELECT COUNT(*) as orphaned
FROM student_behaviors sb
LEFT JOIN student_behaviors_mains sbm ON sb.student_behaviors_mains_id = sbm.id
WHERE sbm.id IS NULL;

-- Expected result: 0
```

### Verify Period Code Format

```sql
-- Find any malformed period codes
SELECT id, period_code_main, period_code
FROM student_behaviors_mains
WHERE period_code_main NOT REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'
   OR period_code NOT REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+$';

-- Expected result: Empty (0 rows)
```

