# 🛠️ Implementation Guide

Step-by-step guide to implement the offline-first education management system in your Laravel + Inertia + Vue 3 application.

## 📋 Prerequisites

- Laravel 8+ with Inertia.js
- Vue 3 with Composition API
- Node.js and npm
- Basic understanding of IndexedDB concepts

## 🚀 Installation Steps

### Step 1: Install Dependencies

```bash
# Install Dexie.js for IndexedDB management
npm install dexie
```

### Step 2: File Structure

The offline system consists of these core files:

```
resources/js/offline/
├── dexie.js              # Database schema and setup
├── api.js                # Network status and API management  
├── syncQueue.js          # Sync queue management
└── useOfflineResource.js # Main Vue composable
```

### Step 3: Database Schema Configuration

The system automatically supports these education resources:

```javascript
// From resources/js/offline/dexie.js
export const resourceSchemas = {
  // Core education resources
  lessons: 'id, title, content, course_id, teacher_id, updated_at, synced_at, is_dirty',
  students: 'id, name, email, class_id, grade_id, updated_at, synced_at, is_dirty',
  courses: 'id, name, description, teacher_id, grade_id, updated_at, synced_at, is_dirty',
  
  // Assessment and grading
  quiz_answers: 'id, quiz_id, student_id, answers, score, submitted_at, updated_at, synced_at, is_dirty',
  assignments: 'id, title, description, course_id, due_date, updated_at, synced_at, is_dirty',
  grades: 'id, student_id, assignment_id, score, graded_at, updated_at, synced_at, is_dirty',
  
  // System tables
  sync_queue: 'id, method, url, payload, timestamp, retry_count, status, priority, resource_type',
  offline_metadata: 'key, value, updated_at'
};
```

## 🔧 Laravel Backend Setup

### Step 1: Add Health Check Route

Add this to your `routes/api.php`:

```php
// Health check endpoint for connectivity testing
Route::head('/health-check', function () {
    return response('', 200);
});
```

### Step 2: API Resource Routes

Ensure your API routes follow RESTful conventions:

```php
// Example API routes
Route::apiResource('lessons', LessonController::class);
Route::apiResource('students', StudentController::class);
Route::apiResource('quiz-answers', QuizAnswerController::class);
```

### Step 3: CSRF Token Setup

Ensure CSRF token is available in your main layout:

```html
<!-- In resources/views/app.blade.php -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

## 🎯 Vue Component Integration

### Basic Usage Example

```vue
<template>
  <div class="lesson-manager">
    <!-- Offline indicator -->
    <div v-if="!isOnline" class="offline-banner">
      📴 You're offline - changes will sync when connected
    </div>
    
    <!-- Sync status -->
    <div v-if="syncStatus !== 'synced'" class="sync-status">
      {{ syncStatus === 'syncing' ? '⏳ Syncing...' : '📤 Has unsynced changes' }}
    </div>
    
    <!-- Loading state -->
    <div v-if="loading" class="loading">
      Loading lessons...
    </div>
    
    <!-- Lessons list -->
    <div v-else>
      <div v-for="lesson in data" :key="lesson.id" class="lesson-card">
        <h3>{{ lesson.title }}</h3>
        <p>{{ lesson.content }}</p>
        <button @click="editLesson(lesson)">Edit</button>
        <button @click="deleteLesson(lesson.id)">Delete</button>
      </div>
    </div>
    
    <!-- Add new lesson -->
    <button @click="showCreateForm = true">Add New Lesson</button>
    
    <!-- Create form -->
    <div v-if="showCreateForm" class="create-form">
      <input v-model="newLesson.title" placeholder="Lesson title" />
      <textarea v-model="newLesson.content" placeholder="Lesson content"></textarea>
      <button @click="createLesson">Save</button>
      <button @click="showCreateForm = false">Cancel</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useOfflineResource } from '@/offline/useOfflineResource';

// Initialize offline resource management for lessons
const {
  data,
  loading,
  error,
  syncStatus,
  isOnline,
  loadAll,
  create,
  update,
  delete: deleteItem
} = useOfflineResource('lessons');

