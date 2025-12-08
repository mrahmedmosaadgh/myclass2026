<!--
  OLD PATTERN: User Card using traditional usePage().props.auth.user
  
  This represents the old way of accessing user data directly from Inertia props.
  Issues with this approach:
  - No loading states
  - No error handling
  - No offline support
  - Manual role checking
-->

<template>
  <div class="user-card">
    <div class="flex items-center space-x-3">
      <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
        <span class="text-gray-600 font-medium">{{ user?.name?.charAt(0) || '?' }}</span>
      </div>
      <div>
        <h3 class="font-medium">{{ user?.name || 'Unknown User' }}</h3>
        <p class="text-sm text-gray-600">{{ user?.email || 'No email' }}</p>
        <div class="flex space-x-1 mt-1">
          <span v-if="user?.user_role === 'teacher'" class="badge bg-blue-100 text-blue-800">Teacher</span>
          <span v-if="user?.user_role === 'student'" class="badge bg-green-100 text-green-800">Student</span>
          <span v-if="user?.user_role === 'admin'" class="badge bg-red-100 text-red-800">Admin</span>
          <span v-if="!user" class="badge bg-gray-100 text-gray-800">Not Loaded</span>
        </div>
      </div>
    </div>
    
    <!-- Issues with old pattern -->
    <div class="mt-3 text-xs text-red-600">
      <div>❌ No loading state</div>
      <div>❌ No error handling</div>
      <div>❌ No offline support</div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

// OLD PATTERN: Direct access to Inertia props
const user = computed(() => usePage().props.auth.user);
</script>

<style scoped>
.user-card {
  @apply p-4 border rounded-lg bg-white;
}

.badge {
  @apply px-2 py-1 rounded text-xs font-medium;
}
</style>
