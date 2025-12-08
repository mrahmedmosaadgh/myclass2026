# Curriculum Database Schema Documentation

## Overview

The curriculum database schema consists of five main tables that work together to provide a comprehensive curriculum management system. The schema follows Laravel conventions and implements proper relationships, constraints, and indexes for optimal performance.

## Table Structure

### 1. curricula

**Purpose:** Main curriculum table storing curriculum metadata and status.

```sql
CREATE TABLE `curricula` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `grade_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `active` tinyint NOT NULL DEFAULT '0' COMMENT '0=inactive, 1=active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `curricula_school_id_active_index` (`school_id`,`active`),
  KEY `curricula_school_id_grade_id_subject_id_index` (`school_id`,`grade_id`,`subject_id`),
  UNIQUE KEY `curricula_school_id_grade_id_subject_id_name_unique` (`school_id`,`grade_id`,`subject_id`,`name`),
  CONSTRAINT `curricula_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curricula_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curricula_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
);
```

**Fields:**
- `id`: Primary key
- `name`: Curriculum name (required)
- `description`: Optional curriculum description
- `grade_id`: Foreign key to grades table
- `school_id`: Foreign key to schools table
- `subject_id`: Foreign key to subjects table
- `active`: Status flag (0=inactive, 1=active)
- `created_at`, `updated_at`: Laravel timestamps
- `deleted_at`: Soft delete timestamp

**Business Rules:**
- Only one curriculum can be active per school+subject+grade combination
- Curriculum names must be unique within school+subject+grade scope
- Soft deletes preserve historical data

---

### 2. curriculum_lessons

**Purpose:** Detailed lesson content within curricula.

```sql
CREATE TABLE `curriculum_lessons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `curriculum_id` bigint unsigned NOT NULL,
  `selected` tinyint NOT NULL DEFAULT '1' COMMENT '0=not selected, 1=selected',
  `topic_number` varchar(255) NOT NULL,
  `topic_title` varchar(255) NOT NULL,
  `lesson_number` varchar(255) NOT NULL,
  `lesson_title` varchar(255) NOT NULL,
  `page_number` int DEFAULT NULL,
  `description` text,
  `standard` varchar(255) DEFAULT NULL,
  `strand` varchar(255) DEFAULT NULL,
  `content` text,
  `skill` text,
  `activities` text,
  `assignment` text,
  `assessment` text,
  `notes_admin` text,
  `notes_teacher` text,
  `objective` text,
  `data` json DEFAULT NULL,
  `type` enum('main','revision','quiz','project','extra') NOT NULL DEFAULT 'main',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `curriculum_lessons_school_id_curriculum_id_index` (`school_id`,`curriculum_id`),
  KEY `curriculum_lessons_school_id_selected_index` (`school_id`,`selected`),
  KEY `curriculum_lessons_school_id_type_index` (`school_id`,`type`),
  UNIQUE KEY `curriculum_lessons_unique` (`curriculum_id`,`topic_number`,`lesson_number`),
  CONSTRAINT `curriculum_lessons_curriculum_id_foreign` FOREIGN KEY (`curriculum_id`) REFERENCES `curricula` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curriculum_lessons_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
);
```

**Fields:**
- `id`: Primary key
- `school_id`: Foreign key to schools table
- `curriculum_id`: Foreign key to curricula table
- `selected`: Selection status for lesson inclusion
- `topic_number`, `topic_title`: Topic organization
- `lesson_number`, `lesson_title`: Lesson identification
- `page_number`: Reference page number
- `description`: Lesson description
- `standard`, `strand`: Educational standards
- `content`, `skill`: Learning content and skills
- `activities`, `assignment`, `assessment`: Teaching materials
- `notes_admin`, `notes_teacher`: Administrative and teacher notes
- `objective`: Learning objectives
- `data`: Additional JSON data
- `type`: Lesson type (main, revision, quiz, project, extra)

**Business Rules:**
- Lessons must be unique within curriculum by topic+lesson number
- Selected lessons are included in active curriculum
- JSON data field allows flexible additional information

---

### 3. curriculum_lesson_plans

**Purpose:** Teacher-specific lesson plans based on curriculum lessons.

```sql
CREATE TABLE `curriculum_lesson_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `curriculum_lesson_id` bigint unsigned DEFAULT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `grade_id` bigint unsigned NOT NULL,
  `classroom_id` bigint unsigned NOT NULL,
  `teacher_id` bigint unsigned NOT NULL,
  `co_teacher_ids` json DEFAULT NULL COMMENT 'Array of teacher IDs for co-teachers',
  `title` varchar(255) NOT NULL,
  `page_number` int DEFAULT NULL,
  `cw` text COMMENT 'Class work',
  `hw` text COMMENT 'Home work',
  `objectives` text,
  `materials` json DEFAULT NULL COMMENT 'Teaching materials and resources',
  `plan` json DEFAULT NULL COMMENT 'Detailed lesson plan structure',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0=draft, 1=active, 2=completed',
  `planned_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `curriculum_lesson_plans_school_id_teacher_id_index` (`school_id`,`teacher_id`),
  KEY `curriculum_lesson_plans_school_id_classroom_id_index` (`school_id`,`classroom_id`),
  KEY `curriculum_lesson_plans_school_id_status_index` (`school_id`,`status`),
  KEY `curriculum_lesson_plans_planned_date_index` (`planned_date`),
  CONSTRAINT `curriculum_lesson_plans_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curriculum_lesson_plans_curriculum_lesson_id_foreign` FOREIGN KEY (`curriculum_lesson_id`) REFERENCES `curriculum_lessons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curriculum_lesson_plans_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curriculum_lesson_plans_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curriculum_lesson_plans_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curriculum_lesson_plans_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
);
```

