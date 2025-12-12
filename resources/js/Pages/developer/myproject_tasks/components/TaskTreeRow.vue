<template>
  <tr 
    :class="[
      'transition-colors',
      task.depth > 0 ? 'bg-gray-25 dark:bg-gray-900' : 'dark:bg-gray-800',
      editingField ? 'bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-400' : 'hover:bg-gray-50 dark:hover:bg-gray-700'
    ]"
  >
    <!-- Task Title with Hierarchy -->
    <td class="px-6 py-4">
      <div class="flex items-center" :style="{ paddingLeft: `${task.depth * 24}px` }">
        <!-- Expand/Collapse Button -->
        <button
          v-if="hasSubtasks"
          @click="$emit('toggle-expand', task.id)"
          class="mr-2 p-1 hover:bg-gray-200 rounded transition-colors"
        >
          <svg 
            class="w-4 h-4 transition-transform"
            :class="expanded ? 'rotate-90' : ''"
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
        
        <!-- Spacer for tasks without subtasks -->
        <div v-else class="w-6 mr-2"></div>

        <!-- Task Icon -->
        <div class="mr-3">
          <div 
            :class="[
              'w-3 h-3 rounded-full',
              getStatusColor(task.status)
            ]"
          ></div>
        </div>

        <!-- Task Content -->
        <div class="flex-1 min-w-0">
          <!-- Title Editing -->
          <div class="flex items-center gap-2">
            <div 
              v-if="editingField !== 'title'"
              @dblclick="startEdit('title')"
              class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 px-1 py-0.5 rounded"
              title="Double-click to edit"
            >
              {{ task.title }}
            </div>
            <div v-else class="flex items-center gap-2 flex-1">
              <input
                ref="titleInput"
                v-model="editValues.title"
                @blur="saveEdit('title')"
                @keydown.enter="saveEdit('title')"
                @keydown.escape="cancelEdit"
                @keydown.tab="saveEdit('title')"
                class="text-sm font-medium text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border border-blue-300 dark:border-gray-600 rounded px-2 py-1 flex-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                maxlength="255"
                placeholder="Enter task title..."
              >
              <button
                @click="saveEdit('title')"
                class="p-1 text-green-600 hover:text-green-800 rounded"
                title="Save"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </button>
              <button
                @click="cancelEdit"
                class="p-1 text-red-600 hover:text-red-800 rounded"
                title="Cancel"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            <span 
              v-if="task.depth > 0"
              class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full"
            >
              L{{ task.depth }}
            </span>
            <!-- Compact status/priority icons near title -->
            <div class="flex items-center gap-1 ml-1">
              <span :title="'Status: ' + task.status" :class="['w-2.5 h-2.5 rounded-full', getStatusColor(task.status)]"></span>
              <span :title="'Priority: ' + task.priority" :class="['w-2.5 h-2.5 rounded-full', task.priority === 'high' ? 'bg-red-500' : task.priority === 'medium' ? 'bg-orange-500' : 'bg-gray-400']"></span>
            </div>
          </div>
          
          <!-- Description Editing -->
          <div v-if="task.description || editingField === 'description'" class="mt-1">
            <div 
              v-if="editingField !== 'description'"
              @dblclick="startEdit('description')"
              class="text-sm text-gray-500 dark:text-gray-300 truncate cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 px-1 py-0.5 rounded"
              title="Double-click to edit"
            >
              {{ task.description || 'Add description...' }}
            </div>
            <div v-else class="flex items-start gap-2">
              <textarea
                ref="descriptionInput"
                v-model="editValues.description"
                @blur="saveEdit('description')"
                @keydown.ctrl.enter="saveEdit('description')"
                @keydown.escape="cancelEdit"
                @keydown.tab="saveEdit('description')"
                class="text-sm text-gray-500 dark:text-gray-300 bg-white dark:bg-gray-800 border border-blue-300 dark:border-gray-600 rounded px-2 py-1 flex-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                rows="2"
                placeholder="Enter description... (Ctrl+Enter to save, Esc to cancel)"
              ></textarea>
              <div class="flex flex-col gap-1">
                <button
                  @click="saveEdit('description')"
                  class="p-1 text-green-600 hover:text-green-800 rounded"
                  title="Save (Ctrl+Enter)"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                </button>
                <button
                  @click="cancelEdit"
                  class="p-1 text-red-600 hover:text-red-800 rounded"
                  title="Cancel (Esc)"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Add description button when no description exists -->
          <div v-if="!task.description && editingField !== 'description'" class="mt-1">
            <button
              @click="startEdit('description')"
              class="text-xs text-gray-400 hover:text-gray-600 hover:bg-gray-100 px-1 py-0.5 rounded transition-colors"
            >
              + Add description
            </button>
          </div>
        </div>
      </div>
    </td>





    <!-- Due Date -->
    <td class="px-4 py-4">
      <div v-if="editingField !== 'due_date'" class="text-sm text-gray-900 dark:text-gray-100">
        <span 
          @dblclick="startEdit('due_date')"
          class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 px-1 py-0.5 rounded"
          title="Double-click to edit"
        >
          {{ formatDate(task.due_date) }}
        </span>
      </div>
      <div v-else class="flex items-center gap-2">
        <input
          ref="dueDateInput"
          v-model="editValues.due_date"
          @blur="saveEdit('due_date')"
          @keydown.enter="saveEdit('due_date')"
          @keydown.escape="cancelEdit"
          @keydown.tab="saveEdit('due_date')"
          type="date"
          class="text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-blue-300 dark:border-gray-600 rounded px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
        <button
          @click="saveEdit('due_date')"
          class="p-1 text-green-600 hover:text-green-800 rounded"
          title="Save"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </button>
        <button
          @click="cancelEdit"
          class="p-1 text-red-600 hover:text-red-800 rounded"
          title="Cancel"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </td>

    <!-- Actions -->
    <td class="px-4 py-4 text-right">
      <div class="flex justify-end items-center gap-1 relative group">
        <!-- 3-dots menu trigger -->
        <button
          class="p-1 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700 rounded transition-colors"
          title="More"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.75a1.25 1.25 0 110-2.5 1.25 1.25 0 010 2.5zm0 6a1.25 1.25 0 110-2.5 1.25 1.25 0 010 2.5zm0 6a1.25 1.25 0 110-2.5 1.25 1.25 0 010 2.5z"></path>
          </svg>
        </button>
        <!-- Hover menu -->
        <div class="absolute right-0 top-2 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-3 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity z-10">
          <div class="text-xs font-semibold text-gray-500 dark:text-gray-300 mb-2">Quick Edit</div>
          <div class="space-y-2">
            <div class="flex items-center justify-between gap-2">
              <span class="text-xs text-gray-600 dark:text-gray-300">Status</span>
              <select
                :value="task.status"
                @change="updateStatus($event.target.value)"
                class="text-xs border border-gray-300 dark:border-gray-600 rounded px-2 py-1 focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                :class="getStatusBadgeClass(task.status)"
              >
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
              </select>
            </div>
            <div class="flex items-center justify-between gap-2">
              <span class="text-xs text-gray-600 dark:text-gray-300">Priority</span>
              <select
                :value="task.priority"
                @change="updatePriority($event.target.value)"
                class="text-xs border border-gray-300 dark:border-gray-600 rounded px-2 py-1 focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                :class="getPriorityBadgeClass(task.priority)"
              >
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
          </div>
          <div class="my-2 border-t border-gray-100 dark:border-gray-700"></div>
          <div class="flex items-center justify-end gap-1">
            <button
              @click="$emit('add-subtask', task)"
              class="px-2 py-1 text-xs text-green-600 hover:text-green-800 hover:bg-green-50 dark:hover:bg-green-900/30 rounded"
              title="Add subtask"
            >Add subtask</button>
            <button
              @click="$emit('edit', task)"
              class="px-2 py-1 text-xs text-blue-600 hover:text-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded"
              title="Edit"
            >Edit</button>
            <button
              @click="showMoveDialog = true"
              class="px-2 py-1 text-xs text-purple-600 hover:text-purple-800 hover:bg-purple-50 dark:hover:bg-purple-900/30 rounded"
              title="Move"
            >Move</button>
            <button
              @click="$emit('delete', task)"
              class="px-2 py-1 text-xs text-red-600 hover:text-red-800 hover:bg-red-50 dark:hover:bg-red-900/30 rounded"
              title="Delete"
            >Delete</button>
          </div>
        </div>
      </div>
    </td>
  </tr>

  <!-- Editing Help Row -->
  <tr v-if="editingField" class="bg-blue-50 dark:bg-blue-900/30">
    <td colspan="3" class="px-6 py-2 text-xs text-blue-700 dark:text-blue-200 border-l-4 border-blue-400">
      <div class="flex items-center gap-4">
        <span class="font-medium">Editing {{ editingField.replace('_', ' ') }}:</span>
        <span v-if="editingField === 'title'">Press Enter or Tab to save, Esc to cancel</span>
        <span v-else-if="editingField === 'description'">Press Ctrl+Enter or Tab to save, Esc to cancel</span>
        <span v-else-if="editingField === 'due_date'">Press Enter or Tab to save, Esc to cancel</span>
      </div>
    </td>
  </tr>

  <!-- Move Dialog -->
  <tr v-if="showMoveDialog">
    <td colspan="3" class="px-6 py-4 bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-400">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <span class="text-sm font-medium text-blue-900">Move "{{ task.title }}" to:</span>
          <select
            v-model="moveToParentId"
            class="text-sm border border-blue-300 rounded px-3 py-1 bg-white focus:ring-2 focus:ring-blue-500"
          >
            <option :value="null">Root Level</option>
            <option 
              v-for="option in moveOptions" 
              :key="option.id" 
              :value="option.id"
              :disabled="option.id === task.id"
            >
              {{ '  '.repeat(option.depth) }}{{ option.title }}
            </option>
          </select>
        </div>
        <div class="flex gap-2">
          <button
            @click="confirmMove"
            class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors"
          >
            Move
          </button>
          <button
            @click="showMoveDialog = false"
            class="px-3 py-1 text-gray-600 text-sm hover:text-gray-800 transition-colors"
          >
            Cancel
          </button>
        </div>
      </div>
    </td>
  </tr>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  task: {
    type: Object,
    required: true
  },
  expanded: {
    type: Boolean,
    default: false
  },
  viewMode: {
    type: String,
    default: 'tree'
  },
  allTasks: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits([
  'toggle-expand', 
  'edit', 
  'delete', 
  'add-subtask', 
  'move-task',
  'update-status',
  'update-priority',
  'inline-update'
])

