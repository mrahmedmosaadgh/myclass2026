<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Table Header -->
    <div class="px-6 py-4 border-b border-gray-200">
      <h2 class="text-lg font-semibold text-gray-900">Tasks</h2>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && tasks.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks found</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by creating a new task.</p>
    </div>

    <!-- Tasks Table -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              @click="handleSort('title')"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none"
            >
              <div class="flex items-center gap-1">
                Title
                <SortIcon :active="sortBy === 'title'" :direction="sortDirection" />
              </div>
            </th>
            <th
              @click="handleSort('status')"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none"
            >
              <div class="flex items-center gap-1">
                Status
                <SortIcon :active="sortBy === 'status'" :direction="sortDirection" />
              </div>
            </th>
            <th
              @click="handleSort('priority')"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none"
            >
              <div class="flex items-center gap-1">
                Priority
                <SortIcon :active="sortBy === 'priority'" :direction="sortDirection" />
              </div>
            </th>
            <th
              @click="handleSort('due_date')"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none"
            >
              <div class="flex items-center gap-1">
                Due Date
                <SortIcon :active="sortBy === 'due_date'" :direction="sortDirection" />
              </div>
            </th>
            <th
              @click="handleSort('created_at')"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none"
            >
              <div class="flex items-center gap-1">
                Created
                <SortIcon :active="sortBy === 'created_at'" :direction="sortDirection" />
              </div>
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr
            v-for="task in tasks"
            :key="task.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <!-- Title -->
            <td class="px-6 py-4">
              <div class="flex flex-col">
                <div 
                  @dblclick="startEdit(task.id, 'title')"
                  class="text-sm font-medium text-gray-900 truncate max-w-xs cursor-pointer hover:bg-gray-100 px-1 py-0.5 rounded"
                  title="Double-click to edit"
                >
                  {{ task.title }}
                </div>
                <div 
                  v-if="task.description" 
                  @dblclick="startEdit(task.id, 'description')"
                  class="text-sm text-gray-500 truncate max-w-xs cursor-pointer hover:bg-gray-100 px-1 py-0.5 rounded mt-1"
                  title="Double-click to edit"
                >
                  {{ task.description }}
                </div>
                <button
                  v-else
                  @click="startEdit(task.id, 'description')"
                  class="text-xs text-gray-400 hover:text-gray-600 hover:bg-gray-100 px-1 py-0.5 rounded transition-colors text-left mt-1"
                >
                  + Add description
                </button>
              </div>
            </td>

            <!-- Status -->
            <td class="px-6 py-4">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border',
                  getStatusBadgeClass(task.status)
                ]"
              >
                {{ formatStatus(task.status) }}
              </span>
            </td>

            <!-- Priority -->
            <td class="px-6 py-4">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border',
                  getPriorityBadgeClass(task.priority)
                ]"
              >
                {{ formatPriority(task.priority) }}
              </span>
            </td>

            <!-- Due Date -->
            <td class="px-6 py-4">
              <div 
                @dblclick="startEdit(task.id, 'due_date')"
                class="text-sm text-gray-900 cursor-pointer hover:bg-gray-100 px-1 py-0.5 rounded"
                title="Double-click to edit"
              >
                {{ formatDate(task.due_date) }}
              </div>
            </td>

            <!-- Created Date -->
            <td class="px-6 py-4">
              <div class="text-sm text-gray-500">
                {{ formatCreatedDate(task.created_at) }}
              </div>
            </td>

            <!-- Actions -->
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <button
                  @click="$emit('edit', task)"
                  class="text-blue-600 hover:text-blue-800 transition-colors"
                  title="Edit task"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
                <button
                  @click="$emit('delete', task)"
                  class="text-red-600 hover:text-red-800 transition-colors"
                  title="Delete task"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import SortIcon from './SortIcon.vue'

const props = defineProps({
  tasks: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  sortBy: {
    type: String,
    default: 'created_at'
  },
  sortDirection: {
    type: String,
    default: 'desc'
  }
})

const emit = defineEmits(['edit', 'delete', 'sort', 'inline-update'])

// Handle sorting
const handleSort = (column) => {
  let direction = 'asc'
  if (props.sortBy === column && props.sortDirection === 'asc') {
    direction = 'desc'
  }
  emit('sort', column, direction)
}

// Inline editing placeholder (would need full implementation like TaskTreeRow)
const startEdit = (taskId, field) => {
  // For now, just emit the inline-update event
  // In a full implementation, you'd add editing state management here
  console.log(`Start editing ${field} for task ${taskId}`)
}

// Utility functions
const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    in_progress: 'bg-blue-100 text-blue-800 border-blue-200',
    completed: 'bg-green-100 text-green-800 border-green-200'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 border-gray-200'
}

const getPriorityBadgeClass = (priority) => {
  const classes = {
    low: 'bg-gray-100 text-gray-800 border-gray-200',
    medium: 'bg-orange-100 text-orange-800 border-orange-200',
    high: 'bg-red-100 text-red-800 border-red-200'
  }
  return classes[priority] || 'bg-gray-100 text-gray-800 border-gray-200'
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

const formatDate = (dateString) => {
  if (!dateString) return 'No due date'
  
  const date = new Date(dateString)
  const today = new Date()
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)
  
  // Check if it's today
  if (date.toDateString() === today.toDateString()) {
    return 'Today'
  }
  
  // Check if it's tomorrow
  if (date.toDateString() === tomorrow.toDateString()) {
    return 'Tomorrow'
  }
  
  // Check if it's overdue
  if (date < today) {
    const diffTime = Math.abs(today - date)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return `${diffDays} day${diffDays > 1 ? 's' : ''} overdue`
  }
  
  // Format as regular date
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatCreatedDate = (dateString) => {
  if (!dateString) return ''
  
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>