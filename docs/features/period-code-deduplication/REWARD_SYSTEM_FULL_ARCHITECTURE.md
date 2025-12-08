# Reward System - Complete Architecture Overview

## 🎯 Purpose
Teacher-facing classroom management tool that:
- Records positive/negative student behaviors as points
- Tracks student progress and attendance
- Displays real-time leaderboards
- Manages student avatars with camera/upload support
- Provides kid-friendly gamification with sound/TTS

---

## 📊 Data Model & Flow

### Database Schema (Three-Table Hierarchy)

```
Behaviors (Master List)
├── id: integer (PK)
├── name: string (e.g., "Did homework", "Great attention")
├── type: enum (positive | negative)
├── points: integer (e.g., 5, -3)
├── school_id: FK → Schools
├── year_id: FK → AcademicYears
└── is_active: boolean

StudentBehaviorsMain (Session Parent)
├── id: integer (PK)
├── school_id: FK → Schools
├── year_id: FK → AcademicYears
├── student_id: FK → Students
├── teacher_id: FK → Teachers
├── subject_id: FK → Subjects
├── classroom_id: FK → Classrooms
├── date: date
├── period_code_main: string (auto-generated, e.g., "auto-123abc")
├── period_code: string (user input, e.g., "1.1.1.1")
├── notes: text
└── timestamps

StudentBehavior (Individual Behavior Record)
├── id: integer (PK)
├── school_id: FK → Schools
├── student_behaviors_mains_id: FK → StudentBehaviorsMain
├── student_id: FK → Students
├── attend: boolean (attendance flag)
├── points_plus: integer (positive points awarded)
├── points_minus: integer (negative points deducted)
├── points_details: JSON (metadata: behavior_id, behavior_name, behavior_type)
├── notes: text
└── timestamps

StudentBehaviorsPointAction (Granular Tracking)
├── id: integer (PK)
├── student_behaviors_id: FK → StudentBehavior
├── value: integer (point change)
├── reason_id: FK → Behaviors
├── created_by: FK → Users
├── canceled_by: FK → Users
├── canceled: boolean (soft delete flag)
└── timestamps
```

---

## 🔄 Data Flow

### 1. **Initialization** (Vue Component Load)

```
User Opens reward_sys.vue
  ↓
onMounted Hook
  ├─→ GET /my_classes_with_students
  │    └─→ Returns: [{ id, classroom_name, students: [...] }, ...]
  │
  └─→ GET /api/behaviors
       └─→ Returns: [{ id, name, type, points, ... }, ...]
```

**Frontend State After Init:**
```javascript
classrooms = [ { id: 1, classroom_name: "Class A", students: [...] }, ... ]
behaviors = [ { id: 3, name: "Did homework", type: "positive", points: 5 }, ... ]
```

---

### 2. **Classroom Selection**

```
User selects classroom from dropdown
  ↓
handleClassroomChange(classroomId)
  ├─→ Find classroom in already-loaded classrooms array
  ├─→ Extract students from classroom.students
  └─→ Update frontend state
      students = [ { id: 1, name: "Ahmed", ... }, ... ]
      selectedIds = []
      selectedDate = today's date (YYYY-MM-DD)
      selectedPeriod = "" (user can set to "1.1.1.1")
```

**Key Point:** Students are already loaded in the initial API call, so classroom switch is instant.

---

### 3. **Bulk Behavior Application** (Main Feature)

#### Step A: User Selects Students & Behavior
```
User clicks on student cards to toggle selection
  → selectedIds = [1, 5, 8]

User selects behavior from dropdown
  → selectedBehaviorId = 3 (the "Did homework" behavior)

User clicks "Apply to Selected" button
```

#### Step B: Frontend Sends Requests
```javascript
applyBehaviorToStudents(studentIds=[1,5,8], behaviorId=3, options={
  date: "2025-11-14",
  periodCode: "1.1.1.1"
})

// For each student, sends:
for studentId in [1, 5, 8]:
  POST /api/student-behaviors/quick-create
  {
    student_id: 1,           // or 5, 8
    behavior_id: 3,
    date: "2025-11-14",
    period_code: "1.1.1.1",
    notes: null
  }
```

