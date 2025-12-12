<!--
  Offline System Test Page

  This page demonstrates the offline-first education management system.
  Use this to test and verify that the offline functionality works correctly.
-->

<template>
  <Head title="Offline System Test" />

  <div class="offline-test-page max-w-4xl mx-auto p-6">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Offline-First System Test</h1>
      <p class="text-gray-600">
        Test the offline capabilities of the education management system.
        Try going offline (DevTools → Network → Offline) and see how the system behaves.
      </p>
    </div>

    <!-- System Status -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <!-- Network Status -->
      <div class="bg-white rounded-lg shadow p-6 border-l-4"
            :class="isOnline ? 'border-green-500' : 'border-red-500'">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div :class="isOnline ? 'bg-green-500' : 'bg-red-500'"
                 class="w-8 h-8 rounded-full flex items-center justify-center">
              <span class="text-white text-sm">{{ isOnline ? '🌐' : '📴' }}</span>
            </div>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Network Status</h3>
            <p :class="isOnline ? 'text-green-600' : 'text-red-600'" class="font-medium">
               {{ isOnline ? 'Online' : 'Offline' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Database Status -->
      <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="bg-blue-500 w-8 h-8 rounded-full flex items-center justify-center">
              <span class="text-white text-sm">💾</span>
            </div>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Local Database</h3>
            <p class="text-blue-600 font-medium">{{ dbInfo.totalRecords }} records</p>
          </div>
        </div>
      </div>

      <!-- Sync Status -->
      <div class="bg-white rounded-lg shadow p-6 border-l-4"
           :class="syncStatus === 'synced' ? 'border-green-500' : 'border-orange-500'">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div :class="syncStatus === 'synced' ? 'bg-green-500' : 'bg-orange-500'"
                 class="w-8 h-8 rounded-full flex items-center justify-center">
              <span class="text-white text-sm">{{ syncStatus === 'syncing' ? '⏳' : '🔄' }}</span>
            </div>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Sync Status</h3>
            <p :class="syncStatus === 'synced' ? 'text-green-600' : 'text-orange-600'" class="font-medium">
              {{ syncStatusText }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Test Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-xl font-semibold mb-4">Test Actions</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <button @click="createTestLesson"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
          Create Test Lesson
        </button>

        <button @click="createTestStudent"
                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
          Create Test Student
        </button>

        <button @click="submitTestQuiz"
                class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
          Submit Test Quiz
        </button>

        <button @click="manualSync" :disabled="syncStatus === 'syncing'"
                class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 disabled:opacity-50">
          {{ syncStatus === 'syncing' ? 'Syncing...' : 'Manual Sync' }}
        </button>
      </div>
    </div>

    <!-- Data Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Lessons -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Lessons ({{ lessonsArray.length }})</h3>
            <button @click="lessons.loadAll({ forceRefresh: true })"
                    :disabled="lessons.loading"
                    class="text-sm text-blue-600 hover:text-blue-800 disabled:opacity-50">
              {{ lessons.loading ? 'Loading...' : 'Refresh' }}
            </button>
          </div>
        </div>
        <div class="p-6">
          <div v-if="lessons.loading && lessonsArray.length === 0" class="text-center py-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-2 text-sm text-gray-600">Loading lessons...</p>
          </div>

          <div v-else-if="lessonsArray.length === 0" class="text-center py-4 text-gray-500">
            No lessons found. Create a test lesson to get started.
          </div>

          <div v-else class="space-y-3">
            <div v-for="lesson in lessonsArray.slice(0, 5)" :key="lesson.id"
                 class="border rounded p-3">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <h4 class="font-medium text-sm">{{ lesson.title }}</h4>
                  <p class="text-xs text-gray-600 mt-1">{{ lesson.content ? lesson.content.substring(0, 50) + '...' : '' }}</p>
                  <p class="text-xs text-gray-500 mt-1">Course: {{ lesson.course_id }}</p>
                </div>
                <div class="ml-2">
                  <span v-if="lesson.is_dirty"
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                    📤 Pending
                  </span>
                  <span v-else
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    ✅ Synced
                  </span>
                </div>
              </div>
            </div>

            <div v-if="lessonsArray.length > 5" class="text-center text-sm text-gray-500">
              ... and {{ lessonsArray.length - 5 }} more
            </div>
          </div>
        </div>
      </div>

      <!-- Students -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Students ({{ studentsArray.length }})</h3>
            <button @click="students.loadAll({ forceRefresh: true })"
                    :disabled="students.loading"
                    class="text-sm text-blue-600 hover:text-blue-800 disabled:opacity-50">
              {{ students.loading ? 'Loading...' : 'Refresh' }}
            </button>
          </div>
        </div>
        <div class="p-6">
          <div v-if="students.loading && studentsArray.length === 0" class="text-center py-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-2 text-sm text-gray-600">Loading students...</p>
          </div>

          <div v-else-if="studentsArray.length === 0" class="text-center py-4 text-gray-500">
            No students found. Create a test student to get started.
          </div>

          <div v-else class="space-y-3">
            <div v-for="student in studentsArray.slice(0, 5)" :key="student.id"
                 class="border rounded p-3">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <h4 class="font-medium text-sm">{{ student.name }}</h4>
                  <p class="text-xs text-gray-600 mt-1">{{ student.email }}</p>
                  <p class="text-xs text-gray-500 mt-1">Class: {{ student.class_id }}</p>
                </div>
                <div class="ml-2">
                  <span v-if="student.is_dirty"
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                    📤 Pending
                  </span>
                  <span v-else
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    ✅ Synced
                  </span>
                </div>
              </div>
            </div>

            <div v-if="studentsArray.length > 5" class="text-center text-sm text-gray-500">
              ... and {{ studentsArray.length - 5 }} more
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sync Queue Info -->
    <div class="mt-8 bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Sync Queue Status</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-blue-600">{{ queueStats.pending }}</div>
          <div class="text-sm text-gray-600">Pending</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-red-600">{{ queueStats.failed }}</div>
          <div class="text-sm text-gray-600">Failed</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-orange-600">{{ queueStats.conflicts }}</div>
          <div class="text-sm text-gray-600">Conflicts</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-gray-600">{{ queueStats.total }}</div>
          <div class="text-sm text-gray-600">Total</div>
        </div>
      </div>
    </div>

    <!-- Instructions -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
      <h3 class="text-lg font-medium text-blue-900 mb-3">Testing Instructions</h3>
      <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800">
        <li>Open browser DevTools (F12)</li>
        <li>Go to Network tab</li>
        <li>Check "Offline" checkbox to simulate offline mode</li>
        <li>Try creating lessons, students, or submitting quiz answers</li>
        <li>Notice how data is saved locally and marked as "Pending"</li>
        <li>Uncheck "Offline" to go back online</li>
        <li>Watch as pending data automatically syncs to the server</li>
        <li>Refresh the page to see that data persists</li>
      </ol>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useOfflineResource } from '@/offline/useOfflineResource';
import { getSyncQueueStats, processQueue } from '@/offline/syncQueue';
import { db } from '@/offline/dexie';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';

// Define layout
defineOptions({
  layout: AppLayoutDefault
});

// Initialize resources
const lessons = useOfflineResource('lessons');
const students = useOfflineResource('students');
const quizAnswers = useOfflineResource('quiz_answers');

// Get network status from any resource (they all share the same status)
const { isOnline, syncStatus } = lessons;

// Component state
const dbInfo = ref({ totalRecords: 0 });
const queueStats = ref({ pending: 0, failed: 0, conflicts: 0, total: 0 });

// Computed properties for safe array access
const lessonsArray = computed(() => Array.isArray(lessons.data.value) ? lessons.data.value : []);
const studentsArray = computed(() => Array.isArray(students.data.value) ? students.data.value : []);

const syncStatusText = computed(() => {
  switch (syncStatus.value) {
    case 'syncing': return 'Syncing...';
    case 'stale': return 'Data may be outdated';
    case 'error': return 'Sync error';
    default: return 'All synced';
  }
});

// Load initial data
onMounted(() => {
  loadAllData();
  updateStats();

  // Update stats periodically
  setInterval(updateStats, 5000);
});

function loadAllData() {
  Promise.all([
    lessons.loadAll(),
    students.loadAll(),
    quizAnswers.loadAll()
  ]).catch(err => {
    console.error('Failed to load data:', err);
  });
}

function updateStats() {
  // Update database info
  db.getDatabaseInfo().then(info => {
    dbInfo.value = info;
  });

  // Update queue stats
  getSyncQueueStats().then(stats => {
    queueStats.value = stats;
  });
}

// Test actions
function createTestLesson() {
  const lessonData = {
    title: `Test Lesson ${Date.now()}`,
    content: `This is a test lesson created at ${new Date().toLocaleString()}. It demonstrates offline functionality.`,
    course_id: Math.floor(Math.random() * 10) + 1,
    teacher_id: Math.floor(Math.random() * 5) + 1
  };

  lessons.create(lessonData)
    .then(() => {
      console.log('Test lesson created');
      updateStats();
    })
    .catch(err => {
      console.error('Failed to create test lesson:', err);
    });
}

function createTestStudent() {
  const studentData = {
    name: `Test Student ${Date.now()}`,
    email: `student${Date.now()}@example.com`,
    class_id: Math.floor(Math.random() * 5) + 1,
    grade_id: Math.floor(Math.random() * 12) + 1
  };

  students.create(studentData)
    .then(() => {
      console.log('Test student created');
      updateStats();
    })
    .catch(err => {
      console.error('Failed to create test student:', err);
    });
}

function submitTestQuiz() {
  const quizData = {
    quiz_id: Math.floor(Math.random() * 10) + 1,
    student_id: Math.floor(Math.random() * 100) + 1,
    answers: {
      q1: 'A',
      q2: 'B',
      q3: 'C'
    },
    score: Math.floor(Math.random() * 100),
    submitted_at: new Date().toISOString()
  };

  quizAnswers.create(quizData)
    .then(() => {
      console.log('Test quiz submitted');
      updateStats();
    })
    .catch(err => {
      console.error('Failed to submit test quiz:', err);
    });
}

function manualSync() {
  processQueue()
    .then(results => {
      console.log('Manual sync completed:', results);
      updateStats();

      // Reload data after sync
      if (results.processed > 0) {
        loadAllData();
      }
    })
    .catch(err => {
      console.error('Manual sync failed:', err);
    });
}
</script>

<style scoped>
/* Add any custom styles here */
</style>
