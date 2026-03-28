# Question-Response System Plan
**100% Reusable Real-Time Question/Answer System using Remote Control v1**

---

## 🎯 Overview

A fully generic, real-time question-response system that allows:
- **Teacher/Host**: Send any type of question with custom rules to students
- **Students**: Receive questions via unique code, submit answers in real-time
- **System**: Handle any question format (MCQ, text, multiple answers, etc.) as JSON

Uses the existing `useRealtimeChannel` composable from remote_control v1 for bidirectional communication.

---

## 📁 Folder Structure

```
question_responses/
├── PLAN.md (this file)
├── components/
│   ├── QuestionRenderer.vue       # Generic question display component
│   ├── AnswerInput.vue            # Generic answer input component
│   └── ResponseCollector.vue      # Real-time response viewer for teacher
├── composables/
│   └── useQuestionSession.js      # Session management composable
├── TeacherView.vue                # Teacher/Host page (example parent)
└── StudentView.vue                # Student page (join with code)
```

---

## 🏗️ Architecture

### **Data Flow**

```
Teacher Creates Question → Firebase Channel (channelId = sessionCode)
                              ↓
                    Student Joins via Code
                              ↓
                    Student Receives Question
                              ↓
                    Student Submits Answer
                              ↓
                    Teacher Receives Response
```

### **Firebase Channel Structure**

```javascript
channels/
  └── {sessionCode}/
      ├── state/
      │   ├── question: { ...questionData }
      │   ├── status: "active" | "closed"
      │   └── createdAt: timestamp
      └── events/
          └── {eventId}/
              ├── type: "answer_submitted"
              ├── studentId: "uuid-or-name"
              ├── studentName: "John Doe"
              ├── answer: { ...answerData }
              └── timestamp: timestamp
```

---

## 🧩 Core Components

### **1. QuestionRenderer.vue** (100% Generic)

**Purpose**: Display any question format based on JSON schema

**Props**:
```javascript
{
  questionData: {
    type: Object,
    required: true,
    // Example structure:
    // {
    //   id: "q1",
    //   type: "multiple_choice" | "text" | "multi_select" | "custom",
    //   title: "What is 2+2?",
    //   options: ["1", "2", "3", "4"],
    //   rules: {
    //     required: true,
    //     minSelection: 1,
    //     maxSelection: 1,
    //     allowCustom: false
    //   },
    //   metadata: { ... }
    // }
  },
  readonly: {
    type: Boolean,
    default: false
  }
}
```

**Features**:
- Renders based on `questionData.type`
- Supports: MCQ, multi-select, text input, number, date, custom types
- Validates based on `questionData.rules`
- Emits: `@answer-changed` with answer data

---

### **2. AnswerInput.vue** (100% Generic)

**Purpose**: Capture student answer based on question type

**Props**:
```javascript
{
  questionData: Object,  // Same as QuestionRenderer
  disabled: Boolean
}
```

**Emits**:
```javascript
{
  'answer-submitted': {
    questionId: "q1",
    answer: { ... },  // Format depends on question type
    timestamp: Date.now()
  }
}
```

**Features**:
- Auto-validates before submission
- Shows validation errors
- Supports all question types
- Handles edge cases (empty, invalid format, etc.)

---

### **3. ResponseCollector.vue**

**Purpose**: Display real-time responses for teacher

**Props**:
```javascript
{
  sessionCode: String,
  questionData: Object
}
```

**Features**:
- Shows list of students who answered
- Real-time updates as answers arrive
- Export responses as JSON
- Statistics (total answered, percentage, etc.)
- Visual indicators for answer patterns

---

## 🔧 Composables

### **useQuestionSession.js**

**Purpose**: Manage question session lifecycle

```javascript
export function useQuestionSession(sessionCode, userRole = 'student') {
  // Uses useRealtimeChannel under the hood
  const channel = useRealtimeChannel(sessionCode, {
    firebasePath: 'question_sessions',
    persistence: true,
    logEvents: true
  })

  // Teacher methods
  const publishQuestion = (questionData) => { ... }
  const closeSession = () => { ... }
  const getResponses = () => { ... }
  const exportResponses = () => { ... }

  // Student methods
  const joinSession = (studentInfo) => { ... }
  const submitAnswer = (answerData) => { ... }
  const getCurrentQuestion = () => { ... }

  // Shared state
  const sessionStatus = ref('waiting') // waiting | active | closed
  const currentQuestion = ref(null)
  const responses = ref([])
  const studentInfo = ref(null)

  return {
    // Teacher API
    publishQuestion,
    closeSession,
    getResponses,
    exportResponses,
    
    // Student API
    joinSession,
    submitAnswer,
    getCurrentQuestion,
    
    // Shared
    sessionStatus,
    currentQuestion,
    responses,
    studentInfo,
    isConnected: channel.isConnected
  }
}
```

