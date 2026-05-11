# Quiz v2 Enhancement Plan
**Goal:** Improve v8 quiz system inspired by v7 group quiz features — better UX, easier creation, multi-question support with smooth navigation

---

## 📋 Current State (Quiz v1)

### ✅ What Works
- Basic quiz creation dialog
- Single-question navigation (prev/next)
- Answer selection and validation
- Results display with scoring
- Mobile-responsive design

### ❌ Pain Points
- **Creation is tedious** — manual question entry, no AI integration
- **No bulk import** — can't paste JSON from ChatGPT like v7
- **No inline editing** — can't preview/edit questions before adding
- **No math support** — no LaTeX rendering for formulas
- **Limited navigation** — only prev/next, no jump-to-question
- **No question bank** — can't reuse questions across quizzes
- **No timer/scoring config** — fixed scoring, no time limits

---

## 🎯 Quiz v2 Features (Inspired by v7 Group Quiz)

### 1. **AI-Powered Quiz Generator** 🤖
**Like:** `v7/components/GroupQuizGenerator.vue`

#### Tasks:
- [ ] Create `QuizGeneratorDialog.vue` component
  - Topic input + question count + difficulty selector
  - "Copy Prompt" button → generates ChatGPT/Claude prompt
  - Paste area for JSON response
  - Preview parsed questions with inline editing
  - "Inject" button → adds all questions as quiz element
- [ ] Support markdown + LaTeX in questions/options
  - Use `EditableMath.vue` from v7 for formula rendering
  - Auto-detect `\( ... \)` or `$$ ... $$` syntax
- [ ] JSON validation with helpful error messages
- [ ] "Empty Questions" button for quick placeholder creation

---

### 2. **Improved Quiz Creation Dialog** ✨

#### Tasks:
- [ ] **Bulk Question Import**
  - Paste JSON array of questions
  - CSV import support (question, optA, optB, optC, optD, correct)
  - Drag-and-drop `.json` file upload
- [ ] **Question Preview Grid**
  - Show all questions in cards before adding
  - Inline edit: click to modify question/options
  - Delete individual questions
  - Reorder questions (drag handles)
- [ ] **Math Formula Support**
  - Toolbar button to insert LaTeX
  - Live preview of rendered formulas
  - Auto-escape backslashes for JSON compatibility
- [ ] **Question Templates**
  - True/False template
  - Multiple choice (2/3/4/5 options)
  - Fill-in-the-blank (future)

---

### 3. **Enhanced Quiz Navigation** 🧭

#### Tasks:
- [ ] **Question Palette**
  - Grid of question numbers (1, 2, 3...)
  - Click to jump to any question
  - Color-coded: answered (green), current (blue), unanswered (gray)
- [ ] **Progress Bar**
  - Visual bar showing completion percentage
  - "3 of 10 answered" text
- [ ] **Keyboard Shortcuts**
  - `←` / `→` for prev/next
  - `1-9` to jump to question
  - `Space` to select option
  - `Enter` to submit/next
- [ ] **Swipe Gestures (Mobile)**
  - Swipe left/right to navigate questions
  - Pull-to-refresh to reset quiz

---

### 4. **Scoring & Timer Config** ⏱️

#### Tasks:
- [ ] **Scoring Settings**
  - Points per correct answer (default: 10)
  - Penalty for wrong answer (default: 0)
  - Partial credit option (future)
- [ ] **Timer Options**
  - No timer (default)
  - Per-question timer (e.g., 30s each)
  - Total quiz timer (e.g., 5 minutes)
  - Show countdown in present mode
  - Auto-submit when time expires
- [ ] **Attempt Limits**
  - Allow multiple attempts (default: 1)
  - Show best score or latest score

---

### 5. **Better Results Display** 📊

#### Tasks:
- [ ] **Detailed Breakdown**
  - Show correct answer for each question
  - Highlight user's wrong answers in red
  - Explanation text for each question (optional)
- [ ] **Score Summary Card**
  - Large score display (e.g., "8/10 - 80%")
  - Pass/fail indicator (configurable threshold)
  - Time taken (if timer enabled)
- [ ] **Export Results**
  - Download as PDF
  - Copy to clipboard as text
  - Share link (future: cloud sync)

---

### 6. **Quiz Element Improvements** 🎨

#### Tasks:
- [ ] **Khan Academy Dark Theme**
  - Dark background (`#1a1a1a`) with white text
  - Spacious option cards with subtle borders
  - Hover states: lighten background to `#3a3a3a`
  - Selected option: blue border + blue text
  - Correct answer: green background fade
  - Wrong answer: red background fade
