# Live Question Integration Plan - Presentation V5
**Remote Control Question System Integration for Short Answer Questions**

---

## 🎯 Overview

Integrate the existing remote control question-response system into Presentation V5 to enable teachers to:
- Launch live short-answer questions during presentations
- Collect real-time student responses
- Display responses in presentation mode
- Export responses for grading/analysis

**Scope**: Short answer questions ONLY (text input)

---

## 📁 File Structure

```
/v5/
├── components/
│   ├── LiveQuestionPanel.vue          # NEW: Teacher control panel for live questions
│   ├── LiveQuestionOverlay.vue        # NEW: Student response overlay
│   └── LiveQuestionResults.vue        # NEW: Display collected responses
├── composables/
│   └── useLiveQuestion.js             # NEW: Wrapper around useQuestionSession
├── stores/
│   └── liveQuestionStore.js           # NEW: Pinia store for question state
└── plan/
    ├── live_question_integration_plan.md  # THIS FILE
    └── task_list.md                       # Task checklist
```

---

## 🏗️ Architecture

### Integration Points

1. **Toolbar.vue** - Add "Live Question" button
2. **EditorCanvas.vue** - Display question overlay when active
3. **FloatingAnalytics.vue** - Show live response count
4. **Remote Control System** - Reuse existing components

### Data Flow

```
Teacher clicks "Live Question" → LiveQuestionPanel opens
    ↓
Teacher creates short answer question → Session starts
    ↓
Session code displayed → Students join via /remote-control/question-responses/student
    ↓
Students submit answers → Real-time sync via Firebase
    ↓
Teacher views responses → LiveQuestionResults component
    ↓
Teacher closes session → Export responses (optional)
```

---

## 🧩 New Components

### 1. LiveQuestionPanel.vue
**Location**: `components/LiveQuestionPanel.vue`

**Purpose**: Teacher interface to create and manage live questions

**Features**:
- Quick question input (title + instructions)
- Auto-generate session code
- Display session code prominently
- Show live response count
- Close session button
- Export responses button

**Props**: None (uses store)

**Integration**: Opens as modal/panel when toolbar button clicked

---

### 2. LiveQuestionOverlay.vue
**Location**: `components/LiveQuestionOverlay.vue`

**Purpose**: Display active question info during presentation

**Features**:
- Show session code (large, readable)
- Show question text
- Display response count
- Countdown timer (optional)
- Minimizable

**Props**: None (uses store)

**Integration**: Renders in presentation mode when question is active

---

### 3. LiveQuestionResults.vue
**Location**: `components/LiveQuestionResults.vue`

**Purpose**: Display collected responses

**Features**:
- List all student responses
- Show student names
- Timestamp for each response
- Export as JSON
- Export as CSV
- Clear/anonymize student names option

**Props**: 
```javascript
{
  responses: Array,
  questionData: Object
}
```

---

## 🔧 New Composable

### useLiveQuestion.js
**Location**: `composables/useLiveQuestion.js`

**Purpose**: Simplified wrapper around remote control's `useQuestionSession`

**API**:
```javascript
export function useLiveQuestion() {
  const store = useLiveQuestionStore()
  
  // Teacher methods
  const createQuestion = (questionText) => { ... }
  const startSession = () => { ... }
  const closeSession = () => { ... }
  const exportResponses = (format = 'json') => { ... }
  
  // Computed
  const isActive = computed(() => store.isActive)
  const sessionCode = computed(() => store.sessionCode)
  const responses = computed(() => store.responses)
  const responseCount = computed(() => store.responses.length)
  
  return {
    createQuestion,
    startSession,
    closeSession,
    exportResponses,
    isActive,
    sessionCode,
    responses,
    responseCount
  }
}
```

**Implementation**: Uses `useQuestionSession` from remote control system

---

## 🗄️ New Store

### liveQuestionStore.js
**Location**: `stores/liveQuestionStore.js`

**State**:
```javascript
{
  isActive: false,
  sessionCode: null,
  questionData: null,
  responses: [],
  isPanelOpen: false,
  isResultsOpen: false
}
```