---

## 📄 Pages/Views

### **TeacherView.vue** (Example Parent Component)

**Route**: `/remote-control/question-responses/teacher`

**Features**:
- Generate unique session code (6-digit alphanumeric)
- Create question using JSON editor or form builder
- Display session code prominently for students
- Show real-time responses using `ResponseCollector`
- Export responses as JSON
- Close session when done

**Example Question JSON**:
```javascript
{
  id: "demo_q1",
  type: "multiple_choice",
  title: "What is the capital of France?",
  options: ["London", "Paris", "Berlin", "Madrid"],
  correctAnswer: 1, // Optional for auto-grading
  rules: {
    required: true,
    maxSelection: 1
  },
  timeLimit: 60, // Optional: seconds
  metadata: {
    subject: "Geography",
    difficulty: "easy"
  }
}
```

---

### **StudentView.vue**

**Route**: `/remote-control/question-responses/student`

**Features**:
- Input session code to join
- If logged in: Use `auth.user.name` and `auth.user.id`
- If not logged in: 
  - Ask for name
  - Generate UUID: `crypto.randomUUID()`
  - Store in localStorage for session persistence
- Display question using `QuestionRenderer`
- Submit answer using `AnswerInput`
- Show confirmation after submission
- Show "waiting for next question" state

**Student Info Structure**:
```javascript
{
  id: "uuid-or-user-id",
  name: "John Doe",
  isAuthenticated: true/false,
  joinedAt: timestamp
}
```

---

## 🔐 Authentication Handling

### **Logged In User**:
```javascript
import { usePage } from '@inertiajs/vue3'
const { auth } = usePage().props

if (auth?.user) {
  studentInfo.value = {
    id: auth.user.id,
    name: auth.user.name,
    isAuthenticated: true
  }
}
```

### **Guest User**:
```javascript
// Check localStorage first
let guestId = localStorage.getItem('question_session_guest_id')
if (!guestId) {
  guestId = crypto.randomUUID()
  localStorage.setItem('question_session_guest_id', guestId)
}

// Ask for name
const guestName = prompt("Enter your name:")

studentInfo.value = {
  id: guestId,
  name: guestName || 'Anonymous',
  isAuthenticated: false
}
```

---

## 🛣️ Routes

### **Backend Routes** (`routes/web.php` or dedicated route file)

```php
// Teacher routes
Route::get('/remote-control/question-responses/teacher', function () {
    return Inertia::render('myclass2026/features/remot_control/v1/examples/question_responses/TeacherView');
})->name('question-responses.teacher');

// Student routes
Route::get('/remote-control/question-responses/student', function () {
    return Inertia::render('myclass2026/features/remot_control/v1/examples/question_responses/StudentView');
})->name('question-responses.student');

// Optional: Direct join with code
Route::get('/remote-control/question-responses/join/{code}', function ($code) {
    return Inertia::render('myclass2026/features/remot_control/v1/examples/question_responses/StudentView', [
        'sessionCode' => $code
    ]);
})->name('question-responses.join');
```

---

## 📊 Question Types Support

### **1. Multiple Choice (Single)**
```javascript
{
  type: "multiple_choice",
  options: ["A", "B", "C", "D"],
  rules: { maxSelection: 1 }
}
// Answer format: { selectedIndex: 1 }
```

### **2. Multiple Choice (Multiple)**
```javascript
{
  type: "multi_select",
  options: ["A", "B", "C", "D"],
  rules: { minSelection: 1, maxSelection: 3 }
}
// Answer format: { selectedIndexes: [0, 2] }
```

### **3. Text Input**
```javascript
{
  type: "text",
  rules: { minLength: 10, maxLength: 500 }
}
// Answer format: { text: "Student's answer..." }
```

