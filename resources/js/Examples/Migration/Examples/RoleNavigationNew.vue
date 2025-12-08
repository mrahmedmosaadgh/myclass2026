<!--
  NEW PATTERN: Role-based navigation using clean composable
  
  Benefits of this approach:
  - Clean, readable role checking
  - Centralized role management
  - Easy to maintain
  - Consistent across the app
-->

<template>
  <nav class="role-navigation">
    <h3 class="font-medium mb-3">Navigation (New Pattern)</h3>
    
    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-2">
      <div v-for="i in 3" :key="i" class="h-4 bg-gray-200 rounded animate-pulse"></div>
    </div>
    
    <!-- Navigation Links -->
    <div v-else class="space-y-2">
      <!-- Admin Links -->
      <div v-if="can('admin')" class="nav-section">
        <h4 class="text-sm font-medium text-gray-700">Admin</h4>
        <ul class="nav-links">
          <li><a href="/admin/dashboard" class="nav-link">Admin Dashboard</a></li>
          <li><a href="/admin/users" class="nav-link">Manage Users</a></li>
          <li><a href="/admin/schools" class="nav-link">Manage Schools</a></li>
        </ul>
      </div>
      
      <!-- Teacher Links -->
      <div v-if="can('teacher')" class="nav-section">
        <h4 class="text-sm font-medium text-gray-700">Teacher</h4>
        <ul class="nav-links">
          <li><a href="/teacher/dashboard" class="nav-link">Teacher Dashboard</a></li>
          <li><a href="/teacher/classes" class="nav-link">My Classes</a></li>
          <li><a href="/teacher/grades" class="nav-link">Grades</a></li>
        </ul>
      </div>
      
      <!-- Student Links -->
      <div v-if="can('student')" class="nav-section">
        <h4 class="text-sm font-medium text-gray-700">Student</h4>
        <ul class="nav-links">
          <li><a href="/student/dashboard" class="nav-link">Student Dashboard</a></li>
          <li><a href="/student/assignments" class="nav-link">Assignments</a></li>
          <li><a href="/student/grades" class="nav-link">My Grades</a></li>
        </ul>
      </div>
      
      <!-- Parent Links -->
      <div v-if="can('parent')" class="nav-section">
        <h4 class="text-sm font-medium text-gray-700">Parent</h4>
        <ul class="nav-links">
          <li><a href="/parent/dashboard" class="nav-link">Parent Dashboard</a></li>
          <li><a href="/parent/children" class="nav-link">My Children</a></li>
        </ul>
      </div>
      
      <!-- Role-specific shortcuts -->
      <div class="nav-section">
        <h4 class="text-sm font-medium text-gray-700">Quick Access</h4>
        <ul class="nav-links">
          <li v-if="isAdmin">
            <a href="/admin/quick-stats" class="nav-link">📊 Quick Stats</a>
          </li>
          <li v-if="isTeacher">
            <a href="/teacher/today" class="nav-link">📅 Today's Classes</a>
          </li>
          <li v-if="isStudent">
            <a href="/student/homework" class="nav-link">📝 Homework</a>
          </li>
        </ul>
      </div>
    </div>
    
    <!-- User Info -->
    <div v-if="user" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
      <div class="text-sm">
        <div class="font-medium">{{ user.name }}</div>
        <div class="text-gray-600">{{ user.email }}</div>
        <div class="flex flex-wrap gap-1 mt-1">
          <span v-for="role in roles" :key="role" 
                class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
            {{ role }}
          </span>
        </div>
      </div>
    </div>
    
    <!-- Benefits of new pattern -->
    <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded">
      <h4 class="font-medium text-green-800 text-sm">Benefits of New Pattern</h4>
      <ul class="text-green-600 text-xs mt-1 space-y-1">
        <li>✅ Clean, readable role checking with <code>can()</code></li>
        <li>✅ Built-in role type checks (isAdmin, isTeacher, etc.)</li>
        <li>✅ Centralized role management</li>
        <li>✅ Consistent across the entire app</li>
        <li>✅ Easy to maintain and extend</li>
        <li>✅ Loading states included</li>
      </ul>
    </div>
  </nav>
</template>

<script setup>
import { useUser } from '@/composables/useUserContext.js';

// NEW PATTERN: Clean, simple role checking
const { 
  user,
  roles,
  can,
  isAdmin,
  isTeacher,
  isStudent,
  isLoading
} = useUser();
</script>

<style scoped>
.role-navigation {
  @apply p-4 border rounded-lg bg-white;
}

.nav-section {
  @apply mb-3;
}

.nav-links {
  @apply ml-2 space-y-1;
}

.nav-link {
  @apply text-blue-600 hover:text-blue-800 text-sm;
}

code {
  @apply bg-gray-200 px-1 rounded text-xs;
}
</style>
