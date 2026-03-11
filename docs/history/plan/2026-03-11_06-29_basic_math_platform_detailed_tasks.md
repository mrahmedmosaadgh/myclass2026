# 📋 Basic Math Platform - Detailed Task List
**Reference Plan:** `2026-03-11_06-20_basic_math_platform_master_launch_plan.md`  
**Created:** 2026-03-11  
**Status Legend:** `[ ]` Todo · `[/]` In Progress · `[x]` Done

---

## 🔥 PHASE 1 — Assessment Engine (Days 1-2)

### 1.1 Firebase Setup
- [ ] Create Firebase project in Firebase Console
- [ ] Enable Realtime Database and set region
- [ ] Add Firebase SDK credentials to `.env` (`VITE_FIREBASE_*`)
- [ ] Verify `useBMFirebase.js` connects without errors
- [ ] Set Firebase Security Rules (students read own data, teachers read class data)

### 1.2 Adaptive Question Algorithm
- [ ] Define difficulty thresholds: `easy (1)`, `medium (2)`, `hard (3)`
- [ ] Review `BMAdaptiveEngine.php` → adjust logic for accurate CAT progression
- [ ] Ensure wrong answer on easy → stays easy; right answer on hard → stays hard
- [ ] Cap difficulty so students don't get stuck in impossible questions

### 1.3 Question Generator Expansion
- [ ] **Addition**: Generate questions up to 20 (not just single digit)
- [ ] **Subtraction**: Generate proper take-away problems (no negative results)
- [ ] **Multiplication**: Simple times tables (1×1 to 5×5 for Grade 2)
- [ ] **Division**: Whole-number results only (e.g., 10÷2=5)
- [ ] **Fractions**: Fraction addition with same denominator (e.g., 2/8 + 3/8)
- [ ] **Number Sense**: Counting, recognizing which number is bigger (future module)

### 1.4 Backend Response Fix (already done ✅)
- [x] Fix `correct_answer` missing from `bm_assessment_responses` insert
- [x] Store `current_question_html` in session for feedback overlay
- [x] Return `feedback` prop with `isCorrect`, `correctAnswer`, `questionHtml`, `userAnswer`

---

## 🎨 PHASE 2 — Student Experience Polish (Days 3-5)

### 2.1 Assessment Question Page (`AssessmentQuestion.vue`)
- [x] Domain color-coded badge (blue, teal, purple, orange, pink)
- [x] Dynamic timer chip (green → yellow → red)
- [x] Answer input box with glow effect on typing
- [x] Clear input after submit
- [x] Keyboard-reactive `NumberPad` component (numbers only)
- [x] Flow protection: confirm dialog before leaving active assessment
- [x] Feedback overlay: green ✅ Correct / red ❌ Wrong
- [x] Feedback includes original question + wrong answer (crossed out)
- [x] Feedback auto-dismiss: 2s correct, 4.5s wrong
- [x] Countdown progress bar on feedback overlay
- [ ] **TODO**: Add confetti animation on correct answer (`canvas-confetti`)
- [ ] **TODO**: Add mascot cheer animation on correct streak (3+ correct)
- [ ] **TODO**: Add sound effects toggle (ding for correct, buzz for wrong)

### 2.2 Assessment Results Page (`AssessmentResults.vue`)
- [x] Score circle: green if ≥80%, red if <80%
- [x] Auto-redirect to retry if score < 80%
- [x] Domain Mastery radar chart (`BMScoreRadar.vue`)
- [ ] **TODO**: Add confetti burst on passing score (≥80%)
- [ ] **TODO**: Show "Grade Level Equivalent" badge (e.g., "Mid-Grade 1")
- [ ] **TODO**: Add share badge button (generate image to share)
- [ ] **TODO**: Download PDF report button

### 2.3 Assessment History Page (`AssessmentHistory.vue`)
- [ ] List all past assessments for the student with date, score, domain breakdown
- [ ] Show improvement trend (score went up/down between retakes)
- [ ] Highlight best score and current score

### 2.4 Assessment Welcome/Launch Page (`AssessmentWelcome.vue`)
- [ ] Show student name and greeting
- [ ] Show last score if retaking (did they pass last time?)
- [ ] Explain what domains will be tested
- [ ] Big "START" button with mascot graphic

---

## 📊 PHASE 3 — Scoring & Analytics (Days 3-4)

