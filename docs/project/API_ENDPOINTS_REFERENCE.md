# 🔌 Reward System API Reference

## Authentication
All endpoints require authentication via Laravel Sanctum.

```javascript
// Headers required
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

---

## 📚 Endpoints

### 1. Initialize Classroom Session

**POST** `/api/student-behaviors/init-classroom`

Creates or finds a session for a classroom and ensures all students have behavior records.

**Request:**
```json
{
  "classroom_id": 5,
  "date": "2025-11-16",
  "period_code": "1.7.1.2"
}
```

**Response:**
```json
{
  "message": "Initialized classroom session",
  "student_behaviors_mains_id": 123,
  "student_behaviors": [
    {
      "id": 456,
      "student_id": 10,
      "attend": true,
      "points_plus": 15,
      "points_minus": 5,
      "student": {
        "id": 10,
        "name": "Ahmed Ali"
      },
      "point_actions": [...]
    }
  ],
  "created": 25,
  "skipped": 0
}
```

---

### 2. Apply Behavior (Quick Create)

**POST** `/api/student-behaviors/quick-create`

Applies a behavior to a student and creates a point action.

**Request:**
```json
{
  "student_id": 10,
  "behavior_id": 3,
  "date": "2025-11-16",
  "period_code": "1.7.1.2",
  "notes": "Great participation today"
}
```

**Response:**
```json
{
  "id": 456,
  "student_id": 10,
  "attend": true,
  "student": {
    "id": 10,
    "name": "Ahmed Ali"
  },
  "behavior_main": {...}
}
```

---

### 3. Update Single Attendance

**POST** `/api/student-attendance`

Updates attendance for one student.

**Request:**
```json
{
  "student_id": 10,
  "attend": true,
  "date": "2025-11-16",
  "period_code": "1.7.1.2",
  "classroom_id": 5
}
```

**Response:**
```json
{
  "success": true,
  "message": "Attendance updated successfully",
  "data": {
    "id": 456,
    "student_id": 10,
    "attend": true
  }
}
```

---

### 4. Batch Update Attendance

**POST** `/api/student-attendance/batch`

Updates attendance for multiple students at once.

**Request:**
```json
{
  "attendance": [
    {
      "student_id": 10,
      "attend": true,
      "date": "2025-11-16",
      "period_code": "1.7.1.2",
      "classroom_id": 5
    },
    {
      "student_id": 11,
      "attend": false,
      "date": "2025-11-16",
      "period_code": "1.7.1.2",
      "classroom_id": 5
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Updated 2 students, 0 failed",
  "updated": 2,
  "failed": 0
}
```

---

### 5. Get Recent Actions (History)

**GET** `/api/student-behaviors/recent-actions`

Fetches recent point actions with ability to filter.

**Query Parameters:**
- `classroom_id` (optional): Filter by classroom
- `date` (optional): Filter by specific date
- `limit` (optional): Number of results (default: 10, max: 50)

**Example:**
```
GET /api/student-behaviors/recent-actions?classroom_id=5&date=2025-11-16&limit=10
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 789,
      "student_behaviors_id": 456,
      "reason_id": 3,
      "value": 5,
      "action_type": "positive",
      "note": "Great participation",
      "canceled": false,
      "canceled_by": null,
      "canceled_at": null,
      "cancel_reason": null,
      "created_by": 1,
      "created_at": "2025-11-16T14:30:00.000000Z",
      "student_behavior": {
        "student": {
          "id": 10,
          "name": "Ahmed Ali"
        }
      },
      "behavior": {
        "id": 3,
        "name": "Great participation",
        "value": 5
      },
      "created_by": {
        "id": 1,
        "name": "Mr. Smith"
      }
    }
  ]
}
```

---

### 6. Cancel Action (Undo)

**POST** `/api/student-behaviors/actions/{actionId}/cancel`

Cancels (undoes) a point action without deleting it.

**Request:**
```json
{
  "cancel_reason": "Undone by teacher"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Action canceled successfully",
  "data": {
    "id": 789,
    "canceled": true,
    "canceled_by": 1,
    "canceled_at": "2025-11-16T14:35:00.000000Z",
    "cancel_reason": "Undone by teacher",
    "student_behavior": {...},
    "behavior": {...},
    "canceled_by": {
      "id": 1,
      "name": "Mr. Smith"
    }
  }
}
```

---

### 7. Get Leaderboard

**GET** `/api/leaderboard`

Gets top students by points within a date range.

**Query Parameters:**
- `classroom_id` (optional): Filter by classroom
- `start_date` (required): Start date (YYYY-MM-DD)
- `end_date` (required): End date (YYYY-MM-DD)
- `limit` (optional): Number of results (default: 10, max: 20)

**Example:**
```
GET /api/leaderboard?classroom_id=5&start_date=2025-11-01&end_date=2025-11-16&limit=5
```

**Response:**
```json
[
  {
    "student_id": 10,
    "positive": 50,
    "negative": 5,
    "total": 45
  },
  {
    "student_id": 11,
    "positive": 40,
    "negative": 0,
    "total": 40
  }
]
```

---

### 8. Get Behaviors List

**GET** `/api/behaviors`

Gets all available behaviors (positive and negative).

**Response:**
```json
[
  {
    "id": 1,
    "name": "Great participation",
    "type": "positive",
    "value": 5,
    "points": 5
  },
  {
    "id": 2,
    "name": "Homework completed",
    "type": "positive",
    "value": 10,
    "points": 10
  },
  {
    "id": 3,
    "name": "Late to class",
    "type": "negative",
    "value": -3,
    "points": -3
  }
]
```

---

## 🔄 Frontend Service Usage

### JavaScript/Vue Example

```javascript
import rewardPointService from './reward_sys_point_action.js'

