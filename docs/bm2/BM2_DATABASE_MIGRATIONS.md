# BM2 Database Migrations - Basic Math Platform

**Created:** 2026-03-11  
**Prefix:** bm2_ (all tables)  
**Status:** ✅ Complete - Ready for Migration

---

## 📊 Database Schema Overview

### Core Tables (7 total)

1. **bm2_assessments** - Main assessment sessions
2. **bm2_assessment_questions** - Individual question responses
3. **bm2_questions_bank** - Question repository
4. **bm2_learning_paths** - Personalized learning recommendations
5. **bm2_badges** - Gamification badge definitions
6. **bm2_student_badges** - Student badge earnings (pivot)
7. **bm2_student_avatars** - Avatar customization

---

## 🗄️ Table Descriptions

### 1. bm2_assessments
**Purpose:** Tracks overall assessment sessions and results

**Key Fields:**
- `student_id` - Foreign key to users table
- `overall_score` - Final percentage score (0-100)
- `grade_level_equivalent` - Performance level (K, 1, 2)
- `performance_level` - emerging/developing/proficient/advanced
- `skill_breakdown` - JSON: {"addition": 85, "subtraction": 72}
- `recommended_modules` - JSON: Learning path recommendations
- `firebase_session_id` - Real-time sync reference

**Indexes:** student_id, type, created_at

---

### 2. bm2_assessment_questions
**Purpose:** Individual question responses during assessments

**Key Fields:**
- `assessment_id` - Foreign key to bm2_assessments
- `question_bank_id` - Reference to question bank (nullable)
- `question_type` - addition/subtraction/number_sense/fractions/word_problem
- `difficulty` - easy/medium/hard
- `is_correct` - Boolean response accuracy
- `time_taken_seconds` - Response time metric
- `hints_used` - Number of hints consumed
- `question_order` - Sequence in adaptive test
- `was_adaptive` - Was this question adaptively selected?

**Indexes:** assessment_id, question_type, difficulty, is_correct

---

### 3. bm2_questions_bank
**Purpose:** Repository of all available questions

**Key Fields:**
- `question_text` - The actual question
- `grade_level` - K/1/2
- `topic` - addition/subtraction/number_sense/fractions/patterns/measurement
- `question_format` - multiple_choice/true_false/fill_in_blank/short_answer/matching/drag_drop
- `options` - JSON array of answer choices
- `correct_answer` - Correct response
- `explanation` - Post-answer explanation
- `visual_properties` - JSON for interactive elements
- `times_used` - Usage tracking
- `success_rate` - Historical accuracy percentage
- `discrimination_index` - How well it differentiates students

**Indexes:** grade_level, topic, difficulty, question_format, is_active

---

### 4. bm2_learning_paths
**Purpose:** Personalized learning recommendations

**Key Fields:**
- `student_id` - Foreign key to users
- `assessment_id` - Generating assessment reference
- `recommended_modules` - JSON: Module/lesson recommendations with priority
- `total_lessons` / `completed_lessons` - Progress tracking
- `completion_percentage` - Progress metric
- `status` - not_started/in_progress/completed
- `target_completion_date` - Goal date

**Indexes:** student_id, status, created_at

---

### 5. bm2_badges
**Purpose:** Gamification badge definitions

**Key Fields:**
- `name` - Badge title
- `description` - What it represents
- `category` - achievement/milestone/skill_mastery/speed/consistency
- `earning_criteria` - JSON: Conditions to unlock
- `points_value` - Points awarded
- `rarity` - common/uncommon/rare/epic/legendary

**Indexes:** category, rarity

**Initial Badges (10):**
1. First Steps (Complete first assessment)
2. Math Wizard (100% score)
3. Dedicated Learner (5 assessments)
4. Century Club (100 assessments)
5. Addition Ace (90%+ addition accuracy)
6. Subtraction Star (90%+ subtraction accuracy)
7. Number Sense Ninja (90%+ number sense accuracy)
8. Speed Demon (Fast completion with accuracy)
9. On Fire! (7-day streak)
10. Unstoppable (30-day streak)

---

### 6. bm2_student_badges
**Purpose:** Track badges earned by students (pivot table)

**Key Fields:**
- `student_id` - Foreign key to users
- `badge_id` - Foreign key to bm2_badges
- `assessment_id` - Context (which assessment earned it)
- `earned_for` - Description of achievement
- `earned_at` - Timestamp
- `points_awarded` - Points from this badge
- `is_displayed` - Show on profile?

**Unique Constraint:** (student_id, badge_id) - Can only earn each badge once

**Indexes:** student_id, earned_at

---

### 7. bm2_student_avatars
**Purpose:** Avatar customization system

**Key Fields:**
- `student_id` - Foreign key to users
- `avatar_config` - JSON: Customization options
  ```json
  {
    "base": "wizard",
    "hair_color": "brown",
    "shirt_color": "blue",
    "accessory": "glasses",
    "background": "stars"
  }
  ```
