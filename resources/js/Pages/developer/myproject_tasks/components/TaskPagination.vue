<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-6 py-4">
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
      <!-- Results Info -->
      <div class="text-sm text-gray-700">
        Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total }} results
      </div>

      <!-- Pagination Controls -->
      <div class="flex items-center gap-2">
        <!-- Previous Button -->
        <button
          @click="goToPage(pagination.current_page - 1)"
          :disabled="pagination.current_page <= 1"
          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          Previous
        </button>

        <!-- Page Numbers -->
        <div class="flex items-center gap-1">
          <!-- First page -->
          <button
            v-if="showFirstPage"
            @click="goToPage(1)"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            1
          </button>

          <!-- First ellipsis -->
          <span v-if="showFirstEllipsis" class="px-2 text-gray-500">...</span>

          <!-- Visible page numbers -->
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              page === pagination.current_page
                ? 'bg-blue-600 text-white border border-blue-600'
                : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </button>

          <!-- Last ellipsis -->
          <span v-if="showLastEllipsis" class="px-2 text-gray-500">...</span>

          <!-- Last page -->
          <button
            v-if="showLastPage"
            @click="goToPage(pagination.last_page)"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            {{ pagination.last_page }}
          </button>
        </div>

        <!-- Next Button -->
        <button
          @click="goToPage(pagination.current_page + 1)"
          :disabled="pagination.current_page >= pagination.last_page"
          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          Next
        </button>
      </div>

      <!-- Per Page Selector -->
      <div class="flex items-center gap-2">
        <label for="per-page" class="text-sm text-gray-700">Show:</label>
        <select
          id="per-page"
          :value="pagination.per_page"
          @change="changePerPage($event.target.value)"
          class="px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="20">20</option>
          <option value="50">50</option>
        </select>
        <span class="text-sm text-gray-700">per page</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  pagination: {
    type: Object,
    required: true,
    default: () => ({
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
      from: 0,
      to: 0
    })
  }
})

const emit = defineEmits(['update:page', 'update:per-page'])

// Computed properties for pagination logic
const visiblePages = computed(() => {
  const current = props.pagination.current_page
  const last = props.pagination.last_page
  const delta = 2 // Number of pages to show on each side of current page
  
  let start = Math.max(1, current - delta)
  let end = Math.min(last, current + delta)
  
  // Adjust if we're near the beginning or end
  if (current <= delta + 1) {
    end = Math.min(last, 2 * delta + 2)
  }
  if (current >= last - delta) {
    start = Math.max(1, last - 2 * delta - 1)
  }
  
  const pages = []
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const showFirstPage = computed(() => {
  return props.pagination.last_page > 1 && !visiblePages.value.includes(1)
})

const showLastPage = computed(() => {
  return props.pagination.last_page > 1 && !visiblePages.value.includes(props.pagination.last_page)
})

const showFirstEllipsis = computed(() => {
  return showFirstPage.value && visiblePages.value[0] > 2
})

const showLastEllipsis = computed(() => {
  return showLastPage.value && visiblePages.value[visiblePages.value.length - 1] < props.pagination.last_page - 1
})

// Methods
const goToPage = (page) => {
  if (page >= 1 && page <= props.pagination.last_page && page !== props.pagination.current_page) {
    emit('update:page', page)
  }
}

const changePerPage = (perPage) => {
  const newPerPage = parseInt(perPage)
  if (newPerPage !== props.pagination.per_page) {
    emit('update:per-page', newPerPage)
  }
}
</script>