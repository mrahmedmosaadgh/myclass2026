<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';

// Configure axios with credentials for Sanctum
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// State
const loading = ref(true);
const stats = ref(null);
const recentAssessments = ref([]);
const badges = ref([]);
const learningPath = ref(null);
const skillProgress = ref({});
const error = ref(null); // { type, title, message }

// Computed
const totalAssessments = computed(() => {
  return stats.value?.total_assessments || 0;
});

const averageScore = computed(() => {
  return stats.value?.average_score || 0;
});

const bestScore = computed(() => {
  return stats.value?.best_score || 0;
});

const timeSpent = computed(() => {
  return stats.value?.total_time_minutes || 0;
});

const currentStreak = computed(() => {
  return stats.value?.current_streak || 0;
});

const totalBadges = computed(() => {
  return badges.value?.length || 0;
});

// Methods
const fetchDashboardData = async () => {
  try {
    const response = await axios.get('/api/v2/bm2/student/dashboard');
    stats.value = response.data.data.stats;
    recentAssessments.value = response.data.data.recent_assessments;
    badges.value = response.data.data.badges || [];
    learningPath.value = response.data.data.learning_path;
    skillProgress.value = response.data.data.skill_progress || {};
    error.value = null; // Clear any previous errors
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    
    // Handle authentication errors specifically
    if (error.response && (error.response.status === 401 || error.response.status === 403)) {
      error.value = {
        type: 'auth',
        title: 'Authentication Required',
        message: 'Please login to view your dashboard.',
      };
    }
    
    // Load mock data for development if API fails (but don't show auth errors in mock)
    if (!error.value || error.value.type !== 'auth') {
      loadMockData();
    }
  } finally {
    loading.value = false;
  }
};

const loadMockData = () => {
  // Mock data for testing when API is not ready
  stats.value = {
    total_assessments: 5,
    average_score: 78.5,
    best_score: 95.0,
    total_time_minutes: 45,
    current_streak: 3,
  };
  
  recentAssessments.value = [
    {
      id: 1,
      title: 'Basic Math Placement Test',
      type: 'placement',
      completed_at: new Date().toISOString(),
      overall_score: 85.5,
      performance_level: 'proficient',
    },
    {
      id: 2,
      title: 'Addition Practice',
      type: 'progress',
      completed_at: new Date(Date.now() - 86400000).toISOString(),
      overall_score: 92.0,
      performance_level: 'advanced',
    },
  ];
  
  badges.value = [
    {
      id: 1,
      name: 'First Steps',
      description: 'Complete your first assessment',
      icon: '🎯',
      earned_at: new Date().toISOString(),
    },
  ];
};

const startNewAssessment = () => {
  router.visit('/bm2/assessment/start');
};

const viewAssessmentResults = (assessmentId) => {
  router.visit(`/bm2/assessment/${assessmentId}/results`);
};

const viewAllBadges = () => {
  router.visit('/bm2/badges');
};

const viewLearningPaths = () => {
  router.visit('/bm2/learning-paths');
};