// Component state
const showCreateForm = ref(false);
const newLesson = ref({ title: '', content: '' });

// Load lessons on component mount
onMounted(() => {
  loadAll().catch(err => {
    console.error('Failed to load lessons:', err);
  });
});

// Create new lesson
function createLesson() {
  create(newLesson.value)
    .then(lesson => {
      console.log('Lesson created:', lesson);
      newLesson.value = { title: '', content: '' };
      showCreateForm.value = false;
    })
    .catch(err => {
      console.error('Failed to create lesson:', err);
    });
}

// Edit lesson
function editLesson(lesson) {
  const updatedData = { title: lesson.title + ' (Updated)' };
  
  update(lesson.id, updatedData)
    .then(updated => {
      console.log('Lesson updated:', updated);
    })
    .catch(err => {
      console.error('Failed to update lesson:', err);
    });
}

// Delete lesson
function deleteLesson(id) {
  if (confirm('Are you sure you want to delete this lesson?')) {
    deleteItem(id)
      .then(() => {
        console.log('Lesson deleted');
      })
      .catch(err => {
        console.error('Failed to delete lesson:', err);
      });
  }
}
</script>
```

## 🔄 Advanced Usage Examples

### Multiple Resources in One Component

```vue
<script setup>
import { useOfflineResource } from '@/offline/useOfflineResource';

// Manage multiple resources
const lessons = useOfflineResource('lessons');
const students = useOfflineResource('students');
const quizAnswers = useOfflineResource('quiz_answers', {
  syncPriority: 1, // Critical priority for quiz data
  offlineCapabilities: ['create', 'read'] // Only allow creating and reading offline
});

// Load all data
onMounted(() => {
  Promise.all([
    lessons.loadAll(),
    students.loadAll(),
    quizAnswers.loadAll()
  ]).catch(err => {
    console.error('Failed to load data:', err);
  });
});
</script>
```

### Custom Configuration

```vue
<script setup>
import { useOfflineResource } from '@/offline/useOfflineResource';
import { SYNC_PRIORITIES } from '@/offline/syncQueue';

const lessons = useOfflineResource('lessons', {
  syncPriority: SYNC_PRIORITIES.HIGH,
  cacheDuration: 1800000, // 30 minutes
  offlineCapabilities: ['read', 'create', 'update'], // No delete offline
  optimisticUpdates: true,
  autoSync: true
});
</script>
```

## 🧪 Testing the Implementation

### Test Offline Functionality

1. **Open browser DevTools**
2. **Go to Network tab**
3. **Check "Offline" checkbox**
4. **Try creating/updating data**
5. **Uncheck "Offline"**
6. **Watch data sync automatically**

### Test Data Persistence

```javascript
// In browser console
import { db } from '@/offline/dexie';

// Check stored data
db.lessons.toArray().then(console.log);

// Check sync queue
db.sync_queue.toArray().then(console.log);

// Get sync statistics
db.getSyncStats('lessons').then(console.log);
```

## 🔧 Troubleshooting

### Common Issues

1. **CSRF Token Missing**
   ```javascript
   // Check if token is available
   console.log(document.querySelector('meta[name="csrf-token"]')?.content);
   ```

2. **Database Not Opening**
   ```javascript
   // Check database status
   import { db } from '@/offline/dexie';
   console.log('Database open:', db.isOpen());
   ```

3. **Sync Queue Not Processing**
   ```javascript
   // Manually trigger sync
   import { processQueue } from '@/offline/syncQueue';
   processQueue().then(console.log);
   ```

## 🚀 Next Steps

1. **Add to your main app.js**:
   ```javascript
   // Import offline system
   import '@/offline/dexie.js'; // Initialize database
   ```

2. **Create example components** using the patterns above

3. **Test thoroughly** with network on/off

4. **Add error handling** for your specific use cases

5. **Customize** resource schemas for your needs

---

**Ready to implement?** Start with the basic lesson management example and expand from there!
