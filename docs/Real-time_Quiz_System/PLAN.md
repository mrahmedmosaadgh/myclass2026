# Real-time Quiz System - Implementation Plan

## Goal

Create a **Reusable Real-time Question System** where teachers can broadcast questions to students in real-time with flexible settings and instant feedback.

## Key Features

### Teacher Features

-   **Question Selection**: Pick questions from existing question library
-   **Live Mode Options**:
    -   **Access Code Mode**: Generate unique code for students to enter
    -   **Direct Mode**: Direct link or auto-push to active students
-   **Session Controls**:
    -   Start/Pause/Resume session
    -   Next question
    -   Add time to timer
    -   End session
-   **Live Monitoring**:
    -   Real-time answer statistics
    -   Participant list with status
    -   Live result charts
    -   Leaderboard (optional)

### Student Features

-   **Join Session**: Enter access code or use direct link
-   **Waiting Room**: See participant count while waiting for teacher to start
-   **Active Question View**:
    -   Question content displayed instantly
    -   Synchronized timer
    -   Answer input (adapts to question type: multiple choice, numeric, text)
-   **Feedback**:
    -   Answer confirmation
    -   Current score (if enabled)
    -   Correct answer reveal (if enabled)

## Technical Architecture

### Backend Components

#### Models

-   `QuizSession`: Manages session lifecycle
-   `QuizSessionParticipant`: Tracks student participation

#### Controllers

-   `QuizSessionController`:
    -   `store`: Create new session with settings
    -   `join`: Validate code and add student
    -   `updateState`: Teacher controls (start, next, end)
    -   `submitAnswer`: Student answer submission
    -   `updateSettings`: Modify settings mid-session

#### Services

-   `FirebaseService`: Helper for pushing events to Firebase paths

### Frontend Components

#### Reusable Components

1. **`<LiveSessionHost />`** (Teacher)

    - Props: `sessionId`, `initialSettings`
    - Features: Timer controls, question dispatcher, live stats, settings panel

2. **`<LiveSessionClient />`** (Student)
    - Props: `sessionCode`, `studentId`
    - Features: Question display, timer sync, answer input

#### Pages

1. **`TeacherTestPage.vue`** (`/quiz/live/test`)

    - Question selector from library
    - Live mode toggle (Access Code / Direct)
    - Embeds `<LiveSessionHost />`

2. **`StudentPage.vue`** (`/quiz/live/join`)
    - Code input for joining
    - Embeds `<LiveSessionClient />`

## Settings System

### JSON Settings Structure

```json
{
    "timer": 60,
    "auto_submit": true,
    "show_results": false,
    "show_correct_answer": true,
    "allow_retry": false,
    "shuffle_options": true
}
```

### Extensibility

New settings can be added without database schema changes by simply updating the JSON structure.

## Real-time Flow

### Session Creation

1. Teacher selects question and settings
2. Backend creates session in MySQL
3. Backend generates unique access code
4. Teacher receives control interface

### Student Join

1. Student enters access code
2. Backend validates and adds to MySQL
3. Firebase broadcasts `StudentJoined` event to teacher channel
4. Teacher sees updated participant list

### Question Broadcast

1. Teacher clicks "Next Question"
2. Backend updates MySQL with current question
3. Backend broadcasts `QuestionActive` event with full question data to public channel
4. All students receive and display question instantly

### Answer Submission

1. Student submits answer
2. Backend saves to MySQL
3. Backend broadcasts `AnswerSubmitted` event to teacher channel
4. Teacher sees live statistics update

## Security Considerations

-   Access codes are unique and validated
-   Firebase channels are scoped to session codes
-   Teacher channel is separate from student channel
-   All data persistence goes through Laravel backend
-   Firebase only used for signaling, not data storage
