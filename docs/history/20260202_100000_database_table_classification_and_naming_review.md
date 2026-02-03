# 20260202 | Database Table Classification & Naming Review

## 📊 Executive Summary

This document analyzes and classifies all database tables related to **Questions**, **Quizzes**, and **Exams** in the system. The analysis includes:
- Table classification by system/module
- Current naming conventions assessment
- Naming confusion issues identified
- Suggested improvements for clarity

---

## 🗂️ 1. SYSTEM OVERVIEW

The database uses **3 distinct systems** for managing assessments:

| System | Purpose | Prefix | Status |
|--------|---------|--------|--------|
| **Core Question Bank** | General, reusable questions | `question_*` | Active |
| **General Quiz System** | Interactive quizzes/tests | `quiz_*` | Active |
| **Live Exam Module** | Managed exam execution | `qu_*` | Active |
| **Qudrat Exam System** | Specialized standardized exams | `qdrat_*` | Active |

---

## 📋 2. TABLE CLASSIFICATION BY SYSTEM

### **A. CORE QUESTION BANK SYSTEM** ✅
*Foundational repository for all general questions*

#### Core Tables:

| Table Name | Purpose | Status | Notes |
|------------|---------|--------|-------|
| `question_types` | Defines question formats (MCQ, True/False, Essay, etc.) | ✅ Active | Metadata table with behavioral flags |
| `questions` | Main question repository with curriculum alignment | ✅ Active | Links to grades, subjects, includes Bloom taxonomy |
| `question_options` | Answer choices for questions | ✅ Active | Links to questions, includes distractor analysis |
| `question_banks` | **LEGACY TABLE - DUPLICATE/OVERLAPPING** | ⚠️ Active | Redundant with `questions` table; creates confusion |

#### Key Relationships:
```
question_types (1) ──→ (M) questions
questions (1) ──→ (M) question_options
```

#### Issues Identified:
- **❌ Naming Confusion**: `question_banks` vs `questions`
  - `questions` = Normalized Q&A repository
  - `question_banks` = Denormalized, legacy structure with redundant fields
  - Both serve similar purposes but with different schemas
- **❌ Field Duplication**: Both tables store question text, type, difficulty, explanation, etc.
- **❌ Unclear Purpose**: "Bank" terminology suggests a collection, but it's structured like individual questions

---

### **B. GENERAL QUIZ SYSTEM** 📝
*Interactive quizzes built from the question bank*

#### Core Tables:

| Table Name | Purpose | Status | Notes |
|------------|---------|--------|-------|
| `quizzes` | Quiz definition (name, settings, metadata) | ✅ Active | School-scoped, has time limits, shuffling options |
| `quiz_question` | **PIVOT TABLE** - Links questions to quizzes | ✅ Active | Many-to-many relationship; includes order_index |
| `quiz_attempts` | Individual student attempts on a quiz | ✅ Active | Tracks start time, completion, score |
| `quiz_attempt_answers` | Specific answers to specific questions | ✅ Active | Individual response tracking |
| `quiz_sessions` | **LIVE/REAL-TIME QUIZ SESSION** | ✅ Active | Teacher-initiated, live polling-style quizzes |
| `quiz_session_participants` | Students in a live session | ✅ Active | Tracks participation and real-time score |

#### Key Relationships:
```
quizzes (1) ──→ (M) quiz_question ──→ (M) questions
                       ↓
quizzes (1) ──→ (M) quiz_attempts ──→ (M) quiz_attempt_answers
                       ↓
                  quiz_sessions (1) ──→ (M) quiz_session_participants
```

#### Issues Identified:
- **⚠️ Naming Clarity**: Table names are clear, but relationship between `quiz_attempts` and `quiz_sessions` could be clearer
- **❌ Potential Overlap**: 
  - `quiz_attempts` can reference both `quiz_id` directly OR via `quiz_session_id`
  - This allows ambiguous states (orphaned attempts not tied to a session)