// Move dialog state
const showMoveDialog = ref(false)
const moveToParentId = ref(null)

// Inline editing state
const editingField = ref(null)
const editValues = ref({
  title: '',
  description: '',
  due_date: ''
})

// Template refs
const titleInput = ref(null)
const descriptionInput = ref(null)
const dueDateInput = ref(null)

// Use allTasks from props instead of inject

// Computed properties
const hasSubtasks = computed(() => {
  // Check if there are any tasks in allTasks that have this task as parent
  return props.allTasks.some(task => task.parent_id === props.task.id)
})

const moveOptions = computed(() => {
  // Filter out the current task and its descendants to prevent circular moves
  return props.allTasks.filter(t => 
    t.id !== props.task.id && 
    !isDescendant(t.id, props.task.id)
  )
})

// Methods
const updateStatus = (newStatus) => {
  emit('update-status', {
    task: props.task,
    status: newStatus
  })
}

const updatePriority = (newPriority) => {
  emit('update-priority', {
    task: props.task,
    priority: newPriority
  })
}

const confirmMove = () => {
  emit('move-task', {
    task: props.task,
    newParentId: moveToParentId.value
  })
  showMoveDialog.value = false
  moveToParentId.value = null
}

// Inline editing methods
const startEdit = async (field) => {
  if (editingField.value) return // Already editing something
  
  editingField.value = field
  editValues.value = {
    title: props.task.title || '',
    description: props.task.description || '',
    due_date: props.task.due_date || ''
  }
  
  // Focus the input after DOM update
  await nextTick()
  const inputRef = field === 'title' ? titleInput.value : 
                   field === 'description' ? descriptionInput.value :
                   field === 'due_date' ? dueDateInput.value : null
  
  if (inputRef) {
    inputRef.focus()
    if (field === 'title' || field === 'description') {
      inputRef.select()
    }
  }
}

