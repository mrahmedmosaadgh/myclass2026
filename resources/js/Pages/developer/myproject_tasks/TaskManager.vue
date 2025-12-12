<template>
  <div class="myproject-tasks-container">
    <!-- Header -->
     <Head title="MyProject Tasks" />
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 px-6 py-4">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">MyProject Tasks</h1>
          <p class="text-gray-600 dark:text-gray-300 mt-1">Manage your project tasks efficiently</p>
        </div>
        <button
          @click="showCreateForm = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Add Task
        </button>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-6">
      <!-- Filters Component -->
      <TaskFilters
        v-model:filters="filters"
        @update:filters="handleFiltersUpdate"
        class="mb-6"
      />

      <!-- Soft Loading Bar (prevents table flash) -->
       <div class="h-4">

         <div v-if="loading" class="mb-3 h-1 w-full bg-blue-100 overflow-hidden rounded">
           <div class="h-full w-1/3 bg-blue-600 animate-pulse"></div>
          </div>
        </div>

      <!-- Task Tree Component (always rendered to avoid flash) -->
      <TaskTree
        :tasks="tasks"
        :loading="loading"
        :view-mode="viewMode"
        @edit="handleEdit"
        @delete="handleDelete"
        @add-subtask="handleAddSubtask"
        @move-task="handleMoveTask"
        @update-status="handleUpdateStatus"
        @update-priority="handleUpdatePriority"
        @inline-update="handleInlineUpdate"
        @toggle-view-mode="toggleViewMode"
        class="mb-6"
        :class="{ 'opacity-60 pointer-events-none': loading }"
      />

      <!-- Pagination Component -->
      <TaskPagination
        v-if="pagination.total > 0"
        :pagination="pagination"
        @update:page="handlePageUpdate"
        @update:per-page="handlePerPageUpdate"
      />
    </div>

    <!-- Task Form Modal -->
    <div
      v-if="showCreateForm || showEditForm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeForm"
    >
      <TaskForm
        :task="editingTask"
        :is-editing="showEditForm"
        @save="handleSave"
        @cancel="closeForm"
        class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      />
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteConfirm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="showDeleteConfirm = false"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Delete</h3>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete the task "{{ deletingTask?.title }}"? This action cannot be undone.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="showDeleteConfirm = false"
            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="confirmDelete"
            :disabled="deleting"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50"
          >
            {{ deleting ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Notifications -->
    <div
      v-if="notification.show"
      :class="[
        'fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 transition-all duration-300',
        notification.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
      ]"
    >
      {{ notification.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, provide } from 'vue'
import TaskTree from './components/TaskTree.vue'
import TaskForm from './components/TaskForm.vue'
import TaskFilters from './components/TaskFilters.vue'
import TaskPagination from './components/TaskPagination.vue'
import { useTasks } from './composables/useTasks.js'

// Composable for task management
const {
  tasks,
  loading,
  pagination,
  filters,
  viewMode,
  fetchTasks,
  createTask,
  updateTask,
  deleteTask,
  createSubtask,
  moveTask,
  showNotification
} = useTasks()

// Tasks are now passed as props to child components

// Modal states
const showCreateForm = ref(false)
const showEditForm = ref(false)
const showDeleteConfirm = ref(false)
const editingTask = ref(null)
const deletingTask = ref(null)
const deleting = ref(false)

// Notification state
const notification = reactive({
  show: false,
  message: '',
  type: 'success'
})

// Handle filters update
const handleFiltersUpdate = (newFilters) => {
  Object.assign(filters, newFilters)
  fetchTasks()
}

// Handle view mode toggle
const toggleViewMode = () => {
  viewMode.value = viewMode.value === 'tree' ? 'flat' : 'tree'
  fetchTasks()
}

// Handle pagination
const handlePageUpdate = (page) => {
  filters.page = page
  fetchTasks()
}

const handlePerPageUpdate = (perPage) => {
  filters.per_page = perPage
  filters.page = 1 // Reset to first page
  fetchTasks()
}

// Handle task operations
const handleEdit = (task) => {
  editingTask.value = { ...task }
  showEditForm.value = true
}

const handleDelete = (task) => {
  deletingTask.value = task
  showDeleteConfirm.value = true
}

const handleSave = async (taskData) => {
  try {
    if (showEditForm.value) {
      await updateTask(editingTask.value.id, taskData)
      showNotification('Task updated successfully', 'success')
    } else if (creatingSubtaskFor.value) {
      await createSubtask(creatingSubtaskFor.value.id, taskData)
      showNotification('Subtask created successfully', 'success')
    } else {
      await createTask(taskData)
      showNotification('Task created successfully', 'success')
    }
    closeForm()
    fetchTasks()
  } catch (error) {
    showNotification(error.message || 'Operation failed', 'error')
  }
}

// Handle subtask creation
const creatingSubtaskFor = ref(null)

const handleAddSubtask = (parentTask) => {
  creatingSubtaskFor.value = parentTask
  showCreateForm.value = true
}

// Handle task moving
const handleMoveTask = async (moveData) => {
  try {
    await moveTask(moveData.task.id, moveData.newParentId)
    showNotification('Task moved successfully', 'success')
    fetchTasks()
  } catch (error) {
    showNotification(error.message || 'Failed to move task', 'error')
  }
}

// Handle status updates
const handleUpdateStatus = async (updateData) => {
  try {
    await updateTask(updateData.task.id, { status: updateData.status })
    showNotification('Status updated successfully', 'success')
    fetchTasks()
  } catch (error) {
    showNotification(error.message || 'Failed to update status', 'error')
  }
}

// Handle priority updates
const handleUpdatePriority = async (updateData) => {
  try {
    await updateTask(updateData.task.id, { priority: updateData.priority })
    showNotification('Priority updated successfully', 'success')
    fetchTasks()
  } catch (error) {
    showNotification(error.message || 'Failed to update priority', 'error')
  }
}

// Handle inline updates
const handleInlineUpdate = async (updateData) => {
  try {
    const { task, field, value } = updateData
    const updatePayload = { [field]: value }
    
    console.log('Inline update:', { task: task.id, field, value, updatePayload })
    
    await updateTask(task.id, updatePayload)
    
    // Show success notification with field-specific message
    const fieldNames = {
      title: 'Title',
      description: 'Description',
      due_date: 'Due date'
    }
    const fieldName = fieldNames[field] || 'Field'
    showNotification(`${fieldName} updated successfully`, 'success')
    
    // Refresh tasks to show updated data
    fetchTasks()
  } catch (error) {
    console.error('Inline update error:', error)
    showNotification(error.message || 'Failed to update task', 'error')
  }
}

const confirmDelete = async () => {
  if (!deletingTask.value) return
  
  deleting.value = true
  try {
    await deleteTask(deletingTask.value.id)
    showNotification('Task deleted successfully', 'success')
    showDeleteConfirm.value = false
    fetchTasks()
  } catch (error) {
    showNotification(error.message || 'Delete failed', 'error')
  } finally {
    deleting.value = false
    deletingTask.value = null
  }
}

const closeForm = () => {
  showCreateForm.value = false
  showEditForm.value = false
  editingTask.value = null
  creatingSubtaskFor.value = null
}

// Watch for notifications from composable
const unwatchNotification = showNotification.subscribe((message, type) => {
  notification.message = message
  notification.type = type
  notification.show = true
  
  setTimeout(() => {
    notification.show = false
  }, 3000)
})

// Initialize
onMounted(() => {
  fetchTasks()
})
</script>

<style scoped>
.myproject-tasks-container {
  min-height: 100vh;
  background-color: #f9fafb;
  display: flex;
  flex-direction: column;
}

:deep(html.dark) .myproject-tasks-container {
  background-color: #111827; /* gray-900 */
}

/* Custom scrollbar for modal */
.max-h-\[90vh\]::-webkit-scrollbar {
  width: 6px;
}

.max-h-\[90vh\]::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>