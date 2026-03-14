<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';

// State
const loading = ref(true);
const dashboardData = ref(null);
const selectedTab = ref('overview');

// Tabs
const tabs = [
  { id: 'overview', label: 'Overview', icon: '📊' },
  { id: 'students', label: 'Students', icon: '👥' },
  { id: 'leaderboard', label: 'Leaderboard', icon: '🏆' },
  { id: 'skills', label: 'Skills Analysis', icon: '📈' },
];

// Computed
const overview = computed(() => dashboardData.value?.overview || {});
const recentActivity = computed(() => dashboardData.value?.recent_activity || []);
const topPerformers = computed(() => dashboardData.value?.top_performers || []);
const strugglingStudents = computed(() => dashboardData.value?.struggling_students || []);

// Methods
const fetchDashboard = async () => {
  try {
    const response = await axios.get('/api/v2/bm2/teacher/dashboard');
    dashboardData.value = response.data.data;
  } catch (error) {
    console.error('Error fetching dashboard:', error);
    // Load mock data for development
    loadMockData();
  } finally {
    loading.value = false;
  }
};

const loadMockData = () => {
  // Mock data for testing
  dashboardData.value = {
    overview: {
      total_students: 25,
      total_assessments: 142,
      class_average_score: 78.5,
      total_badges_earned: 89,
      active_learning_paths: 18,
    },
    recent_activity: [
      { id: 1, student_name: 'Ahmed Ali', student_id: 1, type: 'placement', score: 85, completed_at: new Date().toISOString() },
      { id: 2, student_name: 'Fatima Hassan', student_id: 2, type: 'progress', score: 92, completed_at: new Date(Date.now() - 3600000).toISOString() },
      { id: 3, student_name: 'Omar Mohamed', student_id: 3, type: 'placement', score: 67, completed_at: new Date(Date.now() - 7200000).toISOString() },
      { id: 4, student_name: 'Layla Ibrahim', student_id: 4, type: 'final', score: 95, completed_at: new Date(Date.now() - 86400000).toISOString() },
      { id: 5, student_name: 'Youssef Ahmed', student_id: 5, type: 'progress', score: 73, completed_at: new Date(Date.now() - 172800000).toISOString() },
    ],
    top_performers: [
      { id: 1, name: 'Layla Ibrahim', email: 'layla@example.com', total_points: 250, badges_count: 8, assessments_completed: 12, average_score: 94.5 },
      { id: 2, name: 'Fatima Hassan', email: 'fatima@example.com', total_points: 220, badges_count: 7, assessments_completed: 10, average_score: 91.2 },
      { id: 3, name: 'Ahmed Ali', email: 'ahmed@example.com', total_points: 195, badges_count: 6, assessments_completed: 9, average_score: 87.3 },
      { id: 4, name: 'Omar Mohamed', email: 'omar@example.com', total_points: 175, badges_count: 5, assessments_completed: 8, average_score: 82.1 },
      { id: 5, name: 'Youssef Ahmed', email: 'youssef@example.com', total_points: 160, badges_count: 5, assessments_completed: 7, average_score: 79.8 },
    ],
    struggling_students: [
      { id: 6, name: 'Sara Mahmoud', email: 'sara@example.com', assessments_completed: 3, average_score: 52.3, last_active: new Date(Date.now() - 259200000).toISOString() },
      { id: 7, name: 'Khaled Yasser', email: 'khaled@example.com', assessments_completed: 2, average_score: 48.5, last_active: new Date(Date.now() - 345600000).toISOString() },
    ],
  };
};

const viewStudentProgress = (studentId) => {
  router.visit(`/bm2/teacher/student/${studentId}`);
};

