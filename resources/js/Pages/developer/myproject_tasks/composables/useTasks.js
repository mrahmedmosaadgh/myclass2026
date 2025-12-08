import { ref, reactive } from 'vue'
import axios from 'axios'

/**
 * Composable for managing MyProject Tasks
 * Provides reactive state and methods for CRUD operations
 */
export function useTasks() {
  // Reactive state
  const tasks = ref([])
  const loading = ref(false)
  const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: 0,
    to: 0
  })

  const filters = reactive({
    status: '',
    priority: '',
    search: '',
    sort_by: 'created_at',
    sort_direction: 'desc',
    page: 1,
    per_page: 10
  })

  const viewMode = ref('tree') // 'tree' or 'flat'

  // Notification system
  const notificationCallbacks = []
  const showNotification = (message, type = 'success') => {
    notificationCallbacks.forEach(callback => callback(message, type))
  }
  showNotification.subscribe = (callback) => {
    notificationCallbacks.push(callback)
    return () => {
      const index = notificationCallbacks.indexOf(callback)
      if (index > -1) notificationCallbacks.splice(index, 1)
    }
  }

  // API base URL
  const API_BASE = '/api/myproject_tasks'

  /**
   * Fetch tasks with current filters and pagination
   */
  const fetchTasks = async () => {
    loading.value = true
    try {
      const params = new URLSearchParams()
      
      // Add view mode
      params.append('view', viewMode.value)
      
      // Add filters to params
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== '' && value !== null && value !== undefined) {
          params.append(key, value)
        }
      })

      const response = await axios.get(`${API_BASE}?${params.toString()}`)
      
      if (response.data.success) {
        tasks.value = response.data.data
        
        // Handle pagination for flat view
        if (response.data.pagination) {
          Object.assign(pagination, response.data.pagination)
        } else {
          // Reset pagination for tree view
          Object.assign(pagination, {
            current_page: 1,
            last_page: 1,
            per_page: response.data.total_tasks || 0,
            total: response.data.total_tasks || 0,
            from: 1,
            to: response.data.total_tasks || 0
          })
        }
      } else {
        throw new Error(response.data.message || 'Failed to fetch tasks')
      }
    } catch (error) {
      console.error('Error fetching tasks:', error)
      showNotification(
        error.response?.data?.message || error.message || 'Failed to fetch tasks',
        'error'
      )
      tasks.value = []
    } finally {
      loading.value = false
    }
  }

  /**
   * Create a new task
   */
  const createTask = async (taskData) => {
    try {
      const response = await axios.post(API_BASE, taskData)
      
      if (response.data.success) {
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Failed to create task')
      }
    } catch (error) {
      console.error('Error creating task:', error)
      
      // Handle validation errors
      if (error.response?.status === 422) {
        const errors = error.response.data.errors
        const errorMessages = Object.values(errors).flat().join(', ')
        throw new Error(errorMessages)
      }
      
      throw new Error(
        error.response?.data?.message || error.message || 'Failed to create task'
      )
    }
  }

  /**
   * Update an existing task
   */
  const updateTask = async (id, taskData) => {
    try {
      console.log('Updating task:', { id, taskData })
      
      const response = await axios.put(`${API_BASE}/${id}`, taskData)
      
      console.log('Update response:', response.data)
      
      if (response.data.success) {
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Failed to update task')
      }
    } catch (error) {
      console.error('Error updating task:', error)
      console.error('Error response:', error.response?.data)
      
      // Handle validation errors
      if (error.response?.status === 422) {
        const errors = error.response.data.errors
        const errorMessages = Object.values(errors).flat().join(', ')
        throw new Error(errorMessages)
      }
      
      throw new Error(
        error.response?.data?.message || error.message || 'Failed to update task'
      )
    }
  }

  /**
   * Delete a task
   */
  const deleteTask = async (id) => {
    try {
      const response = await axios.delete(`${API_BASE}/${id}`)
      
      if (response.data.success) {
        return true
      } else {
        throw new Error(response.data.message || 'Failed to delete task')
      }
    } catch (error) {
      console.error('Error deleting task:', error)
      throw new Error(
        error.response?.data?.message || error.message || 'Failed to delete task'
      )
    }
  }

  /**
   * Get a single task by ID
   */
  const getTask = async (id) => {
    try {
      const response = await axios.get(`${API_BASE}/${id}`)
      
      if (response.data.success) {
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Failed to fetch task')
      }
    } catch (error) {
      console.error('Error fetching task:', error)
      throw new Error(
        error.response?.data?.message || error.message || 'Failed to fetch task'
      )
    }
  }

  /**
   * Create a subtask under a parent task
   */
  const createSubtask = async (parentId, taskData) => {
    try {
      const response = await axios.post(`${API_BASE}/${parentId}/subtasks`, taskData)
      
      if (response.data.success) {
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Failed to create subtask')
      }
    } catch (error) {
      console.error('Error creating subtask:', error)
      
      // Handle validation errors
      if (error.response?.status === 422) {
        const errors = error.response.data.errors
        const errorMessages = Object.values(errors).flat().join(', ')
        throw new Error(errorMessages)
      }
      
      throw new Error(
        error.response?.data?.message || error.message || 'Failed to create subtask'
      )
    }
  }

  /**
   * Move a task to a new parent
   */
  const moveTask = async (taskId, newParentId) => {
    try {
      const response = await axios.patch(`${API_BASE}/${taskId}/move`, {
        parent_id: newParentId
      })
      
      if (response.data.success) {
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Failed to move task')
      }
    } catch (error) {
      console.error('Error moving task:', error)
      
      // Handle validation errors
      if (error.response?.status === 422) {
        const errors = error.response.data.errors
        const errorMessages = Object.values(errors).flat().join(', ')
        throw new Error(errorMessages)
      }
      
      throw new Error(
        error.response?.data?.message || error.message || 'Failed to move task'
      )
    }
  }

  /**
   * Reorder tasks within the same parent
   */
  const reorderTasks = async (parentId, taskIds) => {
    try {
      const response = await axios.post(`${API_BASE}/reorder`, {
        parent_id: parentId,
        task_ids: taskIds
      })
      
      if (response.data.success) {
        return true
      } else {
        throw new Error(response.data.message || 'Failed to reorder tasks')
      }
    } catch (error) {
      console.error('Error reordering tasks:', error)
      throw new Error(
        error.response?.data?.message || error.message || 'Failed to reorder tasks'
      )
    }
  }

  /**
   * Get subtasks of a specific task
   */
  const getSubtasks = async (taskId) => {
    try {
      const response = await axios.get(`${API_BASE}/${taskId}/subtasks`)
      
      if (response.data.success) {
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Failed to fetch subtasks')
      }
    } catch (error) {
      console.error('Error fetching subtasks:', error)
      throw new Error(
        error.response?.data?.message || error.message || 'Failed to fetch subtasks'
      )
    }
  }

  /**
   * Reset filters to default values
   */
  const resetFilters = () => {
    filters.status = ''
    filters.priority = ''
    filters.search = ''
    filters.sort_by = 'created_at'
    filters.sort_direction = 'desc'
    filters.page = 1
    filters.per_page = 10
  }

  /**
   * Get status options for dropdowns
   */
  const getStatusOptions = () => [
    { value: '', label: 'All Statuses' },
    { value: 'pending', label: 'Pending' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'completed', label: 'Completed' }
  ]

  /**
   * Get priority options for dropdowns
   */
  const getPriorityOptions = () => [
    { value: '', label: 'All Priorities' },
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' }
  ]

  /**
   * Get status badge class for styling
   */
  const getStatusBadgeClass = (status) => {
    const classes = {
      pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
      in_progress: 'bg-blue-100 text-blue-800 border-blue-200',
      completed: 'bg-green-100 text-green-800 border-green-200'
    }
    return classes[status] || 'bg-gray-100 text-gray-800 border-gray-200'
  }

  /**
   * Get priority badge class for styling
   */
  const getPriorityBadgeClass = (priority) => {
    const classes = {
      low: 'bg-gray-100 text-gray-800 border-gray-200',
      medium: 'bg-orange-100 text-orange-800 border-orange-200',
      high: 'bg-red-100 text-red-800 border-red-200'
    }
    return classes[priority] || 'bg-gray-100 text-gray-800 border-gray-200'
  }

  /**
   * Format date for display
   */
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

  return {
    // State
    tasks,
    loading,
    pagination,
    filters,
    viewMode,
    
    // Methods
    fetchTasks,
    createTask,
    updateTask,
    deleteTask,
    getTask,
    createSubtask,
    moveTask,
    reorderTasks,
    getSubtasks,
    resetFilters,
    
    // Utilities
    getStatusOptions,
    getPriorityOptions,
    getStatusBadgeClass,
    getPriorityBadgeClass,
    formatDate,
    showNotification
  }
}