**Actions**:
- `openPanel()` - Open question creation panel
- `closePanel()` - Close panel
- `setQuestion(data)` - Set question data
- `setSessionCode(code)` - Set session code
- `addResponse(response)` - Add new response
- `clearSession()` - Reset all state
- `toggleResults()` - Show/hide results

---

## 🔗 Integration Steps

### Step 1: Add Button to Toolbar
**File**: `components/Toolbar.vue`

Add button:
```vue
<button @click="openLiveQuestion" class="toolbar-btn">
  <svg><!-- Live icon --></svg>
  Live Question
</button>
```

### Step 2: Add Panel to Index.vue
**File**: `Index.vue`

Add component:
```vue
<LiveQuestionPanel />
<LiveQuestionOverlay v-if="!ui.isEditMode" />
```

### Step 3: Update FloatingAnalytics
**File**: `components/FloatingAnalytics.vue`

Add live question indicator when active

### Step 4: Create Store
**File**: `stores/liveQuestionStore.js`

Implement Pinia store

### Step 5: Create Composable
**File**: `composables/useLiveQuestion.js`

Wrapper around remote control system

### Step 6: Build Components
Create all three new components

---

## 📊 Question Data Structure (Extended with Grading Criteria)

```javascript
{
  id: "generated-uuid",
  type: "text",
  title: "What is the main idea of this slide?",
  instructions: "Answer in 1-2 sentences",
  rules: {
    required: true,
    minLength: 10,
    maxLength: 500
  },
  timeLimit: 120, // seconds (optional)
  createdAt: timestamp,
  
  // NEW: Grading criteria with multiple points
  grading: {
    criteria: [
      {
        id: "criterion-1",
        label: "Identifies main topic",
        description: "Mentions the core subject discussed",
        points: 3,
        keywords: ["topic", "subject", "main", "core"]
      },
      {
        id: "criterion-2",
        label: "Explains significance",
        description: "Explains why this topic matters",
        points: 4,
        keywords: ["important", "significance", "why", "because"]
      },
      {
        id: "criterion-3",
        label: "Provides example",
        description: "Includes a relevant example or evidence",
        points: 3,
        keywords: ["example", "evidence", "such as", "for instance"]
      }
    ],
    maxPoints: 10,
    gradingMode: "manual" | "auto" | "hybrid", // manual: teacher grades, auto: keyword-based, hybrid: both
    showCriteriaToStudents: false // whether students see the grading rubric
  }
}
```

---

## 📊 Response Data Structure (With Grading)

```javascript
{
  studentId: "uuid-or-user-id",
  studentName: "John Doe",
  isAuthenticated: true/false,
  answer: {
    text: "The main idea is... This is important because... For instance..."
  },
  timestamp: 1234567890,
  submittedAt: "2024-03-28T10:30:00Z",
  
  // NEW: Grading results
  grading: {
    status: "pending" | "graded" | "auto-graded",
    autoScore: 7, // calculated from keyword matching
    teacherScore: null, // set by teacher
    finalScore: 7, // autoScore if not graded, otherwise teacherScore
    criterionResults: [
      {
        criterionId: "criterion-1",
        matched: true,
        matchedKeywords: ["topic", "main"],
        pointsEarned: 3,
        teacherOverride: null
      },
      {
        criterionId: "criterion-2",
        matched: true,
        matchedKeywords: ["important", "why"],
        pointsEarned: 4,
        teacherOverride: null
      },
      {
        criterionId: "criterion-3",
        matched: false,
        matchedKeywords: [],
        pointsEarned: 0,
        teacherOverride: 3 // teacher manually awarded partial points
      }
    ],
    teacherNotes: "Good explanation but could include more detail on examples",
    gradedAt: "2024-03-28T10:35:00Z",
    gradedBy: "teacher-uuid"
  }
}
```

---

## 🎨 UI/UX Design

### LiveQuestionPanel (Modal)
- Clean, minimal design
- Large session code display
- Real-time response counter
- Quick close button
- Export dropdown (JSON/CSV)

### LiveQuestionOverlay (Floating)
- Top-right corner in presentation mode
- Minimizable
- Shows: Code, Question, Count
- Draggable (optional)

### LiveQuestionResults (Panel)
- Scrollable list of responses
- Search/filter by student name
- Sort by time/name
- Bulk export options

---

## 🔐 Student Access

