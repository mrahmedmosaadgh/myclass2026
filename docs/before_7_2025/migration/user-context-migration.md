# 🔄 User Context Migration Guide

This guide helps you migrate from the old `$page.props.auth.user` pattern to the new `useUserContext` composable system.

## 📋 Migration Overview

### Before (Old Pattern)
```javascript
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
```

### After (New Pattern)
```javascript
import { useUser } from '@/composables/useUserContext.js';

const { user, isTeacher, isStudent, roles } = useUser();
```

## 🎯 Migration Steps

### Step 1: Identify Usage Patterns

First, identify how your components currently use user data:

#### Pattern A: Basic User Info
```javascript
// OLD
const user = computed(() => usePage().props.auth.user);
const userName = computed(() => user.value?.name);
const userRole = computed(() => user.value?.user_role);

// NEW
const { user } = useUser();
// user.name and user.user_role are directly available
```

#### Pattern B: Role Checks
```javascript
// OLD
const isTeacher = computed(() => 
  usePage().props.auth.user?.user_role === 'teacher' ||
  usePage().props.auth.user?.roles?.includes('teacher')
);

// NEW
const { isTeacher } = useUser();
```

#### Pattern C: School/Classroom Data
```javascript
// OLD
const schools = computed(() => usePage().props.auth.user?.schools || []);
const classroom = computed(() => usePage().props.auth.user?.classroom);

// NEW
const { schools, studentClassroom } = useUserContext();
// or for teachers:
const { schools, teacherClassrooms } = useTeacher();
```

### Step 2: Choose the Right Composable

| Use Case | Recommended Composable | Reason |
|----------|----------------------|---------|
| Basic user info, role checks | `useUser()` | Lightweight, fast loading |
| Teacher dashboard/features | `useTeacher()` | Teacher-specific optimizations |
| Student dashboard/features | `useStudent()` | Student-specific optimizations |
| Complex admin interfaces | `useUserContext()` | Full control and customization |

### Step 3: Update Component by Component

#### Example 1: Simple User Card

**Before:**
```vue
<template>
  <div class="user-card">
    <h3>{{ user?.name }}</h3>
    <p>{{ user?.email }}</p>
    <span v-if="user?.user_role === 'teacher'" class="badge">Teacher</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
</script>
```

**After:**
```vue
<template>
  <div class="user-card">
    <div v-if="isLoading">Loading...</div>
    <div v-else-if="user">
      <h3>{{ user.name }}</h3>
      <p>{{ user.email }}</p>
      <span v-if="isTeacher" class="badge">Teacher</span>
    </div>
  </div>
</template>

<script setup>
import { useUser } from '@/composables/useUserContext.js';

const { user, isTeacher, isLoading } = useUser();
</script>
```

#### Example 2: Teacher Dashboard

**Before:**
```vue
<template>
  <div v-if="isTeacher">
    <h2>Teacher Dashboard</h2>
    <div v-if="user?.classroom">
      <h3>Your Classrooms</h3>
      <div v-for="classroom in user.classroom" :key="classroom.id">
        {{ classroom.name }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
const isTeacher = computed(() => user.value?.user_role === 'teacher');
</script>
```

**After:**
```vue
<template>
  <div v-if="isTeacher">
    <h2>Teacher Dashboard</h2>
    <div v-if="isLoading">Loading classrooms...</div>
    <div v-else-if="classrooms.length > 0">
      <h3>Your Classrooms</h3>
      <div v-for="classroom in classrooms" :key="classroom.id">
        {{ classroom.name }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { useTeacher } from '@/composables/useUserContext.js';

const { isTeacher, classrooms, isLoading } = useTeacher();
</script>
```

## 🔧 Common Migration Patterns

### 1. Role-Based Navigation

**Before:**
```javascript
const canAccessAdmin = computed(() => {
  const user = usePage().props.auth.user;
  return user?.user_role === 'admin' || user?.roles?.includes('admin');
});
```

**After:**
```javascript
const { can, isAdmin } = useUser();
const canAccessAdmin = computed(() => can('admin') || isAdmin.value);
```

### 2. School-Based Filtering

**Before:**
```javascript
const userSchools = computed(() => usePage().props.auth.user?.schools || []);
const belongsToSchool = (schoolId) => {
  return userSchools.value.some(school => school.id === schoolId);
};
```

**After:**
```javascript
const { belongsToSchool } = useUserContext();
// belongsToSchool(schoolId) is now available directly
```

### 3. Conditional Rendering

**Before:**
```vue
<template>
  <div>
    <div v-if="$page.props.auth.user?.user_role === 'teacher'">
      Teacher content
    </div>
    <div v-if="$page.props.auth.user?.user_role === 'student'">
      Student content
    </div>
  </div>
</template>
```

**After:**
```vue
<template>
  <div>
    <div v-if="isTeacher">Teacher content</div>
    <div v-if="isStudent">Student content</div>
  </div>
</template>

<script setup>
import { useUser } from '@/composables/useUserContext.js';
const { isTeacher, isStudent } = useUser();
</script>
```

## ⚠️ Important Considerations

### 1. Async Loading
The new system loads data asynchronously. Always handle loading states:

```javascript
const { user, isLoading } = useUser();

// Wait for data to load before using it
watchEffect(() => {
  if (!isLoading.value && user.value) {
    // Safe to use user data here
  }
});
```

### 2. Error Handling
Add error handling for better user experience:

```javascript
const { user, hasErrors, errors } = useUser();

// Show error messages if something fails
if (hasErrors.value) {
  console.error('User data errors:', errors.value);
}
```

### 3. Backward Compatibility
During migration, you can use both patterns:

```javascript
// Keep old pattern working
const legacyUser = computed(() => usePage().props.auth.user);

// Add new pattern
const { user: newUser } = useUser();

// Use whichever is available
const user = computed(() => newUser.value || legacyUser.value);
```

## 📝 Migration Checklist

- [ ] Identify all components using `usePage().props.auth.user`
- [ ] Choose appropriate composable for each component
- [ ] Add loading state handling
- [ ] Add error state handling
- [ ] Test role-based functionality
- [ ] Test offline functionality
- [ ] Update any custom user-related utilities
- [ ] Remove old patterns once migration is complete

## 🎉 Benefits After Migration

1. **Better Performance**: Segmented loading and caching
2. **Offline Support**: Works without internet connection
3. **Type Safety**: Better TypeScript support
4. **Reactive Updates**: Automatic updates when data changes
5. **Error Handling**: Built-in error states and recovery
6. **Developer Experience**: Cleaner, more intuitive API

## 🆘 Need Help?

If you encounter issues during migration:

1. Check the [useUserContext documentation](../composables/useUserContext.md)
2. Look at the [examples](../../resources/js/Examples/UseUserContextExamples.vue)
3. Use the legacy compatibility mode temporarily:

```javascript
const { getLegacyFormat } = useUserContext();
const legacyUser = getLegacyFormat(); // Same as old auth.user format
```
