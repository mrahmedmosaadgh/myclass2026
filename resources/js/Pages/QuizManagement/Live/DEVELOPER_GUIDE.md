# Live Quiz System - Developer Quick Reference

## Quick Start

### Running the System

1. **Start Laravel Backend:**
```bash
php artisan serve
```

2. **Start Vite Dev Server:**
```bash
npm run dev
```

3. **Access Pages:**
- Teacher: `http://localhost:8000/quiz/live/teacher`
- Student: `http://localhost:8000/quiz/live/join`

---

## Code Examples

### Creating a Session (Teacher)

```javascript
// TeacherTestPage.vue
const createSession = async () => {
    creating.value = true
    try {
        // 1. Create session in MySQL
        const response = await axios.post('/api/quiz-sessions', {
            settings: {
                timer: 60,
                auto_submit: true,
                show_correct_answer: false
            }
        })
        
        session.value = response.data.session
        
        // 2. Initialize Firebase node
        const sessionRef = firebaseRef(
            database,
            `quiz_sessions/${session.value.access_code}`
        )
        
        await set(sessionRef, {
            status: 'waiting',
            current_question_id: null,
            participants: {}
        })
        
        // 3. Setup real-time listeners
        setupFirebaseListeners()
        
    } catch (error) {
        console.error('Failed to create session:', error)
    } finally {
        creating.value = false
    }
}
```

### Broadcasting a Question (Teacher)

```javascript
const startSession = async () => {
    try {
        // 1. Update MySQL
        await axios.post(`/api/quiz-sessions/${session.value.id}/state`, {
            action: 'start',
            question_id: selectedQuestion.value.id
        })
        
        // 2. Broadcast via Firebase
        const sessionRef = firebaseRef(
            database,
            `quiz_sessions/${session.value.access_code}`
        )
        
        await set(sessionRef, {
            status: 'active',
            current_question_id: selectedQuestion.value.id,
            current_question: {
                id: selectedQuestion.value.id,
                question_text: selectedQuestion.value.question_text,
                type: selectedQuestion.value.type,
                options: selectedQuestion.value.options
            }
        })
        
        session.value.status = 'active'
        
    } catch (error) {
        console.error('Failed to start session:', error)
    }
}
```

### Joining a Session (Student)

```javascript
// StudentPage.vue
const joinSession = async () => {
    joining.value = true
    try {
        // 1. Join via API
        const response = await axios.post('/api/quiz-sessions/join', {
            access_code: accessCode.value.toUpperCase()
        })
        
        session.value = response.data.session
        participant.value = response.data.participant
        
        // 2. Setup Firebase listener
        setupFirebaseListener()
        
    } catch (error) {
        console.error('Failed to join:', error)
    } finally {
        joining.value = false
    }
}
```

### Listening for Questions (Student)

```javascript
const setupFirebaseListener = () => {
    const sessionRef = firebaseRef(
        database,
        `quiz_sessions/${accessCode.value.toUpperCase()}`
    )
    
    sessionListener = onValue(sessionRef, (snapshot) => {
        const data = snapshot.val()
        
        if (!data) return
        
        // Update session status
        if (data.status) {
            session.value.status = data.status
        }
        
        // Receive new question
        if (data.current_question) {
            currentQuestion.value = data.current_question
            answer.value = null
            submitted.value = false
            feedback.value = null
            
            // Start timer
            if (session.value.settings?.timer) {
                startTimer(session.value.settings.timer)
            }
        }
        
        // Update participant count
        if (data.participants) {
            participantCount.value = Object.keys(data.participants).length
        }
    })
}
```

### Submitting an Answer (Student)

```javascript
const submitAnswer = async () => {
    if (!answer.value || submitted.value) return
    
    submitting.value = true
    try {
        const response = await axios.post(
            `/api/quiz-sessions/${session.value.id}/answers`,
            {
                question_id: currentQuestion.value.id,
                answer: answer.value
            }
        )
        
        submitted.value = true
        feedback.value = response.data
        
        // Update local participant score
        participant.value.score = response.data.participant.score
        
    } catch (error) {
        console.error('Failed to submit answer:', error)
    } finally {
        submitting.value = false
    }
}
```

### Timer Implementation

