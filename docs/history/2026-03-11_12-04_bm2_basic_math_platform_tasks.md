# 2026-03-11 12:04 | Basic Math Platform (bm2) - Master Task List

**Project:** Basic Math Assessment & Learning Platform  
**Prefix:** bm2 (all routes, components, and database tables)  
**Target:** K-2 Early Elementary (Ages 5-8)  
**Status:** ⏳ Awaiting Confirmation to Begin Development

---

## 📋 OVERVIEW

This task list covers the complete 14-day launch plan for the Basic Math platform with all paths prefixed with `bm2` to ensure clean separation from existing code.

### Key Decisions:
- ✅ **Frontend Path:** `resources/js/Pages/Courses/bm2/`
- ✅ **Route Prefix:** `/bm2/` in all API and web routes
- ✅ **Database:** New migrations with `bm2_` prefix (no reuse of existing tables)
- ✅ **Firebase:** Realtime Database for live scoring and leaderboards
- ✅ **Assessment First:** Build benchmark test before curriculum content

---

## 🎯 PHASE 1: BENCHMARK ASSESSMENT ENGINE (Days 1-7)

### Task 1.1: Project Setup & Firebase Configuration
**Priority:** 🔴 CRITICAL | **Estimate:** 3 hours

- [ ] **1.1.1** Create Firebase project
  - Initialize Realtime Database
  - Configure security rules
  - Add Firebase config to `.env`
  
- [ ] **1.1.2** Set up Vue.js entry point
  - Create `resources/js/bm2-assessment.js` (separate bundle)
  - Configure Vite multi-entry build
  - Implement code splitting for assessment engine
  
- [ ] **1.1.3** Create base layout components
  - `Pages/Courses/bm2/Layouts/Bm2AssessmentLayout.vue`
  - `Pages/Courses/bm2/Layouts/Bm2StudentLayout.vue`
  - `Pages/Courses/bm2/Layouts/Bm2TeacherLayout.vue`

**Deliverable:** Firebase connected, build system configured

---

### Task 1.2: Database Schema & Migrations
**Priority:** 🔴 CRITICAL | **Estimate:** 4 hours

- [ ] **1.2.1** Create migration: `create_bm2_assessments_table.php`
  ```php
  Schema::create('bm2_assessments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('student_id')->constrained('users');
      $table->decimal('overall_score', 5, 2);
      $table->string('grade_level_equivalent', 10);
      $table->json('skill_breakdown');
      $table->timestamp('started_at');
      $table->timestamp('completed_at')->nullable();
      $table->string('firebase_session_id', 100);
      $table->timestamps();
  });
  ```

- [ ] **1.2.2** Create migration: `create_bm2_assessment_questions_table.php`
  ```php
  Schema::create('bm2_assessment_questions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('assessment_id')->constrained('bm2_assessments');
      $table->text('question_text');
      $table->enum('question_type', ['addition', 'subtraction', 'number_sense']);
      $table->enum('difficulty', ['easy', 'medium', 'hard']);
      $table->boolean('is_correct');
      $table->integer('time_taken_seconds');
      $table->integer('hints_used')->default(0);
      $table->timestamps();
  });
  ```

- [ ] **1.2.3** Create migration: `create_bm2_learning_paths_table.php`
  ```php
  Schema::create('bm2_learning_paths', function (Blueprint $table) {
      $table->id();
      $table->foreignId('student_id')->constrained('users');
      $table->json('recommended_modules');
      $table->timestamps();
  });
  ```

- [ ] **1.2.4** Create migration: `create_bm2_questions_bank_table.php`
  ```php
  Schema::create('bm2_questions_bank', function (Blueprint $table) {
      $table->id();
      $table->text('question_text');
      $table->enum('subject', ['math']);
      $table->enum('grade_level', ['K', '1', '2']);
      $table->enum('topic', ['addition', 'subtraction', 'number_sense', 'fractions']);
      $table->enum('difficulty', ['easy', 'medium', 'hard']);
      $table->json('options')->nullable();
      $table->text('correct_answer');
      $table->integer('estimated_time_seconds')->default(30);
      $table->timestamps();
  });
  ```

**Deliverable:** Complete database schema ready for assessments

---

### Task 1.3: Backend API Development
**Priority:** 🔴 CRITICAL | **Estimate:** 6 hours

- [ ] **1.3.1** Create Controller: `Bm2AssessmentController.php`
  - Method: `startAssessment()` - Initialize new assessment session
  - Method: `submitAnswer()` - Record student response
  - Method: `getNextQuestion()` - Adaptive algorithm logic
  - Method: `completeAssessment()` - Calculate final score
  - Method: `getResults()` - Return detailed analytics

