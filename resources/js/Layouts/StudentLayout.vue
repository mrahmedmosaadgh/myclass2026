<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';

const navigation = [
  { name: 'Dashboard', href: route('dashboard'), icon: '📊' },
  { name: 'My Classes', href: route('dashboard'), icon: '📚' },
  { name: 'Assignments', href: route('dashboard'), icon: '📝' },
  { name: 'Grades', href: route('dashboard'), icon: '📈' },
  { name: 'Attendance', href: route('dashboard'), icon: '📅' },
  { name: 'Study Materials', href: route('dashboard'), icon: '📖' },
  { name: 'Messages', href: route('dashboard'), icon: '💬' },
  { name: 'Schedule', href: route('dashboard'), icon: '🕒' },
];

const quickActions = [
  { name: 'Submit Assignment', href: route('dashboard'), icon: '📤' },
  { name: 'Join Class', href: route('dashboard'), icon: '🔗' },
  { name: 'View Grades', href: route('dashboard'), icon: '📊' },
];
</script>

<template>
  <BaseLayout>
    <!-- Top Navigation -->
    <template #navigation>
      <div class="flex space-x-4">
        <Link v-for="action in quickActions" 
              :key="action.name"
              :href="action.href"
              class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-md text-green-600 bg-green-50 hover:bg-green-100">
          <span class="mr-1">{{ action.icon }}</span>
          {{ action.name }}
        </Link>
      </div>
    </template>

    <!-- Responsive Navigation -->
    <template #responsive-navigation>
      <div class="pt-2 pb-3 space-y-1">
        <Link v-for="item in navigation"
              :key="item.name"
              :href="item.href"
              class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300">
          {{ item.icon }} {{ item.name }}
        </Link>
      </div>
    </template>

    <!-- Sidebar -->
    <template #sidebar>
      <nav class="mt-5 px-2">
        <div class="space-y-1">
          <Link v-for="item in navigation"
                :key="item.name"
                :href="item.href"
                :class="[$page.url.startsWith(item.href) 
                  ? 'bg-green-50 text-green-600'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                  'group flex items-center px-2 py-2 text-base font-medium rounded-md']">
            <span class="mr-3 flex-shrink-0">{{ item.icon }}</span>
            {{ item.name }}
          </Link>
        </div>
      </nav>

      <!-- Progress Overview -->
      <div class="mt-8 px-4">
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
          My Progress
        </h3>
        <div class="mt-2 space-y-2">
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">Overall Grade</p>
            <p class="text-2xl font-semibold text-gray-900">A-</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">Attendance Rate</p>
            <p class="text-2xl font-semibold text-gray-900">95%</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">Due Assignments</p>
            <p class="text-2xl font-semibold text-gray-900">3</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <slot></slot>
  </BaseLayout>
</template>
