<script setup>
import { ref, onMounted, computed } from 'vue';
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

// Quick actions - can be filtered from navigation or hardcoded
const quickActions = computed(() => {
  // You can either hardcode these or filter from navigation
  // For now, keeping hardcoded for quick actions
  return [
    { name: 'Add User', href: route('dashboard'), icon: '➕' },
    { name: 'Create Class', href: route('dashboard'), icon: '📚' },
    { name: 'View Reports', href: route('dashboard'), icon: '📊' },
  ];
});

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
        <!-- Loading state -->
        <div v-if="navStore.isLoading" class="pl-3 pr-4 py-2 text-gray-500">
          Loading navigation...
        </div>
        
        <template v-else v-for="item in navigation" :key="item.id">
          <button v-if="item.children && item.children.length > 0"
                  @click="toggleExpand(item.label)"
                  class="w-full text-left block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300">
            {{ item.icon }} {{ item.label }}
          </button>
          <div v-if="item.children && item.children.length > 0 && expandedItems.has(item.label)" class="ml-4">
            <Link v-for="child in item.children"
                  :key="child.id"
                  :href="child.route ? route(child.route) : '#'"
                  class="block pl-3 pr-4 py-2 border-l-4 text-sm font-medium hover:bg-gray-50 hover:border-gray-300">
              {{ child.icon }} {{ child.label }}
            </Link>
          </div>
          <Link v-else-if="!item.children || item.children.length === 0"
                :href="item.route ? route(item.route) : '#'"
                class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300">
            {{ item.icon }} {{ item.label }}
          </Link>
        </template>
      </div>
    </template>

    <!-- Sidebar -->
    <template #sidebar>
      <nav class="mt-5 px-2">
        <div v-if="navStore.isLoading" class="text-gray-500 px-2 py-2">
          Loading navigation...
        </div>
        
        <div v-else class="space-y-1">
          <template v-for="item in navigation" :key="item.id">
            <!-- Items with children -->
            <div v-if="item.children && item.children.length > 0" class="space-y-1">
              <button @click="toggleExpand(item.label)"
                      class="w-full group flex items-center px-2 py-2 text-base font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <span class="mr-3 flex-shrink-0">{{ item.icon }}</span>
                {{ item.label }}
                <svg :class="[expandedItems.has(item.label) ? 'transform rotate-90' : '', 'ml-auto h-5 w-5']"
                     xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 20 20"
                     fill="currentColor">
                  <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
              </button>

              <div v-show="expandedItems.has(item.label)" class="space-y-1">
                <Link v-for="child in item.children"
                      :key="child.id"
                      :href="child.route ? route(child.route) : '#'"
                      :class="[child.route && $page.url.startsWith(route(child.route))
                        ? 'bg-indigo-50 text-indigo-600'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                        'group flex items-center pl-10 pr-2 py-2 text-sm font-medium rounded-md']">
                  <span class="mr-3 flex-shrink-0">{{ child.icon }}</span>
                  {{ child.label }}
                </Link>
              </div>
            </div>

            <!-- Regular items -->
            <Link v-else
                  :href="item.route ? route(item.route) : '#'"
                  :class="[item.route && $page.url.startsWith(route(item.route))
                    ? 'bg-indigo-50 text-indigo-600'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                    'group flex items-center px-2 py-2 text-base font-medium rounded-md']">
              <span class="mr-3 flex-shrink-0">{{ item.icon }}</span>
              {{ item.label }}
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
