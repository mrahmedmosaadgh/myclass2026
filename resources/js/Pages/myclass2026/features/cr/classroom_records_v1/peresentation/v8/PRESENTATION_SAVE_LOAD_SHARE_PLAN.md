# Presentation Save/Load/Share Feature - Implementation Plan

**Date:** 2026-05-15
**Scope:** Allow teachers to save presentations to their account, load them, create shareable links for students, and track student scores/attempts

---

## Requirements

1. **Save Presentation** - Teacher can save current presentation to their account as JSON
2. **Load Presentation** - Teacher can load previously saved presentations
3. **Shareable Link** - Generate unique shareable link for students
4. **Student Access** - Students can access presentation via share link (read-only)
5. **Score Tracking** - Track all student attempts and scores for shared presentations
6. **Attempt History** - View detailed attempt history per student

---

## Database Schema

### 1. User Presentations Table
```sql
CREATE TABLE user_presentations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  presentation_data JSON NOT NULL,
  share_token VARCHAR(64) UNIQUE,
  is_public BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_share_token (share_token)
);
```

### 2. Student Presentation Attempts Table
```sql
CREATE TABLE student_presentation_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  presentation_id BIGINT UNSIGNED NOT NULL,
  student_identifier VARCHAR(255) NOT NULL, -- Could be email, name, or anonymous ID
  quiz_attempts JSON NOT NULL, -- Array of quiz attempt data
  total_score INT DEFAULT 0,
  total_questions INT DEFAULT 0,
  completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (presentation_id) REFERENCES user_presentations(id) ON DELETE CASCADE,
  INDEX idx_presentation_id (presentation_id),
  INDEX idx_student_identifier (student_identifier)
);
```

---

## API Endpoints

### Save Presentation
**POST** `/api/presentations/save`
```json
Request: {
  "title": "Math Quiz - Chapter 5",
  "description": "Quiz on fractions and decimals",
  "presentation_data": { ... } // Full presentation JSON
}
Response: {
  "id": 123,
  "share_token": "abc123xyz",
  "share_url": "https://qudratpro.com/student-presentation/abc123xyz"
}
```

### Load Presentation
**GET** `/api/presentations/{id}`
```json
Response: {
  "id": 123,
  "title": "Math Quiz - Chapter 5",
  "description": "Quiz on fractions and decimals",
  "presentation_data": { ... },
  "share_token": "abc123xyz",
  "share_url": "https://qudratpro.com/student-presentation/abc123xyz",
  "created_at": "2026-05-15T10:00:00Z"
}
```

### List User Presentations
**GET** `/api/presentations`
```json
Response: [
  {
    "id": 123,
    "title": "Math Quiz - Chapter 5",
    "description": "Quiz on fractions and decimals",
    "share_token": "abc123xyz",
    "share_url": "https://qudratpro.com/student-presentation/abc123xyz",
    "created_at": "2026-05-15T10:00:00Z",
    "attempt_count": 15
  }
]
```

### Delete Presentation
**DELETE** `/api/presentations/{id}`
```json
Response: { "success": true }
```

### Load Shared Presentation (Student View)
**GET** `/api/presentations/shared/{shareToken}`
```json
Response: {
  "id": 123,
  "title": "Math Quiz - Chapter 5",
  "description": "Quiz on fractions and decimals",
  "presentation_data": { ... }
}
```

### Submit Student Attempt
**POST** `/api/presentations/shared/{shareToken}/attempt`
```json
Request: {
  "student_identifier": "student@example.com",
  "quiz_attempts": [ ... ], // Array of quiz attempt data
  "total_score": 80,
  "total_questions": 10
}
Response: {
  "success": true,
  "attempt_id": 456
}
```

### Get Presentation Statistics
**GET** `/api/presentations/{id}/statistics`
```json
Response: {
  "total_attempts": 15,
  "unique_students": 12,
  "average_score": 75.5,
  "high_score": 100,
  "low_score": 40,
  "score_distribution": {
    "90-100": 3,
    "80-89": 5,
    "70-79": 4,
    "60-69": 2,
    "below-60": 1
  }
}
```

