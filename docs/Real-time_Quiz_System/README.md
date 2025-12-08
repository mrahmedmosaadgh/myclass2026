# Real-time Quiz System Documentation

## Overview

The Real-time Quiz System is a live, interactive question-and-answer platform that allows teachers to broadcast questions to students in real-time. It uses **MySQL** for persistent data storage and **Firebase** for real-time event signaling.

## Architecture

### Hybrid Approach: MySQL + Firebase Signaling

-   **MySQL**: Stores all persistent data (sessions, participants, answers, scores)
-   **Firebase**: Handles real-time event broadcasting between teacher and students
-   **Benefits**:
    -   Data integrity and relational structure via MySQL
    -   Instant updates and low latency via Firebase
    -   Prevents "Thundering Herd" problem by sending question data in Firebase events

### Communication Channels

-   `quiz_{access_code}`: Public channel for all students (receives questions, timer updates)
-   `quiz_{access_code}_teacher`: Private channel for teacher (receives answer submissions, participant updates)

## Database Schema

### `quiz_sessions` Table

Tracks live quiz session instances.

| Column                | Type              | Description                                                  |
| --------------------- | ----------------- | ------------------------------------------------------------ |
| `id`                  | bigint            | Primary key                                                  |
| `quiz_id`             | bigint (nullable) | Foreign key to quizzes table (nullable for ad-hoc questions) |
| `teacher_id`          | bigint            | Foreign key to users table                                   |
| `access_code`         | string (unique)   | Unique code for students to join (e.g., "AB12CD")            |
| `status`              | enum              | Session status: 'waiting', 'active', 'completed'             |
| `current_question_id` | bigint (nullable) | Foreign key to questions table                               |
| `settings`            | json              | Flexible settings (timer, auto_submit, show_results, etc.)   |
| `started_at`          | timestamp         | When session started                                         |
| `ended_at`            | timestamp         | When session ended                                           |
| `created_at`          | timestamp         | Record creation time                                         |
| `updated_at`          | timestamp         | Record update time                                           |

### `quiz_session_participants` Table

Tracks students who joined a session.

| Column            | Type      | Description                                            |
| ----------------- | --------- | ------------------------------------------------------ |
| `id`              | bigint    | Primary key                                            |
| `quiz_session_id` | bigint    | Foreign key to quiz_sessions                           |
| `student_id`      | bigint    | Foreign key to users table                             |
| `score`           | integer   | Participant's current score                            |
| `status`          | enum      | Participant status: 'joined', 'active', 'disconnected' |
| `created_at`      | timestamp | Record creation time                                   |
| `updated_at`      | timestamp | Record update time                                     |

### `quiz_attempts` Table (Modified)

Added link to live sessions.

| New Column | Type | Description |

# Real-time Quiz System Documentation

## Overview

The Real-time Quiz System is a live, interactive question-and-answer platform that allows teachers to broadcast questions to students in real-time. It uses **MySQL** for persistent data storage and **Firebase** for real-time event signaling.

## Architecture

### Hybrid Approach: MySQL + Firebase Signaling

-   **MySQL**: Stores all persistent data (sessions, participants, answers, scores)
-   **Firebase**: Handles real-time event broadcasting between teacher and students
-   **Benefits**:
    -   Data integrity and relational structure via MySQL
    -   Instant updates and low latency via Firebase
    -   Prevents "Thundering Herd" problem by sending question data in Firebase events

### Communication Channels

-   `quiz_{access_code}`: Public channel for all students (receives questions, timer updates)
-   `quiz_{access_code}_teacher`: Private channel for teacher (receives answer submissions, participant updates)

## Database Schema

### `quiz_sessions` Table

Tracks live quiz session instances.

| Column                | Type              | Description                                                  |
| --------------------- | ----------------- | ------------------------------------------------------------ |
| `id`                  | bigint            | Primary key                                                  |
| `quiz_id`             | bigint (nullable) | Foreign key to quizzes table (nullable for ad-hoc questions) |
| `teacher_id`          | bigint            | Foreign key to users table                                   |
| `access_code`         | string (unique)   | Unique code for students to join (e.g., "AB12CD")            |
| `status`              | enum              | Session status: 'waiting', 'active', 'completed'             |
| `current_question_id` | bigint (nullable) | Foreign key to questions table                               |
| `settings`            | json              | Flexible settings (timer, auto_submit, show_results, etc.)   |
| `started_at`          | timestamp         | When session started                                         |
| `ended_at`            | timestamp         | When session ended                                           |
| `created_at`          | timestamp         | Record creation time                                         |
| `updated_at`          | timestamp         | Record update time                                           |

### `quiz_session_participants` Table

Tracks students who joined a session.

| Column            | Type      | Description                                            |
| ----------------- | --------- | ------------------------------------------------------ |
| `id`              | bigint    | Primary key                                            |
| `quiz_session_id` | bigint    | Foreign key to quiz_sessions                           |
| `student_id`      | bigint    | Foreign key to users table                             |
| `score`           | integer   | Participant's current score                            |
| `status`          | enum      | Participant status: 'joined', 'active', 'disconnected' |
| `created_at`      | timestamp | Record creation time                                   |
| `updated_at`      | timestamp | Record update time                                     |

### `quiz_attempts` Table (Modified)

Added link to live sessions.

| New Column        | Type              | Description                                                  |
| ----------------- | ----------------- | ------------------------------------------------------------ |
| `quiz_session_id` | bigint (nullable) | Foreign key to quiz_sessions (links attempt to live session) |

## Implementation Progress

### ✅ Completed

1. **Database Migrations**

    - Created `quiz_sessions` table
    - Created `quiz_session_participants` table
    - Added `quiz_session_id` to `quiz_attempts` table
    - All migrations successfully applied

2. **Backend (Laravel)**

    - ✅ QuizSession model with relationships and helper methods
    - ✅ QuizSessionParticipant model with relationships
    - ✅ QuizSessionController with full CRUD operations
    - ✅ API routes for session management
    - ✅ Web routes for teacher and student pages

3. **Frontend (Vue.js)**
    - ✅ TeacherTestPage.vue - Teacher control interface
    - ✅ StudentPage.vue - Student participation interface
    - ✅ Firebase real-time integration
    - ✅ Timer functionality
    - ✅ Answer submission and feedback

### 🚧 Pending

1. **Testing & Refinement**
    - Test real-time synchronization
    - Test timer accuracy
    - Test answer validation
    - UI/UX improvements

## Next Steps

1. Test the complete flow (teacher creates session → student joins → question broadcast → answer submission)
2. Add live statistics and charts for teacher dashboard
3. Implement leaderboard functionality
4. Add more question types support
5. Enhance error handling and edge cases

## Files Created

**Backend:**

-   `app/Models/QuizSession.php`
-   `app/Models/QuizSessionParticipant.php`
-   `app/Http/Controllers/QuizSessionController.php`
-   `database/migrations/2025_11_29_103013_create_live_quiz_tables.php`
-   `database/migrations/2025_11_29_103014_add_quiz_session_id_to_quiz_attempts_table.php`

**Frontend:**

-   `resources/js/Pages/QuizManagement/Live/TeacherTestPage.vue`
-   `resources/js/Pages/QuizManagement/Live/StudentPage.vue`

**Routes:**

-   API routes in `routes/api.php`
-   Web routes in `routes/web.php`
