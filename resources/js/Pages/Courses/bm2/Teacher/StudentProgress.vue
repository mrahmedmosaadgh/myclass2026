<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  studentId: {
    type: [Number, String],
    required: true,
  },
});

// State
const loading = ref(true);
const studentData = ref(null);
const selectedTab = ref('overview');

// Tabs
const tabs = [
  { id: 'overview', label: 'Overview', icon: '📊' },
  { id: 'history', label: 'Assessment History', icon: '📝' },
  { id: 'badges', label: 'Badges', icon: '🏆' },
  { id: 'path', label: 'Learning Path', icon: '🚀' },
];

// Computed
const student = computed(() => studentData.value?.student || {});
const overview = computed(() => studentData.value?.overview || {});
const skillBreakdown = computed(() => studentData.value?.skill_breakdown || {});
const assessmentHistory = computed(() => studentData.value?.assessment_history || []);
const badges = computed(() => studentData.value?.badges || []);
const learningPath = computed(() => studentData.value?.learning_path || null);
const performanceTrend = computed(() => studentData.value?.performance_trend || []);

// Methods
const fetchStudentProgress = async () => {
  try {
    const response = await axios.get(`/api/v2/bm2/teacher/student/${props.studentId}/progress`);
    studentData.value = response.data.data;
  } catch (error) {
    console.error('Error fetching student progress:', error);
    // Load mock data for development
    loadMockData();
  } finally {
    loading.value = false;
  }
};

const loadMockData = () => {
  studentData.value = {
    student: {
      id: props.studentId,
      name: 'Ahmed Ali',
      email: 'ahmed@example.com',
    },
    overview: {
      total_assessments: 8,
      average_score: 85.5,
      highest_score: 95,
      lowest_score: 72,
      current_streak: 5,
      total_points: 195,
    },
    skill_breakdown: {
      addition: { percentage: 88, questions_answered: 25, correct: 22 },
      subtraction: { percentage: 82, questions_answered: 20, correct: 16 },
      number_sense: { percentage: 90, questions_answered: 15, correct: 14 },
    },
    assessment_history: [
      { id: 1, title: 'Basic Math Placement Test', type: 'placement', score: 85, performance_level: 'proficient', completed_at: new Date().toISOString(), time_taken_minutes: 12.5 },
      { id: 2, title: 'Progress Assessment', type: 'progress', score: 88, performance_level: 'proficient', completed_at: new Date(Date.now() - 86400000).toISOString(), time_taken_minutes: 10.2 },
      { id: 3, title: 'Addition Focus', type: 'progress', score: 92, performance_level: 'advanced', completed_at: new Date(Date.now() - 172800000).toISOString(), time_taken_minutes: 8.7 },
    ],
    badges: [
      { id: 1, name: 'First Steps', category: 'achievement', rarity: 'common', earned_at: new Date().toISOString(), points: 10 },
      { id: 2, name: 'Dedicated Learner', category: 'milestone', rarity: 'uncommon', earned_at: new Date(Date.now() - 86400000).toISOString(), points: 25 },
      { id: 3, name: 'Addition Ace', category: 'skill_mastery', rarity: 'rare', earned_at: new Date(Date.now() - 172800000).toISOString(), points: 30 },
    ],
    learning_path: {
      id: 1,
      title: 'Number Sense Foundation',
      status: 'active',
      completion_percentage: 45,
      modules: [
        { id: 1, title: 'Numbers 1-10', order: 1, completed: true },
        { id: 2, title: 'Simple Addition', order: 2, completed: true },
        { id: 3, title: 'Number Patterns', order: 3, completed: false },
        { id: 4, title: 'Advanced Counting', order: 4, completed: false },
      ],
    },
  };
};

const getScoreColor = (score) => {
  if (score >= 90) return 'text-green-600 bg-green-100';
  if (score >= 70) return 'text-blue-600 bg-blue-100';
  if (score >= 60) return 'text-yellow-600 bg-yellow-100';
  return 'text-red-600 bg-red-100';
};

const getPerformanceLevelColor = (level) => {
  switch (level) {
    case 'advanced': return 'text-purple-600 bg-purple-100';
    case 'proficient': return 'text-blue-600 bg-blue-100';
    case 'developing': return 'text-yellow-600 bg-yellow-100';
    case 'emerging': return 'text-red-600 bg-red-100';
    default: return 'text-gray-600 bg-gray-100';
  }
};