const saveEdit = async (field) => {
  if (!editingField.value) return
  
  const newValue = editValues.value[field]
  const oldValue = props.task[field] || ''
  
  // Check if value actually changed
  if (newValue === oldValue) {
    cancelEdit()
    return
  }
  
  // Validate required fields
  if (field === 'title' && !newValue.trim()) {
    // Don't save empty title, focus back to input
    const inputRef = titleInput.value
    if (inputRef) {
      inputRef.focus()
      inputRef.select()
    }
    return
  }
  
  // Validate due date
  if (field === 'due_date' && newValue) {
    const selectedDate = new Date(newValue)
    if (isNaN(selectedDate.getTime())) {
      // Invalid date, focus back to input
      const inputRef = dueDateInput.value
      if (inputRef) {
        inputRef.focus()
      }
      return
    }
  }
  
  // Emit inline update
  emit('inline-update', {
    task: props.task,
    field: field,
    value: field === 'description' && !newValue.trim() ? null : 
           field === 'due_date' && !newValue.trim() ? null :
           newValue.trim()
  })
  
  editingField.value = null
}

const cancelEdit = () => {
  editingField.value = null
  editValues.value = {
    title: '',
    description: '',
    due_date: ''
  }
}

const toggleQuickEdit = () => {
  // Cycle through editable fields or start with title
  if (!editingField.value) {
    startEdit('title')
  }
}