- **Suggestion**: Clarify when to use:
  - `quiz_attempts` alone = Self-paced quiz taking
  - `quiz_sessions` + `quiz_attempt_answers` = Live, teacher-controlled polling

---

### **C. LIVE EXAM MODULE (QU_* PREFIX)** 🎯
*Managed, structured exam delivery system*

#### Core Tables:

| Table Name | Purpose | Status | Notes |
|------------|---------|--------|-------|
| `qu_exams` | Exam definition (title, type, duration, passing score) | ✅ Active | Tracks multiple attempts, mark calculation method |
| `qu_questions` | Questions specific to the exam module | ✅ Active | Subject-scoped, includes Bloom levels |
| `qu_exam_questions` | **PIVOT TABLE** - Links questions to exams | ✅ Active | Many-to-many with ordering |

#### Expected (but NOT found in migrations):
| Table | Status | Clarification Needed |
|-------|--------|---------------------|
| `qu_attempts` | ❓ Referenced in user request | Likely tracks individual exam attempts |
| `qu_answers` | ❓ Referenced in user request | Likely tracks individual question responses in exams |

#### Key Relationships:
```
qu_exams (1) ──→ (M) qu_exam_questions ──→ (M) qu_questions
```

#### Issues Identified:
- **❌ CRITICAL**: Missing `qu_attempts` and `qu_answers` tables in migrations
  - These are referenced in your initial request but not implemented
  - Need to clarify: Do exams share `quiz_attempts`/`quiz_attempt_answers` or have separate tables?
- **⚠️ Naming Redundancy**: `qu_` prefix creates parallel structure to `question_*`
  - `qu_questions` vs `questions` – which one to use?
  - Creates unclear data governance
- **Suggestion**: Consolidate to single question repository OR clearly document when each is used

---

### **D. QUDRAT EXAM SYSTEM (QDRAT_* PREFIX)** 🏆
*Specialized standardized exam content (appears to be Arabic/localized)*

#### Core Tables:

| Table Name | Purpose | Status | Notes |
|------------|---------|--------|-------|
| `qdrat_question_types` | Question type metadata (e.g., "اختيار من متعدد") | ✅ Active | Bilingual support (Arabic display names) |
| `qdrat_questions` | Qudrat exam questions | ✅ Active | Includes Arabic content field |
| `qdrat_question_difficulties` | Difficulty levels for Qudrat questions | ✅ Active | Custom ordering |

#### Expected (but NOT found in migrations):
| Table | Status | Clarification Needed |
|-------|--------|---------------------|
| `qdrat_skill_levels` | ❓ Referenced in user request | For skill-based assessment tracking |
| `qdrat_skills` | ❓ Referenced in user request | Skill definitions/taxonomies |

#### Issues Identified:
- **❌ CRITICAL**: Missing `qdrat_skills` and `qdrat_skill_levels` tables
  - Referenced in your initial classification but no migrations found
- **❌ Complete Separation**: Qudrat system is isolated with no links to other question systems
  - Data cannot be shared across systems
  - Maintenance burden with duplicate structures

---

## 🚨 3. MAJOR NAMING & DESIGN ISSUES

### Issue #1: Multiple Question Table Systems ⚠️ **CRITICAL**

**Problem:**
```
❌ Three separate question repositories:
  1. questions (general, normalized)
  2. question_banks (legacy, denormalized)
  3. qu_questions (exam-specific)
  4. qdrat_questions (standardized exams)
```

**Confusion Created:**
- Developers unclear which table to use
- Data duplication across systems
- Impossible to reuse questions across modules
- Migration complexity

**Recommended Solution:**
```sql
CONSOLIDATE to a single "questions" table with system flags:

CREATE TABLE questions (
    id,
    question_text,
    question_type_id,
    system ENUM('general', 'exam', 'qudrat', 'standardized'),
    -- ... other fields
);

-- Keep specialized tables only for system-specific data:
CREATE TABLE qudrat_questions (
    id,
    question_id,  -- FK to questions
    arabic_content,
    skill_id,
    -- ... qudrat-specific fields only
);
```