**Fields:**
- `id`: Primary key
- `school_id`: Foreign key to schools table
- `curriculum_lesson_id`: Optional link to curriculum lesson
- `subject_id`, `grade_id`, `classroom_id`: Context identifiers
- `teacher_id`: Primary teacher
- `co_teacher_ids`: JSON array of co-teacher IDs
- `title`: Lesson plan title
- `page_number`: Reference page
- `cw`, `hw`: Class work and homework
- `objectives`: Learning objectives
- `materials`, `plan`: JSON structured teaching materials and plan
- `status`: Plan status (0=draft, 1=active, 2=completed)
- `planned_date`: Scheduled date

**Business Rules:**
- Lesson plans can be independent or linked to curriculum lessons
- Multiple teachers can collaborate via co_teacher_ids
- Status tracking enables workflow management

---

### 4. curriculum_maps

**Purpose:** Academic year mapping and weekly planning.

```sql
CREATE TABLE `curriculum_maps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `grade_id` bigint unsigned NOT NULL,
  `teacher_id` bigint unsigned NOT NULL,
  `curriculum_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `weekly_plan` json DEFAULT NULL COMMENT 'JSON structure: {week_number: {lessons: [], objectives: [], assessments: []}}',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0=draft, 1=active, 2=completed',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `curriculum_maps_school_id_academic_year_id_index` (`school_id`,`academic_year_id`),
  KEY `curriculum_maps_school_id_teacher_id_index` (`school_id`,`teacher_id`),
  KEY `curriculum_maps_school_id_status_index` (`school_id`,`status`),
  UNIQUE KEY `curriculum_maps_unique` (`school_id`,`academic_year_id`,`subject_id`,`grade_id`,`teacher_id`),
  CONSTRAINT `curriculum_maps_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curriculum_maps_curriculum_id_foreign` FOREIGN KEY (`curriculum_id`) REFERENCES `curricula` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curriculum_maps_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curriculum_maps_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curriculum_maps_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curriculum_maps_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
);
```

**Fields:**
- `id`: Primary key
- `school_id`: Foreign key to schools table
- `academic_year_id`: Foreign key to academic years
- `subject_id`, `grade_id`, `teacher_id`: Context identifiers
- `curriculum_id`: Optional link to curriculum
- `title`, `description`: Map metadata
- `weekly_plan`: JSON structured weekly planning
- `status`: Map status
- `start_date`, `end_date`: Planning period

**Business Rules:**
- One map per school+academic_year+subject+grade+teacher combination
- Weekly plans stored as structured JSON
- Maps can span specific date ranges

---

### 5. question_banks

**Purpose:** Assessment questions linked to curricula and lessons.