### **4. Number Input**
```javascript
{
  type: "number",
  rules: { min: 0, max: 100 }
}
// Answer format: { value: 42 }
```

### **5. Custom Type**
```javascript
{
  type: "custom",
  customRenderer: "DrawingCanvas",
  rules: { ... }
}
// Answer format: { customData: { ... } }
```

---

## 🎨 UI/UX Considerations

### **Teacher View**:
- Large, prominent session code display (QR code optional)
- Live counter of students joined
- Live counter of responses received
- Visual progress bar
- Ability to close/reopen session
- Export button always visible

### **Student View**:
- Clean, distraction-free interface
- Clear question display
- Easy-to-use answer inputs
- Confirmation message after submission
- "Waiting for next question" state
- Reconnection handling if disconnected

---

## 🔄 Real-Time Synchronization

### **Using `useRealtimeChannel`**:

```javascript
// Teacher publishes question
const { sendCommand, state } = useRealtimeChannel(sessionCode)

sendCommand({
  type: 'publish_question',
  data: questionData
})

// Student receives question
watch(state, (newState) => {
  if (newState?.question) {
    currentQuestion.value = newState.question
  }
})

// Student submits answer
sendCommand({
  type: 'submit_answer',
  data: {
    studentId: studentInfo.value.id,
    studentName: studentInfo.value.name,
    answer: answerData,
    timestamp: Date.now()
  }
})

// Teacher receives answers via events
onEvent((event) => {
  if (event.type === 'submit_answer') {
    responses.value.push(event.data)
  }
})
```

---

## 🧪 Testing Strategy

### **Manual Testing Scenarios**:

1. **Teacher creates session** → Verify code generation
2. **Student joins with code** → Verify connection
3. **Teacher publishes MCQ** → Student sees question
4. **Student submits answer** → Teacher sees response
5. **Multiple students join** → All receive same question
6. **Teacher exports responses** → Valid JSON output
7. **Guest user flow** → UUID generation, localStorage persistence
8. **Logged-in user flow** → Uses auth.user data
9. **Network disconnect** → Reconnection handling
10. **Session close** → Students see "session ended" message

---

## 📦 Export Format

### **Response Export JSON**:
```javascript
{
  sessionCode: "ABC123",
  question: { ...questionData },
  responses: [
    {
      studentId: "uuid-123",
      studentName: "John Doe",
      isAuthenticated: true,
      answer: { selectedIndex: 1 },
      timestamp: 1234567890,
      submittedAt: "2024-03-28T10:30:00Z"
    },
    // ... more responses
  ],
  statistics: {
    totalStudents: 25,
    totalResponses: 23,
    responseRate: 0.92,
    averageResponseTime: 12.5 // seconds
  },
  createdAt: "2024-03-28T10:25:00Z",
  closedAt: "2024-03-28T10:35:00Z"
}
```

---

## 🚀 Implementation Steps

1. ✅ **Create folder structure**
2. ✅ **Write this PLAN.md**
3. **Create `useQuestionSession.js` composable**
4. **Create `QuestionRenderer.vue` component**
5. **Create `AnswerInput.vue` component**
6. **Create `ResponseCollector.vue` component**
7. **Create `TeacherView.vue` page**
8. **Create `StudentView.vue` page**
9. **Add routes to Laravel**
10. **Test with multiple question types**
11. **Test with multiple concurrent students**
12. **Add to examples dashboard**

---

## 🎯 Key Advantages

✅ **100% Reusable**: Works with any question format via JSON
✅ **Real-Time**: Instant synchronization using Firebase
✅ **Flexible Auth**: Works for both logged-in and guest users
✅ **Scalable**: Can handle multiple concurrent sessions
✅ **Exportable**: All data exportable as JSON
✅ **Type-Safe**: Clear data structures and validation
✅ **Offline-Ready**: Uses existing offline storage from v1
✅ **No Backend Logic**: Pure Firebase real-time communication

---

## 📝 Notes

- The parent component (TeacherView) is just an **example** - any component can use the system
- The system is **100% generic** - it doesn't care about question content
- All validation is **rule-based** from JSON
- The system can be extended to support **any custom question type**
- Firebase security rules should be added for production use
- Consider adding **time limits** and **auto-close** features
- Consider adding **analytics** and **insights** for teachers

---

**Status**: 📋 Planning Complete - Ready for Implementation
