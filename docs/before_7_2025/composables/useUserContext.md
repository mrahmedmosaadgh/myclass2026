# 🧑‍💻 useUserContext Composable

A Vue 3 composable for easy access to segmented user context with offline-first caching and auto-sync capabilities.

## 📋 Table of Contents

- [Quick Start](#quick-start)
- [Available Composables](#available-composables)
- [API Reference](#api-reference)
- [Usage Examples](#usage-examples)
- [Configuration Options](#configuration-options)
- [Best Practices](#best-practices)

## 🚀 Quick Start

### Basic Usage

```javascript
import { useUser } from '@/composables/useUserContext.js';

export default {
  setup() {
    const { user, isTeacher, isStudent, roles, can } = useUser();
    
    return {
      user,
      isTeacher,
      isStudent,
      roles,
      can
    };
  }
}
```

### Full Context Access

```javascript
import { useUserContext } from '@/composables/useUserContext.js';

export default {
  setup() {
    const {
      // User data
      user,
      profile,
      permissions,
      school,
      classroom,
      schedule,
      
      // Computed helpers
      isTeacher,
      schools,
      teacherClassrooms,
      
      // Methods
      refresh,
      clearCache
    } = useUserContext();
    
    return {
      user,
      isTeacher,
      schools,
      refresh
    };
  }
}
```

## 📚 Available Composables

### 1. `useUser()` - Basic User Info
Perfect for components that only need basic user information.

```javascript
const { user, isTeacher, isStudent, isAdmin, roles, can } = useUser();
```

**Returns:**
- `user` - Basic user profile (id, name, email, role)
- `isTeacher`, `isStudent`, `isAdmin` - Role checks
- `roles` - Array of user roles
- `can(permission)` - Permission checker function

### 2. `useUserContext(options)` - Full Context
Complete access to all user context segments with configuration options.

```javascript
const context = useUserContext({
  segments: ['profile', 'school', 'classroom'],
  autoLoad: true,
  refreshOnFocus: true
});
```

### 3. `useTeacher()` - Teacher-Specific Data
Optimized for teacher components with teacher-specific aliases.

```javascript
const { teacher, classrooms, schools, schedule } = useTeacher();
```

### 4. `useStudent()` - Student-Specific Data
Optimized for student components with student-specific aliases.

```javascript
const { classroom, schedule, primarySchool } = useStudent();
```

## 📖 API Reference

### Reactive State

| Property | Type | Description |
|----------|------|-------------|
| `user` | `Ref<Object>` | Basic user info (id, name, email, role) |
| `profile` | `Ref<Object>` | Full user profile segment |
| `permissions` | `Ref<Object>` | User permissions and roles |
| `school` | `Ref<Object>` | School information |
| `classroom` | `Ref<Object>` | Classroom data |
| `schedule` | `Ref<Object>` | User schedule |

### Computed Properties

| Property | Type | Description |
|----------|------|-------------|
| `isTeacher` | `ComputedRef<boolean>` | True if user is a teacher |
| `isStudent` | `ComputedRef<boolean>` | True if user is a student |
| `isAdmin` | `ComputedRef<boolean>` | True if user is an admin |
| `roles` | `ComputedRef<Array>` | Array of user roles |
| `schools` | `ComputedRef<Array>` | Array of user's schools |
| `teacherClassrooms` | `ComputedRef<Array>` | Teacher's classrooms |
| `studentClassroom` | `ComputedRef<Object>` | Student's classroom |
| `hasSchedule` | `ComputedRef<boolean>` | True if user has schedule |

### Methods

| Method | Parameters | Description |
|--------|------------|-------------|
| `refresh()` | - | Refresh all segments from server |
| `refreshSegment(segment)` | `segment: string` | Refresh specific segment |
| `can(permission)` | `permission: string` | Check if user has permission |
| `belongsToSchool(schoolId)` | `schoolId: number` | Check if user belongs to school |
| `clearCache()` | - | Clear all cached data |
| `getCacheStats()` | - | Get cache statistics |

### Loading & Error States

| Property | Type | Description |
|----------|------|-------------|
| `isLoading` | `ComputedRef<boolean>` | True if any segment is loading |
| `isFullyLoaded` | `ComputedRef<boolean>` | True if all segments are loaded |
| `loading` | `ComputedRef<Object>` | Loading state per segment |
| `hasErrors` | `ComputedRef<boolean>` | True if any segment has errors |
| `errors` | `ComputedRef<Object>` | Error messages per segment |

## 💡 Usage Examples

### Role-Based Conditional Rendering

```vue
<template>
  <div>
    <!-- Teacher-only content -->
    <div v-if="isTeacher">
      <h2>Teacher Dashboard</h2>
      <p>You have {{ classrooms.length }} classrooms</p>
    </div>
    
    <!-- Student-only content -->
    <div v-if="isStudent">
      <h2>Student Dashboard</h2>
      <p>Your classroom: {{ classroom?.name }}</p>
    </div>
    
    <!-- Permission-based content -->
    <button v-if="can('admin')" @click="adminAction">
      Admin Action
    </button>
  </div>
</template>

<script setup>
import { useUserContext } from '@/composables/useUserContext.js';

const { 
  isTeacher, 
  isStudent, 
  classrooms, 
  classroom, 
  can 
} = useUserContext();
</script>
```

### Loading States

```vue
<template>
  <div>
    <div v-if="isLoading" class="loading">
      Loading user data...
    </div>
    
    <div v-else-if="hasErrors" class="error">
      <p>Error loading user data:</p>
      <ul>
        <li v-for="(error, segment) in errors" :key="segment" v-if="error">
          {{ segment }}: {{ error }}
        </li>
      </ul>
    </div>
    
    <div v-else-if="isFullyLoaded">
      <!-- Your content here -->
    </div>
  </div>
</template>

<script setup>
import { useUserContext } from '@/composables/useUserContext.js';

const { isLoading, hasErrors, errors, isFullyLoaded } = useUserContext();
</script>
```

### Cache Management

```vue
<template>
  <div>
    <div class="cache-status">
      Cache Health: {{ cacheHealth.percentage }}%
      <button @click="refresh">Refresh All</button>
      <button @click="clearCache">Clear Cache</button>
    </div>
  </div>
</template>

<script setup>
import { useUserContext } from '@/composables/useUserContext.js';

const { cacheHealth, refresh, clearCache } = useUserContext();
</script>
```

## ⚙️ Configuration Options

```javascript
const context = useUserContext({
  // Which segments to load
  segments: ['profile', 'permissions', 'school', 'classroom', 'schedule'],
  
  // Auto-load on component mount
  autoLoad: true,
  
  // Watch for Inertia page changes
  watchForChanges: true,
  
  // Refresh when window gains focus
  refreshOnFocus: false
});
```

## 🎯 Best Practices

### 1. Choose the Right Composable
- Use `useUser()` for basic user info
- Use `useTeacher()` or `useStudent()` for role-specific components
- Use `useUserContext()` for complex components needing full access

### 2. Handle Loading States
Always handle loading and error states in your components:

```javascript
const { user, isLoading, hasErrors } = useUser();

// Show loading spinner while data loads
// Show error message if something fails
// Show content when data is ready
```

### 3. Optimize Segment Loading
Only load the segments you need:

```javascript
// Only load profile and permissions for a simple user card
const context = useUserContext({
  segments: ['profile', 'permissions']
});
```

### 4. Cache Management
- The composable automatically manages cache expiry (7 days)
- Use `refresh()` to force update from server
- Use `clearCache()` when user logs out or switches accounts

### 5. Backward Compatibility
For existing components, you can still get the legacy format:

```javascript
const { getLegacyFormat } = useUserContext();
const legacyUser = getLegacyFormat(); // Same as old $page.props.auth.user
```

## 🔧 Integration with Existing Code

The composable is designed to work alongside existing code. You can gradually migrate components:

```javascript
// Old way (still works)
const user = computed(() => usePage().props.auth.user);

// New way (recommended)
const { user } = useUser();
```

Both approaches will work during the transition period.
