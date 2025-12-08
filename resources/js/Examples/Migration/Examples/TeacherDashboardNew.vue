<!--
  NEW PATTERN: Teacher Dashboard using useTeacher composable
  
  Benefits of this approach:
  - Teacher-specific composable
  - Automatic loading states
  - Segmented data loading
  - Error handling
  - Offline support
-->

<template>
  <div class="teacher-dashboard">
    <div v-if="isTeacher">
      <h2 class="text-lg font-semibold mb-4">Teacher Dashboard</h2>
      
      <!-- Loading State -->
      <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="i in 4" :key="i" class="card">
          <div class="animate-pulse">
            <div class="h-4 bg-gray-200 rounded mb-2"></div>
            <div class="h-3 bg-gray-200 rounded mb-1"></div>
            <div class="h-3 bg-gray-200 rounded"></div>
          </div>
        </div>
      </div>
      
      <!-- Error State -->
      <div v-else-if="hasErrors" class="card bg-red-50 border-red-200">
        <h3 class="font-medium text-red-800 mb-2">Error Loading Data</h3>
        <ul class="text-red-600 text-sm">
          <li v-for="(error, segment) in errors" :key="segment" v-if="error">
            {{ segment }}: {{ error }}
          </li>
        </ul>
      </div>
      
      <!-- Success State -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- User Info -->
        <div class="card">
          <h3 class="font-medium mb-2">Teacher Information</h3>
          <div v-if="user">
            <p><strong>Name:</strong> {{ user.name }}</p>
            <p><strong>Email:</strong> {{ user.email }}</p>
            <p><strong>Role:</strong> {{ user.user_role }}</p>
          </div>
        </div>
        
        <!-- Classrooms -->
        <div class="card">
          <h3 class="font-medium mb-2">Your Classrooms</h3>
          <div v-if="classrooms && classrooms.length > 0">
            <div v-for="classroom in classrooms" :key="classroom.id" class="classroom-item">
              <div class="font-medium">{{ classroom.name }}</div>
              <div class="text-xs text-gray-600">ID: {{ classroom.id }}</div>
            </div>
          </div>
          <div v-else class="text-gray-500 text-sm">
            No classrooms assigned
          </div>
        </div>
        
        <!-- Schools -->
        <div class="card">
          <h3 class="font-medium mb-2">Schools</h3>
          <div v-if="schools && schools.length > 0">
            <div v-for="school in schools" :key="school.id" class="school-item">
              <div class="font-medium">{{ school.name }}</div>
              <div class="text-xs text-gray-600">ID: {{ school.id }}</div>
            </div>
          </div>
          <div v-else class="text-gray-500 text-sm">
            No schools assigned
          </div>
        </div>
        
        <!-- Schedule -->
        <div class="card">
          <h3 class="font-medium mb-2">Schedule</h3>
          <div v-if="hasSchedule">
            <div class="text-green-600 text-sm">
              ✅ Schedule available ({{ schedule?.length || 0 }} entries)
            </div>
          </div>
          <div v-else class="text-gray-500 text-sm">
            No schedule available
          </div>
        </div>
        
        <!-- Benefits -->
        <div class="card bg-green-50 border-green-200 md:col-span-2">
          <h3 class="font-medium mb-2 text-green-800">Benefits of New Pattern</h3>
          <ul class="text-green-600 text-sm space-y-1">
            <li>✅ Teacher-specific composable with optimized loading</li>
            <li>✅ Automatic loading states for better UX</li>
            <li>✅ Segmented data loading (only loads what's needed)</li>
            <li>✅ Built-in error handling and recovery</li>
            <li>✅ Offline support with 7-day caching</li>
            <li>✅ Reactive updates when data changes</li>
          </ul>
        </div>
      </div>
      
      <!-- Actions -->
      <div class="mt-4 flex space-x-2">
        <button @click="refresh" :disabled="isLoading" 
                class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 disabled:opacity-50">
          {{ isLoading ? 'Loading...' : 'Refresh Data' }}
        </button>
        <button @click="refreshSegment('classroom')" :disabled="loading.classroom"
                class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700 disabled:opacity-50">
          Refresh Classrooms
        </button>
      </div>
    </div>
    
    <div v-else class="text-gray-500">
      Not a teacher account
    </div>
  </div>
</template>

<script setup>
import { useTeacher } from '@/composables/useUserContext.js';

// NEW PATTERN: Using teacher-specific composable
const { 
  user,
  isTeacher,
  classrooms,
  schools,
  schedule,
  hasSchedule,
  isLoading,
  hasErrors,
  errors,
  loading,
  refresh,
  refreshSegment
} = useTeacher();
</script>

<style scoped>
.teacher-dashboard {
  @apply p-4;
}

.card {
  @apply p-4 border rounded-lg bg-white;
}

.classroom-item, .school-item {
  @apply p-2 bg-gray-50 rounded mb-1;
}
</style>