- [ ] **1.3.2** Create Controller: `Bm2QuestionController.php`
  - Method: `index()` - List questions with filters
  - Method: `store()` - Add new question to bank
  - Method: ` getRandomQuestion()` - Fetch based on adaptive logic

- [ ] **1.3.3** Create Request Validators
  - `Bm2StartAssessmentRequest.php`
  - `Bm2SubmitAnswerRequest.php`

- [ ] **1.3.4** Create Model Classes
  - `Bm2Assessment.php`
  - `Bm2AssessmentQuestion.php`
  - `Bm2LearningPath.php`
  - `Bm2QuestionBank.php`

- [ ] **1.3.5** Create Service Layer
  - `Bm2AdaptiveScoringService.php` - Handles difficulty adjustment
  - `Bm2ReportGenerator.php` - Creates PDF reports

**Deliverable:** Fully functional REST API for assessment operations

---

### Task 1.4: Frontend Assessment Components
**Priority:** 🔴 CRITICAL | **Estimate:** 8 hours

- [ ] **1.4.1** Create Assessment Engine Components
  - `Pages/Courses/bm2/Components/Bm2QuestionPlayer.vue`
  - `Pages/Courses/bm2/Components/Bm2AdaptiveEngine.vue`
  -Pages/Courses/bm2/Components/Bm2 ProgressBar.vue`
  - `Pages/Courses/bm2/Components/Bm2CelebrationAnimation.vue`
  - `Pages/Courses/bm2/Components/Bm2Timer.vue`

- [ ] **1.4.2** Create Composables
  - `composables/useBm2Assessment.js` - Assessment state management
  - `composables/useBm2Scoring.js` - Score calculations
  - `composables/useBm2FirebaseSync.js` - Real-time Firebase sync

- [ ] **1.4.3** Create Question Type Components
  - `Pages/Courses/bm2/Questions/Bm2AdditionQuestion.vue`
  - `Pages/Courses/bm2/Questions/Bm2SubtractionQuestion.vue`
  - `Pages/Courses/bm2/Questions/Bm2NumberSenseQuestion.vue`

- [ ] **1.4.4** Implement Adaptive Algorithm
  - Easy → Medium → Hard progression logic
  - Response time tracking
  - Hint usage tracking

**Deliverable:** Interactive assessment interface with real-time adaptation

---

### Task 1.5: Gamification & Engagement
**Priority:** 🟡 HIGH | **Estimate:** 5 hours

- [ ] **1.5.1** Create Character Mascot System
  - `Pages/Courses/bm2/Components/Bm2Mascot.vue` - Animated guide
  - Encouragement messages library
  - Reaction animations (happy, thinking, celebrating)

- [ ] **1.5.2** Implement Badge System
  - Migration: `create_bm2_badges_table.php`
  - `Pages/Courses/bm2/Components/Bm2BadgeCollection.vue`
  - Unlock conditions (completion, accuracy, speed)

- [ ] **1.5.3** Create Avatar Customization
  - Migration: `create_bm2_student_avatars_table.php`
  - `Pages/Courses/bm2/Components/Bm2AvatarSelector.vue`
  - Unlock with badges/points

- [ ] **1.5.4** Add Celebration Effects
  - Integrate `canvas-confetti` package
  - Sound effects (optional, toggleable)
  - Micro-celebrations every 5 questions

**Deliverable:** Engaging, game-like student experience

---

### Task 1.6: Teacher Dashboard & Analytics
**Priority:** 🟡 HIGH | **Estimate:** 6 hours

- [ ] **1.6.1** Create Dashboard Pages
  - `Pages/Courses/bm2/Teacher/Bm2Dashboard.vue`
  - `Pages/Courses/bm2/Teacher/Bm2ClassList.vue`
  - `Pages/Courses/bm2/Teacher/Bm2StudentDetail.vue`

- [ ] **1.6.2** Implement Leaderboards
  - Firebase integration for real-time updates
  - `Pages/Courses/bm2/Components/Bm2Leaderboard.vue`
  - Filter by class, week, month

- [ ] **1.6.3** Create Analytics Components
  - `Pages/Courses/bm2/Analytics/Bm2SkillBreakdown.vue`
  - `Pages/Courses/bm2/Analytics/Bm2ProgressChart.vue`
  - `Pages/Courses/bm2/Analytics/Bm2StrugglingStudents.vue`

- [ ] **1.6.4** PDF Report Generator
  - Server-side PDF generation (DomPDF or Snappy)
  - Template: Skill breakdown + recommendations
  - Download from teacher dashboard

**Deliverable:** Comprehensive teacher analytics dashboard

---

### Task 1.7: Parent Portal
**Priority:** 🟢 MEDIUM | **Estimate:** 4 hours

- [ ] **1.7.1** Create Parent Pages
  - `Pages/Courses/bm2/Parent/Bm2ChildProgress.vue`
  - `Pages/Courses/bm2/Parent/Bm2AssessmentResults.vue`

- [ ] **1.7.2** Email Notifications
  - Assessment completion notification
  - Weekly progress report (scheduled job)

**Deliverable:** Parent visibility into child's progress

---

### Task 1.8: Routing & Integration
**Priority:** 🟡 HIGH | **Estimate:** 3 hours

- [ ] **1.8.1** Add Web Routes (`routes/web.php`)
  ```php
  Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
      Route::prefix('bm2')->name('bm2.')->group(function () {
          // Student Routes
          Route::get('/assessment/start', [Bm2AssessmentController::class, 'startPage'])
                ->name('assessment.start');
          Route::get('/dashboard', [Bm2StudentController::class, 'dashboard'])
                ->name('student.dashboard');
          
          // Teacher Routes
          Route::get('/teacher/dashboard', [Bm2TeacherController::class, 'dashboard'])
                ->name('teacher.dashboard');
          Route::get('/teacher/class/{classId}', [Bm2TeacherController::class, 'classDetail'])
                ->name('teacher.class.detail');
          
          // Parent Routes
          Route::get('/parent/child/{childId}', [Bm2ParentController::class, 'progress'])
                ->name('parent.progress');
      });
  });
  ```

- [ ] **1.8.2** Add API Routes (`routes/api_v2.php`)
  ```php
  Route::middleware(['auth:sanctum'])->group(function () {
      Route::prefix('bm2')->name('bm2.')->group(function () {
          Route::post('/assessments/start', [Bm2AssessmentController::class, 'start']);
          Route::post('/assessments/{id}/submit-answer', [Bm2AssessmentController::class, 'submitAnswer']);
          Route::get('/assessments/{id}/results', [Bm2AssessmentController::class, 'getResults']);
          Route::get('/questions/random', [Bm2QuestionController::class, 'getRandomQuestion']);
      });
  });
  ```

- [ ] **1.8.3** Menu Integration
  - Add to Laravel menu configuration
  - Separate menu items for Student/Teacher/Parent roles

**Deliverable:** All routes accessible and integrated into navigation

---

### Task 1.9: Testing & Quality Assurance
**Priority:** 🔴 CRITICAL | **Estimate:** 4 hours

- [ ] **1.9.1** Unit Tests
  - `Bm2AdaptiveScoringServiceTest.php`
  - `Bm2AssessmentTest.php`

- [ ] **1.9.2** Feature Tests
  - Assessment flow end-to-end
  - API endpoint testing

- [ ] **1.9.3** Load Testing
  - Simulate 50 concurrent students taking assessments
  - Monitor Firebase performance

- [ ] **1.9.4** User Acceptance Testing
  - Internal team testing
  - Bug fixing sprint

**Deliverable:** Stable, tested assessment engine

---

## 📚 PHASE 2: CURRICULUM CONTENT (Days 8-10)

### Task 2.1: Question Bank Creation
**Priority:** 🟡 HIGH | **Estimate:** 8 hours

- [ ] **2.1.1** Create Initial Question Sets
  - Addition: 50 questions (easy: 20, medium: 20, hard: 10)
  - Subtraction: 50 questions (easy: 20, medium: 20, hard: 10)
  - Number Sense: 30 questions (counting, place value)
  - Total: 130 base questions

- [ ] **2.1.2** Question Templates for Endless Generator
  - Define variable templates (e.g., `[NUM1] + [NUM2] = ?`)
  - Set constraints per grade level
  - Implement randomization logic

**Deliverable:** Robust question bank for adaptive testing

---

### Task 2.2: Lesson Content (Micro-Lessons)
**Priority:** 🟢 MEDIUM | **Estimate:** 12 hours

- [ ] **2.2.1** Module 1: Number Sense (5 lessons)
  - Script for 5 video lessons (3 min each)
  - Interactive practice activities
  - Quiz questions per lesson

- [ ] **2.2.2** Module 2: Addition (5 lessons)
  - Video scripts + visual demonstrations
  - Manipulative-based practice (virtual counters)
  - Word problem scenarios

- [ ] **2.2.3** Module 3: Subtraction (5 lessons)
  - Video scripts + visual demonstrations
  - Real-world context problems
  - Relationship to addition

**Deliverable:** 15 complete micro-lessons (5-7 minutes each)

---

## 🚀 PHASE 3: BETA LAUNCH PREPARATION (Days 11-14)

### Task 3.1: Beta Tester Recruitment
**Priority:** 🟡 HIGH | **Estimate:** 3 hours

- [ ] **3.1.1** Create Landing Page
  - `Pages/Courses/bm2/Public/Bm2Landing.vue`
  - Waitlist signup form
  - Demo video integration

- [ ] **3.1.2** Outreach Campaign
  - Email templates for parent recruitment
  - Social media graphics
  - Facebook group posts

**Deliverable:** 5-10 committed beta testing families

---

### Task 3.2: Beta Testing Execution
**Priority:** 🔴 CRITICAL | **Estimate:** 6 hours

- [ ] **3.2.1** Onboarding Sessions
  - Schedule 30-min Zoom calls
  - Screen-share observation protocol
  - Feedback collection form

- [ ] **3.2.2** Iteration Sprint
  - Daily bug fixes based on feedback
  - UX improvements
  - Performance optimizations

**Deliverable:** Refined product with proven engagement

---

### Task 3.3: Soft Launch
**Priority:** 🟡 HIGH | **Estimate:** 4 hours

- [ ] **3.3.1** Marketing Assets
  - Demo video (3-minute Loom)
  - Testimonial graphics
  - Social proof compilation

- [ ] **3.3.2** Sales Funnel
  - Pricing page setup
  - Payment integration (Stripe/Laravel Cashier)
  - Discount codes for early adopters

- [ ] **3.3.3** Launch Campaign
  - Email blast to waitlist
  - Facebook/Instagram ads ($50 budget)
  - Local parent group outreach

**Deliverable:** First 10 paying customers

---

## 📊 SUCCESS METRICS & CHECKPOINTS

### Day 7 Checkpoint (MVP Complete)
- [ ] Assessment engine functional
- [ ] 130+ questions in bank
- [ ] Teacher dashboard shows live data
- [ ] Firebase sync working flawlessly

### Day 10 Checkpoint (Content Ready)
- [ ] 15 micro-lessons complete
- [ ] Endless question generator working
- [ ] Student engagement features polished

### Day 14 Checkpoint (Launch Ready)
- [ ] 5+ successful beta tests completed
- [ ] Critical bugs resolved
- [ ] 10+ families on waitlist
- [ ] Payment system tested

---

## 🛠️ TECHNICAL SPECIFICATIONS

### File Naming Convention
```
Frontend: Bm2*.vue (components), Bm2*.js (composables)
Backend: Bm2*Controller.php, Bm2*Service.php
Database: bm2_*_table.php (migrations)
Routes: bm2.* (route names)
```

### Component Hierarchy
```
Pages/Courses/bm2/
├── Assessment/
│   ├── Bm2AssessmentApp.vue
│   ├── Bm2QuestionPlayer.vue
│   └── Bm2Results.vue
├── Components/
│   ├── Bm2ProgressBar.vue
│   ├── Bm2CelebrationAnimation.vue
│   └── Bm2Mascot.vue
├── Teacher/
│   ├── Bm2Dashboard.vue
│   └── Bm2Analytics.vue
└── Parent/
    └── Bm2ChildProgress.vue