```javascript
const startTimer = (duration) => {
    timeRemaining.value = duration
    
    // Clear existing timer
    if (timerInterval.value) {
        clearInterval(timerInterval.value)
    }
    
    // Start countdown
    timerInterval.value = setInterval(() => {
        timeRemaining.value--
        
        if (timeRemaining.value <= 0) {
            clearInterval(timerInterval.value)
            
            // Auto-submit if enabled
            if (session.value.settings?.auto_submit && !submitted.value) {
                submitAnswer()
            }
        }
    }, 1000)
}

// Cleanup on unmount
onUnmounted(() => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value)
    }
})
```

---

## Backend API Reference

### Create Session
```http
POST /api/quiz-sessions
Content-Type: application/json
Authorization: Bearer {token}

{
  "quiz_id": 123,  // optional
  "settings": {
    "timer": 60,
    "auto_submit": true,
    "show_correct_answer": false
  }
}

Response:
{
  "session": {
    "id": 1,
    "access_code": "ABC123",
    "status": "waiting",
    "settings": {...}
  }
}
```

### Join Session
```http
POST /api/quiz-sessions/join
Content-Type: application/json
Authorization: Bearer {token}

{
  "access_code": "ABC123"
}

Response:
{
  "session": {...},
  "participant": {
    "id": 1,
    "student_id": 456,
    "score": 0,
    "status": "joined"
  }
}
```

### Update Session State
```http
POST /api/quiz-sessions/{id}/state
Content-Type: application/json
Authorization: Bearer {token}

{
  "action": "start",  // start|next|pause|end
  "question_id": 789  // required for start/next
}

Response:
{
  "session": {...},
  "message": "Session updated successfully"
}
```

### Submit Answer
```http
POST /api/quiz-sessions/{id}/answers
Content-Type: application/json
Authorization: Bearer {token}

{
  "question_id": 789,
  "answer": 2  // option_id or numeric value
}

Response:
{
  "is_correct": true,
  "participant": {
    "id": 1,
    "score": 10
  }
}
```

---

## Firebase Operations

### Initialize Firebase

```javascript
import { initializeApp } from 'firebase/app'
import { getDatabase } from 'firebase/database'
import { getAuth, signInAnonymously } from 'firebase/auth'

const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
  databaseURL: import.meta.env.VITE_FIREBASE_DATABASE_URL,
  // ... other config
}

const app = initializeApp(firebaseConfig)
const database = getDatabase(app)
const auth = getAuth(app)

// Anonymous auth for security rules
await signInAnonymously(auth)

export { app, database, auth }
```

### Write Data

```javascript
import { ref as firebaseRef, set, update } from 'firebase/database'
import { database } from '@/firebase/init'

// Set entire node
const sessionRef = firebaseRef(database, `quiz_sessions/${code}`)
await set(sessionRef, {
  status: 'active',
  current_question: {...}
})

// Update specific fields
await update(sessionRef, {
  status: 'completed'
})
```

### Read Data (One-time)

```javascript
import { ref as firebaseRef, get } from 'firebase/database'
import { database } from '@/firebase/init'

const sessionRef = firebaseRef(database, `quiz_sessions/${code}`)
const snapshot = await get(sessionRef)

if (snapshot.exists()) {
  const data = snapshot.val()
  console.log(data)
}
```

### Listen to Changes

```javascript
import { ref as firebaseRef, onValue } from 'firebase/database'
import { database } from '@/firebase/init'

const sessionRef = firebaseRef(database, `quiz_sessions/${code}`)

const unsubscribe = onValue(sessionRef, (snapshot) => {
  const data = snapshot.val()
  // Handle updates
})

// Cleanup
onUnmounted(() => {
  unsubscribe()
})
```

### Listen to Child Events

```javascript
import { ref as firebaseRef, onChildAdded, onChildChanged } from 'firebase/database'

const participantsRef = firebaseRef(database, `quiz_sessions/${code}/participants`)

// New participant joins
onChildAdded(participantsRef, (snapshot) => {
  const participant = snapshot.val()
  console.log('New participant:', participant)
})

// Participant score updates
onChildChanged(participantsRef, (snapshot) => {
  const participant = snapshot.val()
  console.log('Participant updated:', participant)
})
```

---

## Database Queries

### Get Active Sessions

```php
$activeSessions = QuizSession::where('status', 'active')
    ->with(['teacher', 'currentQuestion', 'participants.student'])
    ->get();
```

### Get Session by Access Code