#### Step C: Backend Processes (quickCreate Endpoint)

```
POST /api/student-behaviors/quick-create { student_id, behavior_id, date, period_code }
  ↓
Controller validates input:
  ✓ student_id exists in students table
  ✓ behavior_id exists in behaviors table
  ✓ date is valid date format
  ✓ period_code is nullable string
  ↓
Retrieves context from authenticated teacher:
  teacher = Teacher.where(user_id = auth()->id())
  school = teacher.school
  year = AcademicYear.where(active=1)
  behavior = Behavior.find(behavior_id)
  student = Student.find(student_id)
  ↓
Extracts points from behavior:
  type = behavior.type  // "positive" or "negative"
  points = behavior.points  // e.g., 5
  
  if type == "positive":
    points_plus = 5, points_minus = 0
  else:
    points_plus = 0, points_minus = 5
  ↓
Creates StudentBehaviorsMain record:
  INSERT INTO student_behaviors_mains (
    school_id, year_id, student_id, teacher_id,
    subject_id=1, classroom_id=1,
    period_code_main="auto-xyz123",
    period_code="1.1.1.1",
    date="2025-11-14",
    notes=null
  )
  ↓
Creates StudentBehavior record:
  INSERT INTO student_behaviors (
    school_id, student_behaviors_mains_id,
    student_id, attend=true,
    points_plus=5, points_minus=0,
    points_details=JSON({ behavior_id:3, behavior_name:"Did homework", behavior_type:"positive" })
  )
  ↓
Returns: 201 Created with behavior record
```

**Timeline:** 3 requests × ~200-300ms each = ~1 second for 3 students

---

### 4. **Student Summary Loading**

```
User clicks "Load Summaries" button
  ↓
loadAllSummaries()
  ├─→ For each student in students array:
  │    GET /api/student-behaviors/{studentId}
  │      ↓
  │      Controller aggregates:
  │      SELECT SUM(points_plus) FROM student_behaviors WHERE student_id=X
  │      SELECT SUM(points_minus) FROM student_behaviors WHERE student_id=X
  │      ↓
  │      Returns: { positive: 10, negative: 3, total: 7 }
  │
  └─→ Stores in frontend: studentPoints[studentId] = { positive, negative, total }

// Displayed in student cards:
+10 ⭐
-3 ⚠️
─────
 7 (total)
```

---

### 5. **Leaderboard Display**

```
User clicks "Leaderboard" button
  ↓
openLeaderboard() → loadLeaderboardData()
  ↓
GET /api/leaderboard?classroom_id=1&start_date=2025-11-14&end_date=2025-11-14&limit=5
  ↓
Controller groups by student_id:
  SELECT student_id, SUM(points_plus + points_minus) as total
  FROM student_behaviors_mains, student_behaviors
  WHERE date BETWEEN start_date AND end_date
  AND classroom_id = ?
  GROUP BY student_id
  ORDER BY total DESC
  LIMIT 5
  ↓
Returns:
  [
    { student_id: 5, positive: 15, negative: 2, total: 13 },
    { student_id: 8, positive: 10, negative: 5, total: 5 },
    ...
  ]
  ↓
Frontend displays with medals:
  🥇 #1 - Student 5 (13 points)
  🥈 #2 - Student 8 (5 points)
  🥉 #3 - ...
```

---

## 🏗️ Architecture Components

### Frontend Files

#### **Main Page** (`reward_sys.vue`)
- **Purpose:** Primary UI container and state management
- **Size:** ~420 lines (refactored from 4900)
- **Key State:**
  - `classrooms` - loaded from `/my_classes_with_students`
  - `behaviors` - loaded from `/api/behaviors`
  - `students` - selected from classroom
  - `selectedIds` - student checkboxes
  - `selectedBehaviorId` - behavior dropdown
  - `selectedDate` - date picker
  - `selectedPeriod` - period code input
  - `studentPoints` - cached summaries
  - `leaderboard` - cached leaderboard data