```

### Firebase Structure
```javascript
{
  "bm2_live_assessments": {
    "{sessionId}": {
      "studentId": "123",
      "currentQuestion": 15,
      "score": 80,
      "isActive": true
    }
  },
  "bm2_leaderboards": {
    "{classId}": {
      "{studentId}": {
        "name": "Ahmed M.",
        "score": 95,
        "badges": 12
      }
    }
  }
}
```

---

## ⚠️ BLOCKERS & DEPENDENCIES

### Required Before Starting:
1. ✅ User confirmation to proceed with development
2. ⏳ Firebase project creation credentials
3. ⏳ Database backup (if working on production server)

### External Dependencies:
- Firebase Realtime Database setup
- canvas-confetti npm package
- DomPDF for report generation

---

## 📝 NOTES

- **DO NOT** use any existing `Pages/Courses/bm/` files
- **DO NOT** reuse existing migrations - all new `bm2_` tables
- **All routes** must use `bm2` prefix
- **Focus on assessment first**, curriculum content second
- **Keep lessons under 7 minutes** (research-backed attention span)

---

## ✅ APPROVAL REQUIRED

**Status:** ⏳ AWAITING CONFIRMATION

Ready to begin development upon user approval. Once confirmed:
1. Start with Task 1.1 (Firebase setup)
2. Create first migration
3. Build assessment engine core
4. Daily progress updates with demos

**Next Action:** User review and approval of this task list

---

**Created:** 2026-03-11 12:04  
**Last Updated:** 2026-03-11 12:04  
**Plan Version:** 1.0  
**Approval Status:** Pending