```php
$session = QuizSession::where('access_code', $code)
    ->with(['quiz', 'teacher', 'participants'])
    ->firstOrFail();
```

### Get Participant with Score

```php
$participant = QuizSessionParticipant::where('quiz_session_id', $sessionId)
    ->where('student_id', $studentId)
    ->with('student')
    ->first();
```

### Get Top Participants

```php
$leaderboard = QuizSessionParticipant::where('quiz_session_id', $sessionId)
    ->with('student')
    ->orderBy('score', 'desc')
    ->limit(10)
    ->get();
```

### Update Participant Score

```php
$participant = QuizSessionParticipant::find($id);
$participant->incrementScore(10);

// Or directly
QuizSessionParticipant::where('id', $id)->increment('score', 10);
```

---

## Common Patterns

### Cleanup Pattern

```javascript
// Component setup
let sessionListener = null
let timerInterval = null

// Setup listeners
const setupListeners = () => {
  sessionListener = onValue(sessionRef, callback)
  timerInterval = setInterval(updateTimer, 1000)
}

// Cleanup on unmount
onUnmounted(() => {
  if (sessionListener) {
    sessionListener()  // Unsubscribe from Firebase
  }
  if (timerInterval) {
    clearInterval(timerInterval)
  }
})
```

### Error Handling Pattern

```javascript
const performAction = async () => {
  loading.value = true
  try {
    const response = await axios.post('/api/endpoint', data)
    // Success handling
    $q.notify({
      type: 'positive',
      message: 'Success!'
    })
  } catch (error) {
    console.error('Action failed:', error)
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Action failed'
    })
  } finally {
    loading.value = false
  }
}
```

### Loading State Pattern

```javascript
const state = reactive({
  loading: false,
  data: null,
  error: null
})

const fetchData = async () => {
  state.loading = true
  state.error = null
  
  try {
    const response = await axios.get('/api/data')
    state.data = response.data
  } catch (error) {
    state.error = error.message
  } finally {
    state.loading = false
  }
}
```

---

## Testing

### Manual Testing Checklist

**Teacher Flow:**
- [ ] Create session successfully
- [ ] Access code displays correctly
- [ ] Select question from list
- [ ] Start session broadcasts question
- [ ] See participants join in real-time
- [ ] Pause session works
- [ ] End session completes properly

**Student Flow:**
- [ ] Enter access code
- [ ] Join session successfully
- [ ] See waiting screen
- [ ] Receive question when teacher starts
- [ ] Timer counts down correctly
- [ ] Submit answer before timer expires
- [ ] Receive instant feedback
- [ ] Score updates correctly
- [ ] See completion screen

**Edge Cases:**
- [ ] Invalid access code shows error
- [ ] Joining completed session prevented
- [ ] Timer auto-submits when enabled
- [ ] Multiple students can join simultaneously
- [ ] Disconnected students handled gracefully
- [ ] Session can be ended mid-question

### Unit Test Examples

```javascript
// Test access code generation
test('generates unique access code', () => {
  const code1 = QuizSession.generateAccessCode()
  const code2 = QuizSession.generateAccessCode()
  
  expect(code1).toHaveLength(6)
  expect(code2).toHaveLength(6)
  expect(code1).not.toBe(code2)
})

// Test answer validation
test('validates correct answer', () => {
  const question = {
    type: 'multiple_choice',
    options: [
      { id: 1, is_correct: false },
      { id: 2, is_correct: true }
    ]
  }
  
  const isCorrect = validateAnswer(question, 2)
  expect(isCorrect).toBe(true)
})
```

---

## Environment Variables

### Required Variables

```env
# Firebase Configuration
VITE_FIREBASE_API_KEY=your_api_key
VITE_FIREBASE_AUTH_DOMAIN=your_project.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=your_project_id
VITE_FIREBASE_DATABASE_URL=https://your_project.firebaseio.com
VITE_FIREBASE_STORAGE_BUCKET=your_project.appspot.com
VITE_FIREBASE_MESSAGING_SENDER_ID=your_sender_id
VITE_FIREBASE_APP_ID=your_app_id
VITE_FIREBASE_MEASUREMENT_ID=your_measurement_id
```

### Development vs Production

```javascript
// Use emulator in development
if (import.meta.env.DEV) {
  connectDatabaseEmulator(database, 'localhost', 9000)
  connectAuthEmulator(auth, 'http://localhost:9099')
}
```

