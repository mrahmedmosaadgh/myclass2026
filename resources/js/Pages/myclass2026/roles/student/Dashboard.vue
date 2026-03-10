<script setup>
import RoleLayout from '../../_shared/layouts/RoleLayout.vue';
import AppCard from '../../_shared/components/AppCard.vue';
import { Link } from '@inertiajs/vue3';

// Features mapped from STRUCTURE.md for the Student role
const modules = [
  {
    title: 'My Coursework',
    icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    color: 'text-blue-500',
    links: [
      { name: 'My Classes', url: '/Student/Dashboard', exists: true },
      { name: 'My Schedule', url: '/Students/Schedule', exists: true },
    ]
  },
  {
    title: 'Assessments',
    icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
    color: 'text-green-500',
    links: [
      { name: 'Take Exam', url: '/my_class/QuQuestionBankSystem/QuTakeExam', exists: true },
      { name: 'My Exam Results', url: '/my_class/QuQuestionBankSystem/QuStudentExamList', exists: true },
      { name: 'My Grades', url: '#', isComingSoon: true },
    ]
  },
  {
    title: 'Practice & Review',
    icon: 'M13 10V3L4 14h7v7l9-11h-7z',
    color: 'text-yellow-500',
    links: [
      { name: 'Skill Practice', url: '/SkillPractice', exists: true },
      { name: 'Vocabulary Flashcards', url: '/VocabularyFlashcards', exists: true },
    ]
  }
];
</script>

<template>
  <RoleLayout title="Student Portal">
    
    <div class="mb-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 bg-gradient-to-r from-blue-500 to-cyan-500">
        <h2 class="text-2xl font-bold text-white mb-2">Welcome to your classes!</h2>
        <p class="text-blue-100">Review your schedule, take exams, and practice your skills below.</p>
      </div>
    </div>

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
              <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600">{{ link.name }}</span>
              <svg class="h-4 w-4 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
