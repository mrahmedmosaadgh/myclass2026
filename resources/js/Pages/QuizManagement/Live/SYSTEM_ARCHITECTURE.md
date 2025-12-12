# Live Quiz System - Complete Architecture Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Architecture Design](#architecture-design)
3. [Data Flow](#data-flow)
4. [Firebase Realtime Database Structure](#firebase-realtime-database-structure)
5. [Backend Components](#backend-components)
6. [Frontend Components](#frontend-components)
7. [Real-time Communication](#real-time-communication)
8. [Session Lifecycle](#session-lifecycle)
9. [Security Considerations](#security-considerations)
10. [Troubleshooting](#troubleshooting)

---

## System Overview

The Live Quiz System is a real-time, interactive quiz platform that enables teachers to conduct live quiz sessions with students. It combines the reliability of MySQL for persistent data storage with Firebase Realtime Database for instant, bidirectional communication.

### Key Features
- **Real-time Question Broadcasting**: Teachers can push questions to all students instantly
- **Live Participant Tracking**: See who's joined and their current scores in real-time
- **Timed Questions**: Configurable timer with auto-submit functionality
- **Instant Feedback**: Students receive immediate feedback on their answers
- **Session Management**: Full control over session lifecycle (waiting, active, completed)
- **Access Code System**: Simple 6-character codes for easy joining

---

## Architecture Design

### Hybrid Architecture: MySQL + Firebase

```
┌─────────────────────────────────────────────────────────────┐
│                     LIVE QUIZ SYSTEM                         │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐              ┌──────────────┐            │
│  │   MySQL DB   │              │   Firebase   │            │
│  │              │              │   Realtime   │            │
│  │ - Sessions   │              │   Database   │            │
│  │ - Participants│             │              │            │
│  │ - Questions  │              │ - Live State │            │
│  │ - Scores     │              │ - Events     │            │
│  └──────────────┘              └──────────────┘            │
│         ▲                              ▲                    │
│         │                              │                    │
│         │                              │                    │
│  ┌──────┴──────────────────────────────┴──────┐            │
│  │         Laravel Backend (API)               │            │
│  │  - QuizSessionController                    │            │
│  │  - Models (QuizSession, Participant)        │            │
│  │  - Authentication & Authorization           │            │
│  └─────────────────────────────────────────────┘            │
│         ▲                              ▲                    │
│         │                              │                    │
│         │ HTTP/REST                    │ WebSocket          │
│         │                              │                    │
│  ┌──────┴──────────┐          ┌───────┴──────────┐         │
│  │  Teacher View   │          │   Student View   │         │
│  │  (Vue.js)       │          │   (Vue.js)       │         │
│  └─────────────────┘          └──────────────────┘         │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### Why This Hybrid Approach?

**MySQL Advantages:**
- Persistent, reliable data storage
- Complex queries and relationships
- Data integrity and ACID compliance
- Historical data and analytics

**Firebase Advantages:**
- Real-time synchronization (< 100ms latency)
- Automatic client updates
- Offline support
- Scalable real-time connections

**Combined Benefits:**
- Prevents "Thundering Herd" problem (all students hitting API simultaneously)
- Reduces server load
- Instant UI updates without polling
- Reliable data persistence

---

## Data Flow

### Teacher Creates Session Flow

```
1. Teacher clicks "Create Session"
   ↓
2. Frontend → POST /api/quiz-sessions
   ↓
3. Backend creates record in MySQL
   - Generates unique access code
   - Sets status = 'waiting'
   - Stores settings (timer, auto_submit, etc.)
   ↓
4. Backend returns session data
   ↓
5. Frontend initializes Firebase node
   - Path: quiz_sessions/{access_code}
   - Initial data: { status: 'waiting', participants: {} }
   ↓
6. Frontend sets up Firebase listener
   - Listens for participant joins
   - Listens for status changes
```

### Student Joins Session Flow

```
1. Student enters access code
   ↓
2. Frontend → POST /api/quiz-sessions/join
   ↓
3. Backend validates access code
   ↓
4. Backend creates participant record in MySQL
   - Links to session
   - Initializes score = 0
   - Sets status = 'joined'
   ↓
5. Backend returns session + participant data
   ↓
6. Frontend sets up Firebase listener
   - Path: quiz_sessions/{access_code}
   - Listens for: status changes, current_question updates
   ↓
7. Teacher's Firebase listener detects new participant
   - Updates participant count in real-time
```

### Question Broadcast Flow

```
1. Teacher selects question and clicks "Start"
   ↓
2. Frontend → POST /api/quiz-sessions/{id}/state
   - action: 'start'
   - question_id: {id}
   ↓
3. Backend updates MySQL
   - session.status = 'active'
   - session.current_question_id = {id}
   - participants.status = 'active'
   ↓
4. Backend returns updated session
   ↓
5. Frontend updates Firebase
   - Path: quiz_sessions/{access_code}
   - Data: {
       status: 'active',
       current_question_id: {id},
       current_question: {full question object with options}
     }
   ↓
6. All students' Firebase listeners trigger
   - Receive question data instantly
   - Start local timer
   - Display question and options
```

### Answer Submission Flow

```
1. Student selects answer and clicks "Submit"
   ↓
2. Frontend → POST /api/quiz-sessions/{id}/answers
   - question_id: {id}
   - answer: {selected_option_id or numeric value}
   ↓
3. Backend validates answer
   - Loads question with options
   - Checks if answer matches correct option
   ↓
4. Backend updates participant score (if correct)
   - Increments score by points_per_question
   ↓
5. Backend returns result
   - is_correct: true/false
   - participant: {updated participant with new score}
   ↓
6. Frontend displays feedback
   - Shows correct/incorrect message
   - Updates local score display
```

---

## Firebase Realtime Database Structure

### Database Schema

```json
{
  "quiz_sessions": {
    "{ACCESS_CODE}": {
      "status": "waiting|active|completed",
      "current_question_id": 123,
      "current_question": {
        "id": 123,
        "question_text": "What is 2+2?",
        "type": "multiple_choice",
        "options": [
          { "id": 1, "option_text": "3", "is_correct": false },
          { "id": 2, "option_text": "4", "is_correct": true },
          { "id": 3, "option_text": "5", "is_correct": false }
        ]
      },
      "participants": {
        "{STUDENT_ID}": {
          "name": "John Doe",
          "score": 10,
          "status": "active",
          "joined_at": 1234567890
        }
      },
      "timer": {
        "started_at": 1234567890,
        "duration": 60
      }
    }
  }
}
```

### Key Paths

| Path | Purpose | Who Writes | Who Reads |
|------|---------|------------|-----------|
| `quiz_sessions/{code}` | Root session node | Teacher | All |
| `quiz_sessions/{code}/status` | Session state | Teacher | All |
| `quiz_sessions/{code}/current_question` | Active question | Teacher | Students |
| `quiz_sessions/{code}/participants` | Participant list | Teacher | Teacher |

---

## Backend Components

### Models

#### QuizSession Model
**File:** `app/Models/QuizSession.php`

**Relationships:**
- `belongsTo(Quiz)` - Optional quiz reference
- `belongsTo(User, 'teacher_id')` - Session creator
- `belongsTo(Question, 'current_question_id')` - Active question
- `hasMany(QuizSessionParticipant)` - All participants
- `hasMany(QuizAttempt)` - All attempts made during session

**Key Methods:**
```php
generateAccessCode(): string  // Creates unique 6-char code
isActive(): bool              // Checks if status === 'active'
isWaiting(): bool             // Checks if status === 'waiting'
isCompleted(): bool           // Checks if status === 'completed'
```

**Fillable Fields:**
- `quiz_id` - Optional reference to pre-built quiz
- `teacher_id` - User who created the session
- `access_code` - Unique join code
- `status` - Current state (waiting/active/completed)
- `current_question_id` - Question being displayed
- `settings` - JSON configuration
- `started_at` - Session start timestamp
- `ended_at` - Session end timestamp

#### QuizSessionParticipant Model
**File:** `app/Models/QuizSessionParticipant.php`

**Relationships:**
- `belongsTo(QuizSession)` - Parent session
- `belongsTo(User, 'student_id')` - Student user

**Key Methods:**
```php
isActive(): bool              // Checks if status === 'active'
isDisconnected(): bool        // Checks if status === 'disconnected'
incrementScore(int): void     // Adds points to score
```

**Fillable Fields:**
- `quiz_session_id` - Parent session
- `student_id` - Participating user
- `score` - Current points
- `status` - Connection state (joined/active/disconnected)

### Controller

#### QuizSessionController
**File:** `app/Http/Controllers/QuizSessionController.php`

**Endpoints:**

| Method | Route | Purpose | Auth |
|--------|-------|---------|------|
| POST | `/api/quiz-sessions` | Create new session | Teacher |
| POST | `/api/quiz-sessions/join` | Join with access code | Student |
| GET | `/api/quiz-sessions/{id}` | Get session details | Any |
| POST | `/api/quiz-sessions/{id}/state` | Update session state | Teacher |
| PATCH | `/api/quiz-sessions/{id}/settings` | Update settings | Teacher |
| POST | `/api/quiz-sessions/{id}/answers` | Submit answer | Student |

**Key Actions:**

```php
store(Request)           // Create session, generate code
join(Request)            // Validate code, create participant
updateState(Request)     // Handle start/next/end/pause actions
submitAnswer(Request)    // Validate answer, update score
updateSettings(Request)  // Modify session configuration
show(QuizSession)        // Return full session data
```

### Database Schema

**quiz_sessions Table:**
```sql
CREATE TABLE quiz_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    quiz_id BIGINT NULL,
    teacher_id BIGINT NOT NULL,
    access_code VARCHAR(6) UNIQUE NOT NULL,
    status ENUM('waiting', 'active', 'completed') DEFAULT 'waiting',
    current_question_id BIGINT NULL,
    settings JSON NULL,
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (current_question_id) REFERENCES questions(id) ON DELETE SET NULL
);
```

**quiz_session_participants Table:**
```sql
CREATE TABLE quiz_session_participants (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    quiz_session_id BIGINT NOT NULL,
    student_id BIGINT NOT NULL,
    score INT DEFAULT 0,
    status ENUM('joined', 'active', 'disconnected') DEFAULT 'joined',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (quiz_session_id) REFERENCES quiz_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## Frontend Components

### Teacher Control Page
**File:** `resources/js/Pages/QuizManagement/Live/TeacherTestPage.vue`

**Purpose:** Teacher interface for creating and controlling live quiz sessions

**Key Features:**
- Session creation with settings configuration
- Question selection from question bank
- Real-time participant list
- Session control buttons (Start, Pause, End)
- Access code display

**State Management:**
```javascript
const session = ref(null)              // Current session data
const selectedQuestion = ref(null)     // Question to broadcast
const questions = ref([])              // Available questions
const participants = ref([])           // Joined students
const creating = ref(false)            // Loading state
const settings = ref({                 // Session configuration
    timer: 60,
    auto_submit: true,
    show_correct_answer: false
})
```

**Firebase Integration:**
```javascript
// Setup listener for participants
const participantsRef = firebaseRef(
    database,
    `quiz_sessions/${session.value.access_code}/participants`
)

participantsListener = onValue(participantsRef, (snapshot) => {
    const data = snapshot.val()
    // Update participants list in real-time
})
```

**Key Methods:**
- `loadQuestions()` - Fetch available questions from API
- `createSession()` - Create new session and initialize Firebase
- `setupFirebaseListeners()` - Listen for participant joins
- `startSession()` - Broadcast question to students
- `pauseSession()` - Temporarily halt session
- `endSession()` - Complete session and show results

### Student Participation Page
**File:** `resources/js/Pages/QuizManagement/Live/StudentPage.vue`

**Purpose:** Student interface for joining and participating in live quizzes

**Key Features:**
- Access code entry
- Waiting room with participant count
- Question display with timer
- Answer submission
- Instant feedback
- Final score display

**State Management:**
```javascript
const accessCode = ref("")             // Join code
const session = ref(null)              // Session data
const participant = ref(null)          // Student's participant record
const currentQuestion = ref(null)      // Active question
const answer = ref(null)               // Selected answer
const submitted = ref(false)           // Submission state
const feedback = ref(null)             // Answer result
const timeRemaining = ref(0)           // Timer countdown
```

**Firebase Integration:**
```javascript
// Setup listener for session updates
const sessionRef = firebaseRef(
    database,
    `quiz_sessions/${accessCode.value.toUpperCase()}`
)

sessionListener = onValue(sessionRef, (snapshot) => {
    const data = snapshot.val()
    
    // Update session status
    if (data.status) {
        session.value.status = data.status
    }
    
    // Receive new question
    if (data.current_question) {
        currentQuestion.value = data.current_question
        startTimer(session.value.settings.timer)
    }
})
```

**Key Methods:**
- `joinSession()` - Validate code and join session
- `setupFirebaseListener()` - Listen for questions and status changes
- `startTimer()` - Begin countdown for current question
- `submitAnswer()` - Send answer to backend for validation

### Firebase Initialization
**File:** `resources/js/firebase/init.js`

**Purpose:** Initialize Firebase app and services

**Configuration:**
```javascript
const firebaseConfig = {
  apiKey: "...",
  authDomain: "livequestion-7d083.firebaseapp.com",
  projectId: "livequestion-7d083",
  databaseURL: "https://livequestion-7d083-default-rtdb.firebaseio.com",
  // ... other config
}
```

**Initialization:**
```javascript
// Initialize app
const app = initializeApp(firebaseConfig)

// Initialize services
const database = getDatabase(app)
const auth = getAuth(app)

// Anonymous authentication for security rules
signInAnonymously(auth)
```

**Exports:**
- `app` - Firebase app instance
- `database` - Realtime Database instance
- `auth` - Authentication instance

---

## Real-time Communication

### Firebase Realtime Database Operations

#### Writing Data (Teacher)
```javascript
import { ref as firebaseRef, set } from "firebase/database"
import { database } from "@/firebase/init"

// Create session node
const sessionRef = firebaseRef(
    database,
    `quiz_sessions/${accessCode}`
)

await set(sessionRef, {
    status: "active",
    current_question_id: questionId,
    current_question: questionData
})
```

#### Reading Data (Student)
```javascript
import { ref as firebaseRef, onValue } from "firebase/database"
import { database } from "@/firebase/init"

// Listen to session updates
const sessionRef = firebaseRef(
    database,
    `quiz_sessions/${accessCode}`
)

const unsubscribe = onValue(sessionRef, (snapshot) => {
    const data = snapshot.val()
    // Handle updates
})

// Cleanup
onUnmounted(() => {
    unsubscribe()
})
```

### Event Types

| Event | Trigger | Data | Recipients |
|-------|---------|------|------------|
| Session Created | Teacher creates session | Initial session state | N/A |
| Student Joined | Student enters code | Participant info | Teacher |
| Session Started | Teacher clicks start | Status change | All |
| Question Broadcast | Teacher sends question | Full question object | Students |
| Session Paused | Teacher pauses | Status change | All |
| Session Ended | Teacher ends | Final status | All |

### Synchronization Strategy

**Teacher → Students (Broadcast):**
- Teacher writes to Firebase
- All students' listeners trigger automatically
- No API calls needed for students to receive updates

**Student → Teacher (Individual):**
- Student submits via API (MySQL write)
- Teacher can optionally listen to Firebase for real-time updates
- Prevents race conditions with centralized validation

---

## Session Lifecycle

### 1. Waiting State
**Status:** `waiting`

**Teacher Actions:**
- Configure settings (timer, auto-submit, etc.)
- Select question to broadcast
- View joining participants
- Start session when ready

**Student Actions:**
- Enter access code
- Join session
- Wait for teacher to start
- See other participants joining

**Firebase State:**
```json
{
  "status": "waiting",
  "current_question_id": null,
  "participants": {
    "123": { "name": "John", "score": 0, "status": "joined" }
  }
}
```

### 2. Active State
**Status:** `active`

**Teacher Actions:**
- Monitor participant answers (via API)
- View real-time scores
- Pause if needed
- Move to next question
- End session

**Student Actions:**
- View question and timer
- Select answer
- Submit before time expires
- Receive instant feedback
- Wait for next question

**Firebase State:**
```json
{
  "status": "active",
  "current_question_id": 456,
  "current_question": {
    "id": 456,
    "question_text": "What is 2+2?",
    "type": "multiple_choice",
    "options": [...]
  },
  "timer": {
    "started_at": 1234567890,
    "duration": 60
  }
}
```

### 3. Completed State
**Status:** `completed`

**Teacher Actions:**
- View final results
- Export scores
- Create new session

**Student Actions:**
- View final score
- See ranking (if enabled)
- Exit session

**Firebase State:**
```json
{
  "status": "completed",
  "current_question_id": null,
  "final_results": {
    "total_participants": 25,
    "average_score": 75
  }
}
```

---

## Security Considerations

### Authentication
- All API endpoints require `auth:sanctum` middleware
- Firebase uses anonymous authentication for security rules
- Teacher ownership verified before state changes

### Authorization
```php
// Teacher-only actions
if ($session->teacher_id !== Auth::id()) {
    return response()->json(['message' => 'Unauthorized'], 403);
}

// Participant verification
$participant = QuizSessionParticipant::where('quiz_session_id', $session->id)
    ->where('student_id', Auth::id())
    ->first();
```

### Firebase Security Rules
```json
{
  "rules": {
    "quiz_sessions": {
      "$sessionCode": {
        ".read": "auth != null",
        ".write": "auth != null"
      }
    }
  }
}
```

### Data Validation
```php
$validated = $request->validate([
    'access_code' => 'required|string|exists:quiz_sessions,access_code',
    'question_id' => 'required|exists:questions,id',
    'answer' => 'required',
]);
```

---

## Troubleshooting

### Common Issues

#### 1. Firebase Connection Errors
**Symptom:** `400 Bad Request` to `identitytoolkit.googleapis.com`

**Solution:**
```javascript
// Use emulator in development
if (window.location.hostname === 'localhost') {
    connectDatabaseEmulator(database, "localhost", 9000)
    connectAuthEmulator(auth, "http://localhost:9099")
}
```

#### 2. Students Not Receiving Questions
**Symptom:** Question doesn't appear after teacher starts

**Debugging:**
```javascript
// Check Firebase listener
console.log('Listening to:', `quiz_sessions/${accessCode}`)

// Verify data structure
onValue(sessionRef, (snapshot) => {
    console.log('Received data:', snapshot.val())
})
```

**Common Causes:**
- Access code mismatch (case sensitivity)
- Firebase listener not set up
- Network connectivity issues

#### 3. Timer Not Syncing
**Symptom:** Different students see different times

**Solution:**
```javascript
// Use server timestamp
import { serverTimestamp } from "firebase/database"

await set(sessionRef, {
    timer: {
        started_at: serverTimestamp(),
        duration: 60
    }
})
```

#### 4. Duplicate Participants
**Symptom:** Same student appears multiple times

**Solution:**
```php
// Check for existing participant before creating
$participant = QuizSessionParticipant::where('quiz_session_id', $session->id)
    ->where('student_id', Auth::id())
    ->first();

if (!$participant) {
    $participant = QuizSessionParticipant::create([...]);
}
```

### Debug Checklist

- [ ] Firebase config loaded correctly
- [ ] Anonymous auth successful
- [ ] Access code matches exactly
- [ ] Firebase listeners set up before data changes
- [ ] Network tab shows Firebase requests
- [ ] Console shows no JavaScript errors
- [ ] MySQL records created successfully
- [ ] User authenticated with Sanctum

### Logging

**Backend:**
```php
\Log::info('Session created', ['session_id' => $session->id]);
\Log::info('Answer submitted', ['participant_id' => $participant->id, 'is_correct' => $isCorrect]);
```

**Frontend:**
```javascript
console.log('Firebase initialized:', app)
console.log('Session data received:', data)
console.log('Timer started:', timeRemaining.value)
```

---

## Performance Optimization

### Reducing Firebase Reads
- Use `once()` instead of `onValue()` for one-time reads
- Unsubscribe from listeners when component unmounts
- Limit data depth with `.child()` references

### Caching Strategies
- Cache question data in localStorage
- Reuse Firebase connections
- Batch participant updates

### Scalability Considerations
- Firebase Realtime Database: ~100,000 concurrent connections
- Consider Firebase sharding for > 10,000 simultaneous sessions
- Use Cloud Functions for complex server-side logic

---

## Future Enhancements

### Planned Features
- [ ] Leaderboard with real-time rankings
- [ ] Multiple question types (true/false, fill-in-blank)
- [ ] Question pools and randomization
- [ ] Team-based competitions
- [ ] Analytics dashboard
- [ ] Export results to CSV/PDF
- [ ] Mobile app support
- [ ] Voice/video integration

### Technical Improvements
- [ ] WebSocket fallback for Firebase
- [ ] Progressive Web App (PWA) support
- [ ] Offline mode with sync
- [ ] Redis caching layer
- [ ] GraphQL API option
- [ ] Automated testing suite

---

## Conclusion

The Live Quiz System successfully combines MySQL's reliability with Firebase's real-time capabilities to create an engaging, interactive learning experience. The hybrid architecture ensures data integrity while providing instant updates, making it suitable for classrooms of any size.

For questions or contributions, please refer to the main project documentation.
