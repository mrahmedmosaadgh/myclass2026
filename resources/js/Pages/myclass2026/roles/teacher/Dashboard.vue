<script setup>
import RoleLayout from '../../_shared/layouts/RoleLayout.vue';
import AppCard from '../../_shared/components/AppCard.vue';
import { Link } from '@inertiajs/vue3';

// Features mapped directly from GUIDE.md / STRUCTURE.md
const modules = [
  {
    title: 'Instructional Planning',
    icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    color: 'text-indigo-600',
    links: [
      { name: 'Curriculum Map', url: '/my_class/admin/Curriculum', exists: true },
      { name: 'Weekly Planning', url: '/WeeklyPlans', exists: true },
      { name: 'Lesson Prep', url: '/LessonTemplateManager', exists: true },
    ]
  },
  {
    title: 'Assessment Tools',
    icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    color: 'text-green-600',
    links: [
      { name: 'Question Bank', url: '/my_class/QuQuestionBankSystem/QuQuestionList.vue', exists: true },
      { name: 'Exam Builder', url: '/my_class/QuQuestionBankSystem/QuExamForm.vue', exists: true },
      { name: 'Exam Grading', url: '/my_class/QuQuestionBankSystem/QuGrading.vue', exists: true },
      { name: 'Exam Reports', url: '/my_class/QuQuestionBankSystem/QuAnalyticsDashboard.vue', exists: true },
    ]
  },
  {
    title: 'Live Interaction',
    icon: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
    color: 'text-red-600',
    links: [
      { name: 'Live Quiz', url: '/my_class/QuQuestionBankSystem/QuQuizManagement', exists: true },
      { name: 'Smart Scanner', url: '/myclass2026/features/smart-scanner', isNew: true },
      { name: 'Live Lesson (Zoom)', url: '#', isComingSoon: true },
    ]
  },
  {
    title: 'Records & Tracking',
    icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
    color: 'text-yellow-600',
    links: [
      { name: 'Class Records', url: '/my_class/teacher/StudentRecords.vue', exists: true },
      { name: 'Topic Tracking', url: '#', isComingSoon: true },
    ]
  },
  {
    title: 'Time Management',
    icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    color: 'text-teal-600',
    links: [
      { name: 'Calendar', url: '/my_class/teacher/Calendar/Index.vue', exists: true },
      { name: 'Timetable', url: '/my_class/teacher/schedule', exists: true },
      { name: 'To-Do List', url: '#', isComingSoon: true },
    ]
  },
  {
    title: 'Lesson Delivery',
    icon: 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z',
    color: 'text-purple-600',
    links: [
      { name: 'Lesson Presentation', url: '/my_class/teacher/lesson_presentation', exists: true },
      { name: 'Presentation V2', url: '/my_class/teacher/peresntation_2', exists: true },
    ]
  }
];
</script>

<template>
  <RoleLayout title="Teacher Workspace">
    
    <div class="mb-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 bg-gradient-to-r from-indigo-500 to-purple-600">
        <h2 class="text-2xl font-bold text-white mb-2">Welcome back, Teacher!</h2>
        <p class="text-indigo-100">Access all your instructional tools, assessments, and class records from here.</p>
      </div>
    </div>

    <!-- Modules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      
      <AppCard v-for="(module, idx) in modules" :key="idx" class="hover:shadow-md transition-shadow">
        <template #header>
          <div class="flex items-center space-x-3 w-full">
            <svg class="h-6 w-6" :class="module.color" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="module.icon" />
            </svg>
            <h3 class="text-lg font-semibold text-gray-900">{{ module.title }}</h3>
          </div>
        </template>
        
        <ul class="space-y-3">
          <li v-for="(link, i) in module.links" :key="i">
            
            <Link v-if="link.url !== '#'" :href="link.url" class="group flex items-center justify-between p-2 rounded-md hover:bg-gray-50 transition-colors">
              <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-600">{{ link.name }}</span>
              
              <span v-if="link.isNew" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                New
              </span>
              <svg v-else class="h-4 w-4 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </Link>
            
            <div v-else class="flex items-center justify-between p-2 rounded-md opacity-60 cursor-not-allowed">
              <span class="text-sm font-medium text-gray-500">{{ link.name }}</span>
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                Soon
              </span>
            </div>
            
          </li>
        </ul>
      </AppCard>

    </div>
  </RoleLayout>
</template>