---

## Debugging Tips

### Enable Firebase Debug Logging

```javascript
import { enableLogging } from 'firebase/database'

enableLogging(true)
```

### Log All Firebase Events

```javascript
onValue(sessionRef, (snapshot) => {
  console.log('Firebase update:', {
    path: snapshot.ref.toString(),
    data: snapshot.val(),
    timestamp: new Date().toISOString()
  })
})
```

### Monitor Network Requests

```javascript
// Add axios interceptor
axios.interceptors.request.use(config => {
  console.log('API Request:', config.method.toUpperCase(), config.url)
  return config
})

axios.interceptors.response.use(
  response => {
    console.log('API Response:', response.status, response.data)
    return response
  },
  error => {
    console.error('API Error:', error.response?.status, error.response?.data)
    return Promise.reject(error)
  }
)
```

### Check Firebase Connection

```javascript
import { ref as firebaseRef, onDisconnect } from 'firebase/database'

const connectedRef = firebaseRef(database, '.info/connected')
onValue(connectedRef, (snapshot) => {
  if (snapshot.val() === true) {
    console.log('Connected to Firebase')
  } else {
    console.log('Disconnected from Firebase')
  }
})
```

---

## Performance Tips

### Optimize Firebase Reads

```javascript
// Bad: Reading entire session repeatedly
onValue(sessionRef, callback)

// Good: Read only what you need
const statusRef = firebaseRef(database, `quiz_sessions/${code}/status`)
onValue(statusRef, callback)
```

### Batch Updates

```javascript
// Bad: Multiple writes
await set(ref1, data1)
await set(ref2, data2)
await set(ref3, data3)

// Good: Single update
await update(sessionRef, {
  'field1': data1,
  'field2': data2,
  'field3': data3
})
```

### Debounce Frequent Updates

```javascript
import { debounce } from 'lodash-es'

const updateScore = debounce(async (score) => {
  await axios.post('/api/update-score', { score })
}, 500)
```

---

## Common Issues & Solutions

### Issue: Firebase 400 Error

**Problem:** `400 Bad Request` to `identitytoolkit.googleapis.com`

**Solution:**
```javascript
// Add emulator connection in development
import { connectAuthEmulator } from 'firebase/auth'

if (window.location.hostname === 'localhost') {
  connectAuthEmulator(auth, 'http://localhost:9099')
}
```

### Issue: Students Not Receiving Questions

**Problem:** Firebase listener not triggering

**Solution:**
```javascript
// Ensure listener is set up BEFORE data changes
setupFirebaseListener()  // Call this first
await joinSession()      // Then join

// Verify path matches exactly
console.log('Listening to:', `quiz_sessions/${accessCode.value}`)
```

### Issue: Timer Desync

**Problem:** Different students see different times

**Solution:**
```javascript
// Use server timestamp
import { serverTimestamp } from 'firebase/database'

await update(sessionRef, {
  'timer/started_at': serverTimestamp()
})
```

### Issue: Memory Leaks

**Problem:** Listeners not cleaned up

**Solution:**
```javascript
// Always unsubscribe
let unsubscribe = null

onMounted(() => {
  unsubscribe = onValue(ref, callback)
})

onUnmounted(() => {
  if (unsubscribe) {
    unsubscribe()
  }
})
```

---

## Resources

### Documentation
- [Firebase Realtime Database Docs](https://firebase.google.com/docs/database)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Vue 3 Composition API](https://vuejs.org/guide/extras/composition-api-faq.html)
- [Quasar Framework](https://quasar.dev/)

### Tools
- [Firebase Console](https://console.firebase.google.com/)
- [Laravel Telescope](https://laravel.com/docs/telescope) - Debug API calls
- [Vue DevTools](https://devtools.vuejs.org/) - Inspect component state

### Support
- Check `docs/Real-time_Quiz_System/` for additional documentation
- Review `SYSTEM_ARCHITECTURE.md` for detailed architecture
- See `firepase_plan.md` for Firebase emulator setup

---

## Next Steps

1. Review `SYSTEM_ARCHITECTURE.md` for complete system overview
2. Set up Firebase emulator for local development
3. Test teacher and student flows manually
4. Implement additional features (leaderboard, analytics, etc.)
5. Add comprehensive error handling
6. Write automated tests

Happy coding! 🚀
