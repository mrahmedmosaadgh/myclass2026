<!--
  Complete Lesson Management Component
  
  This is a real-world example showing how to use the offline-first system
  for managing lessons in an education management application.
  
  Features:
  - Full CRUD operations (Create, Read, Update, Delete)
  - Offline support with automatic sync
  - Network status indicators
  - Optimistic updates
  - Error handling
  - Loading states
-->

<template>
  <div class="lesson-manager max-w-6xl mx-auto p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Lesson Management</h1>
      
      <!-- Network & Sync Status -->
      <div class="flex items-center space-x-4">
        <!-- Network Status -->
        <div class="flex items-center space-x-2">
          <div :class="isOnline ? 'bg-green-500' : 'bg-red-500'" class="w-3 h-3 rounded-full"></div>
          <span class="text-sm font-medium">{{ isOnline ? 'Online' : 'Offline' }}</span>
        </div>
        
        <!-- Sync Status -->
        <div v-if="syncStatus !== 'synced'" class="flex items-center space-x-2">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500" v-if="syncStatus === 'syncing'"></div>
          <span class="text-sm" :class="{
            'text-blue-600': syncStatus === 'syncing',
            'text-orange-600': syncStatus === 'stale',
            'text-red-600': syncStatus === 'error'
          }">
            {{ syncStatusText }}
          </span>
        </div>
        
        <!-- Manual Sync Button -->
        <button @click="manualSync" :disabled="syncStatus === 'syncing'"
                class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50">
          {{ syncStatus === 'syncing' ? 'Syncing...' : 'Sync Now' }}
        </button>
      </div>
    </div>

    <!-- Offline Banner -->
    <div v-if="!isOnline" class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm">
            You're currently offline. Any changes you make will be saved locally and automatically synced when your connection is restored.
          </p>
        </div>
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
      <strong>Error:</strong> {{ error.message }}
    </div>

    <!-- Actions Bar -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center space-x-4">
        <!-- Search -->
        <div class="relative">
          <input v-model="searchTerm" type="text" placeholder="Search lessons..."
                 class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
        </div>
        
        <!-- Filter -->
        <select v-model="filterCourse" class="border border-gray-300 rounded-lg px-3 py-2">
          <option value="">All Courses</option>
          <option v-for="course in uniqueCourses" :key="course" :value="course">
            Course {{ course }}
          </option>
        </select>
      </div>
      
      <!-- Add Lesson Button -->
      <button @click="openCreateForm" 
              class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center space-x-2">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Add Lesson</span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading && data.length === 0" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
      <p class="mt-4 text-gray-600">Loading lessons...</p>
    </div>

    <!-- Lessons Grid -->
    <div v-else-if="filteredLessons.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="lesson in filteredLessons" :key="lesson.id" 
           class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow">
        <div class="p-6">
          <!-- Lesson Header -->
          <div class="flex justify-between items-start mb-3">
            <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">{{ lesson.title }}</h3>
            
            <!-- Sync Status Indicator -->
            <div class="flex-shrink-0 ml-2">
              <span v-if="lesson.is_dirty" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                📤 Pending
              </span>
              <span v-else class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                ✅ Synced
              </span>
            </div>
          </div>
          
          <!-- Lesson Content -->
          <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ lesson.content }}</p>
          
          <!-- Lesson Meta -->
          <div class="text-xs text-gray-500 mb-4 space-y-1">
            <div>Course ID: {{ lesson.course_id }}</div>
            <div>Teacher ID: {{ lesson.teacher_id }}</div>
            <div v-if="lesson.updated_at">Updated: {{ formatDate(lesson.updated_at) }}</div>
          </div>
          
          <!-- Actions -->
          <div class="flex justify-end space-x-2">
            <button @click="editLesson(lesson)" 
                    class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600">
              Edit
            </button>
            <button @click="deleteLesson(lesson)" 
                    class="px-3 py-1 text-sm bg-red-500 text-white rounded hover:bg-red-600">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No lessons found</h3>
      <p class="mt-1 text-sm text-gray-500">
        {{ searchTerm || filterCourse ? 'Try adjusting your search or filter.' : 'Get started by creating your first lesson.' }}
      </p>
      <div class="mt-6">
        <button @click="openCreateForm" 
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
          <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add Lesson
        </button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <!-- Modal Header -->
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              {{ editingLesson ? 'Edit Lesson' : 'Create New Lesson' }}
            </h3>
            <button @click="closeForm" class="text-gray-400 hover:text-gray-600">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <!-- Form -->
          <form @submit.prevent="saveLesson" class="space-y-4">
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
              <input v-model="form.title" type="text" id="title" required
                     class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
              <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
              <textarea v-model="form.content" id="content" rows="4" required
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="course_id" class="block text-sm font-medium text-gray-700">Course ID</label>
                <input v-model="form.course_id" type="number" id="course_id" required
                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
              </div>
              
              <div>
                <label for="teacher_id" class="block text-sm font-medium text-gray-700">Teacher ID</label>
                <input v-model="form.teacher_id" type="number" id="teacher_id" required
                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
              </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" @click="closeForm" 
                      class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" :disabled="formLoading"
                      class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:opacity-50">
                {{ formLoading ? 'Saving...' : (editingLesson ? 'Update' : 'Create') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useOfflineResource } from '@/offline/useOfflineResource';

// Initialize offline resource management for lessons
const {
  data,
  loading,
  error,
  syncStatus,
  isOnline,
  loadAll,
  create,
  update,
  delete: deleteItem,
  sync
} = useOfflineResource('lessons');

// Component state
const showForm = ref(false);
const editingLesson = ref(null);
const formLoading = ref(false);
const searchTerm = ref('');
const filterCourse = ref('');

// Form data
const form = ref({
  title: '',
  content: '',
  course_id: '',
  teacher_id: ''
});

// Computed properties
const syncStatusText = computed(() => {
  switch (syncStatus.value) {
    case 'syncing': return 'Syncing...';
    case 'stale': return 'Data may be outdated';
    case 'error': return 'Sync error';
    default: return 'Synced';
  }
});

const uniqueCourses = computed(() => {
  return [...new Set(data.value.map(lesson => lesson.course_id))].sort();
});

const filteredLessons = computed(() => {
  let filtered = data.value;
  
  if (searchTerm.value) {
    const search = searchTerm.value.toLowerCase();
    filtered = filtered.filter(lesson => 
      lesson.title.toLowerCase().includes(search) ||
      lesson.content.toLowerCase().includes(search)
    );
  }
  
  if (filterCourse.value) {
    filtered = filtered.filter(lesson => lesson.course_id == filterCourse.value);
  }
  
  return filtered;
});

// Lifecycle
onMounted(() => {
  loadAll().catch(err => {
    console.error('Failed to load lessons:', err);
  });
});

// Methods
function openCreateForm() {
  editingLesson.value = null;
  form.value = {
    title: '',
    content: '',
    course_id: '',
    teacher_id: ''
  };
  showForm.value = true;
}

function editLesson(lesson) {
  editingLesson.value = lesson;
  form.value = {
    title: lesson.title,
    content: lesson.content,
    course_id: lesson.course_id,
    teacher_id: lesson.teacher_id
  };
  showForm.value = true;
}

function closeForm() {
  showForm.value = false;
  editingLesson.value = null;
  form.value = {
    title: '',
    content: '',
    course_id: '',
    teacher_id: ''
  };
}

function saveLesson() {
  formLoading.value = true;
  
  const lessonData = { ...form.value };
  
  const savePromise = editingLesson.value 
    ? update(editingLesson.value.id, lessonData)
    : create(lessonData);
  
  savePromise
    .then(() => {
      closeForm();
    })
    .catch(err => {
      console.error('Failed to save lesson:', err);
    })
    .finally(() => {
      formLoading.value = false;
    });
}

function deleteLesson(lesson) {
  if (confirm(`Are you sure you want to delete "${lesson.title}"?`)) {
    deleteItem(lesson.id)
      .catch(err => {
        console.error('Failed to delete lesson:', err);
      });
  }
}

function manualSync() {
  sync().catch(err => {
    console.error('Manual sync failed:', err);
  });
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