// Initialize session
const result = await axios.post('/api/student-behaviors/init-classroom', {
  classroom_id: 5,
  date: '2025-11-16',
  period_code: '1.7.1.2'
})

// Apply behavior to multiple students
const applyResult = await rewardPointService.applyBehaviorToStudents(
  [10, 11, 12], // student IDs
  3, // behavior ID
  {
    date: '2025-11-16',
    periodCode: '1.7.1.2',
    classroomId: 5
  }
)

// Update attendance
const attendanceResult = await rewardPointService.updateAttendance(
  10, // student ID
  true, // present
  {
    date: '2025-11-16',
    periodCode: '1.7.1.2',
    classroomId: 5
  }
)

// Get history
const historyResult = await rewardPointService.getRecentActions({
  classroomId: 5,
  date: '2025-11-16',
  limit: 10
})

// Undo action
const undoResult = await rewardPointService.undoAction(
  789, // action ID
  'Undone by teacher'
)
```

---

## 🛡️ Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated"
}
```

### 422 Validation Error
```json
{
  "message": "Validation failed",
  "errors": {
    "student_id": ["The student id field is required."],
    "behavior_id": ["The selected behavior id is invalid."]
  }
}
```

### 404 Not Found
```json
{
  "message": "Student behavior record not found for this period"
}
```

### 500 Server Error
```json
{
  "message": "Failed to create behavior record",
  "error": "Database connection failed"
}
```

---

## 📊 Database Schema Reference

### student_behaviors_mains
Session-level container for a classroom period.

```sql
id                      BIGINT PRIMARY KEY
school_id               BIGINT FK
year_id                 BIGINT FK
teacher_id              BIGINT FK
subject_id              BIGINT FK
classroom_id            BIGINT FK
period_code_main        VARCHAR
period_code             VARCHAR
date                    DATE
notes                   TEXT
created_at              TIMESTAMP
updated_at              TIMESTAMP
```

### student_behaviors
Per-student record within a session.

```sql
id                              BIGINT PRIMARY KEY
school_id                       BIGINT FK
student_behaviors_mains_id      BIGINT FK
student_id                      BIGINT FK
attend                          BOOLEAN
notes                           TEXT
created_at                      TIMESTAMP
updated_at                      TIMESTAMP
```

### student_behaviors_point_actions
Individual point transactions (audit trail).

```sql
id                      BIGINT PRIMARY KEY
student_behaviors_id    BIGINT FK
reason_id               BIGINT FK (behaviors.id)
value                   INTEGER (+ or -)
action_type             VARCHAR
note                    TEXT
canceled                BOOLEAN DEFAULT false
canceled_by             BIGINT FK (users.id)
canceled_at             TIMESTAMP
cancel_reason           VARCHAR
created_by              BIGINT FK (users.id)
created_at              TIMESTAMP
updated_at              TIMESTAMP
```

---

## 🔐 Security Notes

1. **Authentication Required**: All endpoints require valid Sanctum token
2. **Teacher Verification**: System verifies user is a teacher
3. **School Isolation**: Teachers can only access their school's data
4. **Audit Trail**: All actions tracked with user ID and timestamp
5. **Soft Deletes**: Actions are canceled, not deleted (preserves history)

---

## 🚀 Performance Optimizations

1. **Eager Loading**: Relations loaded in single query
2. **Database Aggregation**: Leaderboard uses SQL aggregation
3. **Relation Checks**: Model checks if relations already loaded
4. **Batch Operations**: Attendance can be updated in bulk
5. **Indexed Columns**: Foreign keys and date columns indexed

---

## 📝 Notes

- All dates should be in `YYYY-MM-DD` format
- Period codes follow format: `semester.week.day.period` (e.g., "1.7.1.2")
- Point values: positive = +N, negative = -N
- Canceled actions still appear in history but don't affect totals
- System automatically calculates points_plus, points_minus, total_points

---

**Last Updated:** 2025-11-16
**API Version:** 2.0
