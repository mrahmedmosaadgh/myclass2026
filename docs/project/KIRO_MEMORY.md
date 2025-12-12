# Kiro AI Assistant - Project Memory

## Critical Guidelines - NEVER FORGET

### 1. Routing System

**⚠️ IMPORTANT: This project uses Inertia.js, NOT Vue Router!**

-   ✅ Use: `import { router, Head } from '@inertiajs/vue3'`
-   ✅ Use: `router.visit('/path')`
-   ❌ Never use: `import { useRouter } from 'vue-router'`
-   ❌ Never use: `router.push({ name: 'route-name' })`

**Navigation Examples:**

```javascript
// ✅ CORRECT - Inertia.js
import { router } from "@inertiajs/vue3";
router.visit("/questions");
router.visit(`/questions/${id}/edit`);

// ❌ WRONG - Vue Router (Don't use!)
import { useRouter } from "vue-router";
const router = useRouter();
router.push({ name: "questions" });
```

### 2. Page Titles

**⚠️ ALWAYS add `<Head>` component to all pages!**

```vue
<template>
    <Head title="Page Title" />
    <!-- or dynamic -->
    <Head :title="dynamicTitle" />

    <!-- Rest of template -->
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
</script>
```

### 3. Page Wrapper Components

**⚠️ NEVER use `<q-page>` or `<QPage>` in Vue components!**

-   ❌ Never use: `<q-page>` or `<QPage>`
-   ✅ Use: Regular `<div>` with classes instead

**Example:**

```vue
<!-- ❌ WRONG -->
<template>
    <q-page class="my-page"> Content </q-page>
</template>

<!-- ✅ CORRECT -->
<template>
    <Head title="My Page" />
    <div class="my-page">Content</div>
</template>
```

### 4. Laravel Routes for Inertia

**Routes should return Inertia::render()**

```php
// routes/web.php
Route::get('/questions', function () {
    return Inertia::render('QuestionManagement/QuestionBank');
})->name('questions.index');

Route::get('/questions/{id}/edit', function ($id) {
    return Inertia::render('QuestionManagement/QuestionEditor', [
        'questionId' => $id
    ]);
})->name('questions.edit');
```

### 5. Receiving Props in Inertia Pages

```vue
<script setup>
import { Head } from "@inertiajs/vue3";

// Define props that come from Laravel controller
const props = defineProps({
    questionId: {
        type: [String, Number],
        default: null,
    },
    question: {
        type: Object,
        default: null,
    },
});
</script>
```

## Project Structure

### Frontend Stack

-   **Framework**: Vue 3 (Composition API)
-   **UI Library**: Quasar Framework
-   **Routing**: Inertia.js (NOT Vue Router)
-   **HTTP Client**: Axios
-   **State**: Reactive refs and computed

### Backend Stack

-   **Framework**: Laravel 10
-   **Database**: MySQL
-   **API**: RESTful APIs
-   **Authentication**: Laravel Sanctum

## Common Patterns

### Page Template Structure

```vue
<template>
    <Head title="Page Title" />
    <div class="page-wrapper">
        <!-- Page content -->
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { router, Head } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import axios from "axios";

const $q = useQuasar();

// Component logic
</script>

<style scoped lang="scss">
.page-wrapper {
    // Styles
}
</style>
```

### Navigation Pattern

```javascript
// Navigate to another page
router.visit("/path");

// Navigate with data
router.visit("/path", {
    method: "post",
    data: { key: "value" },
});

// Go back
router.visit(window.history.back());
```

### API Calls Pattern

```javascript
const loadData = async () => {
    try {
        const response = await axios.get("/api/endpoint");
        if (response.data.success) {
            // Handle success
        }
    } catch (error) {
        $q.notify({
            type: "negative",
            message: "Error message",
            caption: error.response?.data?.error?.message || error.message,
        });
    }
};
```

## File Locations

### Pages (Inertia)

-   Location: `resources/js/Pages/`
-   Example: `resources/js/Pages/QuestionManagement/QuestionBank.vue`

### Components

-   Location: `resources/js/Components/`
-   Example: `resources/js/Components/QuestionBank/QuestionCard.vue`

### Routes

-   Web Routes: `routes/web.php` (Inertia pages)
-   API Routes: `routes/api.php` (JSON APIs)

### Controllers

-   Location: `app/Http/Controllers/`
-   Example: `app/Http/Controllers/QuestionController.php`

## Quick Reference

### ✅ DO's

-   Use Inertia.js for routing
-   Add `<Head>` to all pages
-   Use regular `<div>` instead of `<q-page>`
-   Use `router.visit()` for navigation
-   Import from `@inertiajs/vue3`
-   Use Quasar components (except q-page)
-   Use Composition API
-   Use axios for API calls

### ❌ DON'Ts

-   Don't use Vue Router
-   Don't use `<q-page>` or `<QPage>`
-   Don't forget `<Head>` component
-   Don't use `router.push()` or `router.replace()`
-   Don't import from `vue-router`
-   Don't run migrations without checking dependencies
-   Don't create new migrations for existing tables

## Notes

-   Database migrations have dependency issues - be careful when running migrations
-   The project uses Quasar Framework for UI components
-   All pages should be responsive (mobile-first)
-   Use Quasar's notify for user feedback
-   Follow existing code patterns in the project

---

**Last Updated**: 2025-11-25
**Project**: MyClass9 - Educational Management System
