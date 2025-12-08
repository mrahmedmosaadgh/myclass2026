<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';


const navigation = [
  { name: 'Dashboard', href: route('dashboard'), icon: '📊' },
  {
    name: 'Users',
    icon: '👥',
    children: [
      { name: 'Teachers', href: route('dashboard'), icon: '👨‍🏫' },
      { name: 'Students', href: route('dashboard'), icon: '👨‍🎓' },
      { name: 'Parents', href: route('dashboard'), icon: '👨‍👩‍👧' },
      { name: 'Staff', href: route('dashboard'), icon: '👥' },
    ]
  },
  {
    name: 'Academics',
    icon: '🎓',
    children: [
      { name: 'Classes', href: route('dashboard'), icon: '📚' },
      { name: 'Subjects', href: route('dashboard'), icon: '📖' },
      { name: 'Schedules', href: route('dashboard'), icon: '📅' },
    ]
  },
  { name: 'Attendance', href: route('dashboard'), icon: '📋' },
  { name: 'Reports', href: route('dashboard'), icon: '📊' },
  { name: 'Finance', href: route('dashboard'), icon: '💰' },
  { name: 'Settings', href: route('dashboard'), icon: '⚙️' },
];

const quickActions = [
  { name: 'Add User', href: route('dashboard'), icon: '➕' },
  { name: 'Create Class', href: route('dashboard'), icon: '📚' },
  { name: 'View Reports', href: route('dashboard'), icon: '📊' },
];

const expandedItems = ref(new Set());

const toggleExpand = (itemName) => {
  if (expandedItems.value.has(itemName)) {
    expandedItems.value.delete(itemName);
  } else {
    expandedItems.value.add(itemName);
  }
};
</script>

<template>
  <BaseLayout>
    <!-- Top Navigation -->
    <template #navigation>
      <div class="flex space-x-4">
        <Link v-for="action in quickActions"
              :key="action.name"
              :href="action.href"
              class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-md text-indigo-600 bg-indigo-50 hover:bg-indigo-100">
          <span class="mr-1">{{ action.icon }}</span>
          {{ action.name }}
        </Link>
      </div>
    </template>

    <!-- Responsive Navigation -->
    <template #responsive-navigation>
      <div class="pt-2 pb-3 space-y-1">
        <template v-for="item in navigation" :key="item.name">
          <button v-if="item.children"
                  @click="toggleExpand(item.name)"
                  class="w-full text-left block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300">
            {{ item.icon }} {{ item.name }}
          </button>
          <div v-if="item.children && expandedItems.has(item.name)" class="ml-4">
            <Link v-for="child in item.children"
                  :key="child.name"
                  :href="child.href"
                  class="block pl-3 pr-4 py-2 border-l-4 text-sm font-medium hover:bg-gray-50 hover:border-gray-300">
              {{ child.icon }} {{ child.name }}
            </Link>
          </div>
          <Link v-else-if="!item.children"
                :href="item.href"
                class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300">
            {{ item.icon }} {{ item.name }}
          </Link>
        </template>
      </div>
    </template>

    <!-- Sidebar -->
    <template #sidebar>
      <nav class="mt-5 px-2">
        <div class="space-y-1">
          <template v-for="item in navigation" :key="item.name">
            <!-- Items with children -->
            <div v-if="item.children" class="space-y-1">
              <button @click="toggleExpand(item.name)"
                      class="w-full group flex items-center px-2 py-2 text-base font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <span class="mr-3 flex-shrink-0">{{ item.icon }}</span>
                {{ item.name }}
                <svg :class="[expandedItems.has(item.name) ? 'transform rotate-90' : '', 'ml-auto h-5 w-5']"
                     xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 20 20"
                     fill="currentColor">
                  <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
              </button>

              <div v-show="expandedItems.has(item.name)" class="space-y-1">
                <Link v-for="child in item.children"
                      :key="child.name"
                      :href="child.href"
                      :class="[$page.url.startsWith(child.href)
                        ? 'bg-indigo-50 text-indigo-600'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                        'group flex items-center pl-10 pr-2 py-2 text-sm font-medium rounded-md']">
                  <span class="mr-3 flex-shrink-0">{{ child.icon }}</span>
                  {{ child.name }}
                </Link>
              </div>
            </div>

            <!-- Regular items -->
            <Link v-else
                  :href="item.href"
                  :class="[$page.url.startsWith(item.href)
                    ? 'bg-indigo-50 text-indigo-600'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                    'group flex items-center px-2 py-2 text-base font-medium rounded-md']">
              <span class="mr-3 flex-shrink-0">{{ item.icon }}</span>
              {{ item.name }}
            </Link>
          </template>
        </div>
      </nav>

      <!-- System Overview -->
      <div class="mt-8 px-4">
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
          System Overview
        </h3>
        <div class="mt-2 space-y-2">
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">Active Users</p>
            <p class="text-2xl font-semibold text-gray-900">1,234</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">Classes Today</p>
            <p class="text-2xl font-semibold text-gray-900">42</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">System Status</p>
            <p class="text-sm font-medium text-green-600">All Systems Operational</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <slot></slot>
  </BaseLayout>
</template>

<style scoped>
.transform {
  transition: transform 0.15s ease-in-out;
}
</style>
