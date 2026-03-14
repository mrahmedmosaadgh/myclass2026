<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';

// State
const loading = ref(true);
const learningPaths = ref([]);
const activePath = ref(null);
const selectedFilter = ref('all');

// Filters
const filters = [
  { id: 'all', label: 'All Paths', icon: '📚' },
  { id: 'active', label: 'Active', icon: '🔥' },
  { id: 'completed', label: 'Completed', icon: '✅' },
  { id: 'recommended', label: 'Recommended', icon: '⭐' },
];

// Computed
const filteredPaths = computed(() => {
  if (selectedFilter.value === 'all') {
    return learningPaths.value;
  }
  
  if (selectedFilter.value === 'active') {
    return learningPaths.value.filter(path => path.status === 'active');
  }
  
  if (selectedFilter.value === 'completed') {
    return learningPaths.value.filter(path => path.status === 'completed');
  }
  
  if (selectedFilter.value === 'recommended') {
    return learningPaths.value.filter(path => path.is_recommended);
  }
  
  return learningPaths.value;
});

const currentActivePath = computed(() => {
  return learningPaths.value.find(path => path.status === 'active');
});

// Methods
const fetchLearningPaths = async () => {
  try {
    const response = await axios.get('/api/v2/bm2/student/learning-paths');
    learningPaths.value = response.data.data.learning_paths || [];
    activePath.value = learningPaths.value.find(p => p.status === 'active');
  } catch (error) {
    console.error('Error fetching learning paths:', error);
    // Load mock data for development
    loadMockPaths();
  } finally {
    loading.value = false;
  }
};

const loadMockPaths = () => {
  // Mock data for testing
  learningPaths.value = [
    {
      id: 1,
      student_id: 1,
      title: 'Number Sense Foundation',
      description: 'Master basic number recognition, counting, and simple addition',
      status: 'active',
      priority: 'high',
      modules: [
        {
          id: 1,
          title: 'Numbers 1-10',
          order: 1,
          completed: true,
          practice_sessions: 3,
        },
        {
          id: 2,
          title: 'Simple Addition',
          order: 2,
          completed: false,
          practice_sessions: 5,
        },
        {
          id: 3,
          title: 'Number Patterns',
          order: 3,
          completed: false,
          practice_sessions: 0,
        },
      ],
      completion_percentage: 33,
      started_at: new Date(Date.now() - 7 * 86400000).toISOString(),
      estimated_completion: new Date(Date.now() + 14 * 86400000).toISOString(),
      is_recommended: true,
    },
    {
      id: 2,
      student_id: 1,
      title: 'Subtraction Mastery',
      description: 'Learn subtraction basics and build confidence',
      status: 'recommended',
      priority: 'medium',
      modules: [
        {
          id: 4,
          title: 'Introduction to Subtraction',
          order: 1,
          completed: false,
          practice_sessions: 0,
        },
        {
          id: 5,
          title: 'Subtraction Facts',
          order: 2,
          completed: false,
          practice_sessions: 0,
        },
      ],
      completion_percentage: 0,
      started_at: null,
      estimated_completion: null,
      is_recommended: true,
    },
    {
      id: 3,
      student_id: 1,
      title: 'Counting Champion',
      description: 'Complete counting exercises up to 100',
      status: 'completed',
      priority: 'high',
      modules: [
        {
          id: 6,
          title: 'Counting to 20',
          order: 1,
          completed: true,
          practice_sessions: 5,
        },
        {
          id: 7,
          title: 'Counting to 50',
          order: 2,
          completed: true,
          practice_sessions: 8,
        },
        {
          id: 8,
          title: 'Counting to 100',
          order: 3,
          completed: true,
          practice_sessions: 10,
        },
      ],
      completion_percentage: 100,
      started_at: new Date(Date.now() - 30 * 86400000).toISOString(),
      completed_at: new Date(Date.now() - 2 * 86400000).toISOString(),
      is_recommended: false,
    },
  ];
  activePath.value = learningPaths.value.find(p => p.status === 'active');
};

const startPractice = (moduleId) => {
  // Navigate to practice session
  router.visit(`/bm2/practice/${moduleId}`);
};

const viewModuleDetails = (module) => {
  // Show module details modal or navigate
  alert(`View details for: ${module.title}`);
};

const getStatusColor = (status) => {
  switch (status) {
    case 'active':
      return 'bg-gradient-to-r from-blue-500 to-cyan-500';
    case 'completed':
      return 'bg-gradient-to-r from-green-500 to-emerald-500';
    case 'recommended':
      return 'bg-gradient-to-r from-orange-500 to-yellow-500';
    default:
      return 'bg-gradient-to-r from-gray-400 to-gray-500';
  }
};