// Global keyboard shortcuts
const handleGlobalKeydown = (event) => {
  // Only handle shortcuts when this row is focused or being edited
  if (!editingField.value) return
  
  // Prevent default browser behavior for our shortcuts
  if (event.key === 'Escape') {
    event.preventDefault()
    cancelEdit()
  }
}

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('keydown', handleGlobalKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleGlobalKeydown)
})

const isDescendant = (taskId, ancestorId) => {
  // Check if taskId is a descendant of ancestorId
  const task = props.allTasks.find(t => t.id === taskId)
  if (!task) return false
  
  let currentTask = task
  while (currentTask.parent_id) {
    if (currentTask.parent_id === ancestorId) {
      return true
    }
    currentTask = props.allTasks.find(t => t.id === currentTask.parent_id)
    if (!currentTask) break
  }
  
  return false
}

// Utility functions
const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-yellow-400',
    in_progress: 'bg-blue-400',
    completed: 'bg-green-400'
  }
  return colors[status] || 'bg-gray-400'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPriorityBadgeClass = (priority) => {
  const classes = {
    low: 'bg-gray-100 text-gray-800',
    medium: 'bg-orange-100 text-orange-800',
    high: 'bg-red-100 text-red-800'
  }
  return classes[priority] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  if (!dateString) return 'No due date'
  
  const date = new Date(dateString)
  const today = new Date()
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)
  
  if (date.toDateString() === today.toDateString()) {
    return 'Today'
  }
  
  if (date.toDateString() === tomorrow.toDateString()) {
    return 'Tomorrow'
  }
  
  if (date < today) {
    const diffTime = Math.abs(today - date)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return `${diffDays} day${diffDays > 1 ? 's' : ''} overdue`
  }
  
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>

<style scoped>
.bg-gray-25 {
  background-color: #fafafa;
}
</style>