#### **Service Module** (`reward_sys_comp/reward_sys_point_action.js`)
- **Purpose:** Centralized API communication
- **Size:** ~650 lines
- **Key Exports:**
  - `applyBehaviorToStudents(studentIds, behaviorId, options)` - POST each student
  - `getStudentSummary(studentId)` - GET `/api/student-behaviors/{id}`
  - `fetchLeaderboard(options)` - GET `/api/leaderboard`
  - `fetchBehaviors()` - GET `/api/behaviors`
  - `addPoint()`, `cancelPoint()`, `restorePoint()` - point management (unused in current MVP)

#### **Selection Manager** (`reward_sys_comp/reward_sys_selection.js`)
- **Purpose:** Manages selected students and bulk actions
- **Key Methods:**
  - `toggleSelected(studentId)` - toggle checkbox
  - `clearSelection()` - clear all
  - `markSelectedPresent()` - UI notification
  - `markSelectedAbsent()` - UI notification
  - `applyBehaviorToSelected(behaviorId)` - calls applyBehaviorToStudents

---

### Backend Components

#### **Route Definitions** (`routes/api.php`)
```php
// Behavior master list
GET /api/behaviors → BehaviorController@index

// Standard behavior recording (unused in MVP)
POST /api/student-behaviors → StudentBehaviorController@store

// MAIN ENDPOINT: Quick-create behavior
POST /api/student-behaviors/quick-create → StudentBehaviorController@quickCreate

// Student summary aggregation
GET /api/student-behaviors/{studentId} → StudentBehaviorController@studentSummary

// Leaderboard aggregation
GET /api/leaderboard → StudentBehaviorController@leaderboard

// Web route (not API):
GET /my_classes_with_students → ClassroomSubjectTeacherController@myClassesWithStudents
```

#### **Controllers**

##### `StudentBehaviorController::quickCreate()`
- **Input Validation:**
  ```php
  student_id → exists:students
  behavior_id → exists:behaviors
  date → date format
  period_code → nullable|string
  notes → nullable|string
  ```
  
- **Context Retrieval:**
  ```php
  $user = auth()->user()
  $teacher = Teacher::where('user_id', $user->id)
  $school = $teacher->school
  $year = AcademicYear::where('active', 1)
  $behavior = Behavior::find(behavior_id)
  $student = Student::find(student_id)
  ```
  
- **Point Extraction:**
  ```php
  $type = $behavior->type  // "positive" or "negative"
  $points = $behavior->points
  
  if ($type === 'positive') {
    $points_plus = $points
    $points_minus = 0
  } else {
    $points_plus = 0
    $points_minus = $points
  }
  ```
  
- **Record Creation:**
  - Creates `StudentBehaviorsMain` with auto-generated period_code_main
  - Creates `StudentBehavior` with points_plus/points_minus
  - Returns 201 with created record

##### `StudentBehaviorController::studentSummary($studentId)`
- **Logic:**
  ```php
  $behaviors = StudentBehavior::where('student_id', $studentId)->get()
  
  foreach ($behaviors as $behavior) {
    $total_positive += $behavior->points_plus
    $total_negative += $behavior->points_minus
  }
  
  return { positive, negative, total: positive - negative }
  ```

##### `StudentBehaviorController::leaderboard()`
- **Input Validation:**
  ```php
  classroom_id → nullable|integer
  start_date → required|date
  end_date → required|date
  limit → nullable|integer|max:20
  ```
  
- **Aggregation:**
  ```php
  GROUP BY student_id
  WHERE date BETWEEN start_date AND end_date
  AND classroom_id = ? (if provided)
  ORDER BY (points_plus - points_minus) DESC
  LIMIT ?
  ```

#### **Models**

##### `Behavior`
```php
$fillable = ['name', 'type', 'points', 'school_id', 'year_id']
hasMany(StudentBehavior)
```

