# Database Schema Documentation

## Tables

### quiz_sessions

Stores the state and configuration of live quiz sessions.

```sql
CREATE TABLE quiz_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id BIGINT UNSIGNED NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,
    access_code VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('waiting', 'active', 'completed') DEFAULT 'waiting',
    current_question_id BIGINT UNSIGNED NULL,
    settings JSON NULL,
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (current_question_id) REFERENCES questions(id) ON DELETE SET NULL
);
```

#### Fields Description

-   **id**: Primary key
-   **quiz_id**: Optional reference to a quiz (allows ad-hoc questions without a quiz)
-   **teacher_id**: The teacher who created the session
-   **access_code**: Unique code for students to join (e.g., "X9Y2", "AB12CD")
-   **status**: Current session state
    -   `waiting`: Session created, waiting for teacher to start
    -   `active`: Session in progress
    -   `completed`: Session ended
-   **current_question_id**: The question currently being displayed
-   **settings**: JSON object with flexible configuration
-   **started_at**: When the teacher started the session
-   **ended_at**: When the session was completed

#### Example Settings JSON

```json
{
    "timer": 60,
    "auto_submit": true,
    "show_results": false,
    "show_correct_answer": true,
    "allow_retry": false,
    "shuffle_options": true,
    "points_per_question": 10
}
```

### quiz_session_participants

Tracks which students have joined a session and their performance.

```sql
CREATE TABLE quiz_session_participants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_session_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    score INTEGER DEFAULT 0,
    status ENUM('joined', 'active', 'disconnected') DEFAULT 'joined',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (quiz_session_id) REFERENCES quiz_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### Fields Description

-   **id**: Primary key
-   **quiz_session_id**: Reference to the session
-   **student_id**: The student who joined
-   **score**: Current accumulated score
-   **status**: Participant connection state
    -   `joined`: Student has joined but session hasn't started
    -   `active`: Student is actively participating
    -   `disconnected`: Student lost connection

### quiz_attempts (Modified)

Added link to live sessions for tracking which attempts were made during live sessions.

```sql
ALTER TABLE quiz_attempts
ADD COLUMN quiz_session_id BIGINT UNSIGNED NULL AFTER quiz_id,
ADD FOREIGN KEY (quiz_session_id) REFERENCES quiz_sessions(id) ON DELETE SET NULL;
```

## Relationships

```
quiz_sessions
├── belongs to: quizzes (optional)
├── belongs to: users (teacher)
├── belongs to: questions (current_question)
├── has many: quiz_session_participants
└── has many: quiz_attempts

quiz_session_participants
├── belongs to: quiz_sessions
└── belongs to: users (student)

quiz_attempts
├── belongs to: quizzes
├── belongs to: users (student)
└── belongs to: quiz_sessions (optional)
```

## Indexes

Recommended indexes for performance:

```sql
-- quiz_sessions
CREATE INDEX idx_access_code ON quiz_sessions(access_code);
CREATE INDEX idx_teacher_status ON quiz_sessions(teacher_id, status);
CREATE INDEX idx_status ON quiz_sessions(status);

-- quiz_session_participants
CREATE INDEX idx_session_student ON quiz_session_participants(quiz_session_id, student_id);
CREATE INDEX idx_student_sessions ON quiz_session_participants(student_id);
```

## Migration Files

1. **2025_11_29_103013_create_live_quiz_tables.php**

    - Creates `quiz_sessions` table
    - Creates `quiz_session_participants` table

2. **2025_11_29_103014_add_quiz_session_id_to_quiz_attempts_table.php**
    - Adds `quiz_session_id` column to `quiz_attempts` table
