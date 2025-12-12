Here's an **improved and clarified version** of your AI prompt, tailored for use **within an existing Laravel project** under a **subfolder `course_management`**, and keeping the system flexible, scalable, and developer-friendly.

---

## ✅ Refined Prompt for AI (Laravel-focused, Subfolder-Based)

> 🚀 **Prompt:**
>
> Inside an existing Laravel project, create a **modular Course Management System** within a new **`course_management` subfolder/module**. The goal is to manage structured educational content with this hierarchy:
>
> ```
> Course → Levels → Sections → Lessons
> ```
>
> ### 📚 Entity Definitions:
>
> * **Course**
>
>   * Represents a subject or program (e.g., “Grade 5 Math”)
>   * Fields: `id`, `name`, `description`, `created_by`, `timestamps`, `softDeletes`
> * **Level**
>
>   * Represents a major unit of study (e.g., “Fractions”)
>   * Fields: `id`, `title`, `order`, `course_id`, `created_by`, `timestamps`, `softDeletes`
> * **Section**
>
>   * Represents a topic group within a level (e.g., “Add unlike fractions”)
>   * Fields: `id`, `title`, `order`, `level_id`, `created_by`, `timestamps`, `softDeletes`
> * **Lesson**
>
>   * Represents a single lesson
>   * Fields:
>
>     * `id`
>     * `title` — name of the lesson
>     * `text` — short instructional summary
>     * `data` — `JSON` for future enhancements (e.g., WPM goal, difficulty level, quiz mode)
>     * `order`, `section_id`, `created_by`, `timestamps`, `softDeletes`
>
> ---
>
> ### ⚙️ Backend Requirements:
>
> ✅ **Migrations**
>
> * Add migrations for all 4 models with foreign key relationships
> * Use `onDelete('cascade')` for nested relations
> * Use `softDeletes()` and `timestamps()` in all tables
> * Include `created_by` (user ID) for auditing
>
> ✅ **Models**
>
> * Create Eloquent models: `Course`, `Level`, `Section`, `Lesson`
> * Define all relationships properly (hasMany / belongsTo)
>
> ✅ **Controllers**
>
> * Create namespaced controllers under `App\Http\Controllers\CourseManagement`
>
>   * `CourseController`, `LevelController`, `SectionController`, `LessonController`
> * Include full CRUD methods (index, store, update, destroy)
>
> ✅ **Routes**
>
> * Prefix routes under `/course-management/`
> * Use nested routes for hierarchy
>   Example:
>
>   ```
>   GET /course-management/courses
>   POST /course-management/courses/{course}/levels
>   POST /course-management/levels/{level}/sections
>   POST /course-management/sections/{section}/lessons
>   ```
>
> ✅ **Folder Structure**
>
> ```
> app/
> └── Http/
>     └── Controllers/
>         └── CourseManagement/
>             ├── CourseController.php
>             ├── LevelController.php
>             ├── SectionController.php
>             └── LessonController.php
>
> database/
> └── migrations/
>     ├── 2025_07_17_000000_create_courses_table.php
>     ├── 2025_07_17_000001_create_levels_table.php
>     ├── 2025_07_17_000002_create_sections_table.php
>     └── 2025_07_17_000003_create_lessons_table.php
>
> resources/
> └── js/
>     └── Pages/
>         └── CourseManagement/
>             └── Course/
>             └── Level/
>             └── Section/
>             └── Lesson/
> ```
>
> ✅ **Optional Enhancements**
>
> * Use UUIDs for IDs if needed
> * Plan for future support of media (videos, images) inside `lesson.data`
> * Support reordering (via `order` column) for all levels, sections, and lessons
>
> 🔐 **Authentication/Authorization**
>
> * Assume it's not required for now, but reserve `created_by` for future linking to users.
>
> ✅ **Tech Stack Assumed**
>
> * Laravel 12
> * Vue 3 with Inertia.js
> * MySQL
>
> 🎯 Goal: Let admins easily manage curriculum content structured as Course → Levels → Sections → Lessons, with rich customization using `data` JSON field for future upgrades like time limits, quiz modes, or media.

---

 understand the project first before you start . if there is something not clear ask me before you start