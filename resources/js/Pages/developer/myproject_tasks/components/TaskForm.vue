<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-xl font-semibold text-gray-900">
        {{ isEditing ? 'Edit Task' : 'Create New Task' }}
      </h2>
      <button
        @click="$emit('cancel')"
        class="text-gray-400 hover:text-gray-600 transition-colors"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <!-- Form -->
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Title Field -->
      <div>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
          Title <span class="text-red-500">*</span>
        </label>
        <input
          id="title"
          v-model="form.title"
          type="text"
          required
          maxlength="255"
          placeholder="Enter task title..."
          :class="[
            'w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
            errors.title ? 'border-red-300' : 'border-gray-300'
          ]"
        >
        <p v-if="errors.title" class="mt-1 text-sm text-red-600">
          {{ errors.title }}
        </p>
      </div>

      <!-- Description Field -->
      <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
          Description
        </label>
        <textarea
          id="description"
          v-model="form.description"
          rows="4"
          placeholder="Enter task description..."
          :class="[
            'w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-vertical',
            errors.description ? 'border-red-300' : 'border-gray-300'
          ]"
        ></textarea>
        <p v-if="errors.description" class="mt-1 text-sm text-red-600">
          {{ errors.description }}
        </p>
      </div>

      <!-- Status and Priority Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Status Field -->
        <div>
          <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
            Status <span class="text-red-500">*</span>
          </label>
          <select
            id="status"
            v-model="form.status"
            required
            :class="[
              'w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
              errors.status ? 'border-red-300' : 'border-gray-300'
            ]"
          >
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
          </select>
          <p v-if="errors.status" class="mt-1 text-sm text-red-600">
            {{ errors.status }}
          </p>
        </div>

        <!-- Priority Field -->
        <div>
          <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
            Priority <span class="text-red-500">*</span>
          </label>
          <select
            id="priority"
            v-model="form.priority"
            required
            :class="[
              'w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
              errors.priority ? 'border-red-300' : 'border-gray-300'
            ]"
          >
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>
          <p v-if="errors.priority" class="mt-1 text-sm text-red-600">
            {{ errors.priority }}
          </p>
        </div>
      </div>

      <!-- Due Date Field -->
      <div>
        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
          Due Date
        </label>
        <input
          id="due_date"
          v-model="form.due_date"
          type="date"
          :min="isEditing ? undefined : today"
          :class="[
            'w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
            errors.due_date ? 'border-red-300' : 'border-gray-300'
          ]"
        >
        <p v-if="errors.due_date" class="mt-1 text-sm text-red-600">
          {{ errors.due_date }}
        </p>
        <p class="mt-1 text-sm text-gray-500">
          Leave empty if no due date is required
        </p>
      </div>

      <!-- Form Actions -->
      <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
        <button
          type="button"
          @click="$emit('cancel')"
          class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="submitting || !isFormValid"
          class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        >
          <svg
            v-if="submitting"
            class="animate-spin h-4 w-4"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ submitting ? 'Saving...' : (isEditing ? 'Update Task' : 'Create Task') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'

const props = defineProps({
  task: {
    type: Object,
    default: null
  },
  isEditing: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['save', 'cancel'])

// Form state
const form = reactive({
  title: '',
  description: '',
  status: 'pending',
  priority: 'medium',
  due_date: ''
})

const errors = reactive({
  title: '',
  description: '',
  status: '',
  priority: '',
  due_date: ''
})

const submitting = ref(false)

// Computed properties
const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

const isFormValid = computed(() => {
  return form.title.trim().length > 0 && 
         form.status && 
         form.priority &&
         !Object.values(errors).some(error => error)
})

// Initialize form
onMounted(() => {
  if (props.isEditing && props.task) {
    form.title = props.task.title || ''
    form.description = props.task.description || ''
    form.status = props.task.status || 'pending'
    form.priority = props.task.priority || 'medium'
    form.due_date = props.task.due_date || ''
  }
})

// Validation functions
const validateTitle = () => {
  errors.title = ''
  if (!form.title.trim()) {
    errors.title = 'Title is required'
  } else if (form.title.length > 255) {
    errors.title = 'Title must be less than 255 characters'
  }
}

const validateStatus = () => {
  errors.status = ''
  if (!form.status) {
    errors.status = 'Status is required'
  } else if (!['pending', 'in_progress', 'completed'].includes(form.status)) {
    errors.status = 'Invalid status selected'
  }
}

const validatePriority = () => {
  errors.priority = ''
  if (!form.priority) {
    errors.priority = 'Priority is required'
  } else if (!['low', 'medium', 'high'].includes(form.priority)) {
    errors.priority = 'Invalid priority selected'
  }
}

const validateDueDate = () => {
  errors.due_date = ''
  if (form.due_date) {
    const selectedDate = new Date(form.due_date)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    
    if (isNaN(selectedDate.getTime())) {
      errors.due_date = 'Invalid date format'
    } else if (!props.isEditing && selectedDate < today) {
      errors.due_date = 'Due date cannot be in the past'
    }
  }
}

const validateForm = () => {
  validateTitle()
  validateStatus()
  validatePriority()
  validateDueDate()
  
  return !Object.values(errors).some(error => error)
}

// Handle form submission
const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  submitting.value = true
  
  try {
    const taskData = {
      title: form.title.trim(),
      description: form.description.trim() || null,
      status: form.status,
      priority: form.priority,
      due_date: form.due_date || null
    }

    emit('save', taskData)
  } catch (error) {
    console.error('Form submission error:', error)
  } finally {
    submitting.value = false
  }
}

// Real-time validation
const handleTitleInput = () => {
  validateTitle()
}

const handleStatusChange = () => {
  validateStatus()
}

const handlePriorityChange = () => {
  validatePriority()
}

const handleDueDateChange = () => {
  validateDueDate()
}
</script>