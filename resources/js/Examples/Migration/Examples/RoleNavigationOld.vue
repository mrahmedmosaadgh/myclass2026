<!--
  OLD PATTERN: Role-based navigation using manual checks
  
  Issues with this approach:
  - Verbose role checking
  - Repeated logic
  - Hard to maintain
  - No centralized role management
-->

<template>
  <nav class="role-navigation">
    <h3 class="font-medium mb-3">Navigation (Old Pattern)</h3>
    
    <div class="space-y-2">
      <!-- Admin Links -->
      <div v-if="canAccessAdmin" class="nav-section">
        <h4 class="text-sm font-medium text-gray-700">Admin</h4>
        <ul class="nav-links">
          <li><a href="/admin/dashboard" class="nav-link">Admin Dashboard</a></li>
          <li><a href="/admin/users" class="nav-link">Manage Users</a></li>
          <li><a href="/admin/schools" class="nav-link">Manage Schools</a></li>
        </ul>
      </div>
      
      <!-- Teacher Links -->
      <div v-if="canAccessTeacher" class="nav-section">
        <h4 class="text-sm font-medium text-gray-700">Teacher</h4>
        <ul class="nav-links">
          <li><a href="/teacher/dashboard" class="nav-link">Teacher Dashboard</a></li>
          <li><a href="/teacher/classes" class="nav-link">My Classes</a></li>
          <li><a href="/teacher/grades" class="nav-link">Grades</a></li>
        </ul>
      </div>
      
      <!-- Student Links -->
      <div v-if="canAccessStudent" class="nav-section">
        <h4 class="text-sm font-medium text-gray-700">Student</h4>
        <ul class="nav-links">
          <li><a href="/student/dashboard" class="nav-link">Student Dashboard</a></li>
          <li><a href="/student/assignments" class="nav-link">Assignments</a></li>
          <li><a href="/student/grades" class="nav-link">My Grades</a></li>
        </ul>
      </div>
      
      <!-- Parent Links -->
      <div v-if="canAccessParent" class="nav-section">
        <h4 class="text-sm font-medium text-gray-700">Parent</h4>
        <ul class="nav-links">
          <li><a href="/parent/dashboard" class="nav-link">Parent Dashboard</a></li>
          <li><a href="/parent/children" class="nav-link">My Children</a></li>
        </ul>
      </div>
    </div>
    
    <!-- Issues with old pattern -->
    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
      <h4 class="font-medium text-red-800 text-sm">Issues with Old Pattern</h4>
      <ul class="text-red-600 text-xs mt-1 space-y-1">
        <li>❌ Verbose role checking logic</li>
        <li>❌ Repeated code for each role</li>
        <li>❌ Hard to maintain and update</li>
        <li>❌ No centralized role management</li>
        <li>❌ Prone to inconsistencies</li>
      </ul>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

// OLD PATTERN: Manual role checking with verbose logic
const user = computed(() => usePage().props.auth.user);

const canAccessAdmin = computed(() => {
  const userValue = user.value;
  return userValue?.user_role === 'admin' || 
         userValue?.roles?.includes('admin') ||
         userValue?.user_role === 'super_admin' ||
         userValue?.roles?.includes('super_admin');
});

const canAccessTeacher = computed(() => {
  const userValue = user.value;
  return userValue?.user_role === 'teacher' || 
         userValue?.roles?.includes('teacher') ||
         canAccessAdmin.value; // Admins can access teacher features
});

const canAccessStudent = computed(() => {
  const userValue = user.value;
  return userValue?.user_role === 'student' || 
         userValue?.roles?.includes('student') ||
         canAccessTeacher.value; // Teachers can access student features
});

const canAccessParent = computed(() => {
  const userValue = user.value;
  return userValue?.user_role === 'parent' || 
         userValue?.roles?.includes('parent');
});
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
</style>