##### `StudentBehaviorsMain`
```php
hasMany(StudentBehavior)
belongsTo(School, Teacher, Student, AcademicYear, Subject, Classroom)
```

##### `StudentBehavior`
```php
belongsTo(StudentBehaviorsMain)
belongsTo(Student)
hasMany(StudentBehaviorsPointAction)

// Accessors for point calculations:
getPointsPlusAttribute()   // sum of positive point actions
getPointsMinusAttribute()  // sum of negative point actions
getTotalPointsAttribute()  // plus - minus
```

##### `StudentBehaviorsPointAction`
```php
belongsTo(StudentBehavior)
belongsTo(Behavior, 'reason_id')
belongsTo(User, 'created_by')
belongsTo(User, 'canceled_by')
```

---

## 🔐 Authentication & Context

### User Identification
```
Every request carries:
- Authorization: Bearer {sanctum_token}
- Cookie: XSRF-TOKEN, session_id

Backend retrieves context via:
$user = auth()->user()  // From token
$teacher = Teacher::where('user_id', $user->id)->first()
$school = $teacher->school  // Teacher's associated school
$year = AcademicYear::where('active', 1)->first()  // System-wide
```

### Per-School & Per-Year Isolation
- All behavior records include `school_id` and `year_id`
- Teacher can only see their own school's data
- Active year determines which behaviors are available

---

## 📡 Endpoints Summary

| Method | Endpoint | Input | Output | Purpose |
|--------|----------|-------|--------|---------|
| GET | `/my_classes_with_students` | - | `[{id, name, students:[]}]` | Load teacher's classrooms |
| GET | `/api/behaviors` | - | `[{id, name, type, points}]` | Load available behaviors |
| POST | `/api/student-behaviors/quick-create` | `{student_id, behavior_id, date, period_code}` | `{id, points_plus, points_minus}` | Record behavior (MVP) |
| GET | `/api/student-behaviors/{id}` | - | `{positive, negative, total}` | Get student summary |
| GET | `/api/leaderboard` | `{classroom_id?, start_date, end_date, limit}` | `[{student_id, total}]` | Get leaderboard |

---

## 🚀 Request Lifecycle Example

### Scenario: Teacher applies "Did homework" (+5 points) to 3 students

**Frontend (Time: 0ms)**
```javascript
// User clicks "Apply to Selected"
applyBehaviorToStudents([1, 5, 8], 3, {
  date: "2025-11-14",
  periodCode: "1.1.1.1"
})
```

**Network Request 1 (Time: ~50ms)**
```http
POST /api/student-behaviors/quick-create
Content-Type: application/json
Authorization: Bearer {token}

{
  "student_id": 1,
  "behavior_id": 3,
  "date": "2025-11-14",
  "period_code": "1.1.1.1",
  "notes": null
}

→ 201 Created
{
  "id": 1001,
  "student_behaviors_mains_id": 101,
  "student_id": 1,
  "points_plus": 5,
  "points_minus": 0,
  "points_details": "{...}"
}
```

**Backend (Time: 50-150ms)**
```php
// Validate input
$validated = ['student_id' => 1, 'behavior_id' => 3, ...]

// Get context
$user = auth()->user()  // User #42
$teacher = Teacher::find_by_user(42)  // Teacher #7
$school = $teacher->school  // School #1
$year = AcademicYear::where(active=1)  // Year #2

// Get behavior
$behavior = Behavior::find(3)  // {name: "Did homework", type: "positive", points: 5}

// Create StudentBehaviorsMain
StudentBehaviorsMain::create([
  'school_id' => 1,
  'year_id' => 2,
  'student_id' => 1,
  'teacher_id' => 7,
  'subject_id' => 1,
  'classroom_id' => 1,
  'date' => '2025-11-14',
  'period_code' => '1.1.1.1',
  'period_code_main' => 'auto-xyz123',
  ...
])  // INSERT → ID 101

// Create StudentBehavior
StudentBehavior::create([
  'student_behaviors_mains_id' => 101,
  'student_id' => 1,
  'points_plus' => 5,
  'points_minus' => 0,
  'points_details' => JSON{behavior_id:3, ...},
  ...
])  // INSERT → ID 1001
```

