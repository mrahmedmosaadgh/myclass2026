# 🧪 BM2 Testing Checklist - Complete Guide

**Date:** 2026-03-11  
**Purpose:** Test entire BM2 platform end-to-end  
**Status:** Ready to Execute

---

## 📋 PRE-TESTING CHECKLIST

### ✅ Step 1: Database Migration (15 minutes)

```bash
# Navigate to project root
cd /Users/ahmedmosaad/Herd/myclass2026-main

# Run all BM2 migrations
php artisan migrate

# Expected output:
# Migrating: 2026_03_11_000001_create_bm2_assessments_table.php
# Migrated:  2026_03_11_000001_create_bm2_assessments_table.php (XX ms)
# ... (repeat for all 7 migrations)
```

**Verify Tables Created:**
```bash
# Using MySQL CLI
mysql -u root -p

# Then run:
USE your_database_name;
SHOW TABLES LIKE 'bm2_%';
```

**Expected Result (7 tables):**
```
+------------------------------------------+
| Tables_in_yourdb (bm2_%)                 |
+------------------------------------------+
| bm2_assessments                          |
| bm2_assessment_questions                 |
| bm2_badges                               |
| bm2_learning_paths                       |
| bm2_questions_bank                       |
| bm2_student_avatars                      |
| bm2_student_badges                       |
+------------------------------------------+
```

---

### ✅ Step 2: Seed Initial Data (5 minutes)

```bash
# Seed the 10 badges
php artisan db:seed --class=Bm2BadgesSeeder

# Verify badges were seeded
mysql -u root -p
USE your_database_name;
SELECT id, name, category, rarity FROM bm2_badges ORDER BY display_order;
```

**Expected Result (10 badges):**
```
+----+------------------+---------------+----------+
| id | name             | category      | rarity   |
+----+------------------+---------------+----------+
|  1 | First Steps      | achievement   | common   |
|  2 | Math Wizard      | achievement   | epic     |
|  3 | Dedicated Learner| milestone     | uncommon |
|  4 | Century Club     | milestone     | legendary|
|  5 | Addition Ace     | skill_mastery | rare     |
|  6 | Subtraction Star | skill_mastery | rare     |
|  7 | Number Sense Ninja| skill_mastery| rare     |
|  8 | Speed Demon      | speed         | epic     |
|  9 | On Fire!         | consistency   | rare     |
| 10 | Unstoppable      | consistency   | legendary|
+----+------------------+---------------+----------+
```

---

### ✅ Step 3: Firebase Configuration (15 minutes)

**Get Firebase Credentials:**

1. Go to https://console.firebase.google.com
2. Create new project OR select existing
3. Enable Realtime Database
4. Click Project Settings ⚙️ → General
5. Scroll to "Your apps" → Web app
6. Copy the config values

**Add to .env file:**
```bash
cp .env.bm2.example .env

# Edit .env with your favorite editor
nano .env
# OR
code .env
# OR
vim .env
```

**Paste your Firebase credentials:**
```env
VITE_FIREBASE_API_KEY=AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXX
VITE_FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
VITE_FIREBASE_DATABASE_URL=https://your-project.firebaseio.com
VITE_FIREBASE_PROJECT_ID=your-project-id
VITE_FIREBASE_STORAGE_BUCKET=your-project.appspot.com
VITE_FIREBASE_MESSAGING_SENDER_ID=123456789
VITE_FIREBASE_APP_ID=1:123456789:web:abc123def456
```

**Set Firebase Security Rules:**

In Firebase Console → Realtime Database → Rules:
```json
{
  "rules": {
    "bm2_live_assessments": {
      "$sessionId": {
        ".read": "auth != null",
        ".write": "auth != null"
      }
    },
    "bm2_leaderboards": {
      ".read": "auth != null",
      ".write": "auth != null"
    },
    "bm2_instant_feedback": {
      "$studentId": {
        ".read": "auth != null && auth.uid === $studentId",
        ".write": "auth != null"
      }
    }
  }
}
```

Publish the rules.

---

### ✅ Step 4: Build Frontend Assets (5 minutes)

