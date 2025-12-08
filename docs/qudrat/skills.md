# Qudrat Skills Module Implementation Guide

This document outlines the complete implementation of the Qudrat Skills module, covering the database structure, backend API, and frontend Vue components.

---

## ✅ Summary of Implementation

- **Database**: Core tables for skills, levels, lessons, and questions have been established with migrations.
- **Models**: Eloquent models for each table with defined relationships are in place.
- **Controllers**: API controllers provide full CRUD functionality for each resource.
- **Routes**: RESTful API routes are defined in `routes/qudrat_routes.php`.
- **Frontend**: Vue components have been created for managing and importing skills.
- **Excel Import**: A robust, reusable component for importing data from Excel files has been implemented, complete with validation, preview, and undo functionality.

---

## ✅ Routes

The following routes are defined in `routes/qudrat_routes.php` within the `auth` middleware group and `qdrat` prefix:

```php
// Skill Levels
Route::resource('skill-levels', \App\Http\Controllers\QudratQuantitative\QdratSkillLevelController::class);

// Skills
Route::get('skills/import/template', [\App\Http\Controllers\QudratQuantitative\QdratSkillController::class, 'downloadTemplate']);
Route::post('skills/validate', [\App\Http\Controllers\QudratQuantitative\QdratSkillController::class, 'validateImport']);
Route::post('skills/import', [\App\Http\Controllers\QudratQuantitative\QdratSkillController::class, 'import']);
Route::post('skills/undo/{importId}', [\App\Http\Controllers\QudratQuantitative\QdratSkillController::class, 'undo']);
Route::resource('skills', \App\Http\Controllers\QudratQuantitative\QdratSkillController::class);

// Lesson Categories
Route::resource('lesson-categories', \App\Http\Controllers\QudratQuantitative\QdratLessonCategoryController::class);

// Lessons
Route::resource('lessons', \App\Http\Controllers\QudratQuantitative\QdratLessonController::class);

// Question Difficulties
Route::resource('question-difficulties', \App\Http\Controllers\QudratQuantitative\QdratQuestionDifficultyController::class);

// Questions
Route::resource('questions', \App\Http\Controllers\QudratQuantitative\QdratQuestionController::class);
```

---

## ✅ Vue Components

### File Structure:

```
resources/
├── js/
│   ├── Components/
│   │   └── Common/
│   │       └── ImportExcel.vue     // Reusable component for importing
│   └── Pages/
│       └── QudratQuantitative/
│           └── Admin/
│               └── SkillsManager/
│                   ├── Index.vue       // Display skills and search
│                   └── SkillForm.vue   // Add/Edit skill form
```

### `ImportExcel.vue` Component

A powerful and reusable Vue component for handling data imports from Excel files.

**Features:**
- File selection (.xlsx, .xls).
- Data preview in a modal.
- Column validation against backend logic.
- Import confirmation.
- Display of success and error results.
- Undo functionality for the last import batch.
- Customizable via props for URLs, column definitions, and button/label text.

**Usage Example:**

To use it for importing skills, you would configure it like this:

```vue
<script setup>
import ImportExcel from '@/Components/Common/ImportExcel.vue';

const skillColumns = [
  { key: 'name', label: 'Skill Name', required: true },
  { key: 'description', label: 'Description' },
  { key: 'level_id', label: 'Level ID', required: true },
  { key: 'order', label: 'Display Order' }
];

function reloadSkills() {
  // Logic to refresh the skills list
}
</script>

<template>
  <ImportExcel
    :columns="skillColumns"
    validate-url="/qdrat/skills/validate"
    import-url="/qdrat/skills/import"
    undo-url="/qdrat/skills/undo"
    button-text="Import Skills from Excel"
    @imported="reloadSkills"
  />
</template>
```

---

## ✅ Backend Logic (`QdratSkillController`)

- **`validateImport(Request $request)`**:
  - Receives data rows from the frontend.
  - Checks for required fields and can perform other validation (e.g., check if a record already exists).
  - Returns a status for each row (`new`, `update`, `error`).

- **`import(Request $request)`**:
  - Receives validated data.
  - Creates a unique `import_batch` ID for the transaction.
  - Iterates through rows and creates `QdratSkill` records.
  - Tags each new record with the `import_batch` ID to enable undo.
  - Returns success/error messages and the `importId`.

- **`undo($importId)`**:
  - Finds all skills matching the `import_batch` ID.
  - Deletes the matching records from the database.
  - Returns a success message.

- **`downloadTemplate()`**:
  - Provides a downloadable `.xlsx` template file for users to fill out, ensuring data is in the correct format for import.

---

## ✅ Database

To support the undo functionality, the `qdrat_skills` table requires an additional column:

```php
// In a new or existing migration for the qdrat_skills table
$table->string('import_batch')->nullable()->index();
```
This column links the imported records to a specific import session, allowing for easy reversal.