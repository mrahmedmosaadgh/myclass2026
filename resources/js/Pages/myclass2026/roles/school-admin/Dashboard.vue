<script setup>
import RoleLayout from '../../_shared/layouts/RoleLayout.vue';
import AppCard from '../../_shared/components/AppCard.vue';
import { Link } from '@inertiajs/vue3';

// Features mapped from my_class/admin/ for the School Admin role
const modules = [
  {
    title: 'School Organization',
    icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    color: 'text-indigo-600',
    links: [
      { name: 'Schools', url: '/my_class/admin/Schools' },
      { name: 'Academic Years', url: '/my_class/admin/AcademicYears' },
      { name: 'Semesters', url: '/my_class/admin/Semesters' },
      { name: 'Stages', url: '/my_class/admin/Stages' },
    ]
  },
  {
    title: 'People Management',
    icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    color: 'text-teal-600',
    links: [
      { name: 'Teachers', url: '/my_class/admin/Teachers' },
      { name: 'Students', url: '/my_class/admin/Students' },
      { name: 'Parents', url: '/my_class/admin/StudentParents' },
    ]
  },
  {
    title: 'Academic Structure',
    icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    color: 'text-blue-600',
    links: [
      { name: 'Classrooms', url: '/my_class/admin/Classrooms' },
      { name: 'Subjects', url: '/my_class/admin/Subjects' },
      { name: 'Grades', url: '/my_class/admin/Grades' },
      { name: 'Curriculum', url: '/my_class/admin/Curriculum' },
      { name: 'Course Management', url: '/CourseManagement' },
    ]
  },
  {
    title: 'Scheduling',
    icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    color: 'text-pink-600',
    links: [
      { name: 'Schedules', url: '/my_class/admin/Schedules' },
      { name: 'Schedule Dailies', url: '/my_class/admin/ScheduleDailies' },
      { name: 'Period Activities', url: '/my_class/admin/PeriodActivities' },
      { name: 'Period Details', url: '/my_class/admin/PeriodDetails' },
    ]
  },
  {
    title: 'Assessment Admin',
    icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    color: 'text-green-600',
    links: [
      { name: 'Question Banks', url: '/my_class/admin/QuestionBanks' },
      { name: 'Semester Tests', url: '/my_class/admin/SemesterTests' },
    ]
  },
  {
    title: 'System Settings',
    icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
    color: 'text-gray-600',
    links: [
      { name: 'Menu Management', url: '/Admin/MenuManagement' },
      { name: 'QR Admin', url: '/my_class/admin/qr' },
    ]
  }
];
</script>

<template>
  <RoleLayout title="School Administration">
    <div class="mb-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 bg-gradient-to-r from-gray-800 to-indigo-900 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-white mb-2">School Admin Dashboard</h2>
        <p class="text-gray-200">Manage all structural, academic, and administrative data.</p>
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
            <Link :href="link.url" class="group flex items-center justify-between p-2 rounded-md hover:bg-gray-50 transition-colors">
              <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-600">{{ link.name }}</span>
              <svg class="h-4 w-4 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </Link>
          </li>
        </ul>
      </AppCard>

    </div>
  </RoleLayout>
</template>