- `is_unlocked` - Availability status
- `unlocked_by` - How they unlocked it
- `is_active` - Currently selected avatar

**Unique Constraint:** (student_id, is_active) - Only one active avatar per student

**Indexes:** student_id

---

## 🚀 How to Run Migrations

### Step 1: Backup Database (if needed)
```bash
php artisan db:backup # If you have backup command
# OR manually backup via phpMyAdmin/MySQL client
```

### Step 2: Run Migrations
```bash
php artisan migrate
```

This will run all migration files in order:
1. 2026_03_11_000001_create_bm2_assessments_table.php
2. 2026_03_11_000002_create_bm2_assessment_questions_table.php
3. 2026_03_11_000003_create_bm2_questions_bank_table.php
4. 2026_03_11_000004_create_bm2_learning_paths_table.php
5. 2026_03_11_000005_create_bm2_badges_table.php
6. 2026_03_11_000006_create_bm2_student_badges_table.php
7. 2026_03_11_000007_create_bm2_student_avatars_table.php

### Step 3: Seed Initial Data
```bash
php artisan db:seed --class=Bm2BadgesSeeder
```

This populates the bm2_badges table with 10 initial badges.

---

## 📝 Entity Relationship Diagram

```
┌─────────────────────┐
│      users          │
│   (students)        │
└──────────┬──────────┘
           │
           ├──────────────────────────┐
           │                          │
           ▼                          ▼
┌─────────────────────┐     ┌─────────────────────┐
│  bm2_assessments    │     │   bm2_badges        │
└──────────┬──────────┘     └──────────┬──────────┘
           │                           │
           │                           │
           ▼                           ▼
┌─────────────────────┐     ┌─────────────────────┐
│ bm2_assessment_     │     │ bm2_student_badges  │◄──────┐
│    questions        │     │   (pivot table)     │       │
└─────────────────────┘     └──────────┬──────────┘       │
                                       │                  │
                                       └──────────────────┘
                                       (student_id)

           ┌─────────────────────┐
           │ bm2_questions_bank  │
           └─────────────────────┘
                    ▲
                    │
           (question_bank_id)

           ┌─────────────────────┐
           │ bm2_learning_paths  │
           └─────────────────────┘
                    ▲
                    │
           (student_id)

           ┌─────────────────────┐
           │ bm2_student_avatars │
           └─────────────────────┘
                    ▲
                    │
           (student_id)
```

---

## 🔧 Rollback Commands

### Rollback Last Migration
```bash
php artisan migrate:rollback
```

### Rollback All BM2 Migrations
```bash
php artisan migrate:rollback --step=7
# OR
php artisan migrate:reset --path=database/migrations/2026_03_11
```

### Drop All BM2 Tables (Destructive!)
```sql
DROP TABLE IF EXISTS bm2_student_avatars;
DROP TABLE IF EXISTS bm2_student_badges;
DROP TABLE IF EXISTS bm2_badges;
DROP TABLE IF EXISTS bm2_learning_paths;
DROP TABLE IF EXISTS bm2_assessment_questions;
DROP TABLE IF EXISTS bm2_assessments;
```

---

## 📊 Sample Data Queries

### Get Student Assessment History
```sql
SELECT 
    a.id,
    a.overall_score,
    a.grade_level_equivalent,
    a.performance_level,
    a.skill_breakdown,
    a.created_at as assessment_date
FROM bm2_assessments a
WHERE a.student_id = 1
ORDER BY a.created_at DESC;
```

### Get Badge Collection for Student
```sql
SELECT 
    b.name,
    b.description,
    b.rarity,
    sb.earned_at,
    sb.points_awarded
FROM bm2_student_badges sb
JOIN bm2_badges b ON sb.badge_id = b.id
WHERE sb.student_id = 1
ORDER BY sb.earned_at DESC;
```

### Get Question Analysis
```sql
SELECT 
    qb.topic,
    qb.difficulty,
    COUNT(aq.id) as times_answered,
    AVG(CASE WHEN aq.is_correct THEN 1 ELSE 0 END) * 100 as success_rate
FROM bm2_assessment_questions aq
JOIN bm2_questions_bank qb ON aq.question_bank_id = qb.id
GROUP BY qb.topic, qb.difficulty
ORDER BY qb.topic, qb.difficulty;
```

---

## ⚠️ Important Notes

1. **All tables use `bm2_` prefix** to avoid conflicts with existing tables
2. **Foreign keys cascade on delete** - deleting a student removes their data
3. **JSON fields** require MySQL 5.7+ or PostgreSQL
4. **Indexes** are optimized for common query patterns
5. **No data reuse** from existing `bm_` tables - completely independent

---

## 🎯 Next Steps

After running migrations:
1. ✅ Create Models (Task 1.3)
2. ✅ Build Controllers (Task 1.3)
3. ✅ Develop Frontend Components (Task 1.4)
4. ✅ Implement Firebase Sync (Task 1.1 - Complete)

---

**Status:** ✅ Migrations Complete - Ready for Backend Development
