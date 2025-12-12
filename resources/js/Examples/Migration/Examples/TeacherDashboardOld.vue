<!--
  OLD PATTERN: Teacher Dashboard using traditional approach
  
  Issues with this approach:
  - Manual role checking
  - No loading states for classroom data
  - All data loaded at once
  - No error handling
-->

<template>
  <div class="teacher-dashboard">
    <div v-if="isTeacher">
      <h2 class="text-lg font-semibold mb-4">Teacher Dashboard</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- User Info -->
        <div class="card">
          <h3 class="font-medium mb-2">Teacher Information</h3>
          <p><strong>Name:</strong> {{ user?.name || 'Unknown' }}</p>
          <p><strong>Email:</strong> {{ user?.email || 'Unknown' }}</p>
        </div>
        
        <!-- Classrooms -->
        <div class="card">
          <h3 class="font-medium mb-2">Your Classrooms</h3>
          <div v-if="user?.classroom && user.classroom.length > 0">
            <div v-for="classroom in user.classroom" :key="classroom.id" class="classroom-item">
              {{ classroom.name }}
            </div>
          </div>
          <div v-else class="text-gray-500 text-sm">
            No classrooms assigned
          </div>
        </div>
        
        <!-- Schools -->
        <div class="card">
          <h3 class="font-medium mb-2">Schools</h3>
          <div v-if="user?.schools && user.schools.length > 0">
            <div v-for="school in user.schools" :key="school.id" class="school-item">
              {{ school.name }}
            </div>
          </div>
          <div v-else class="text-gray-500 text-sm">
            No schools assigned
          </div>
        </div>
        
        <!-- Issues -->
        <div class="card bg-red-50 border-red-200">
          <h3 class="font-medium mb-2 text-red-800">Issues with Old Pattern</h3>
          <ul class="text-red-600 text-sm space-y-1">
            <li>❌ No loading states</li>
            <li>❌ Manual role checking</li>
            <li>❌ All data loaded at once</li>
            <li>❌ No error handling</li>
            <li>❌ No offline support</li>
          </ul>
        </div>
      </div>
    </div>
    
    <div v-else class="text-gray-500">
      Not a teacher account
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

// OLD PATTERN: Direct access and manual role checking
const user = computed(() => usePage().props.auth.user);
const isTeacher = computed(() => 
  user.value?.user_role === 'teacher' || 
  user.value?.roles?.includes('teacher')
);
</script>

<style scoped>
.teacher-dashboard {
  @apply p-4;
}

.card {
  @apply p-4 border rounded-lg bg-white;
}

.classroom-item, .school-item {
  @apply p-2 bg-gray-50 rounded mb-1 text-sm;
}
</style>