```bash
# Install dependencies if not done
npm install

# Start Vite dev server (for development)
npm run dev

# OR build for production
npm run build

# Expected output:
# VITE v4.x.x  ready in XXX ms
# ➜  Local:   http://localhost:5173/
# ➜  Network: use --host to expose
```

Keep this running in a separate terminal!

---

## 🧪 FUNCTIONAL TESTING

### ✅ Test 1: Backend API - Start Assessment (10 minutes)

**Using Postman or curl:**

```bash
# First, get an authentication token
# Login as a student user
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"student@example.com","password":"password"}'

# Copy the token from response
# Then test BM2 API
curl -X POST http://localhost/api/v2/bm2/assessment/start \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"type":"placement"}'
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "assessment": {
      "id": 1,
      "student_id": 1,
      "title": "Basic Math Placement Test",
      "type": "placement",
      "is_active": true,
      "started_at": "2026-03-11T12:00:00.000000Z"
    },
    "question": {
      "id": 42,
      "question_text": "What is 5 + 3?",
      "difficulty": "medium",
      "topic": "addition",
      "options": ["7", "8", "9", "10"],
      ...
    }
  },
  "message": "Assessment started successfully"
}
```

**✅ PASS Criteria:**
- [ ] Returns success: true
- [ ] Assessment object created in database
- [ ] First question returned
- [ ] No errors in Laravel logs

**Check Database:**
```sql
SELECT * FROM bm2_assessments ORDER BY id DESC LIMIT 1;
```

---

### ✅ Test 2: Backend API - Submit Answer (10 minutes)

```bash
curl -X POST http://localhost/api/v2/bm2/assessment/1/submit \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "question_id": 42,
    "student_answer": "8",
    "time_taken_seconds": 15,
    "hints_used": 0
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "current_question": {
      "id": 1,
      "is_correct": true,
      "points_earned": 10,
      "student_answer": "8"
    },
    "explanation": "Correct! 5 + 3 = 8",
    "next_question": {
      "id": 57,
      "question_text": "What is 12 - 7?",
      "difficulty": "hard",
      ...
    }
  }
}
```

**✅ PASS Criteria:**
- [ ] Answer recorded correctly
- [ ] Points calculated (10 for correct, 0 for wrong)
- [ ] Next question provided (adaptive)
- [ ] Question usage stats updated

**Check Database:**
```sql
SELECT * FROM bm2_assessment_questions 
WHERE assessment_id = 1 
ORDER BY created_at DESC LIMIT 1;

-- Check if question usage incremented
SELECT times_used FROM bm2_questions_bank WHERE id = 42;
```

---

### ✅ Test 3: Frontend - Start Page (10 minutes)

**Open browser:**
```
http://localhost:5173/bm2/assessment/start
(OR whatever port Vite shows)
```

**Visual Checks:**
- [ ] Beautiful gradient background displays
- [ ] "🎯 Basic Math Placement Test" header visible
- [ ] Instructions card shows clearly
- [ ] Three assessment type cards appear
- [ ] Can click to select assessment type
- [ ] Grade level dropdown works
- [ ] "Start Assessment!" button clickable
- [ ] Pro tips section visible at bottom

**Functional Tests:**
1. Select "Placement Test"
2. Choose grade level "1"
3. Click "Start Assessment!"
4. Should navigate to `/bm2/assessment/{id}`

**Browser Console:**
- [ ] No JavaScript errors
- [ ] No 404 errors for assets
- [ ] API call visible in Network tab
- [ ] Response logged correctly

---

### ✅ Test 4: Frontend - Take Assessment (15 minutes)

**Should load automatically after starting assessment**

**Visual Checks:**
- [ ] Progress bar at top (green/blue gradient)
- [ ] Timer counting up (⏱️ 0:00, 0:01, 0:02...)
- [ ] Score displays (📊 Score: 0)
- [ ] Question number shows (Question 1)
- [ ] Difficulty badge visible (EASY/MEDIUM/HARD)
- [ ] Question text readable
- [ ] Answer options formatted correctly:
  - Multiple choice: A/B/C/D buttons
  - True/False: Two large buttons
  - Fill-in-blank: Text input field
- [ ] Hint button visible (💡 Hint)
- [ ] Submit Answer button enabled when answer selected

