# Curriculum Management System - Technical Documentation

## Overview
The Curriculum Management system was overhauled in December 2025 to move from a flat data structure to a normalized, topic-based hierarchy. This allows for better organization of educational content and more granular control over lessons.

---

## 1. Data Architecture
The system follows a **Curriculum > Topic > Lesson** hierarchy.

### Database Schema
* **Curricula**: The top-level container (e.g., "Envision Math 7").
* **Curriculum Topics**: 
    * Fields: `id`, `curriculum_id`, `number`, `title`, `description`, `sort_order`.
    * Relationship: `belongsTo(Curriculum)`, `hasMany(CurriculumLesson)`.
* **Curriculum Lessons**:
    * Fields: `id`, `topic_id`, `school_id`, `lesson_number`, `lesson_title`, `content`, `type` (enum).
    * Relationship: `belongsTo(CurriculumTopic)`.

---

## 2. Key Components
### `CurriculumManagement.vue`
The main dashboard for managing curricula.
* **Filter Dialog**: Replaced inline filters with a modal to maximize screen real estate.
* **Action Column**: Includes buttons for Editing and the "View Lessons" eye icon.
* **Excel Integration**: Uses `ExcelManager.vue` for bulk data operations.

### `CurriculumLessonsManager.vue`
A full-screen management interface for the internal structure of a curriculum.
* **Auto-Numbering**: Automatically suggests `n+1` for the next Topic or Lesson based on existing data.
* **Topic Grouping**: Lessons are nested under their respective topics.
* **Enum Enforcement**: Lesson types are restricted to `main`, `revision`, `quiz`, `project`, and `extra`.

---

## 3. Excel Import/Export System
A reusable system located in `resources/js/Components/import_excel_sys/`.
* **Exporter**: Generates templates or exports current data to `.xlsx`.
* **Importer**: Provides a client-side preview of Excel data before it is sent to the server.
* **Mapping**: Uses a JSON-based mapping to ensure column headers in Excel match database fields.

---

## 4. API Endpoints
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/curriculum/{curriculum}/topics` | Fetch all topics with nested lessons |
| `POST` | `/api/curriculum/topics` | Create a new topic |
| `POST` | `/api/curriculum/lessons` | Create a new lesson (requires `topic_id`) |
| `POST` | `/api/curriculum/{id}/topics/reorder` | Update `sort_order` for topics |

---

## 5. Recent Fixes
* **Object ID Bug**: Fixed issue where `q-select` passed `[object Object]` to the API; now extracts `.id` explicitly.
* **Missing Columns**: Removed `notes_admin` and `notes_teacher` from controllers as they are not in the current schema.
* **Permissions**: Fixed early return in `CurriculumController@getUserSchools` to ensure role-based logic executes.