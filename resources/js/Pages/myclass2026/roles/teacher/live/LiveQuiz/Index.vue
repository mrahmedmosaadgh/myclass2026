<script setup>
import { ref } from 'vue';
import RoleLayout from '../../../../_shared/layouts/RoleLayout.vue';
import AppCard from '../../../../_shared/components/AppCard.vue';
import { Link } from '@inertiajs/vue3';

// High-level wrapper connecting the user directly to the `QuQuizManagement` legacy module
const recentSessions = ref([
  { id: '1001', title: 'Midterm Prep - Physics', date: 'Yesterday', students: 24, avgScore: 82 },
  { id: '1002', title: 'Pop Quiz - Algebra', date: 'Last Week', students: 25, avgScore: 76 },
]);

</script>

<template>
  <RoleLayout title="Live Quiz Dashboard">
    
    <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <!-- Launch Panel -->
      <AppCard title="Launch a Quiz" class="bg-gradient-to-br from-indigo-50 to-white">
        <div class="text-center py-6">
          <svg class="w-16 h-16 mx-auto text-indigo-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="text-lg font-bold text-gray-900 mb-2">Host an interactive session</h3>
          <p class="text-sm text-gray-600 mb-6">Play quizzes live with your students to test real-time comprehension.</p>
          
          <Link href="/my_class/QuQuestionBankSystem/QuQuizManagement" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
            Open Quiz Manager
          </Link>
        </div>
      </AppCard>

      <!-- Manage Panel -->
      <AppCard title="Smart Scanner" class="bg-gradient-to-br from-blue-50 to-white">
        <div class="text-center py-6">
          <svg class="w-16 h-16 mx-auto text-blue-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <h3 class="text-lg font-bold text-gray-900 mb-2">Paper-based Live Quizzes</h3>
          <p class="text-sm text-gray-600 mb-6">Use the webcam to read student QR cards instantly.</p>
          
          <Link href="/myclass2026/features/smart-scanner" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
            Open Smart Scanner
          </Link>
        </div>
      </AppCard>

    </div>

    <!-- Recent Sessions -->
    <AppCard title="Recent Sessions" no-padding>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Score</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="session in recentSessions" :key="session.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ session.title }}</div>
                <div class="text-sm text-gray-500">ID: {{ session.id }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ session.date }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ session.students }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  {{ session.avgScore }}%
                </span>
              </td>
            </tr>
            <tr v-if="recentSessions.length === 0">
              <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No recent sessions found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </AppCard>

  </RoleLayout>
</template>
