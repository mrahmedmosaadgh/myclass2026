# 🚀 BM2 Quick Start Guide

**Last Updated:** 2026-03-11  
**Status:** Migrations Complete - Ready to Run

---

## ⚡ QUICK SETUP (5 Minutes)

### Step 1: Firebase Setup
```bash
# 1. Go to https://console.firebase.google.com
# 2. Create new project: "myclass-bm2"
# 3. Enable Realtime Database
# 4. Copy config values
```

### Step 2: Add Firebase Credentials
```bash
# Copy example file
cp .env.bm2.example .env

# Edit .env with your Firebase credentials
nano .env  # or use your preferred editor
```

### Step 3: Run Migrations
```bash
# Run all 7 bm2 migrations
php artisan migrate
```

### Step 4: Seed Badges
```bash
# Populate initial badges
php artisan db:seed --class=Bm2BadgesSeeder
```

### Step 5: Build Frontend Assets
```bash
npm run dev
# OR for production
npm run build
```

✅ **Done!** Your database is ready.

---

## 📦 WHAT'S BEEN CREATED

### Database Tables (7)
```
✅ bm2_assessments          - Assessment sessions
✅ bm2_assessment_questions - Question responses  
✅ bm2_questions_bank       - Question repository
✅ bm2_learning_paths       - Personalized paths
✅ bm2_badges               - Badge definitions
✅ bm2_student_badges       - Badge earnings
✅ bm2_student_avatars      - Avatar configs
```

### Initial Data
```
✅ 10 gamification badges seeded
   - First Steps, Math Wizard
   - Dedicated Learner, Century Club
   - Addition Ace, Subtraction Star, Number Sense Ninja
   - Speed Demon
   - On Fire!, Unstoppable
```

### Firebase Integration
```
✅ Realtime Database configured
✅ Real-time sync composable ready
✅ Leaderboard structure defined
✅ Instant feedback system ready
```

---

## 🔧 COMMON COMMANDS

### Migration Commands
```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset all bm2 migrations
php artisan migrate:rollback --step=7

# Check migration status
php artisan migrate:status
```

### Seeder Commands
```bash
# Seed badges only
php artisan db:seed --class=Bm2BadgesSeeder

# Seed everything (if you add more seeders later)
php artisan db:seed
```

### Development Commands
```bash
# Start Laravel server
php artisan serve

# Start Vite dev server
npm run dev

# Build for production
npm run build

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## 📊 DATABASE INFO

### Table Relationships
```
users (students)
├── bm2_assessments
│   └── bm2_assessment_questions
│       └── bm2_questions_bank
├── bm2_learning_paths
├── bm2_student_badges ←→ bm2_badges
└── bm2_student_avatars
```

### Sample Query - Get Student Progress
```sql
SELECT 
    a.overall_score,
    a.grade_level_equivalent,
    a.performance_level,
    COUNT(aq.id) as questions_answered,
    AVG(CASE WHEN aq.is_correct THEN 1 ELSE 0 END) * 100 as accuracy
FROM bm2_assessments a
LEFT JOIN bm2_assessment_questions aq ON a.id = aq.assessment_id
WHERE a.student_id = 1
GROUP BY a.id
ORDER BY a.created_at DESC;
```

---

## 🎯 NEXT DEVELOPMENT STEPS

### Ready to Build (In Order):

1. **Backend Models** (30 min)
   ```bash
   php artisan make:model Bm2Assessment
   php artisan make:model Bm2AssessmentQuestion
   php artisan make:model Bm2QuestionBank
   php artisan make:model Bm2LearningPath
   ```

2. **Controllers** (1 hour)
   ```bash
   php artisan make:controller Bm2AssessmentController --api
   php artisan make:controller Bm2QuestionController --api
   php artisan make:controller Bm2StudentController --api
   ```

3. **Routes** (15 min)
   - Add to `routes/api_v2.php`
   - Add to `routes/web.php`

4. **Frontend Components** (4 hours)
   - Build assessment player
   - Implement Firebase sync
   - Add celebration animations

---

## 📁 FILE LOCATIONS

### Migrations
```
database/migrations/
├── 2026_03_11_000001_create_bm2_assessments_table.php
├── 2026_03_11_000002_create_bm2_assessment_questions_table.php
├── 2026_03_11_000003_create_bm2_questions_bank_table.php
├── 2026_03_11_000004_create_bm2_learning_paths_table.php
├── 2026_03_11_000005_create_bm2_badges_table.php
├── 2026_03_11_000006_create_bm2_student_badges_table.php
└── 2026_03_11_000007_create_bm2_student_avatars_table.php
```

### Seeders
```
database/seeders/
└── Bm2BadgesSeeder.php
```

### Firebase Config
```
resources/js/firebase/
├── bm2-config.js
└── (future files)
```

### Composables
```
resources/js/composables/
└── useBm2FirebaseSync.js
```

### Documentation
```
docs/bm2/
├── BM2_DATABASE_MIGRATIONS.md
├── BM2_PROGRESS_REPORT_2026-03-11.md
└── BM2_QUICK_START.md (this file)
```

---

## ⚠️ TROUBLESHOOTING

### Migration Fails
```bash
# Error: "Table already exists"
# Solution: Table exists from previous run
DROP TABLE bm2_assessments;
DROP TABLE IF EXISTS bm2_assessments;
# Then retry migration
```

### Firebase Connection Error
```bash
# Check .env has valid Firebase credentials
# Verify Firebase project exists
# Check Realtime Database rules allow read/write
```

### Badge Seeder Fails
```bash
# Make sure bm2_badges table exists first
php artisan migrate
# Then run seeder
php artisan db:seed --class=Bm2BadgesSeeder
```

---

## 🎨 FIREBASE SECURITY RULES (Template)

Paste this into Firebase Console → Realtime Database → Rules:

```json
{
  "rules": {
    "bm2_live_assessments": {
      "$sessionId": {
        ".read": "auth != null",
        ".write": "auth != null && auth.uid === $sessionId"
      }
    },
    "bm2_leaderboards": {
      ".read": "auth != null",
      "$classId": {
        ".write": "auth != null"
      }
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

---

## 📞 NEED HELP?

### Reference Documents
- **Full Schema:** `docs/bm2/BM2_DATABASE_MIGRATIONS.md`
- **Progress Report:** `docs/bm2/BM2_PROGRESS_REPORT_2026-03-11.md`
- **Task List:** `docs/history/2026-03-11_12-04_bm2_basic_math_platform_tasks.md`

### Key Files to Review
- Migration files in `database/migrations/`
- Firebase config in `resources/js/firebase/bm2-config.js`
- Badge seeder in `database/seeders/Bm2BadgesSeeder.php`

---

## ✅ VERIFICATION CHECKLIST

After setup, verify:

- [ ] All 7 tables created successfully
- [ ] 10 badges seeded in `bm2_badges` table
- [ ] Firebase connection working (check browser console)
- [ ] No migration errors
- [ ] `.env` file has Firebase credentials
- [ ] Frontend assets compiled without errors

### Quick Verification Query
```sql
-- Should return 10 badges
SELECT COUNT(*) FROM bm2_badges;

-- Should return table list
SHOW TABLES LIKE 'bm2_%';
```

Expected output:
```
bm2_assessments
bm2_assessment_questions
bm2_questions_bank
bm2_learning_paths
bm2_badges
bm2_student_badges
bm2_student_avatars
```

---

**🎉 You're all set! Ready to build the assessment engine.**

**Next:** Backend API Development (Models + Controllers + Services)
