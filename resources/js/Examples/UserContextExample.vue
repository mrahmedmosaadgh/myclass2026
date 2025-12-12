<!--
  Example Vue Component showing how to use the User Context Store
  
  This component demonstrates:
  - Loading segmented user context
  - Displaying cache status
  - Refreshing specific segments
  - Backward compatibility with legacy auth.user format
-->

<template>
  <div class="user-context-example p-6 bg-white rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-6">User Context Store Example</h2>
    
    <!-- Cache Health Status -->
    <div class="mb-6">
      <h3 class="text-lg font-semibold mb-2">Cache Health</h3>
      <div class="flex items-center space-x-4">
        <div class="flex items-center">
          <div 
            class="w-4 h-4 rounded-full mr-2"
            :class="cacheHealth.healthy ? 'bg-green-500' : 'bg-yellow-500'"
          ></div>
          <span>{{ cacheHealth.percentage }}% Cached</span>
        </div>
        <span class="text-sm text-gray-600">
          ({{ cacheHealth.cached }}/{{ cacheHealth.total }} segments)
        </span>
      </div>
    </div>

    <!-- Loading States -->
    <div v-if="isAnyLoading" class="mb-4">
      <div class="flex items-center text-blue-600">
        <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Loading user context...
      </div>
    </div>

    <!-- Error States -->
    <div v-if="hasAnyError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded">
      <h4 class="text-red-800 font-medium">Errors:</h4>
      <ul class="text-red-600 text-sm mt-1">
        <li v-for="(err, segment) in error" :key="segment" v-if="err">
          {{ segment }}: {{ err }}
        </li>
      </ul>
    </div>

    <!-- User Context Segments -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
      
      <!-- Profile Segment -->
      <div class="border rounded-lg p-4">
        <div class="flex justify-between items-center mb-2">
          <h4 class="font-medium">Profile</h4>
          <button 
            @click="refreshSegment('profile')"
            :disabled="loading.profile"
            class="text-blue-600 hover:text-blue-800 text-sm"
          >
            Refresh
          </button>
        </div>
        <div v-if="userProfile" class="text-sm">
          <p><strong>Name:</strong> {{ userProfile.name }}</p>
          <p><strong>Email:</strong> {{ userProfile.email }}</p>
          <p><strong>Role:</strong> {{ userProfile.user_role }}</p>
        </div>
        <div v-else class="text-gray-500 text-sm">No profile data</div>
      </div>

      <!-- Permissions Segment -->
      <div class="border rounded-lg p-4">
        <div class="flex justify-between items-center mb-2">
          <h4 class="font-medium">Permissions</h4>
          <button 
            @click="refreshSegment('permissions')"
            :disabled="loading.permissions"
            class="text-blue-600 hover:text-blue-800 text-sm"
          >
            Refresh
          </button>
        </div>
        <div v-if="userPermissions" class="text-sm">
          <p><strong>Roles:</strong></p>
          <ul class="list-disc list-inside ml-2">
            <li v-for="role in userPermissions.roles" :key="role">{{ role }}</li>
          </ul>
        </div>
        <div v-else class="text-gray-500 text-sm">No permissions data</div>
      </div>

      <!-- School Segment -->
      <div class="border rounded-lg p-4">
        <div class="flex justify-between items-center mb-2">
          <h4 class="font-medium">School</h4>
          <button 
            @click="refreshSegment('school')"
            :disabled="loading.school"
            class="text-blue-600 hover:text-blue-800 text-sm"
          >
            Refresh
          </button>
        </div>
        <div v-if="userSchool" class="text-sm">
          <p><strong>Schools:</strong> {{ userSchool.schools?.length || 0 }}</p>
        </div>
        <div v-else class="text-gray-500 text-sm">No school data</div>
      </div>

      <!-- Classroom Segment -->
      <div class="border rounded-lg p-4">
        <div class="flex justify-between items-center mb-2">
          <h4 class="font-medium">Classroom</h4>
          <button 
            @click="refreshSegment('classroom')"
            :disabled="loading.classroom"
            class="text-blue-600 hover:text-blue-800 text-sm"
          >
            Refresh
          </button>
        </div>
        <div v-if="userClassroom" class="text-sm">
          <p v-if="userClassroom.teacher"><strong>Teacher:</strong> Yes</p>
          <p v-if="userClassroom.classroom"><strong>Classroom:</strong> Available</p>
        </div>
        <div v-else class="text-gray-500 text-sm">No classroom data</div>
      </div>

      <!-- Schedule Segment -->
      <div class="border rounded-lg p-4">
        <div class="flex justify-between items-center mb-2">
          <h4 class="font-medium">Schedule</h4>
          <button 
            @click="refreshSegment('schedule')"
            :disabled="loading.schedule"
            class="text-blue-600 hover:text-blue-800 text-sm"
          >
            Refresh
          </button>
        </div>
        <div v-if="userSchedule" class="text-sm">
          <p><strong>Schedule:</strong> Available</p>
        </div>
        <div v-else class="text-gray-500 text-sm">No schedule data</div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex space-x-4 mb-6">
      <button 
        @click="loadAllSegments(true)"
        :disabled="isAnyLoading"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
      >
        Refresh All
      </button>
      
      <button 
        @click="clearCache"
        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
      >
        Clear Cache
      </button>
    </div>

    <!-- Legacy Compatibility -->
    <div class="border-t pt-4">
      <h3 class="text-lg font-semibold mb-2">Legacy Compatibility</h3>
      <p class="text-sm text-gray-600 mb-2">
        The store provides backward compatibility with the existing auth.user format:
      </p>
      <div v-if="getLegacyUserData" class="bg-gray-50 p-3 rounded text-sm">
        <pre>{{ JSON.stringify(getLegacyUserData, null, 2) }}</pre>
      </div>
      <div v-else class="text-gray-500 text-sm">
        Legacy user data not available (segments still loading)
      </div>
    </div>
  </div>
</template>

<script setup>
import { useUserContextStore } from '@/Stores/userContextStore.js';

// Use the user context store
const {
  // State
  userProfile,
  userPermissions,
  userSchool,
  userClassroom,
  userSchedule,
  loading,
  error,
  
  // Computed
  isAnyLoading,
  hasAnyError,
  cacheHealth,
  getLegacyUserData,
  
  // Methods
  loadAllSegments,
  refreshSegment,
  clearCache
} = useUserContextStore();
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
