<!--
  NEW PATTERN: User Card using useUser composable
  
  This represents the new way of accessing user data through the composable.
  Benefits of this approach:
  - Built-in loading states
  - Error handling
  - Offline support
  - Clean role checking
  - Reactive updates
-->

<template>
  <div class="user-card">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center space-x-3">
      <div class="w-10 h-10 bg-gray-200 rounded-full animate-pulse"></div>
      <div class="space-y-2">
        <div class="h-4 bg-gray-200 rounded animate-pulse w-24"></div>
        <div class="h-3 bg-gray-200 rounded animate-pulse w-32"></div>
      </div>
    </div>
    
    <!-- Error State -->
    <div v-else-if="hasErrors" class="text-red-600 text-sm">
      <div class="flex items-center space-x-2">
        <span>⚠️</span>
        <span>Failed to load user data</span>
      </div>
    </div>
    
    <!-- Success State -->
    <div v-else-if="user" class="flex items-center space-x-3">
      <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
        <span class="text-white font-medium">{{ user.name?.charAt(0) }}</span>
      </div>
      <div>
        <h3 class="font-medium">{{ user.name }}</h3>
        <p class="text-sm text-gray-600">{{ user.email }}</p>
        <div class="flex space-x-1 mt-1">
          <span v-if="isTeacher" class="badge bg-blue-100 text-blue-800">Teacher</span>
          <span v-if="isStudent" class="badge bg-green-100 text-green-800">Student</span>
          <span v-if="isAdmin" class="badge bg-red-100 text-red-800">Admin</span>
          <span v-for="role in roles" :key="role" class="badge bg-purple-100 text-purple-800">{{ role }}</span>
        </div>
      </div>
    </div>
    
    <!-- No User State -->
    <div v-else class="text-gray-500 text-sm">
      No user data available
    </div>
    
    <!-- Benefits of new pattern -->
    <div class="mt-3 text-xs text-green-600">
      <div>✅ Loading states handled</div>
      <div>✅ Error handling built-in</div>
      <div>✅ Offline support included</div>
      <div>✅ Reactive role checking</div>
    </div>
  </div>
</template>

<script setup>
import { useUser } from '@/composables/useUserContext.js';

// NEW PATTERN: Using the composable
const { 
  user, 
  isTeacher, 
  isStudent, 
  isAdmin, 
  roles, 
  isLoading, 
  hasErrors 
} = useUser();
</script>

<style scoped>
.user-card {
  @apply p-4 border rounded-lg bg-white;
}

.badge {
  @apply px-2 py-1 rounded text-xs font-medium;
}
</style>