---

### Issue #2: Quiz Attempts Ambiguity ⚠️ **HIGH PRIORITY**

**Problem:**
```
quiz_attempts table has two optional FKs:
- quiz_id (direct quiz)
- quiz_session_id (live session)

Allows invalid states:
- NULL, NULL = Orphaned attempt
- Both set = Ambiguous (which is primary?)
```

**Recommended Solution:**
```sql
-- Separate concerns:

-- Self-paced attempts
CREATE TABLE quiz_self_attempts (
    id,
    user_id,
    quiz_id,  -- NOT nullable
    started_at,
    completed_at,
    score
);

-- Live session attempts
CREATE TABLE quiz_live_attempts (
    id,
    user_id,
    quiz_session_id,  -- NOT nullable
    joined_at,
    finished_at,
    score
);
```

---

### Issue #3: Missing Exam Attempts & Answers Tables 🔴 **MISSING TABLES**

**Problem:**
```
qu_exams module references:
- qu_attempts (in your request)
- qu_answers (in your request)

But these migrations do NOT exist in the codebase!
```

**Action Needed:**
- Clarify: Are exams using `quiz_attempts` or separate `qu_attempts`?
- Create migration if separate table needed

---

### Issue #4: Qudrat Skills & Skill Levels Missing 🔴 **MISSING TABLES**

**Problem:**
```
qdrat_questions references skills via assessment framework,
but qdrat_skills and qdrat_skill_levels tables don't exist.
```

**Action Needed:**
- Create `qdrat_skills` table
- Create `qdrat_skill_level_mappings` for question-to-skill links

---

## 📝 4. SUGGESTED NAMING IMPROVEMENTS

### Current → Recommended

| Current Table | Issue | Recommended Name | Reason |
|---------------|-------|------------------|--------|
| `question_banks` | Redundant with `questions` | **DELETE** or rename to `lesson_questions` | Clarifies legacy/lesson-scoped use |
| `quiz_question` | Unclear pivot purpose | `quiz_questions` (with underscore) | Consistency; makes pivot purpose clear |
| `qu_questions` | Conflicts with `questions` | `exam_questions` or `qu_exam_questions` | Removes ambiguity |
| `qu_exams` | Generic prefix unclear | `managed_exams` or `qu_structured_exams` | Clarifies this is a different exam system |
| `qu_attempts` | Missing table | `exam_attempts` or `qu_exam_attempts` | Matches `qu_exams` naming |
| `qu_answers` | Missing table | `exam_question_answers` or `qu_exam_answers` | Specificity |
| `qdrat_skills` | Missing table | `qdrat_skills` (REQUIRED) | Skill taxonomy for assessment |
| `qdrat_skill_levels` | Missing table | `qdrat_skill_levels` (REQUIRED) | Proficiency levels per skill |

---

## ✅ 5. PROPOSED ARCHITECTURE

### Clean Database Structure:

```
┌─────────────────────────────────────────────────────┐
│ QUESTION REPOSITORY (Single Source of Truth)       │
├─────────────────────────────────────────────────────┤
│ questions                                           │
│ question_types                                      │
│ question_options                                    │
│ question_tags / question_metadata                   │
└─────────────────────────────────────────────────────┘
         ↓                    ↓                    ↓
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│ QUIZ SYSTEM      │  │ EXAM SYSTEM      │  │ QUDRAT SYSTEM    │
├──────────────────┤  ├──────────────────┤  ├──────────────────┤
│ quizzes          │  │ qu_exams         │  │ qdrat_questions  │
│ quiz_questions   │  │ qu_exam_questions│  │ qdrat_skills     │
│ quiz_attempts    │  │ qu_attempts      │  │ qdrat_skill_lvls │
│ quiz_sessions    │  │ qu_answers       │  │ qdrat_q_to_skill │
│ quiz_attempt_ans │  │                  │  │                  │
└──────────────────┘  └──────────────────┘  └──────────────────┘
```