### 3.1 Scoring Algorithm (`BMScoringService.php`)
- [x] Fix 100% accuracy = 100% score bug
- [x] Fluency is a minor modifier (10% weight) only when accuracy < 100%
- [ ] **TODO**: Add "Grade Level Equivalent" calculation based on score + difficulty
- [ ] **TODO**: Track hints used (future phase when we add hints)

### 3.2 Teacher Dashboard (`Teacher/Dashboard.vue`)
- [ ] Fix `$q` Quasar import error (was using named export incorrectly)
- [ ] Show class average score
- [ ] Show number of students who passed vs. failed
- [ ] List students sorted by score (lowest first for gaps)
- [ ] Domain heatmap: which domains the class struggles with most

### 3.3 Teacher Class Scores (`Teacher/ClassScores.vue`)  
- [ ] Sortable table: Student name | Score | Level | Completed At
- [ ] Filter by domain weakness
- [ ] Export to CSV button

### 3.4 Teacher Gap Analysis (`Teacher/GapAnalysis.vue`)
- [ ] Flag students who scored < 60% in a domain
- [ ] Group by domain: "5 students struggling with Fractions"
- [ ] Quick action: Assign targeted practice module

---

## 👨‍👩‍👦 PHASE 4 — Parent Portal (Days 5-6)

### 4.1 Parent Dashboard (`Parent/Dashboard.vue`)
- [ ] Show child's latest score with colored level badge
- [ ] Radar chart for domain mastery
- [ ] Timeline of past scores (improvement graph)
- [ ] "Your child is performing at: Mid-Grade 1" message

### 4.2 Parent Recommendations (`Parent/Recommendations.vue`)
- [ ] Generate 3-5 specific home activities based on weak domains
- [ ] Example: "Practice counting money (coins) for 10 minutes daily"
- [ ] Link to free external resources (Khan Academy, etc.)

---

## 🌐 PHASE 5 — Navigation & UX (Completed ✅)

- [x] Create `BMLayout.vue` with persistent floating menu button
- [x] Global navigation drawer with Student / Teacher / Parent sections
- [x] Apply `BMLayout` to all 14 BM page components
- [x] Exit Basic Math link in navigation drawer
- [x] Active route highlighting in drawer links

---

## 🚀 PHASE 6 — Beta Launch Prep (Days 13-14)

### 6.1 Pre-Launch Checklist
- [ ] Full assessment run-through: start → 10 questions → results → retry flow
- [ ] Test on mobile (iPhone, iPad)
- [ ] Test all 3 portals (student, teacher, parent)
- [ ] Run `python .agent/scripts/checklist.py .` to audit codebase
- [ ] Fix any critical security or lint issues flagged

### 6.2 Content & Marketing
- [ ] Record 3-minute demo video (Loom)
- [ ] Write short landing page copy
- [ ] Create 3 social media posts (Instagram Reels concept from master plan)
- [ ] Set up email waitlist (Mailchimp or similar)

### 6.3 Deployment
- [ ] Set production `.env` values (Firebase, DB credentials)
- [ ] Run `php artisan migrate` on production DB
- [ ] Run `npm run build` and verify no Vite errors
- [ ] Test production URL end-to-end

---

## 🐛 KNOWN BUGS / OUTSTANDING ISSUES

| Bug | File | Priority |
|-----|------|----------|
| `$q` named export error in Dashboard | `Teacher/Dashboard.vue` | 🔴 High |
| Feedback not showing on Q1 (first question has no `feedback` prop) | `AssessmentQuestion.vue` | 🟡 Medium |
| Navigation drawer links use `route()` — verify all named routes exist | `BMLayout.vue` | 🟡 Medium |
| Score level thresholds don't match scoring levels in master plan | `BMScoringService.php` | 🟡 Medium |
| No error handling if assessment session expires mid-test | `BMAssessmentController.php` | 🟠 Low |

---

## 💡 IMPROVEMENT IDEAS (Backlog)

- [ ] **Voice Questions**: Text-to-speech for K-1 students who can't read
- [ ] **Avatar System**: Unlock custom avatars by earning badges
- [ ] **Streak Counter**: "5 in a row! You're on fire! 🔥"
- [ ] **Hint System**: 3 hints per assessment; using hints lowers fluency score slightly
- [ ] **Word Problems**: Visual context cards for math word problems
- [ ] **Norm Data**: Compare score to national K-2 average benchmarks
- [ ] **Print Report**: Generate PDF with skill breakdown for parent-teacher conferences
- [ ] **Firebase Live Leaderboard**: Real-time class leaderboard during assessments
