<script setup>
import { ref } from 'vue';
import RoleLayout from '../../../_shared/layouts/RoleLayout.vue';
import AppCard from '../../../_shared/components/AppCard.vue';

const recentScores = ref([
  { id: 1, subject: 'Mathematics', title: 'Algebra Midterm', score: 92, max: 100, date: 'Mar 5, 2026' },
  { id: 2, title: 'Cell Biology Quiz', subject: 'Science', score: 85, max: 100, date: 'Mar 2, 2026' },
  { id: 3, title: 'Essay: The Great Gatsby', subject: 'Literature', score: 88, max: 100, date: 'Feb 28, 2026' },
]);

const currentStanding = ref([
  { subject: 'Mathematics', grade: 'A', trend: 'up' },
  { subject: 'Science', grade: 'B+', trend: 'stable' },
  { subject: 'Literature', grade: 'A-', trend: 'up' },
  { subject: 'History', grade: 'B', trend: 'down' },
]);
</script>

<template>
  <RoleLayout title="Academic Progress">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Current Standing Summary -->
      <div class="lg:col-span-1 border-r border-gray-200 pr-0 lg:pr-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Current Grades</h3>
        <div class="space-y-4">
          <div v-for="standing in currentStanding" :key="standing.subject" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex items-center justify-between">
            <span class="font-medium text-gray-700">{{ standing.subject }}</span>
            <div class="flex items-center space-x-3">
              <span class="text-xl font-bold" :class="standing.grade.includes('A') ? 'text-green-600' : 'text-blue-600'">
                {{ standing.grade }}
              </span>
              <svg v-if="standing.trend === 'up'" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
              <svg v-if="standing.trend === 'down'" class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
              <svg v-if="standing.trend === 'stable'" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path></svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Scores -->
      <div class="lg:col-span-2">
        <AppCard title="Recent Assessment Scores" no-padding>
          <ul class="divide-y divide-gray-200">
            <li v-for="score in recentScores" :key="score.id" class="p-4 hover:bg-gray-50 transition-colors">
              <div class="flex justify-between items-center">
                <div>
                  <p class="text-sm font-semibold text-gray-900">{{ score.title }}</p>
                  <p class="text-xs text-gray-500">{{ score.subject }} • {{ score.date }}</p>
                </div>
                <div class="text-right">
                  <div class="text-lg font-bold" :class="score.score >= 90 ? 'text-green-600' : 'text-indigo-600'">
                    {{ score.score }}<span class="text-sm text-gray-500 font-normal">/{{ score.max }}</span>
                  </div>
                </div>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                <div class="h-1.5 rounded-full" 
                     :class="score.score >= 90 ? 'bg-green-500' : 'bg-indigo-500'" 
                     :style="`width: ${(score.score / score.max) * 100}%`"></div>
              </div>
            </li>
          </ul>
        </AppCard>
      </div>

    </div>
  </RoleLayout>
</template>