const getPriorityBadge = (priority) => {
  switch (priority) {
    case 'high':
      return { color: 'text-red-600', bg: 'bg-red-100', label: 'High Priority' };
    case 'medium':
      return { color: 'text-yellow-600', bg: 'bg-yellow-100', label: 'Medium Priority' };
    case 'low':
      return { color: 'text-blue-600', bg: 'bg-blue-100', label: 'Low Priority' };
    default:
      return { color: 'text-gray-600', bg: 'bg-gray-100', label: 'Normal' };
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'Not started';
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};
</script>

<template>
  <Head title="Learning Paths" />

  <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              🎯 My Learning Path
            </h1>
            <p class="mt-2 text-gray-600">
              Your personalized journey to math mastery
            </p>
          </div>
          <button
            @click="$router.visit('/bm2/dashboard')"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all"
          >
            ← Back to Dashboard
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-purple-600"></div>
      </div>

      <div v-else>
        <!-- Active Path Highlight -->
        <div v-if="activePath" class="mb-8">
          <div class="bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6 text-white">
              <div class="flex items-start justify-between mb-4">
                <div>
                  <h2 class="text-2xl font-bold mb-2">
                    🚀 Current Learning Path
                  </h2>
                  <p class="text-blue-100 mb-4">
                    {{ activePath.description }}
                  </p>
                  <div class="flex items-center space-x-4 text-sm">
                    <span class="bg-white/20 px-3 py-1 rounded-full">
                      {{ activePath.modules.length }} Modules
                    </span>
                    <span class="bg-white/20 px-3 py-1 rounded-full">
                      Started: {{ formatDate(activePath.started_at) }}
                    </span>
                    <span class="bg-white/20 px-3 py-1 rounded-full">
                      Est. Completion: {{ formatDate(activePath.estimated_completion) }}
                    </span>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-4xl font-bold mb-2">
                    {{ activePath.completion_percentage }}%
                  </div>
                  <div class="text-blue-100">Complete</div>
                </div>
              </div>

              <!-- Progress Bar -->
              <div class="mb-6">
                <div class="flex justify-between text-sm mb-2">
                  <span>Progress</span>
                  <span>{{ activePath.completion_percentage }}%</span>
                </div>
                <div class="w-full bg-white/20 rounded-full h-3">
                  <div
                    class="bg-white rounded-full h-3 transition-all duration-500"
                    :style="{ width: activePath.completion_percentage + '%' }"
                  ></div>
                </div>
              </div>

              <!-- Next Module -->
              <div class="bg-white/10 rounded-xl p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-sm text-blue-100 mb-1">Next Up</div>
                    <div class="text-lg font-semibold">
                      {{ activePath.modules.find(m => !m.completed)?.title || 'All modules completed!' }}
                    </div>
                  </div>
                  <button
                    v-if="activePath.modules.find(m => !m.completed)"
                    @click="startPractice(activePath.modules.find(m => !m.completed).id)"
                    class="inline-flex items-center px-6 py-3 bg-white text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition-all shadow-lg"
                  >
                    Start Practice →
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter Tabs -->
        <div class="mb-6">
          <div class="flex space-x-2 border-b border-gray-200">
            <button
              v-for="filter in filters"
              :key="filter.id"
              @click="selectedFilter = filter.id"
              :class="[
                'px-4 py-2 text-sm font-medium transition-colors border-b-2',
                selectedFilter === filter.id
                  ? 'border-purple-600 text-purple-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              ]"
            >
              <span class="mr-2">{{ filter.icon }}</span>
              {{ filter.label }}
            </button>
          </div>
        </div>

        <!-- Learning Paths Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="path in filteredPaths"
            :key="path.id"
            class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow border border-gray-200"
          >
            <!-- Path Header -->
            <div :class="['h-2', getStatusColor(path.status)]"></div>
            
            <div class="p-5">
              <!-- Title & Priority -->
              <div class="flex items-start justify-between mb-3">
                <h3 class="text-lg font-bold text-gray-900">
                  {{ path.title }}
                </h3>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-semibold rounded-full',
                    getPriorityBadge(path.priority).bg,
                    getPriorityBadge(path.priority).color,
                  ]"
                >
                  {{ getPriorityBadge(path.priority).label }}
                </span>
              </div>

              <!-- Description -->
              <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                {{ path.description }}
              </p>

              <!-- Modules List -->
              <div class="mb-4">
                <div class="text-xs font-semibold text-gray-700 mb-2">
                  MODULES
                </div>
                <div class="space-y-2">
                  <div
                    v-for="module in path.modules"
                    :key="module.id"
                    :class="[
                      'flex items-center justify-between p-2 rounded-lg text-sm',
                      module.completed ? 'bg-green-50' : 'bg-gray-50',
                    ]"
                  >
                    <div class="flex items-center space-x-2">
                      <span v-if="module.completed" class="text-green-600">✓</span>
                      <span v-else class="text-gray-400">○</span>
                      <span :class="module.completed ? 'text-green-700' : 'text-gray-700'">
                        {{ module.title }}
                      </span>
                    </div>
                    <button
                      v-if="!module.completed && path.status === 'active'"
                      @click="startPractice(module.id)"
                      class="text-xs text-purple-600 hover:text-purple-800 font-medium"
                    >
                      Practice
                    </button>
                  </div>
                </div>
              </div>

              <!-- Progress -->
              <div class="mb-3">
                <div class="flex justify-between text-xs text-gray-600 mb-1">
                  <span>Progress</span>
                  <span>{{ path.completion_percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    :class="[
                      'rounded-full h-2 transition-all duration-500',
                      path.status === 'completed'
                        ? 'bg-green-500'
                        : path.status === 'active'
                        ? 'bg-blue-500'
                        : 'bg-orange-500',
                    ]"
                    :style="{ width: path.completion_percentage + '%' }"
                  ></div>
                </div>
              </div>

              <!-- Stats -->
              <div class="flex items-center justify-between text-xs text-gray-500 pt-3 border-t border-gray-200">
                <span>
                  {{ path.modules.filter(m => m.completed).length }}/{{ path.modules.length }} modules
                </span>
                <span v-if="path.completed_at">
                  Completed: {{ formatDate(path.completed_at) }}
                </span>
                <span v-else-if="path.started_at">
                  Started: {{ formatDate(path.started_at) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredPaths.length === 0" class="text-center py-20">
          <div class="text-6xl mb-4">📚</div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">
            No Learning Paths Found
          </h3>
          <p class="text-gray-600 mb-6">
            Complete an assessment to get your personalized learning path!
          </p>
          <button
            @click="$router.visit('/bm2/assessment/start')"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-pink-700 transition-all shadow-lg"
          >
            Start Assessment →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