### Get Presentation Attempt History
**GET** `/api/presentations/{id}/attempts`
```json
Response: [
  {
    "id": 456,
    "student_identifier": "student@example.com",
    "quiz_attempts": [ ... ],
    "total_score": 80,
    "total_questions": 10,
    "completed_at": "2026-05-15T11:30:00Z"
  }
]
```

---

## Frontend Implementation

### 1. Presentation Management Component
**Location:** `v8/components/PresentationManager.vue`

Features:
- List all saved presentations
- Save current presentation (with title/description dialog)
- Load selected presentation
- Delete presentation
- Copy share link to clipboard
- View statistics
- View attempt history

### 2. Save Dialog Component
**Location:** `v8/components/SavePresentationDialog.vue`

Fields:
- Title (required)
- Description (optional)
- Save button

### 3. Statistics Dashboard Component
**Location:** `v8/components/PresentationStatistics.vue`

Features:
- Total attempts count
- Unique students count
- Average score
- High/low scores
- Score distribution chart
- Attempt history table with export option

### 4. Student Presentation Route (Enhanced)
**Location:** `/student-presentation/{shareToken}`

Enhancements:
- Load presentation data from API using share token
- Prompt for student identifier (name/email) before starting
- Submit all quiz attempts to API on completion
- Show "Your attempt has been saved" message

### 5. Integration with Index.vue
- Add "Save" button in toolbar
- Add "My Presentations" button in toolbar
- Add "Share" button (when presentation is saved)

---

## Implementation Order

1. **Database Migration** - Create tables for user_presentations and student_presentation_attempts
2. **Laravel Models** - Create UserPresentation and StudentPresentationAttempt models
3. **API Routes** - Add all API endpoints in routes/api.php
4. **API Controllers** - Create PresentationController with all methods
5. **Frontend API Client** - Create composable `usePresentationAPI.js` for API calls
6. **Save Dialog Component** - Build SavePresentationDialog.vue
7. **Presentation Manager Component** - Build PresentationManager.vue
8. **Statistics Component** - Build PresentationStatistics.vue
9. **Index.vue Integration** - Add save/load/share buttons to toolbar
10. **Student Presentation Enhancement** - Update student route to load from API and submit attempts

---

## File Structure

```
v8/
├── components/
│   ├── PresentationManager.vue          (NEW)
│   ├── SavePresentationDialog.vue       (NEW)
│   └── PresentationStatistics.vue       (NEW)
├── composables/
│   └── usePresentationAPI.js             (NEW)
└── STUDENT_VIEW_ENHANCEMENT_PLAN.md
```

**Backend:**
```
database/migrations/
├── xxxx_xx_xx_create_user_presentations_table.php          (NEW)
└── xxxx_xx_xx_create_student_presentation_attempts_table.php (NEW)

app/Models/
├── UserPresentation.php                                        (NEW)
└── StudentPresentationAttempt.php                             (NEW)

app/Http/Controllers/API/
└── PresentationController.php                                   (NEW)

routes/api.php
└── Add presentation routes                                      (MODIFY)
```

---

## Security Considerations

1. **Share Token Generation** - Use random 64-character token with crypto secure random
2. **Authentication** - Save/load/delete endpoints require authenticated user
3. **Authorization** - Users can only access their own presentations
4. **Student Identifier** - Allow anonymous identifiers (no auth required for students)
5. **Rate Limiting** - Apply rate limiting to submission endpoints
6. **Data Validation** - Validate presentation JSON structure before saving

---

## Testing Checklist

- [ ] User can save presentation with title and description
- [ ] User can load saved presentation
- [ ] User can delete saved presentation
- [ ] Share link is unique and works correctly
- [ ] Student can access presentation via share link
- [ ] Student attempts are saved to database
- [ ] Statistics dashboard shows correct data
- [ ] Attempt history shows all student attempts
- [ ] Export functionality works for attempt history
- [ ] Authentication works correctly for teacher endpoints
- [ ] No authentication required for student endpoints (share token only)

---

## Future Enhancements

- Add presentation folders/organization
- Allow editing saved presentations
- Add presentation templates
- Add student roster (pre-defined list of students)
- Add email notifications for new attempts
- Add time limits for shared presentations
- Add password protection for shared presentations
