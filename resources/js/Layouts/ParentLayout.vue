<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';

const navigation = [
  { name: 'Dashboard', href: route('parent.dashboard'), icon: '📊' },
  { name: 'My Children', href: route('parent.children'), icon: '👨‍👩‍👧‍👦' },
  { name: 'Academic Progress', href: route('parent.progress'), icon: '📈' },
  { name: 'Attendance', href: route('parent.attendance'), icon: '📅' },
  { name: 'Behavior', href: route('parent.behavior'), icon: '🎯' },
  { name: 'Messages', href: route('parent.messages'), icon: '💬' },
  { name: 'Payments', href: route('parent.payments'), icon: '💳' },
  { name: 'School Calendar', href: route('parent.calendar'), icon: '📆' },
];

const quickActions = [
  { name: 'Report Absence', href: route('parent.report-absence'), icon: '🏥' },
  { name: 'Schedule Meeting', href: route('parent.schedule-meeting'), icon: '🤝' },
  { name: 'Make Payment', href: route('parent.make-payment'), icon: '💰' },
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
              class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-md text-purple-600 bg-purple-50 hover:bg-purple-100">
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
                  ? 'bg-purple-50 text-purple-600'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                  'group flex items-center px-2 py-2 text-base font-medium rounded-md']">
            <span class="mr-3 flex-shrink-0">{{ item.icon }}</span>
            {{ item.name }}
          </Link>
        </div>
      </nav>

      <!-- Children Overview -->
      <div class="mt-8 px-4">
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
          Children Overview
        </h3>
        <div class="mt-2 space-y-2">
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
              <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=John+Doe" alt="John Doe">
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">John Doe</p>
                <p class="text-xs text-gray-500">Grade 8-A</p>
              </div>
            </div>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
              <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Jane+Doe" alt="Jane Doe">
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">Jane Doe</p>
                <p class="text-xs text-gray-500">Grade 6-B</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Important Dates -->
        <h3 class="px-3 mt-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">
          Important Dates
        </h3>
        <div class="mt-2 space-y-2">
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">Parent-Teacher Meeting</p>
            <p class="text-xs text-gray-500">May 15, 2025</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">School Event</p>
            <p class="text-xs text-gray-500">May 20, 2025</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <slot></slot>
  </BaseLayout>
</template>
