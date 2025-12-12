<!--
  MIGRATED EXAMPLE: Teacher Dashboard using new user context system
  
  This is a real migration of the existing TeacherDashboard.vue component
  to demonstrate how the new system works in practice.
  
  Original file: resources/js/Pages/Dashboard/Components/TeacherDashboard.vue
-->

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

<template>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <!-- Header with loading state -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Teacher Dashboard</h3>
            <div v-if="isLoading" class="flex items-center text-blue-600">
                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading teacher data...
            </div>
        </div>

        <!-- Error State -->
        <div v-if="hasErrors" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <h4 class="text-red-800 font-medium mb-2">Error Loading Data</h4>
            <ul class="text-red-600 text-sm space-y-1">
                <li v-for="(error, segment) in errors" :key="segment" v-if="error">
                    <strong>{{ segment }}:</strong> {{ error }}
                </li>
            </ul>
            <button @click="refresh" class="mt-2 px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                Retry
            </button>
        </div>

        <!-- Main Content -->
        <div v-else-if="isTeacher">
            <!-- Welcome Message -->
            <div class="bg-blue-50 p-4 rounded-lg mb-6">
                <p class="text-blue-700">
                    <span v-if="user">Welcome back, {{ user.name }}!</span>
                    <span v-else>Welcome back!</span>
                    Here's your teacher dashboard with all your classroom information.
                </p>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <!-- Classrooms Count -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 rounded-lg text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100">Your Classrooms</p>
                            <p class="text-2xl font-bold">
                                {{ loading.classroom ? '...' : (classrooms?.length || 0) }}
                            </p>
                        </div>
                        <div class="text-blue-200">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Schools Count -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 p-4 rounded-lg text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100">Schools</p>
                            <p class="text-2xl font-bold">
                                {{ loading.school ? '...' : (schools?.length || 0) }}
                            </p>
                        </div>
                        <div class="text-green-200">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 2h8v8H6V6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Schedule Status -->
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-4 rounded-lg text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100">Schedule</p>
                            <p class="text-2xl font-bold">
                                {{ loading.schedule ? '...' : (hasSchedule ? 'Active' : 'None') }}
                            </p>
                        </div>
                        <div class="text-purple-200">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Classrooms Detail -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-lg font-medium text-gray-900">Your Classrooms</h4>
                        <button @click="refreshSegment('classroom')" 
                                :disabled="loading.classroom"
                                class="text-blue-600 hover:text-blue-800 text-sm disabled:opacity-50">
                            {{ loading.classroom ? 'Loading...' : 'Refresh' }}
                        </button>
                    </div>
                    
                    <div v-if="loading.classroom" class="space-y-2">
                        <div v-for="i in 3" :key="i" class="h-4 bg-gray-200 rounded animate-pulse"></div>
                    </div>
                    
                    <div v-else-if="classrooms && classrooms.length > 0" class="space-y-2">
                        <div v-for="classroom in classrooms" :key="classroom.id" 
                             class="p-3 bg-gray-50 rounded-lg">
                            <div class="font-medium text-gray-900">{{ classroom.name }}</div>
                            <div class="text-sm text-gray-600">
                                ID: {{ classroom.id }}
                                <span v-if="classroom.grade" class="ml-2">• Grade: {{ classroom.grade }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="text-gray-500 text-center py-4">
                        No classrooms assigned yet
                    </div>
                </div>

                <!-- Schools Detail -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-lg font-medium text-gray-900">Your Schools</h4>
                        <button @click="refreshSegment('school')" 
                                :disabled="loading.school"
                                class="text-blue-600 hover:text-blue-800 text-sm disabled:opacity-50">
                            {{ loading.school ? 'Loading...' : 'Refresh' }}
                        </button>
                    </div>
                    
                    <div v-if="loading.school" class="space-y-2">
                        <div v-for="i in 2" :key="i" class="h-4 bg-gray-200 rounded animate-pulse"></div>
                    </div>
                    
                    <div v-else-if="schools && schools.length > 0" class="space-y-2">
                        <div v-for="school in schools" :key="school.id" 
                             class="p-3 bg-gray-50 rounded-lg">
                            <div class="font-medium text-gray-900">{{ school.name }}</div>
                            <div class="text-sm text-gray-600">
                                ID: {{ school.id }}
                                <span v-if="school.address" class="ml-2">• {{ school.address }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="text-gray-500 text-center py-4">
                        No schools assigned
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 flex flex-wrap gap-2">
                <button @click="refresh" 
                        :disabled="isLoading"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                    {{ isLoading ? 'Refreshing...' : 'Refresh All Data' }}
                </button>
                
                <a href="/teacher/classes" 
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Manage Classes
                </a>
                
                <a href="/teacher/schedule" 
                   class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    View Schedule
                </a>
            </div>

            <!-- Migration Benefits Notice -->
            <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <h4 class="font-medium text-green-800 mb-2">✨ Enhanced with New User Context System</h4>
                <ul class="text-green-700 text-sm space-y-1">
                    <li>• Faster loading with segmented data</li>
                    <li>• Works offline with 7-day caching</li>
                    <li>• Individual segment refresh capabilities</li>
                    <li>• Better error handling and recovery</li>
                    <li>• Automatic loading states</li>
                </ul>
            </div>
        </div>

        <!-- Not a teacher -->
        <div v-else class="text-center py-8">
            <div class="text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <p>This dashboard is only available for teacher accounts.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>