---

## 🎯 6. ACTION ITEMS

### 🔴 CRITICAL (Must Do)
- [ ] **Audit**: Verify if `qu_attempts` and `qu_answers` exist in actual database
- [ ] **Create Missing**: Implement migrations for:
  - `qu_attempts` (if not exists)
  - `qu_answers` (if not exists)
  - `qdrat_skills` (definitely missing)
  - `qdrat_skill_levels` (definitely missing)
- [ ] **Consolidate**: Decide on single question repository strategy
  - Option A: Migrate all to `questions` table with system type
  - Option B: Keep separate but document clear usage boundaries

### ⚠️ HIGH PRIORITY (Should Do)
- [ ] **Clarify**: Document when to use `quiz_attempts` vs `quiz_sessions`
- [ ] **Refactor**: Split `quiz_attempts` into `quiz_self_attempts` and `quiz_live_attempts`
- [ ] **Deprecate**: Plan removal of `question_banks` table or clarify its exclusive use case
- [ ] **Add Constraints**: Make relationship FKs NOT NULL where appropriate

### 📋 MEDIUM PRIORITY (Nice to Have)
- [ ] **Rename**: Update table/column names for consistency
- [ ] **Document**: Create data dictionary for each table
- [ ] **Add Metadata**: Include system/purpose flags in tables
- [ ] **Create Views**: Materialized views for cross-system question access

---

## 📚 7. CLARIFICATION QUESTIONS FOR STAKEHOLDER

Before implementing recommendations, please clarify:

1. **Question Systems**: 
   - Should all 4 question systems consolidate to one `questions` table?
   - Or keep separate with clear documentation of when to use each?

2. **Quiz Attempts**:
   - Is `quiz_attempts` currently used for BOTH self-paced and live sessions?
   - Should these be separated into distinct tables?

3. **Exam Attempts**:
   - Does the `qu_exams` module currently use `quiz_attempts` or is there a separate system?
   - Are `qu_attempts` and `qu_answers` tables implemented in the actual database?

4. **Qudrat Skills**:
   - What is the skill taxonomy? (e.g., reading, writing, listening, speaking, etc.?)
   - How many skill levels? (e.g., 1-5 proficiency scale?)

5. **Backward Compatibility**:
   - Are there active dependencies on `question_banks` that prevent deprecation?
   - Can we create a migration timeline for consolidation?

---

## 📊 APPENDIX A: Current Table Statistics

```
Core Question Bank:
  - question_types: Metadata (small)
  - questions: Main repository
  - question_options: Answer choices
  - question_banks: Legacy/duplicate

General Quiz System:
  - quizzes: Quiz definitions
  - quiz_question: Pivot table
  - quiz_attempts: Attempt tracking
  - quiz_attempt_answers: Individual responses
  - quiz_sessions: Live polling sessions
  - quiz_session_participants: Participation tracking

Exam System (qu_):
  - qu_exams: Exam definitions
  - qu_questions: Exam questions
  - qu_exam_questions: Pivot table
  - qu_attempts: ❓ STATUS UNKNOWN
  - qu_answers: ❓ STATUS UNKNOWN

Qudrat System (qdrat_):
  - qdrat_question_types: Question type metadata
  - qdrat_questions: Standardized exam questions
  - qdrat_question_difficulties: Difficulty metadata
  - qdrat_skills: ❓ MISSING
  - qdrat_skill_levels: ❓ MISSING
```

---

## 📅 Document Metadata

- **Created**: 2026-02-02
- **Review Status**: Pending Stakeholder Input
- **Next Review**: After clarification questions answered
- **Related Files**: None yet
- **Author**: System Analysis

---

## 🔗 References

- [BUILD_SYSTEM.md](../../BUILD_SYSTEM.md)
- Database Migrations: `database/migrations/`
- Config: `config/database.php`

---
