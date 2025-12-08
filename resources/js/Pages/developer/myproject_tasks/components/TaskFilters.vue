<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex flex-wrap gap-4 items-end">
      <!-- Status Filter -->
      <div class="flex-1 min-w-48">
        <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">
          Status
        </label>
        <select
          id="status-filter"
          v-model="localFilters.status"
          @change="updateFilters"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
        >
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
        </select>
      </div>

      <!-- Priority Filter -->
      <div class="flex-1 min-w-48">
        <label for="priority-filter" class="block text-sm font-medium text-gray-700 mb-2">
          Priority
        </label>
        <select
          id="priority-filter"
          v-model="localFilters.priority"
          @change="updateFilters"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
        >
          <option value="">All Priorities</option>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
      </div>

      <!-- Search Filter -->
      <div class="flex-2 min-w-64">
        <label for="search-filter" class="block text-sm font-medium text-gray-700 mb-2">
          Search
        </label>
        <div class="relative">
          <input
            id="search-filter"
            v-model="localFilters.search"
            @input="debouncedUpdateFilters"
            type="text"
            placeholder="Search by title or description..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
          >
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <button
            v-if="localFilters.search"
            @click="clearSearch"
            class="absolute inset-y-0 right-0 pr-3 flex items-center"
          >
            <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Clear Filters Button -->
      <div class="flex-shrink-0">
        <button
          @click="clearAllFilters"
          :disabled="!hasActiveFilters"
          class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 disabled:text-gray-400 disabled:cursor-not-allowed transition-colors"
        >
          Clear Filters
        </button>
      </div>
    </div>

    <!-- Active Filters Display -->
    <div v-if="hasActiveFilters" class="mt-4 flex flex-wrap gap-2">
      <span class="text-sm text-gray-600">Active filters:</span>
      
      <span
        v-if="localFilters.status"
        class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full"
      >
        Status: {{ formatStatus(localFilters.status) }}
        <button @click="clearFilter('status')" class="hover:text-blue-600">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </span>

      <span
        v-if="localFilters.priority"
        class="inline-flex items-center gap-1 px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-full"
      >
        Priority: {{ formatPriority(localFilters.priority) }}
        <button @click="clearFilter('priority')" class="hover:text-orange-600">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </span>

      <span
        v-if="localFilters.search"
        class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full"
      >
        Search: "{{ localFilters.search }}"
        <button @click="clearFilter('search')" class="hover:text-green-600">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'

const props = defineProps({
  filters: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:filters'])

// Local filters state
const localFilters = reactive({
  status: props.filters.status || '',
  priority: props.filters.priority || '',
  search: props.filters.search || ''
})

// Debounce timer for search
let searchTimeout = null

// Computed properties
const hasActiveFilters = computed(() => {
  return localFilters.status || localFilters.priority || localFilters.search
})

// Watch for external filter changes
watch(() => props.filters, (newFilters) => {
  localFilters.status = newFilters.status || ''
  localFilters.priority = newFilters.priority || ''
  localFilters.search = newFilters.search || ''
}, { deep: true })

// Methods
const updateFilters = () => {
  emit('update:filters', { ...localFilters })
}

const debouncedUpdateFilters = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  
  searchTimeout = setTimeout(() => {
    updateFilters()
  }, 300) // 300ms debounce
}

const clearSearch = () => {
  localFilters.search = ''
  updateFilters()
}

const clearFilter = (filterName) => {
  localFilters[filterName] = ''
  updateFilters()
}

const clearAllFilters = () => {
  localFilters.status = ''
  localFilters.priority = ''
  localFilters.search = ''
  updateFilters()
}

const formatStatus = (status) => {
  const labels = {
    pending: 'Pending',
    in_progress: 'In Progress',
    completed: 'Completed'
  }
  return labels[status] || status
}

const formatPriority = (priority) => {
  const labels = {
    low: 'Low',
    medium: 'Medium',
    high: 'High'
  }
  return labels[priority] || priority
}
</script>