- [ ] **Progress Bar (Top)**
  - Thin horizontal bar (4px height)
  - Blue fill for completed questions
  - Gray track for remaining
  - Smooth animation on progress
- [ ] **Score Badges (Top-Right)**
  - Red badge: `✗ 0` (wrong count)
  - Green badge: `✓ 0` (correct count)
  - Update in real-time after grading
- [ ] **Large Question Number**
  - Display "1.", "2.", etc. before question text
  - Use larger font (24px) and bold weight
- [ ] **Single "Next" Button**
  - Bottom-right corner, light blue (`#63b3ed`)
  - Rounded corners (8px)
  - Disabled state when no answer selected
  - Auto-advance to next question after selection (optional)
- [ ] **Math Formula Rendering**
  - Support fractions (½, ⅓, ⅔, etc.)
  - Support LaTeX: `\( ... \)` or `$$ ... $$`
  - Use KaTeX or MathJax for rendering
  - Fallback to Unicode fractions if no LaTeX
- [ ] **Minimal Chrome**
  - No thick borders or shadows
  - Clean, flat design
  - Focus on content, not decoration

---

### 7. **Question Bank & Reusability** 💾

#### Tasks:
- [ ] **Save Questions to Bank**
  - "Save to Bank" button in generator
  - Store in localStorage: `quiz-question-bank-v8`
  - Tag questions by topic/difficulty
- [ ] **Browse Question Bank**
  - Modal with searchable question list
  - Filter by topic, difficulty, date
  - Select multiple questions → add to quiz
- [ ] **Import/Export Bank**
  - Export bank as JSON
  - Import bank from file
  - Merge with existing bank

---

### 8. **Accessibility & UX Polish** ♿

#### Tasks:
- [ ] **Keyboard Navigation**
  - Tab through options
  - Enter to select
  - Focus indicators on all interactive elements
- [ ] **Screen Reader Support**
  - ARIA labels for all buttons
  - Announce question number and text
  - Announce score and results
- [ ] **Loading States**
  - Skeleton loaders while parsing JSON
  - Progress spinner during import
- [ ] **Error Handling**
  - Clear error messages for invalid JSON
  - Retry button on parse failure
  - Validation warnings (e.g., "No correct answer selected")

---

## 📁 File Structure (Quiz v2)

```
components/quiz-v2/
├── QuizGeneratorDialog.vue       # AI prompt builder + JSON import
├── QuizCreationDialog.vue        # Enhanced creation with bulk import
├── QuizElement.vue               # Main quiz renderer (refactored)
├── QuizQuestionView.vue          # Question display with math support
├── QuizNavigation.vue            # Enhanced nav with question palette
├── QuizResults.vue               # Detailed results with export
├── QuizSettingsPanel.vue         # Scoring, timer, attempt config
├── QuizQuestionBank.vue          # Question bank browser
├── EditableMath.vue              # LaTeX formula editor (from v7)
└── QUIZ_V2_PLAN.md               # This file
```

---

## 🚀 Implementation Phases

### **Phase 1: AI Generator (Week 1)**
- [ ] QuizGeneratorDialog.vue
- [ ] JSON parsing with validation
- [ ] Inline question editing
- [ ] Math formula support

### **Phase 2: Enhanced Navigation (Week 2)**
- [ ] Question palette
- [ ] Progress bar
- [ ] Keyboard shortcuts
- [ ] Swipe gestures

### **Phase 3: Scoring & Timer (Week 3)**
- [ ] Settings panel
- [ ] Timer implementation
- [ ] Auto-submit logic
- [ ] Attempt tracking

### **Phase 4: Question Bank (Week 4)**
- [ ] Bank storage system
- [ ] Browse/search UI
- [ ] Import/export
- [ ] Tagging system

### **Phase 5: Polish & Accessibility (Week 5)**
- [ ] Keyboard navigation
- [ ] Screen reader support
- [ ] Error handling
- [ ] Loading states
- [ ] Export results

---

## 🎨 Design Mockup Notes

**Inspiration:** Khan Academy quiz UI (dark theme, clean, math-focused)

