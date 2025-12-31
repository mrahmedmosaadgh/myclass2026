<script setup>
import { onMounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import { useNavigationStore } from '@/Stores/useNavigationStore';

const navStore = useNavigationStore();

// Fetch menu on mount
onMounted(() => {
  navStore.fetchMenu();
});

// Get navigation from store
const navigation = computed(() => navStore.visibleItems);

const quickActions = computed(() => {
  return [
    { name: 'Take Attendance', href: route('dashboard'), icon: '✍️' },
    { name: 'Create Assignment', href: route('dashboard'), icon: '📋' },
    { name: 'Schedule Class', href: route('dashboard'), icon: '🕒' },
  ];
});
</script>

<template>
  <BaseLayout>
    <!-- Top Navigation -->
    <template #navigation>
      <div class="flex space-x-4">
        <Link v-for="action in quickActions" 
              :key="action.name"
              :href="action.href"
              class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-md text-blue-600 bg-blue-50 hover:bg-blue-100">
          <span class="mr-1">{{ action.icon }}</span>
          {{ action.name }}
        </Link>
      </div>
    </template>

    <!-- Responsive Navigation -->
    <template #responsive-navigation>
      <div class="pt-2 pb-3 space-y-1">
        <div v-if="navStore.isLoading" class="pl-3 pr-4 py-2 text-gray-500">
          Loading navigation...
        </div>
        
        <Link v-else v-for="item in navigation"
              :key="item.id"
              :href="item.route ? route(item.route) : '#'"
              class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300">
          {{ item.icon }} {{ item.label }}
        </Link>
      </div>
    </template>

    <!-- Sidebar -->
    <template #sidebar>
      <nav class="mt-5 px-2">
        <div v-if="navStore.isLoading" class="text-gray-500 px-2 py-2">
          Loading navigation...
        </div>
        
        <div v-else class="space-y-1">
          <Link v-for="item in navigation"
                :key="item.id"
                :href="item.route ? route(item.route) : '#'"
                :class="[item.route && $page.url.startsWith(route(item.route)) 
                  ? 'bg-blue-50 text-blue-600'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                  'group flex items-center px-2 py-2 text-base font-medium rounded-md']">
            <span class="mr-3 flex-shrink-0">{{ item.icon }}</span>
            {{ item.label }}
          </Link>
        </div>
      </nav>

      <!-- Quick Stats -->
      <div class="mt-8 px-4">
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
          Quick Stats
        </h3>
        <div class="mt-2 space-y-2">
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">Today's Classes</p>
            <p class="text-2xl font-semibold text-gray-900">5</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">Pending Assignments</p>
            <p class="text-2xl font-semibold text-gray-900">12</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">Messages</p>
            <p class="text-2xl font-semibold text-gray-900">3</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <slot></slot>
  </BaseLayout>
</template>