const getScoreColor = (score) => {
  if (score >= 90) return 'text-green-600 bg-green-100';
  if (score >= 70) return 'text-blue-600 bg-blue-100';
  if (score >= 60) return 'text-yellow-600 bg-yellow-100';
  return 'text-red-600 bg-red-100';
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getPerformanceIcon = (type) => {
  switch (type) {
    case 'placement': return '🎯';
    case 'progress': return '📈';
    case 'final': return '🏁';
    default: return '📝';
  }
};
</script>

<template>
  <Head title="Teacher Dashboard" />

  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              👨‍🏫 Teacher Dashboard
            </h1>
            <p class="mt-2 text-gray-600">
              Monitor class progress and student achievements
            </p>
          </div>
          <button
            @click="$router.visit('/bm2/dashboard')"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all"
          >
            ← Student View
          </button>
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <div class="bg-white rounded-xl shadow-md p-5 border border-gray-200">
            <div class="text-sm text-gray-600 mb-1">Total Students</div>
            <div class="text-3xl font-bold text-gray-900">{{ overview.total_students }}</div>
            <div class="text-xs text-gray-500 mt-1">👥 Class size</div>
          </div>

          <div class="bg-white rounded-xl shadow-md p-5 border border-gray-200">
            <div class="text-sm text-gray-600 mb-1">Total Assessments</div>
            <div class="text-3xl font-bold text-gray-900">{{ overview.total_assessments }}</div>
            <div class="text-xs text-gray-500 mt-1">📝 Completed</div>
          </div>

          <div class="bg-white rounded-xl shadow-md p-5 border border-gray-200">
            <div class="text-sm text-gray-600 mb-1">Class Average</div>
            <div class="text-3xl font-bold text-gray-900">{{ overview.class_average_score }}%</div>
            <div class="text-xs text-gray-500 mt-1">📊 Overall performance</div>
          </div>

          <div class="bg-white rounded-xl shadow-md p-5 border border-gray-200">
            <div class="text-sm text-gray-600 mb-1">Badges Earned</div>
            <div class="text-3xl font-bold text-gray-900">{{ overview.total_badges_earned }}</div>
            <div class="text-xs text-gray-500 mt-1">🏆 Achievements</div>
          </div>

          <div class="bg-white rounded-xl shadow-md p-5 border border-gray-200">
            <div class="text-sm text-gray-600 mb-1">Active Paths</div>
            <div class="text-3xl font-bold text-gray-900">{{ overview.active_learning_paths }}</div>
            <div class="text-xs text-gray-500 mt-1">🚀 Learning</div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-500">
            <h2 class="text-xl font-bold text-white">Recent Activity</h2>
          </div>
          <div class="divide-y divide-gray-200">
            <div
              v-for="activity in recentActivity"
              :key="activity.id"
              class="p-4 hover:bg-gray-50 transition-colors"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="text-2xl">{{ getPerformanceIcon(activity.type) }}</div>
                  <div>
                    <div class="font-semibold text-gray-900">{{ activity.student_name }}</div>
                    <div class="text-sm text-gray-500">
                      {{ activity.type }} assessment • {{ formatDate(activity.completed_at) }}
                    </div>
                  </div>
                </div>
                <div class="flex items-center space-x-3">
                  <span :class="['px-3 py-1 rounded-full text-sm font-semibold', getScoreColor(activity.score)]">
                    {{ activity.score }}%
                  </span>
                  <button
                    @click="viewStudentProgress(activity.student_id)"
                    class="text-purple-600 hover:text-purple-800 text-sm font-medium"
                  >
                    View →
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Top Performers -->
          <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-yellow-400 to-orange-400">
              <h2 class="text-xl font-bold text-white">⭐ Top Performers</h2>
            </div>
            <div class="divide-y divide-gray-200">
              <div
                v-for="(performer, index) in topPerformers"
                :key="performer.student_id"
                class="p-4 hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-white font-bold', 
                      index === 0 ? 'bg-yellow-500' : 
                      index === 1 ? 'bg-gray-400' : 
                      index === 2 ? 'bg-orange-500' : 'bg-purple-500']">
                      {{ index + 1 }}
                    </div>
                    <div>
                      <div class="font-semibold text-gray-900">{{ performer.name }}</div>
                      <div class="text-sm text-gray-500">
                        {{ performer.badges_count }} badges • {{ performer.assessments_completed }} assessments
                      </div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-lg font-bold text-purple-600">{{ performer.total_points }} pts</div>
                    <div class="text-xs text-gray-500">{{ performer.average_score }}% avg</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Students Needing Attention -->
          <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-red-400 to-pink-400">
              <h2 class="text-xl font-bold text-white">⚠️ Students Needing Attention</h2>
            </div>
            <div class="divide-y divide-gray-200">
              <div
                v-for="student in strugglingStudents"
                :key="student.id"
                class="p-4 hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-semibold text-gray-900">{{ student.name }}</div>
                    <div class="text-sm text-gray-500">
                      Avg Score: {{ student.average_score }}% • Last active: {{ formatDate(student.last_active) }}
                    </div>
                  </div>
                  <button
                    @click="viewStudentProgress(student.id)"
                    class="text-purple-600 hover:text-purple-800 text-sm font-medium"
                  >
                    View →
                  </button>
                </div>
              </div>
              <div v-if="strugglingStudents.length === 0" class="p-8 text-center text-gray-500">
                🎉 All students are doing well!
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Students Tab -->
      <div v-if="!loading && selectedTab === 'students'" class="space-y-6">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-purple-500 to-pink-500">
            <h2 class="text-xl font-bold text-white">All Students</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assessments</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Score</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Badges</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Streak</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="student in dashboardData?.students || []" :key="student.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-medium text-gray-900">{{ student.name }}</div>
                    <div class="text-sm text-gray-500">{{ student.email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ student.assessments_completed }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getScoreColor(student.average_score)]">
                      {{ student.average_score }}%
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ student.badges_count }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600 font-semibold">{{ student.total_points }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">🔥 {{ student.current_streak }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <button
                      @click="viewStudentProgress(student.id)"
                      class="text-purple-600 hover:text-purple-800 font-medium"
                    >
                      View Progress
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Leaderboard Tab -->
      <div v-if="!loading && selectedTab === 'leaderboard'" class="space-y-6">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-yellow-400 to-orange-400">
            <h2 class="text-xl font-bold text-white">🏆 Badge Leaderboard</h2>
          </div>
          <div class="divide-y divide-gray-200">
            <div
              v-for="entry in dashboardData?.leaderboard || []"
              :key="entry.student_id"
              class="p-4 hover:bg-gray-50 transition-colors"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <div :class="['w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-xl',
                    entry.rank === 1 ? 'bg-yellow-500' : 
                    entry.rank === 2 ? 'bg-gray-400' : 
                    entry.rank === 3 ? 'bg-orange-500' : 'bg-purple-500']">
                    {{ entry.rank }}
                  </div>
                  <div>
                    <div class="font-semibold text-gray-900">{{ entry.student_name }}</div>
                    <div class="text-sm text-gray-500">
                      {{ entry.total_badges }} badges • {{ entry.recent_badges?.length || 0 }} recent
                    </div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-2xl font-bold text-purple-600">{{ entry.total_points }}</div>
                  <div class="text-sm text-gray-500">points</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Skills Analysis Tab -->
      <div v-if="!loading && selectedTab === 'skills'" class="space-y-6">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-green-400 to-blue-400">
            <h2 class="text-xl font-bold text-white">📈 Class Skill Analysis</h2>
          </div>
          <div class="p-6">
            <div class="text-center text-gray-500 mb-4">
              Detailed skill breakdown by topic and student performance
            </div>
            <!-- Placeholder for skill charts -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div v-for="skill in ['Addition', 'Subtraction', 'Number Sense']" :key="skill" 
                   class="bg-gray-50 rounded-lg p-4 text-center">
                <div class="text-lg font-semibold text-gray-900 mb-2">{{ skill }}</div>
                <div class="text-3xl font-bold text-purple-600 mb-1">75%</div>
                <div class="text-sm text-gray-500">Class Average</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