**Students join via existing route**:
`/remote-control/question-responses/student`

**Flow**:
1. Student enters session code
2. Student enters name (if not logged in)
3. Student sees question
4. Student submits answer
5. Confirmation shown

**No changes needed** to existing StudentView.vue

---

## � Grading System Overview

### Manual Grading (Teacher-only)
Teacher reviews each response and assigns points per criterion:
- View response and original criteria
- Toggle `matched` for each criterion
- Adjust `pointsEarned` if needed
- Add teacher notes
- Final score = sum of all criterion points

### Auto Grading (Keyword-based)
System analyzes answer text for keyword matches:
- Search for keywords in each criterion
- Calculate `autoScore` automatically
- Display `autoScore` to teacher for review
- Teacher can override individual criteria

### Hybrid Grading (Recommended)
- Auto-grade first for efficiency
- Teacher reviews auto-results
- Teacher can adjust individual criteria
- Final score reflects teacher's final decision

---

## 🎯 Implementation Notes

### Store State Enhancement
```javascript
// liveQuestionStore.js
{
  // ... existing state
  currentQuestion: {
    // ... existing question data
    grading: {
      criteria: [...],
      maxPoints: 10,
      gradingMode: "hybrid"
    }
  },
  responses: [
    {
      // ... existing response data
      grading: {
        status: "pending",
        autoScore: 7,
        teacherScore: null,
        finalScore: 7,
        criterionResults: [...]
      }
    }
  ]
}
```

### Grading UI Components
- **GradingPanel.vue**: Teacher interface for grading responses
- **CriterionRow.vue**: Individual criterion with toggle and points
- **ScoreSummary.vue**: Display total score and breakdown

---

## �🚀 Implementation Phases

### Phase 1: Core Integration (Priority)
- [ ] Create liveQuestionStore.js
- [ ] Create useLiveQuestion.js composable
- [ ] Add button to Toolbar.vue
- [ ] Create LiveQuestionPanel.vue (basic)

### Phase 2: Display & Results
- [ ] Create LiveQuestionOverlay.vue
- [ ] Create LiveQuestionResults.vue
- [ ] Integrate with FloatingAnalytics.vue

### Phase 3: Grading System
- [ ] Add grading criteria to question structure
- [ ] Create GradingPanel.vue component
- [ ] Implement auto-grading logic (keyword matching)
- [ ] Add teacher grading interface
- [ ] Implement score calculation

### Phase 4: Polish & Export
- [ ] Add export functionality (JSON/CSV)
- [ ] Add animations/transitions
- [ ] Add error handling
- [ ] Add session persistence (optional)

---

## 🧪 Testing Checklist

- [ ] Teacher can create question from toolbar
- [ ] Session code generates correctly
- [ ] Students can join via existing route
- [ ] Responses appear in real-time
- [ ] Response count updates live
- [ ] Export JSON works
- [ ] Export CSV works
- [ ] Session closes properly
- [ ] Multiple questions in sequence work
- [ ] Works with both logged-in and guest students

---

## 📦 Dependencies

**Existing Systems**:
- Remote Control v1 (`/remot_control/v1/`)
- `useQuestionSession` composable
- Firebase real-time database
- Existing StudentView.vue

**New Dependencies**: None

---

## ⚠️ Important Notes

1. **Reuse, Don't Rebuild**: Use existing remote control components
2. **Short Answer Only**: No MCQ, no multi-select (for now)
3. **No Backend Changes**: Pure frontend integration
4. **Session Isolation**: Each question = new session code
5. **Student Route**: Students use existing `/remote-control/question-responses/student`

---

## 🎯 Success Criteria

✅ Teacher can launch live question in < 10 seconds
✅ Students can join and submit in < 30 seconds
✅ Responses sync in real-time (< 2 second delay)
✅ Export works for both JSON and CSV
✅ No conflicts with existing presentation features
✅ Clean, intuitive UI

---

## 📝 Future Enhancements (Out of Scope)

- Multiple choice questions
- Image-based questions
- Anonymous responses toggle
- Response analytics/insights
- Question templates library
- Auto-grading for specific answers

---

**Status**: 📋 Planning Complete - Awaiting Confirmation
**Next Step**: Review plan → Create task list → Begin implementation
