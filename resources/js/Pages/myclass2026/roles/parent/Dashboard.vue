<script setup>
import { ref } from 'vue';
import RoleLayout from '../../_shared/layouts/RoleLayout.vue';
import AppCard from '../../_shared/components/AppCard.vue';
import { Link } from '@inertiajs/vue3';

// Features mapped to the new Parent Portal
const modules = [
  {
    title: 'Academic Progress',
    icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
    color: 'text-green-500',
    links: [
      { name: 'Grades & Scores', url: '/myclass2026/roles/parent/ChildProgress' },
      { name: 'Recent Assignments', url: '/myclass2026/roles/parent/ChildProgress' },
      { name: 'Term Reports', url: '/myclass2026/roles/parent/Reports' },
    ]
  },
  {
    title: 'School Communication',
    icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    color: 'text-blue-500',
    links: [
      { name: 'Message Teachers', url: '/myclass2026/roles/parent/Communication' },
      { name: 'School Announcements', url: '/myclass2026/roles/parent/Communication' },
    ]
  },
  {
    title: 'Behavior & Attendance',
    icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    color: 'text-purple-500',
    links: [
      { name: 'Attendance Record', url: '/myclass2026/roles/parent/Reports' },
      { name: 'Behavior Log', url: '/myclass2026/roles/parent/Reports' },
    ]
  }
];

const children = ref([
  { id: 1, name: 'Sara Ahmed', grade: 'Grade 10', school: 'International High School', avatar: 'S' },
  { id: 2, name: 'Ali Ahmed', grade: 'Grade 7', school: 'Middle School', avatar: 'A' },
]);

const selectedChild = ref(children.value[0].id);

</script>

<template>
  <RoleLayout title="Parent Portal">
    
    <div class="mb-8 bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-indigo-500">
      <div class="p-6 bg-gradient-to-r from-indigo-50 to-white">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome to your Family Dashboard</h2>
            <p class="text-gray-600">Monitor your children's progress, attendance, and communicate with the school.</p>
          </div>
          
          <div class="mt-4 md:mt-0 flex items-center space-x-3 bg-white p-3 rounded-lg shadow-sm border border-gray-100">
            <span class="text-sm font-medium text-gray-500">Viewing:</span>
            <select v-model="selectedChild" class="text-sm font-semibold rounded-md border-gray-300 py-1.5 pl-3 pr-8 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 text-indigo-700 bg-indigo-50">
              <option v-for="child in children" :key="child.id" :value="child.id">{{ child.name }} ({{ child.grade }})</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      
      <AppCard v-for="(module, idx) in modules" :key="idx" class="hover:shadow-md transition-shadow">
        <template #header>
          <div class="flex items-center space-x-3 w-full">
            <div :class="module.color + ' bg-opacity-10 p-2 rounded-lg'">
              <svg class="h-6 w-6" :class="module.color" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="module.icon" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">{{ module.title }}</h3>
          </div>
        </template>
        
        <ul class="space-y-2">
          <li v-for="(link, i) in module.links" :key="i">
            <Link :href="link.url" class="group flex items-center justify-between p-2 rounded-md hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
              <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-600 transition-colors">{{ link.name }}</span>
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