**Functional Tests:**

**Test Question 1:**
1. Read question
2. Select an answer
3. Click "Submit Answer"
4. See feedback alert ("🎉 Correct! +10 points!")
5. Next question loads automatically

**Test Question 2:**
1. Notice difficulty may have changed (adaptive)
2. Answer again
3. Check score updates

**Test Hint System:**
1. Click "💡 Hint" button
2. Hint should appear in yellow box
3. Answer question
4. Verify points reduced (hint penalty)

**Timer Test:**
- [ ] Timer updates every second
- [ ] Format is MM:SS
- [ ] Continues running throughout assessment

**Firebase Sync Test:**
1. Open Firebase Console → Realtime Database
2. Navigate to `bm2_live_assessments/{sessionId}`
3. Answer a question
4. Watch Firebase update within 1-2 seconds!
5. Verify data matches:
   ```json
   {
     "currentQuestion": 2,
     "score": 20,
     "isActive": true,
     "lastUpdate": 1234567890
   }
   ```

**Browser Console:**
- [ ] No errors during answer submission
- [ ] API calls successful (status 200)
- [ ] Firebase sync successful
- [ ] No CORS errors

---

### ✅ Test 5: Complete Assessment (10 minutes)

**Answer ~5 questions, then:**

**Option A: Natural Completion**
- Keep answering until no more questions
- Should auto-complete and redirect

**Option B: Manual API Call**
```bash
curl -X POST http://localhost/api/v2/bm2/assessment/1/complete \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "assessment": {
      "id": 1,
      "overall_score": 80.00,
      "performance_level": "proficient",
      "grade_level_equivalent": "1",
      "skill_breakdown": {
        "addition": {"total": 5, "correct": 4, "percentage": 80},
        "subtraction": {"total": 3, "correct": 2, "percentage": 67}
      },
      "completed_at": "2026-03-11T12:15:00.000000Z",
      "total_time_seconds": 180
    },
    "learning_path": {
      "id": 1,
      "title": "Personalized Math Learning Path",
      "recommended_modules": [...],
      "completion_percentage": 0
    }
  }
}
```

**Check Database:**
```sql
-- Verify assessment completed
SELECT 
  overall_score, 
  performance_level, 
  completed_at,
  total_time_seconds
FROM bm2_assessments 
WHERE id = 1;

-- Verify learning path created
SELECT * FROM bm2_learning_paths 
WHERE student_id = 1 
ORDER BY id DESC LIMIT 1;

-- View skill breakdown
SELECT skill_breakdown FROM bm2_assessments WHERE id = 1;
```

**✅ PASS Criteria:**
- [ ] Assessment marked as completed (is_active = false)
- [ ] Overall score calculated correctly
- [ ] Performance level assigned
- [ ] Skill breakdown generated
- [ ] Learning path created with recommendations
- [ ] Redirects to results page (or shows completion message)

---

## 🔍 VERIFICATION CHECKLIST

### Database Verification
```sql
-- Count assessments
SELECT COUNT(*) FROM bm2_assessments;

-- Count questions answered
SELECT COUNT(*) FROM bm2_assessment_questions;

-- View all records with relationships
SELECT 
  a.id,
  a.student_id,
  a.overall_score,
  a.performance_level,
  COUNT(aq.id) as questions_answered,
  lp.id as learning_path_id
FROM bm2_assessments a
LEFT JOIN bm2_assessment_questions aq ON a.id = aq.assessment_id
LEFT JOIN bm2_learning_paths lp ON a.id = lp.assessment_id
GROUP BY a.id, lp.id;
```

### Firebase Verification
1. Open Firebase Console
2. Go to Realtime Database
3. Check these paths exist:
   - `bm2_live_assessments/{sessionId}` ✅
   - `bm2_instant_feedback/{studentId}` (should be empty or cleared)
   - `bm2_leaderboards/{classId}` (if implemented)

### Log Files Check
```bash
# Check Laravel logs for errors
tail -f storage/logs/laravel.log

# Look for errors while testing
grep -i error storage/logs/laravel.log
```

---

## 🐛 COMMON ISSUES & FIXES