**Network Requests 2 & 3 (Time: 150-300ms, 300-450ms)**
Same process for student_id=5 and student_id=8

**Frontend After Responses (Time: 450ms)**
```javascript
// All 3 requests completed
console.log(result)
→ {
    success: true,
    results: [
      { studentId: 1, success: true, data: {...} },
      { studentId: 5, success: true, data: {...} },
      { studentId: 8, success: true, data: {...} }
    ],
    errors: [],
    message: "Applied to 3/3 students"
  }

// Show success notification
$q.notify({ message: "Applied behavior to 3 students", color: 'positive' })

// Load updated summaries
loadAllSummaries()
```

---

## 🐛 Common Issues & Debugging

### Issue: 422 Unprocessable Content

**Possible causes:**
1. **Validation failure on student_id or behavior_id**
   - Student doesn't exist: `exists:students` rule fails
   - Behavior doesn't exist: `exists:behaviors` rule fails
   
2. **Date format issue**
   - Frontend sends: `"2025-11-14"` ✓
   - Backend expects: date format ✓
   - Issue: Check browser console for actual payload

3. **Context retrieval failure**
   - Teacher not found: Check if user is assigned as teacher
   - School not found: Teacher has no associated school
   - Year not found: No active academic year in system

**Debugging:**
- Frontend: Check browser console (F12) for `console.error()` logs
- Backend: Check `storage/logs/laravel.log` for `\Log::debug()` and `\Log::error()` calls

### Issue: Classroom Students Not Loading

**Cause:**
- `/my_classes_with_students` may not be returning students attachment

**Solution:**
- Check ClassroomSubjectTeacherController implementation
- Ensure classrooms are eager-loaded with students relationship

---

## 📝 State Management Summary

### Frontend Reactive State
```javascript
// Loaded once on mount
classrooms: [ { id, classroom_name, students } ]
behaviors: [ { id, name, type, points } ]

// Selected by user
selectedClassroomId: number
selectedDate: string (YYYY-MM-DD)
selectedPeriod: string
selectedBehaviorId: number
selectedIds: number[]

// Cached from API
studentPoints: { [studentId]: { positive, negative, total } }
leaderboard: [ { student_id, total } ]

// UI state
applyingBehavior: boolean
loadingData: boolean
showLeaderboard: boolean
```

### Backend Per-Request Context
```php
$user = auth()->user()
$teacher = Teacher::where('user_id', $user->id)
$school = $teacher->school
$year = AcademicYear::where('active', 1)

// All created records include:
[
  'school_id' => $school->id,
  'year_id' => $year->id,
  'teacher_id' => $teacher->id,
  'student_id' => $validated['student_id'],
  'date' => $validated['date'],
  ...
]
```

---

## ✅ MVP Checklist

- ✅ Classroom selection & student roster loading
- ✅ Behavior selection dropdown
- ✅ Bulk student selection (checkboxes)
- ✅ Apply behavior to multiple students (quickCreate endpoint)
- ✅ Student summary loading (positive/negative/total)
- ✅ Leaderboard display with date filtering
- ✅ Backend validation and context handling
- ✅ Enhanced error logging (frontend + backend)
- ⏳ Attendance tracking (client-side only for MVP)
- ⏳ Avatar management (out of scope for this phase)

---

## 🔮 Future Enhancements

1. **Attendance Persistence** - Backend endpoint to save attendance state
2. **Behavior Editing** - UI to cancel/restore individual behaviors
3. **Custom Period/Classroom** - Use selected classroom instead of hardcoded ID 1
4. **Point Action Details** - View individual point additions/deductions per student
5. **Export Reports** - Generate PDF/Excel leaderboard reports
6. **Classroom-based Filtering** - Derive classroom_id from context instead of hardcoding

---

**Last Updated:** 2025-11-14
**Status:** MVP Complete (Pending Console Output for 422 Debugging)