```sql
CREATE TABLE `question_banks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `curriculum_id` bigint unsigned DEFAULT NULL,
  `curriculum_lessons_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Question head/title',
  `body` text NOT NULL COMMENT 'Question details/content',
  `options` json DEFAULT NULL COMMENT 'Multiple-choice options: {A: "option1", B: "option2", ...}',
  `correct_answer` varchar(255) DEFAULT NULL COMMENT 'Correct answer key (A, B, C, etc.)',
  `resources` json DEFAULT NULL COMMENT 'Images, PDFs, attachments',
  `type` enum('mcq','true_false','fill_blank','essay','short_answer') NOT NULL DEFAULT 'mcq',
  `score` int NOT NULL DEFAULT '1',
  `difficulty` enum('easy','medium','hard') NOT NULL DEFAULT 'medium',
  `notes` text,
  `tags` varchar(255) DEFAULT NULL COMMENT 'Comma-separated tags',
  `status` enum('draft','active','archived') NOT NULL DEFAULT 'draft',
  `author` varchar(255) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `metadata` json DEFAULT NULL COMMENT 'Additional question metadata',
  `notes_admin` text,
  `notes_teacher` text,
  `explanation` json DEFAULT NULL COMMENT 'Answer explanation and reasoning',
  `question_data` json DEFAULT NULL COMMENT 'Additional structured question data',
  `created_by_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `question_banks_school_id_subject_id_index` (`school_id`,`subject_id`),
  KEY `question_banks_school_id_type_index` (`school_id`,`type`),
  KEY `question_banks_school_id_difficulty_index` (`school_id`,`difficulty`),
  KEY `question_banks_school_id_status_index` (`school_id`,`status`),
  KEY `question_banks_created_by_id_index` (`created_by_id`),
  FULLTEXT KEY `question_banks_title_body_tags_fulltext` (`title`,`body`,`tags`),
  CONSTRAINT `question_banks_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `question_banks_curriculum_id_foreign` FOREIGN KEY (`curriculum_id`) REFERENCES `curricula` (`id`) ON DELETE CASCADE,
  CONSTRAINT `question_banks_curriculum_lessons_id_foreign` FOREIGN KEY (`curriculum_lessons_id`) REFERENCES `curriculum_lessons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `question_banks_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `question_banks_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
);
```

**Fields:**
- `id`: Primary key
- `school_id`: Foreign key to schools table
- `subject_id`, `curriculum_id`, `curriculum_lessons_id`: Optional context links
- `title`, `body`: Question content
- `options`: JSON multiple choice options
- `correct_answer`: Answer key
- `resources`: JSON attachments
- `type`: Question type (mcq, true_false, fill_blank, essay, short_answer)
- `score`: Point value
- `difficulty`: Difficulty level (easy, medium, hard)
- `notes`, `tags`: Additional metadata
- `status`: Question status (draft, active, archived)
- `author`, `source`: Attribution
- `metadata`, `explanation`, `question_data`: JSON structured data
- `notes_admin`, `notes_teacher`: Role-specific notes
- `created_by_id`: Creator teacher

**Business Rules:**
- Questions can be linked to curricula, lessons, or standalone
- Full-text search enabled on title, body, and tags
- Flexible question types with structured data support

## Relationships

### Entity Relationship Diagram (Text Format)

```
schools (1) ----< curricula (M)
grades (1) ----< curricula (M)
subjects (1) ----< curricula (M)

curricula (1) ----< curriculum_lessons (M)
curricula (1) ----< curriculum_lesson_plans (M)
curricula (1) ----< curriculum_maps (M)
curricula (1) ----< question_banks (M)

curriculum_lessons (1) ----< curriculum_lesson_plans (M)
curriculum_lessons (1) ----< question_banks (M)

teachers (1) ----< curriculum_lesson_plans (M)
teachers (1) ----< curriculum_maps (M)
teachers (1) ----< question_banks (M)

classrooms (1) ----< curriculum_lesson_plans (M)
academic_years (1) ----< curriculum_maps (M)
```

## Indexes and Performance

### Primary Indexes
- All tables have auto-incrementing primary keys
- Foreign key constraints automatically create indexes

### Custom Indexes
- **curricula**: Composite indexes for filtering and uniqueness
- **curriculum_lessons**: Performance indexes for common queries
- **curriculum_lesson_plans**: Teacher and classroom-based queries
- **curriculum_maps**: Academic year and teacher-based queries
- **question_banks**: Subject, type, difficulty, and status filtering

### Full-Text Search
- **question_banks**: Full-text index on title, body, and tags for content search

## Data Types and Constraints

### TinyInteger Usage
Following the project's preference for explicit integer values over boolean:
- `active`: 0=inactive, 1=active
- `selected`: 0=not selected, 1=selected
- `status`: 0=draft, 1=active, 2=completed

### JSON Fields
Structured data stored as JSON for flexibility:
- `data`: General purpose additional data
- `materials`: Teaching materials and resources
- `plan`: Detailed lesson plan structure
- `weekly_plan`: Weekly planning structure
- `options`: Multiple choice options
- `resources`: File attachments and resources
- `metadata`: Additional structured metadata
- `explanation`: Answer explanations
- `question_data`: Question-specific data

### Enum Fields
Controlled vocabularies for data consistency:
- `type`: Lesson/question types
- `difficulty`: Question difficulty levels
- `status`: Various status fields

## Migration Files

1. `2024_11_30_130314_create_curricula_table.php`
2. `2024_11_30_130328_create_curriculum_lessons_table.php`
3. `2025_05_22_025858_create_curriculum_lesson_plans_table.php`
4. `2025_05_22_064638_create_curriculum_maps_table.php`
5. `2025_11_30_130333_create_question_banks_table.php`

## Backup and Maintenance

### Recommended Backup Strategy
- **Full backups**: Daily during low-usage periods
- **Incremental backups**: Every 4 hours during active periods
- **Point-in-time recovery**: Enable binary logging

### Maintenance Tasks
- **Index optimization**: Monthly ANALYZE TABLE operations
- **Soft delete cleanup**: Quarterly cleanup of old deleted records
- **JSON field optimization**: Monitor and optimize JSON queries

---

*Last Updated: [Current Date]*
*Schema Version: 1.0*
