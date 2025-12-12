<!--
  Examples showing different ways to use the useUserContext composable
  
  This component demonstrates:
  - Basic user info access
  - Role-based conditional rendering
  - Teacher-specific data
  - Student-specific data
  - Cache management
  - Error handling
-->

<template>
  <div class="user-context-examples p-6 space-y-8">
    <h1 class="text-3xl font-bold mb-8">useUserContext Composable Examples</h1>

    <!-- Basic User Info Example -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">1. Basic User Info (useUser)</h2>
      <div v-if="basicUser.isLoading" class="text-blue-600">Loading user info...</div>
      <div v-else-if="basicUser.user" class="space-y-2">
        <p><strong>Name:</strong> {{ basicUser.user.name }}</p>
        <p><strong>Email:</strong> {{ basicUser.user.email }}</p>
        <p><strong>Role:</strong> {{ basicUser.user.user_role }}</p>
        <div class="flex space-x-2 mt-2">
          <span v-if="basicUser.isTeacher" class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">Teacher</span>
          <span v-if="basicUser.isStudent" class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Student</span>
          <span v-if="basicUser.isAdmin" class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm">Admin</span>
        </div>
        <p><strong>Permissions:</strong> {{ basicUser.roles.join(', ') || 'None' }}</p>
      </div>
    </section>

    <!-- Role-Based Conditional Rendering -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">2. Role-Based Conditional Rendering</h2>
      
      <!-- Teacher-only content -->
      <div v-if="fullContext.isTeacher" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded">
        <h3 class="font-medium text-blue-800">Teacher Dashboard</h3>
        <p class="text-blue-600">Welcome, {{ fullContext.user?.name }}! You have access to teacher features.</p>
        <div v-if="fullContext.teacherClassrooms.length > 0" class="mt-2">
          <p class="text-sm"><strong>Your Classrooms:</strong></p>
          <ul class="list-disc list-inside text-sm text-blue-600">
            <li v-for="classroom in fullContext.teacherClassrooms" :key="classroom.id">
              {{ classroom.name }}
            </li>
          </ul>
        </div>
      </div>

      <!-- Student-only content -->
      <div v-if="fullContext.isStudent" class="mb-4 p-4 bg-green-50 border border-green-200 rounded">
        <h3 class="font-medium text-green-800">Student Dashboard</h3>
        <p class="text-green-600">Welcome, {{ fullContext.user?.name }}! Here's your classroom info.</p>
        <div v-if="fullContext.studentClassroom" class="mt-2">
          <p class="text-sm"><strong>Your Classroom:</strong> {{ fullContext.studentClassroom.name }}</p>
        </div>
      </div>

      <!-- Admin-only content -->
      <div v-if="fullContext.isAdmin" class="mb-4 p-4 bg-red-50 border border-red-200 rounded">
        <h3 class="font-medium text-red-800">Admin Dashboard</h3>
        <p class="text-red-600">Welcome, {{ fullContext.user?.name }}! You have administrative access.</p>
      </div>

      <!-- Permission-based checks -->
      <div class="space-y-2">
        <p v-if="fullContext.can('teacher')" class="text-sm text-blue-600">✓ Can access teacher features</p>
        <p v-if="fullContext.can('student')" class="text-sm text-green-600">✓ Can access student features</p>
        <p v-if="fullContext.can('admin')" class="text-sm text-red-600">✓ Can access admin features</p>
      </div>
    </section>

    <!-- Teacher-Specific Example -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">3. Teacher-Specific Data (useTeacher)</h2>
      <div v-if="teacher.isLoading" class="text-blue-600">Loading teacher data...</div>
      <div v-else-if="teacher.isTeacher" class="space-y-4">
        <div v-if="teacher.teacher">
          <h3 class="font-medium">Teacher Information</h3>
          <p class="text-sm text-gray-600">ID: {{ teacher.teacher.id }}</p>
          <p class="text-sm text-gray-600">Name: {{ teacher.teacher.name }}</p>
        </div>

        <div v-if="teacher.schools.length > 0">
          <h3 class="font-medium">Schools</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div v-for="school in teacher.schools" :key="school.id" 
                 class="p-2 border rounded text-sm">
              <p class="font-medium">{{ school.name }}</p>
              <p class="text-gray-600">ID: {{ school.id }}</p>
            </div>
          </div>
        </div>

        <div v-if="teacher.classrooms.length > 0">
          <h3 class="font-medium">Classrooms</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div v-for="classroom in teacher.classrooms" :key="classroom.id" 
                 class="p-2 border rounded text-sm">
              <p class="font-medium">{{ classroom.name }}</p>
              <p class="text-gray-600">ID: {{ classroom.id }}</p>
            </div>
          </div>
        </div>

        <div v-if="teacher.hasSchedule">
          <h3 class="font-medium">Schedule</h3>
          <p class="text-sm text-green-600">✓ Schedule available ({{ teacher.schedule?.length || 0 }} entries)</p>
        </div>
      </div>
      <div v-else class="text-gray-500">Not a teacher account</div>
    </section>

    <!-- Student-Specific Example -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">4. Student-Specific Data (useStudent)</h2>
      <div v-if="student.isLoading" class="text-blue-600">Loading student data...</div>
      <div v-else-if="student.isStudent" class="space-y-4">
        <div v-if="student.classroom">
          <h3 class="font-medium">Classroom Information</h3>
          <div class="p-3 border rounded">
            <p class="font-medium">{{ student.classroom.name }}</p>
            <p class="text-sm text-gray-600">ID: {{ student.classroom.id }}</p>
          </div>
        </div>

        <div v-if="student.primarySchool">
          <h3 class="font-medium">School</h3>
          <div class="p-3 border rounded">
            <p class="font-medium">{{ student.primarySchool.name }}</p>
            <p class="text-sm text-gray-600">ID: {{ student.primarySchool.id }}</p>
          </div>
        </div>

        <div v-if="student.hasSchedule">
          <h3 class="font-medium">Schedule</h3>
          <p class="text-sm text-green-600">✓ Schedule available ({{ student.schedule?.length || 0 }} entries)</p>
        </div>
      </div>
      <div v-else class="text-gray-500">Not a student account</div>
    </section>

    <!-- Cache Management Example -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">5. Cache Management</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
          <h3 class="font-medium mb-2">Cache Health</h3>
          <div class="flex items-center space-x-2">
            <div class="w-4 h-4 rounded-full" 
                 :class="fullContext.cacheHealth.healthy ? 'bg-green-500' : 'bg-yellow-500'"></div>
            <span>{{ fullContext.cacheHealth.percentage }}% Cached</span>
          </div>
          <p class="text-sm text-gray-600">
            {{ fullContext.cacheHealth.cached }}/{{ fullContext.cacheHealth.total }} segments
          </p>
        </div>

        <div>
          <h3 class="font-medium mb-2">Loading States</h3>
          <div class="space-y-1 text-sm">
            <div v-for="(isLoading, segment) in fullContext.loading" :key="segment" 
                 class="flex justify-between">
              <span>{{ segment }}:</span>
              <span :class="isLoading ? 'text-blue-600' : 'text-green-600'">
                {{ isLoading ? 'Loading...' : 'Ready' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="flex space-x-2">
        <button @click="fullContext.refresh()" 
                :disabled="fullContext.isLoading"
                class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 disabled:opacity-50">
          Refresh All
        </button>
        <button @click="fullContext.clearCache()" 
                class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
          Clear Cache
        </button>
        <button @click="showCacheStats" 
                class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700">
          Show Stats
        </button>
      </div>

      <div v-if="cacheStats" class="mt-4 p-3 bg-gray-50 rounded text-sm">
        <pre>{{ JSON.stringify(cacheStats, null, 2) }}</pre>
      </div>
    </section>

    <!-- Error Handling Example -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">6. Error Handling</h2>
      
      <div v-if="fullContext.hasErrors" class="p-3 bg-red-50 border border-red-200 rounded mb-4">
        <h3 class="font-medium text-red-800">Errors Detected:</h3>
        <ul class="text-red-600 text-sm mt-1">
          <li v-for="(error, segment) in fullContext.errors" :key="segment" v-if="error">
            <strong>{{ segment }}:</strong> {{ error }}
          </li>
        </ul>
      </div>
      
      <div v-else class="p-3 bg-green-50 border border-green-200 rounded">
        <p class="text-green-600 text-sm">✓ No errors detected</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useUserContext, useUser, useTeacher, useStudent } from '@/composables/useUserContext.js';

// Different ways to use the composable
const basicUser = useUser();
const fullContext = useUserContext();
const teacher = useTeacher();
const student = useStudent();

// Cache stats for demonstration
const cacheStats = ref(null);

const showCacheStats = async () => {
  try {
    cacheStats.value = await fullContext.getCacheStats();
  } catch (error) {
    console.error('Error getting cache stats:', error);
  }
};
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
