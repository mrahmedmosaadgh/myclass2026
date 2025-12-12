<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Task Tree</h2>
      <div class="flex items-center gap-2">
        <button
          @click="toggleViewMode"
          class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-200"
        >
          {{ viewMode === 'tree' ? 'Flat View' : 'Tree View' }}
        </button>
        <button
          @click="expandAll"
          class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-200"
        >
          Expand All
        </button>
        <button
          @click="collapseAll"
          class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-200"
        >
          Collapse All
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && tasks.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No tasks found</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new task.</p>
    </div>

    <!-- Task Tree -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Task
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Due Date
            </th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
          <template v-for="task in visibleTasks" :key="task.id">
            <TaskTreeRow
              :task="task"
              :expanded="expandedTasks.has(task.id)"
              :view-mode="viewMode"
              :all-tasks="tasks"
              @toggle-expand="toggleExpand"
              @edit="$emit('edit', $event)"
              @delete="$emit('delete', $event)"
              @add-subtask="$emit('add-subtask', $event)"
              @move-task="handleMoveTask"
              @update-status="handleUpdateStatus"
              @update-priority="handleUpdatePriority"
              @inline-update="handleInlineUpdate"
            />
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import TaskTreeRow from './TaskTreeRow.vue'

const props = defineProps({
  tasks: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  viewMode: {
    type: String,
    default: 'tree'
  }
})

const emit = defineEmits([
  'edit', 
  'delete', 
  'add-subtask', 
  'move-task', 
  'update-status', 
  'update-priority',
  'inline-update',
  'toggle-view-mode'
])

// Expanded tasks state
const expandedTasks = ref(new Set())

// Computed property to get visible tasks based on expanded state
const visibleTasks = computed(() => {
  if (props.viewMode === 'flat') {
    return props.tasks
  }
  
  // For tree view with flattened structure from backend
  const visible = []
  const taskMap = new Map()
  
  // Create a map for quick lookup
  props.tasks.forEach(task => {
    taskMap.set(task.id, task)
  })
  
  props.tasks.forEach(task => {
    let shouldShow = true
    
    // Check if all parent tasks are expanded
    let currentTask = task
    while (currentTask.parent_id) {
      const parent = taskMap.get(currentTask.parent_id)
      if (parent && !expandedTasks.value.has(parent.id)) {
        shouldShow = false
        break
      }
      currentTask = parent || { parent_id: null }
    }
    
    if (shouldShow) {
      visible.push(task)
    }
  })
  
  return visible
})

// Auto-expand root tasks by default
watch(() => props.tasks, (newTasks) => {
  if (newTasks.length > 0) {
    // Auto-expand root tasks (depth 0)
    newTasks.forEach(task => {
      if (task.depth === 0) {
        expandedTasks.value.add(task.id)
      }
    })
  }
}, { immediate: true })

// Methods
const toggleExpand = (taskId) => {
  if (expandedTasks.value.has(taskId)) {
    expandedTasks.value.delete(taskId)
  } else {
    expandedTasks.value.add(taskId)
  }
}

const expandAll = () => {
  props.tasks.forEach(task => {
    expandedTasks.value.add(task.id)
  })
}

const collapseAll = () => {
  expandedTasks.value.clear()
}

const toggleViewMode = () => {
  emit('toggle-view-mode')
}

const handleMoveTask = (taskData) => {
  emit('move-task', taskData)
}

const handleUpdateStatus = (taskData) => {
  emit('update-status', taskData)
}

const handleUpdatePriority = (taskData) => {
  emit('update-priority', taskData)
}

const handleInlineUpdate = (updateData) => {
  emit('inline-update', updateData)
}
</script>