const getPerformanceColor = (level) => {
  const colors = {
    advanced: 'text-green-600 bg-green-100',
    proficient: 'text-blue-600 bg-blue-100',
    developing: 'text-yellow-600 bg-yellow-100',
    emerging: 'text-orange-600 bg-orange-100',
  };
  return colors[level] || colors.developing;
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getScoreColor = (score) => {
  if (score >= 90) return 'text-green-500';
  if (score >= 70) return 'text-blue-500';
  if (score >= 40) return 'text-yellow-500';
  return 'text-orange-500';
};

// Lifecycle
onMounted(() => {
  fetchDashboardData();
});
</script>

<template>
  <Head title="BM2 Dashboard" />

  <div class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-green-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-4xl font-bold text-gray-800 mb-2">
            📚 My Learning Dashboard
          </h1>
          <p class="text-gray-600">Track your progress and achievements</p>
        </div>
        <button
          @click="startNewAssessment"
          class="px-6 py-3 bg-primary hover:bg-primary-dark text-white font-bold rounded-lg transition-all shadow-lg"
        >
          🚀 Start New Assessment
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-primary"></div>
      </div>

      <!-- Error Alert -->
      <div v-if="error" :class="[
        'rounded-lg p-4 mb-6 flex items-start',
        error.type === 'auth' ? 'bg-red-50 border-l-4 border-red-400' : 'bg-yellow-50 border-l-4 border-yellow-400'
      ]">
        <div class="flex-shrink-0">
          <span class="text-2xl">{{ error.type === 'auth' ? '🔒' : '⚠️' }}</span>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-sm font-medium" :class="error.type === 'auth' ? 'text-red-800' : 'text-yellow-800'">
            {{ error.title }}
          </h3>
          <div class="mt-2 text-sm" :class="error.type === 'auth' ? 'text-red-700' : 'text-yellow-700'">
            <p>{{ error.message }}</p>
          </div>
          <div v-if="error.type === 'auth'" class="mt-4">
            <a 
              href="/login" 
              class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors"
            >
              🔐 Go to Login
            </a>
          </div>
        </div>
        <button @click="error = null" class="ml-4 text-gray-400 hover:text-gray-600">
          ✕
        </button>
      </div>

      <!-- Dashboard Content -->
      <div v-else class="space-y-6">
        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="text-3xl font-bold text-primary mb-2">
              {{ totalAssessments }}
            </div>
            <div class="text-sm text-gray-500">Assessments</div>
          </div>
          
          <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="text-3xl font-bold" :class="getScoreColor(averageScore)">
              {{ averageScore != null ? averageScore.toFixed(1) : '0.0' }}%
            </div>
            <div class="text-sm text-gray-500">Average Score</div>
          </div>
          
          <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="text-3xl font-bold text-green-600">
              {{ bestScore != null ? bestScore.toFixed(1) : '0.0' }}%
            </div>
            <div class="text-sm text-gray-500">Best Score</div>
          </div>
          
          <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="text-3xl font-bold text-blue-600">
              {{ timeSpent }}
            </div>
            <div class="text-sm text-gray-500">Minutes</div>
          </div>
          
          <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="text-3xl font-bold text-orange-600">
              🔥 {{ currentStreak }}
            </div>
            <div class="text-sm text-gray-500">Day Streak</div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Recent Assessments -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-2xl font-bold text-gray-800">📊 Recent Assessments</h2>
              <button 
                @click="startNewAssessment"
                class="text-primary hover:text-primary-dark font-medium text-sm"
              >
                + New
              </button>
            </div>
            
            <div class="space-y-3">
              <div 
                v-for="assessment in recentAssessments" 
                :key="assessment.id"
                class="border-l-4 rounded-lg p-4 cursor-pointer hover:shadow-md transition-shadow"
                :class="assessment.performance_level === 'advanced' ? 'border-green-500 bg-green-50' : 
                        assessment.performance_level === 'proficient' ? 'border-blue-500 bg-blue-50' :
                        assessment.performance_level === 'developing' ? 'border-yellow-500 bg-yellow-50' :
                        'border-orange-500 bg-orange-50'"
                @click="viewAssessmentResults(assessment.id)"
              >
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <h3 class="font-semibold text-gray-800 mb-1">
                      {{ assessment.title }}
                    </h3>
                    <p class="text-sm text-gray-500">
                      {{ formatDate(assessment.completed_at) }}
                    </p>
                  </div>
                  <div class="text-right">
                    <div class="text-2xl font-bold" :class="getScoreColor(assessment.overall_score)">
                      {{ assessment.overall_score != null && assessment.overall_score !== undefined ? Number(assessment.overall_score).toFixed(1) : '0.0' }}%
                    </div>
                    <span class="inline-block px-2 py-1 rounded text-xs font-medium mt-1" 
                          :class="getPerformanceColor(assessment.performance_level)">
                      {{ assessment.performance_level }}
                    </span>
                  </div>
                </div>
              </div>
              
              <div v-if="recentAssessments.length === 0" class="text-center py-8 text-gray-500">
                No assessments yet. Start your first one!
              </div>
            </div>
          </div>

          <!-- Badges Showcase -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-2xl font-bold text-gray-800">🏆 My Badges</h2>
              <button 
                @click="viewAllBadges"
                class="text-primary hover:text-primary-dark font-medium text-sm"
              >
                View All
              </button>
            </div>
            
            <div class="grid grid-cols-3 gap-3">
              <div 
                v-for="badge in badges.slice(0, 6)" 
                :key="badge.id"
                class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg p-3 text-center cursor-pointer hover:shadow-md transition-shadow"
                @click="viewAllBadges"
              >
                <div class="text-3xl mb-1">{{ badge.icon || '⭐' }}</div>
                <div class="text-xs font-semibold text-gray-700">{{ badge.name }}</div>
              </div>
            </div>
            
            <div v-if="badges.length === 0" class="text-center py-8 text-gray-500">
              No badges yet. Complete assessments to earn them!
            </div>
            
            <div v-if="badges.length > 6" class="text-center mt-4 text-sm text-gray-500">
              + {{ badges.length - 6 }} more badges
            </div>
          </div>
        </div>

        <!-- Learning Path Section -->
        <div v-if="learningPath" class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl shadow-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">🎓 Your Learning Path</h2>
            <button 
              @click="viewLearningPaths"
              class="px-4 py-2 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg transition-colors"
            >
              View Full Path
            </button>
          </div>
          
          <div class="space-y-3">
            <div 
              v-for="(module, index) in learningPath.recommended_modules?.slice(0, 3) || []" 
              :key="index"
              class="bg-white rounded-lg p-4 flex items-center justify-between"
            >
              <div class="flex items-center space-x-3">
                <span class="flex-shrink-0 w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold text-sm">
                  {{ index + 1 }}
                </span>
                <div>
                  <h3 class="font-semibold text-gray-800">{{ module.topic || module.name }}</h3>
                  <p class="text-sm text-gray-500">Priority: {{ module.priority || 'Medium' }}</p>
                </div>
              </div>
              <button 
                @click="viewLearningPaths"
                class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition-colors text-sm font-medium"
              >
                Start Practice
              </button>
            </div>
          </div>
        </div>

        <!-- Skill Progress -->
        <div v-if="Object.keys(skillProgress).length > 0" class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-2xl font-bold text-gray-800 mb-4">📈 Skill Progress</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div 
              v-for="(progress, skill) in skillProgress" 
              :key="skill"
              class="p-4 border-2 rounded-lg"
            >
              <div class="flex justify-between items-center mb-2">
                <span class="font-semibold text-gray-700 capitalize">
                  {{ skill.replace('_', ' ') }}
                </span>
                <span class="text-sm font-medium text-gray-600">
                  {{ progress.percentage }}%
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div 
                  class="h-3 rounded-full transition-all duration-500"
                  :class="progress.percentage >= 90 ? 'bg-green-500' : 
                          progress.percentage >= 70 ? 'bg-blue-500' :
                          progress.percentage >= 40 ? 'bg-yellow-500' :
                          'bg-orange-500'"
                  :style="{ width: `${progress.percentage}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <button
            @click="startNewAssessment"
            class="bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white font-bold py-6 px-4 rounded-xl shadow-lg transition-all transform hover:scale-105"
          >
            <div class="text-2xl mb-2">🚀</div>
            <div class="text-lg">Start Assessment</div>
          </button>
          
          <button
            @click="viewLearningPaths"
            class="bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white font-bold py-6 px-4 rounded-xl shadow-lg transition-all transform hover:scale-105"
          >
            <div class="text-2xl mb-2">📚</div>
            <div class="text-lg">Learning Paths</div>
          </button>
          
          <button
            @click="viewAllBadges"
            class="bg-gradient-to-r from-yellow-500 to-orange-600 hover:from-yellow-600 hover:to-orange-700 text-white font-bold py-6 px-4 rounded-xl shadow-lg transition-all transform hover:scale-105"
          >
            <div class="text-2xl mb-2">🏆</div>
            <div class="text-lg">My Badges</div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.text-primary {
  color: #667eea;
}

.bg-primary {
  background-color: #667eea;
}

.bg-primary:hover {
  background-color: #5568d3;
}
</style>
