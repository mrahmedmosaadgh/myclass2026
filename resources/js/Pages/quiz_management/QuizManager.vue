<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Quiz Management</h1>
        <p class="mt-2 text-sm text-gray-600">Create and manage quizzes for your lessons</p>
      </div>

      <!-- Action Bar -->
      <div class="mb-6 flex justify-between items-center">
        <div class="flex gap-4">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search quizzes..."
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <select
            v-model="filterStatus"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
          </select>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Quiz
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Loading quizzes...</p>
      </div>

      <!-- Quiz List -->
      <div v-else-if="filteredQuizzes.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="quiz in filteredQuizzes"
          :key="quiz.id"
          class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6"
        >
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ quiz.name }}</h3>
              <p class="text-sm text-gray-600 line-clamp-2">{{ quiz.description || 'No description' }}</p>
            </div>
            <span
              :class="getStatusClass(quiz.status)"
              class="px-2 py-1 text-xs font-medium rounded-full"
            >
              {{ quiz.status }}
            </span>
          </div>

          <div class="space-y-2 mb-4 text-sm text-gray-600">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ quiz.questions_count || 0 }} questions</span>
            </div>
            <div v-if="quiz.time_limit_minutes" class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ quiz.time_limit_minutes }} minutes</span>
            </div>
            <div v-if="quiz.grade" class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              <span>{{ quiz.grade.name }}</span>
            </div>
          </div>

          <div class="flex gap-2">
            <button
              @click="editQuiz(quiz)"
              class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium"
            >
              Edit
            </button>
            <button
              @click="viewQuiz(quiz)"
              class="flex-1 px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium"
            >
              View
            </button>
            <button
              @click="deleteQuiz(quiz)"
              class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm font-medium"
            >
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12 bg-white rounded-lg shadow">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No quizzes found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new quiz.</p>
        <div class="mt-6">
          <button
            @click="openCreateModal"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Quiz
          </button>
        </div>
      </div>

      <!-- Create/Edit Modal -->
      <div
        v-if="showModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click.self="closeModal"
      >
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900">
                {{ editingQuiz ? 'Edit Quiz' : 'Create New Quiz' }}
              </h2>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="saveQuiz" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quiz Name *</label>
                <input
                  v-model="quizForm.name"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Enter quiz name"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                  v-model="quizForm.description"
                  rows="3"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Enter quiz description"
                ></textarea>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select
                    v-model="quizForm.status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Time Limit (minutes)</label>
                  <input
                    v-model.number="quizForm.time_limit_minutes"
                    type="number"
                    min="1"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Optional"
                  />
                </div>
              </div>

              <div class="space-y-2">
                <label class="flex items-center gap-2">
                  <input v-model="quizForm.shuffle_questions" type="checkbox" class="rounded" />
                  <span class="text-sm text-gray-700">Shuffle questions</span>
                </label>
                <label class="flex items-center gap-2">
                  <input v-model="quizForm.shuffle_options" type="checkbox" class="rounded" />
                  <span class="text-sm text-gray-700">Shuffle answer options</span>
                </label>
                <label class="flex items-center gap-2">
                  <input v-model="quizForm.allow_review" type="checkbox" class="rounded" />
                  <span class="text-sm text-gray-700">Allow students to review answers</span>
                </label>
              </div>

              <div class="flex gap-3 pt-4">
                <button
                  type="button"
                  @click="closeModal"
                  class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="saving"
                  class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                >
                  {{ saving ? 'Saving...' : (editingQuiz ? 'Update Quiz' : 'Create Quiz') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const quizzes = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const filterStatus = ref('');
const showModal = ref(false);
const editingQuiz = ref(null);
const saving = ref(false);

const quizForm = ref({
  name: '',
  description: '',
  status: 'draft',
  time_limit_minutes: null,
  shuffle_questions: false,
  shuffle_options: false,
  allow_review: true,
  school_id: 1, // You'll need to get this from auth user
});

const filteredQuizzes = computed(() => {
  let filtered = quizzes.value;

  if (searchQuery.value) {
    filtered = filtered.filter(quiz =>
      quiz.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  if (filterStatus.value) {
    filtered = filtered.filter(quiz => quiz.status === filterStatus.value);
  }

  return filtered;
});

const loadQuizzes = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/quizzes');
    
    // Handle new response structure
    if (response.data.quizzes) {
      quizzes.value = response.data.quizzes;
    } else {
      // Fallback for old response format
      quizzes.value = Array.isArray(response.data) ? response.data : [];
    }
  } catch (error) {
    console.error('Failed to load quizzes:', error);
    alert('Failed to load quizzes. Please try again.');
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editingQuiz.value = null;
  quizForm.value = {
    name: '',
    description: '',
    status: 'draft',
    time_limit_minutes: null,
    shuffle_questions: false,
    shuffle_options: false,
    allow_review: true,
    school_id: 1,
  };
  showModal.value = true;
};

const editQuiz = (quiz) => {
  editingQuiz.value = quiz;
  quizForm.value = {
    name: quiz.name,
    description: quiz.description || '',
    status: quiz.status,
    time_limit_minutes: quiz.time_limit_minutes,
    shuffle_questions: quiz.shuffle_questions || false,
    shuffle_options: quiz.shuffle_options || false,
    allow_review: quiz.allow_review !== false,
    school_id: quiz.school_id,
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingQuiz.value = null;
};

const saveQuiz = async () => {
  try {
    saving.value = true;
    
    if (editingQuiz.value) {
      await axios.put(`/api/quizzes/${editingQuiz.value.id}`, quizForm.value);
    } else {
      await axios.post('/api/quizzes', quizForm.value);
    }
    
    await loadQuizzes();
    closeModal();
  } catch (error) {
    console.error('Failed to save quiz:', error);
    alert('Failed to save quiz. Please try again.');
  } finally {
    saving.value = false;
  }
};

const viewQuiz = (quiz) => {
  router.visit(`/quiz-builder/${quiz.id}`);
};

const deleteQuiz = async (quiz) => {
  if (!confirm(`Are you sure you want to delete "${quiz.name}"?`)) {
    return;
  }

  try {
    await axios.delete(`/api/quizzes/${quiz.id}`);
    await loadQuizzes();
  } catch (error) {
    console.error('Failed to delete quiz:', error);
    alert('Failed to delete quiz. Please try again.');
  }
};

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    draft: 'bg-yellow-100 text-yellow-800',
    archived: 'bg-gray-100 text-gray-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(() => {
  loadQuizzes();
});
</script>