### Issue 1: "Table 'bm2_assessments' doesn't exist"
**Fix:** Run migrations
```bash
php artisan migrate
```

### Issue 2: "Firebase API key not configured"
**Fix:** Check .env has Firebase credentials
```bash
cat .env | grep FIREBASE
```

### Issue 3: "401 Unauthorized" on API calls
**Fix:** Ensure auth token is valid and user is logged in

### Issue 4: "CORS Error" in browser console
**Fix:** Check cors.php config allows your frontend URL

### Issue 5: Questions not loading
**Fix:** Seed question bank first
```bash
# You'll need to create questions manually or via seeder
# See next section for quick question seeder
```

---

## ➕ BONUS: Quick Question Seeder

If you need test questions quickly:

```bash
php artisan tinker
```

Then paste:
```php
// Create 5 easy addition questions
for ($i = 1; $i <= 5; $i++) {
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    
    App\Models\Bm2QuestionBank::create([
        'question_text' => "What is {$num1} + {$num2}?",
        'subject' => 'math',
        'grade_level' => '1',
        'topic' => 'addition',
        'difficulty' => 'easy',
        'question_format' => 'multiple_choice',
        'options' => [($num1 + $num2), ($num1 + $num2 + 1), ($num1 + $num2 - 1), ($num1 + $num2 + 2)],
        'correct_answer' => (string)($num1 + $num2),
        'explanation' => "Add {$num1} and {$num2} to get " . ($num1 + $num2),
        'is_active' => true,
        'is_verified' => true,
        'created_by' => 1,
    ]);
}

echo "Created 5 test questions!";
```

---

## ✅ FINAL CHECKLIST

After completing all tests:

### Backend
- [ ] All 7 migrations ran successfully
- [ ] 10 badges seeded
- [ ] API endpoints respond correctly
- [ ] Database records created properly
- [ ] Adaptive algorithm selects appropriate questions
- [ ] Score calculation accurate
- [ ] Learning path generated

### Frontend
- [ ] Start page renders beautifully
- [ ] Take assessment page functional
- [ ] Timer counts up correctly
- [ ] Progress bar animates
- [ ] Questions display properly
- [ ] Answer selection works
- [ ] Submit button functions
- [ ] Feedback alerts show
- [ ] Next question loads
- [ ] Firebase sync working

### Integration
- [ ] Frontend ↔ Backend communication works
- [ ] API authentication successful
- [ ] No console errors
- [ ] No network errors
- [ ] Real-time Firebase updates visible
- [ ] Data persists correctly

---

## 📊 TEST RESULTS TEMPLATE

Copy and fill this out:

```
TEST EXECUTION SUMMARY
======================
Date: _______________
Tester: _____________

Database Setup:
✓ Migrations: PASS / FAIL
✓ Seeding: PASS / FAIL

Firebase Config:
✓ Credentials: PASS / FAIL
✓ Security Rules: PASS / FAIL

Backend API:
✓ Start Assessment: PASS / FAIL
✓ Submit Answer: PASS / FAIL
✓ Get Next Question: PASS / FAIL
✓ Complete Assessment: PASS / FAIL

Frontend:
✓ Start Page: PASS / FAIL
✓ Take Assessment: PASS / FAIL
✓ Visual Design: PASS / FAIL
✓ Interactivity: PASS / FAIL

Integration:
✓ API Calls: PASS / FAIL
✓ Firebase Sync: PASS / FAIL
✓ Data Persistence: PASS / FAIL

ISSUES FOUND:
1. ________________________________
2. ________________________________
3. ________________________________

OVERALL STATUS: PASS / FAIL
READY FOR NEXT PHASE: YES / NO
```

---

## 🚀 WHAT'S NEXT AFTER PASSING?

If all tests pass:

1. ✅ **Celebrate!** You have a working MVP!
2. 📝 Document any minor issues found
3. 🎨 Continue with remaining pages:
   - Results.vue
   - Dashboard.vue
   - Badges.vue
   - LearningPaths.vue
4. 🎮 Add gamification features
5. 👥 Build teacher dashboard
6. 👨‍👩‍👧‍👦 Build parent portal

---

**Ready to start testing? Let me know which step you're on and I'll help troubleshoot!** 🧪