### Quiz Element (Present Mode) — Khan Academy Style
```
┌────────────────────────────────────────────────────────────┐
│ 📝 Adding Mixed Numbers Challenge              [Share] [X] │
├────────────────────────────────────────────────────────────┤
│ ████████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░  1/10  ✗ 0  ✓ 0 │
├────────────────────────────────────────────────────────────┤
│                                                            │
│   1.  Solve: 2½ + 3⅖                                      │
│                                                            │
│   ┌──────────────────────────────────────────────────┐   │
│   │  A.  6⅖                                          │   │
│   └──────────────────────────────────────────────────┘   │
│   ┌──────────────────────────────────────────────────┐   │
│   │  B.  5⅖                                          │   │
│   └──────────────────────────────────────────────────┘   │
│   ┌──────────────────────────────────────────────────┐   │
│   │  C.  5⅗                                          │   │
│   └──────────────────────────────────────────────────┘   │
│   ┌──────────────────────────────────────────────────┐   │
│   │  D.  5 3/10                                      │   │
│   └──────────────────────────────────────────────────┘   │
│                                                            │
│                                              [Next →]      │
└────────────────────────────────────────────────────────────┘
```

**Key Design Elements:**
- **Dark background** (`#1a1a1a` or `#2d2d2d`)
- **White text** for high contrast
- **Thin progress bar** at top (blue fill, gray track)
- **Score badges** in top-right (red for wrong, green for correct)
- **Large question number** (e.g., "1.")
- **Spacious option cards** with hover states
- **Single "Next" button** (bottom-right, light blue)
- **Math formulas** rendered with proper fractions
- **Minimal chrome** — no unnecessary borders

### Quiz Generator Dialog (Dark Theme)
```
┌────────────────────────────────────────────────────────────┐
│ 🤖 AI Quiz Generator                                  [X]  │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Topic          [Science                              ]   │
│  Questions      [5]      Difficulty  [Medium        ▼]   │
│                                                            │
│  ┌──────────────────────────────────────────────────────┐ │
│  │  [Copy Prompt]           [Paste from Clipboard]     │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                            │
│  JSON Input:                                               │
│  ┌──────────────────────────────────────────────────────┐ │
│  │                                                      │ │
│  │  Paste ChatGPT output here...                       │ │
│  │                                                      │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                            │
│  ✓ Preview (3 questions parsed)                           │
│  ┌──────────────────────────────────────────────────────┐ │
│  │  1. What is E = mc²?                      [Edit] [×] │ │
│  │     A) Energy  B) Mass  C) Speed  D) Light           │ │
│  ├──────────────────────────────────────────────────────┤ │
│  │  2. Solve: ½ + ⅓                          [Edit] [×] │ │
│  │     A) ⅚  B) ⅔  C) ⅘  D) 1                           │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                            │
│  [Empty Questions]              [Cancel]  [Inject Quiz]   │
└────────────────────────────────────────────────────────────┘
```

### Color Palette (Khan Academy Inspired)
```css
--bg-primary: #1a1a1a;        /* Main background */
--bg-secondary: #2d2d2d;      /* Card/option background */
--bg-hover: #3a3a3a;          /* Hover state */
--text-primary: #ffffff;      /* Main text */
--text-secondary: #b0b0b0;    /* Secondary text */
--accent-blue: #63b3ed;       /* Progress bar, buttons */
--accent-green: #48bb78;      /* Correct answers */
--accent-red: #f56565;        /* Wrong answers */
--border: #404040;            /* Subtle borders */
```

---

## 🔗 Integration with Existing v8

### Toolbar Button
```vue
<button @click="openQuizGenerator" class="toolbar-btn">
  <svg><!-- AI icon --></svg>
  <span>AI Quiz</span>
</button>
```

### Store Actions (presentationStore.js)
```js
// Add quiz with multiple questions
addQuizV2(quizData) {
  const quizElement = {
    id: 'quiz-' + Date.now(),
    type: 'quiz-v2',
    title: quizData.title,
    questions: quizData.questions,
    settings: quizData.settings,
    userAnswers: {},
    currentQuestionIndex: 0,
    showResults: false,
    // ... positioning
  }
  this.addElement(quizElement)
}
```

---

## 📝 Success Criteria

- [ ] Teacher can create 10-question quiz in **under 2 minutes** using AI
- [ ] Students can navigate quiz with **keyboard only**
- [ ] Math formulas render correctly in **all browsers**
- [ ] Quiz works **offline** (localStorage persistence)
- [ ] Results export as **readable PDF**
- [ ] Question bank stores **100+ questions** without lag
- [ ] Mobile users can complete quiz **without zooming**

---

## 🎯 Next Steps

1. **Review this plan** with team/user
2. **Create QuizGeneratorDialog.vue** (Phase 1, Task 1)
3. **Test JSON parsing** with real ChatGPT output
4. **Iterate on UX** based on user feedback

---

**Last Updated:** 2026-05-11  
**Status:** Planning Phase  
**Owner:** Development Team