const getTypeIcon = (type) => {
  switch (type) {
    case 'placement': return '🎯';
    case 'progress': return '📈';
    case 'final': return '🏁';
    default: return '📝';
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

const getRarityColor = (rarity) => {
  switch (rarity) {
    case 'legendary': return 'from-yellow-400 to-orange-500';
    case 'epic': return 'from-purple-500 to-pink-500';
    case 'rare': return 'from-blue-500 to-cyan-500';
    case 'uncommon': return 'from-green-400 to-teal-500';
    case 'common': return 'from-gray-400 to-gray-500';
    default: return 'from-gray-400 to-gray-500';
  }
};
</script>

<template>
  <Head :title="`${student.name || 'Student'} - Progress`" />

  <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <div class="flex items-center space-x-3 mb-2">
              <button
                @click="$router.visit('/bm2/teacher/dashboard')"
                class="text-purple-600 hover:text-purple-800 text-sm font-medium"
              >
                ← Back to Dashboard
              </button>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">
              👨‍🎓 {{ student.name || 'Loading...' }}
            </h1>
            <p class="mt-2 text-gray-600">
              {{ student.email }}
            </p>
          </div>
          <div class="flex items-center space-x-4">
            <div class="text-right">
              <div class="text-sm text-gray-600">Current Streak</div>
              <div class="text-2xl font-bold text-orange-600">🔥 {{ overview.current_streak || 0 }}</div>
            </div>
            <div class="text-right">
              <div class="text-sm text-gray-600">Total Points</div>
              <div class="text-2xl font-bold text-purple-600">{{ overview.total_points || 0 }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex space-x-4 overflow-x-auto">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="selectedTab = tab.id"
            :class="[
              'px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors border-b-2',
              selectedTab === tab.id
                ? 'border-purple-600 text-purple-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            ]"
          >
            <span class="mr-2">{{ tab.icon }}</span>
            {{ tab.label }}
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

      <!-- Overview Tab -->
      <div v-if="!loading && selectedTab === 'overview'" class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-white rounded-xl shadow-md p-5 border border-gray-200">
            <div class="text-sm text-gray-600 mb-1">Assessments Completed</div>
            <div class="text-3xl font-bold text-gray-900">{{ overview.total_assessments }}</div>
            <div class="text-xs text-gray-500 mt-1">📝 Total tests taken</div>
          </div>

          <div class="bg-white rounded-xl shadow-md p-5 border border-gray-200">
            <div class="text-sm text-gray-600 mb-1">Average Score</div>
            <div class="text-3xl font-bold text-gray-900">{{ overview.average_score }}%</div>
            <div class="text-xs text-gray-500 mt-1">📊 Overall performance</div>
          </div>

          <div class="bg-white rounded-xl shadow-md p-5 border border-gray-200">
            <div class="text-sm text-gray-600 mb-1">Highest Score</div>
            <div class="text-3xl font-bold text-green-600">{{ overview.highest_score }}%</div>
            <div class="text-xs text-gray-500 mt-1">🏆 Best performance</div>
          </div>

          <div class="bg-white rounded-xl shadow-md p-5 border border-gray-200">
            <div class="text-sm text-gray-600 mb-1">Lowest Score</div>
            <div class="text-3xl font-bold text-red-600">{{ overview.lowest_score }}%</div>
            <div class="text-xs text-gray-500 mt-1">📉 Area for improvement</div>
          </div>
        </div>

        <!-- Skill Breakdown -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-cyan-500">
            <h2 class="text-xl font-bold text-white">Skill Breakdown</h2>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="(skill, key) in skillBreakdown" :key="key">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-700 capitalize">{{ key.replace('_', ' ') }}</span>
                  <span class="text-sm font-semibold" :class="getScoreColor(skill.percentage)">
                    {{ skill.percentage }}%
                  </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    :class="['h-3 rounded-full transition-all duration-500',
                      skill.percentage >= 90 ? 'bg-green-500' :
                      skill.percentage >= 70 ? 'bg-blue-500' :
                      skill.percentage >= 60 ? 'bg-yellow-500' : 'bg-red-500']"
                    :style="{ width: skill.percentage + '%' }"
                  ></div>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                  {{ skill.correct }} / {{ skill.questions_answered }} questions correct
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Performance Trend -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-purple-500 to-pink-500">
            <h2 class="text-xl font-bold text-white">Performance Trend</h2>
          </div>
          <div class="p-6">
            <div class="h-48 flex items-end space-x-2">
              <div
                v-for="(point, index) in performanceTrend"
                :key="index"
                class="flex-1 flex flex-col items-center"
              >
                <div
                  :class="['w-full rounded-t-lg transition-all duration-500',
                    point.score >= 90 ? 'bg-green-500' :
                    point.score >= 70 ? 'bg-blue-500' :
                    point.score >= 60 ? 'bg-yellow-500' : 'bg-red-500']"
                  :style="{ height: (point.score / 100 * 100) + '%' }"
                ></div>
                <div class="text-xs text-gray-500 mt-2 transform -rotate-45">
                  {{ new Date(point.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Assessment History Tab -->
      <div v-if="!loading && selectedTab === 'history'" class="space-y-6">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-green-500 to-teal-500">
            <h2 class="text-xl font-bold text-white">Assessment History</h2>
          </div>
          <div class="divide-y divide-gray-200">
            <div
              v-for="assessment in assessmentHistory"
              :key="assessment.id"
              class="p-4 hover:bg-gray-50 transition-colors"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="text-2xl">{{ getTypeIcon(assessment.type) }}</div>
                  <div>
                    <div class="font-semibold text-gray-900">{{ assessment.title }}</div>
                    <div class="text-sm text-gray-500">
                      {{ assessment.type }} • {{ formatDate(assessment.completed_at) }} • {{ assessment.time_taken_minutes }} min
                    </div>
                  </div>
                </div>
                <div class="flex items-center space-x-3">
                  <span :class="['px-3 py-1 rounded-full text-sm font-semibold', getScoreColor(assessment.score)]">
                    {{ assessment.score }}%
                  </span>
                  <span :class="['px-3 py-1 rounded-full text-xs font-semibold capitalize', getPerformanceLevelColor(assessment.performance_level)]">
                    {{ assessment.performance_level }}
                  </span>
                </div>
              </div>
            </div>
            <div v-if="assessmentHistory.length === 0" class="p-8 text-center text-gray-500">
              No assessments completed yet
            </div>
          </div>
        </div>
      </div>

      <!-- Badges Tab -->
      <div v-if="!loading && selectedTab === 'badges'" class="space-y-6">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-yellow-400 to-orange-400">
            <h2 class="text-xl font-bold text-white">🏆 Badge Collection</h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="badge in badges"
                :key="badge.id"
                class="relative overflow-hidden rounded-xl border-2 border-gray-200 hover:border-purple-400 transition-colors"
              >
                <div :class="['absolute top-0 left-0 w-full h-2 bg-gradient-to-r', getRarityColor(badge.rarity)]"></div>
                <div class="p-4">
                  <div class="text-4xl mb-2">
                    {{ badge.category === 'achievement' ? '🏆' :
                       badge.category === 'milestone' ? '📈' :
                       badge.category === 'skill_mastery' ? '💪' :
                       badge.category === 'speed' ? '⚡' : '🔥' }}
                  </div>
                  <div class="font-semibold text-gray-900 mb-1">{{ badge.name }}</div>
                  <div class="text-xs text-gray-500 mb-2 capitalize">{{ badge.category }}</div>
                  <div class="flex items-center justify-between text-xs">
                    <span class="text-purple-600 font-semibold">+{{ badge.points }} pts</span>
                    <span class="text-gray-500">{{ formatDate(badge.earned_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="badges.length === 0" class="text-center py-12 text-gray-500">
              No badges earned yet. Keep practicing! 🎯
            </div>
          </div>
        </div>
      </div>

      <!-- Learning Path Tab -->
      <div v-if="!loading && selectedTab === 'path'" class="space-y-6">
        <div v-if="learningPath" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-500">
            <h2 class="text-xl font-bold text-white">🚀 Current Learning Path</h2>
          </div>
          <div class="p-6">
            <div class="mb-6">
              <h3 class="text-lg font-bold text-gray-900 mb-2">{{ learningPath.title }}</h3>
              <p class="text-sm text-gray-600 mb-4">Status: <span class="font-semibold capitalize">{{ learningPath.status }}</span></p>
              
              <!-- Progress Bar -->
              <div class="mb-4">
                <div class="flex justify-between text-sm mb-2">
                  <span class="text-gray-600">Overall Progress</span>
                  <span class="font-semibold text-purple-600">{{ learningPath.completion_percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    class="bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-full transition-all duration-500"
                    :style="{ width: learningPath.completion_percentage + '%' }"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Modules -->
            <div class="space-y-3">
              <div
                v-for="module in learningPath.modules"
                :key="module.id"
                :class="[
                  'flex items-center justify-between p-4 rounded-lg border-2 transition-colors',
                  module.completed ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-white',
                ]"
              >
                <div class="flex items-center space-x-3">
                  <div :class="[
                    'w-8 h-8 rounded-full flex items-center justify-center',
                    module.completed ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600',
                  ]">
                    {{ module.completed ? '✓' : module.order }}
                  </div>
                  <div>
                    <div :class="['font-semibold', module.completed ? 'text-green-700' : 'text-gray-900']">
                      {{ module.title }}
                    </div>
                    <div class="text-xs text-gray-500">Module {{ module.order }}</div>
                  </div>
                </div>
                <div v-if="module.completed" class="text-green-600 font-semibold text-sm">
                  Complete ✓
                </div>
                <div v-else class="text-purple-600 font-semibold text-sm">
                  In Progress
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="bg-white rounded-xl shadow-md border border-gray-200 p-8 text-center">
          <div class="text-6xl mb-4">🎯</div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">No Active Learning Path</h3>
          <p class="text-gray-600 mb-4">Complete an assessment to generate a personalized learning path!</p>
        </div>
      </div>
    </div>
  </div>
</template>
