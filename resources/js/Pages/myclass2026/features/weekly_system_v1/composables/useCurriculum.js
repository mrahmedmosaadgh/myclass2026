import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * Composable for managing curriculum state and operations
 * 
 * @returns {Object} Curriculum management methods and state
 */
export function useCurriculum() {
  const loading = ref(false)
  const error = ref(null)
  const curricula = ref([])
  const grades = ref([])
  const subjects = ref([])

  // Computed properties
  const hasCurricula = computed(() => curricula.value.length > 0)
  const curriculumCount = computed(() => curricula.value.length)

  /**
   * Fetch curricula from API
   */
  async function fetchCurricula() {
    loading.value = true
    error.value = null
    
    try {
      const response = await fetch('/weekly-system-v1/api/curricula', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
      })
      
      if (!response.ok) {
        throw new Error('Failed to fetch curricula')
      }
      
      const data = await response.json()
      curricula.value = data.data || []
    } catch (err) {
      error.value = err.message
      console.error('Error fetching curricula:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Create a new curriculum
   */
  async function createCurriculum(curriculumData) {
    loading.value = true
    error.value = null
    
    return new Promise((resolve, reject) => {
      router.post(
        '/weekly-system-v1/curriculum-lessons',
        curriculumData,
        {
          preserveScroll: true,
          onSuccess: (page) => {
            loading.value = false
            resolve(page)
          },
          onError: (errors) => {
            loading.value = false
            error.value = errors
            reject(errors)
          }
        }
      )
    })
  }

  /**
   * Update an existing curriculum
   */
  async function updateCurriculum(id, curriculumData) {
    loading.value = true
    error.value = null
    
    return new Promise((resolve, reject) => {
      router.put(
        `/weekly-system-v1/curriculum-lessons/${id}`,
        curriculumData,
        {
          preserveScroll: true,
          onSuccess: (page) => {
            loading.value = false
            resolve(page)
          },
          onError: (errors) => {
            loading.value = false
            error.value = errors
            reject(errors)
          }
        }
      )
    })
  }

  /**
   * Delete a curriculum
   */
  async function deleteCurriculum(id) {
    loading.value = true
    error.value = null
    
    return new Promise((resolve, reject) => {
      router.delete(
        `/weekly-system-v1/curriculum-lessons/${id}`,
        {
          preserveScroll: true,
          onSuccess: (page) => {
            loading.value = false
            // Remove from local list
            const index = curricula.value.findIndex(c => c.id === id)
            if (index !== -1) {
              curricula.value.splice(index, 1)
            }
            resolve(page)
          },
          onError: (errors) => {
            loading.value = false
            error.value = errors
            reject(errors)
          }
        }
      )
    })
  }

  /**
   * Set lock date for a curriculum
   */
  async function setLockDate(id, lockDate) {
    return updateCurriculum(id, { edit_lock_date: lockDate })
  }

  /**
   * Check if a curriculum is editable (not locked)
   */
  function isEditable(curriculum) {
    if (!curriculum.edit_lock_date) {
      return true
    }
    
    const lockDate = new Date(curriculum.edit_lock_date)
    const now = new Date()
    return lockDate > now
  }

  /**
   * Get curriculum by ID
   */
  function getCurriculumById(id) {
    return curricula.value.find(c => c.id === id)
  }

  /**
   * Filter curricula by grade
   */
  function filterByGrade(gradeId) {
    return curricula.value.filter(c => c.grade_id === gradeId)
  }

  /**
   * Filter curricula by subject
   */
  function filterBySubject(subjectId) {
    return curricula.value.filter(c => c.subject_id === subjectId)
  }

  /**
   * Search curricula by name
   */
  function searchByName(query) {
    if (!query) return curricula.value
    
    const lowerQuery = query.toLowerCase()
    return curricula.value.filter(c => 
      c.name.toLowerCase().includes(lowerQuery) ||
      c.description?.toLowerCase().includes(lowerQuery)
    )
  }

  /**
   * Sort curricula
   */
  function sortBy(field, direction = 'asc') {
    curricula.value.sort((a, b) => {
      const aVal = a[field]
      const bVal = b[field]
      
      if (aVal === null && bVal === null) return 0
      if (aVal === null) return direction === 'asc' ? -1 : 1
      if (bVal === null) return direction === 'asc' ? 1 : -1
      
      const comparison = aVal < bVal ? -1 : aVal > bVal ? 1 : 0
      return direction === 'asc' ? comparison : -comparison
    })
  }

  /**
   * Reset state
   */
  function reset() {
    loading.value = false
    error.value = null
    curricula.value = []
  }

  return {
    // State
    loading,
    error,
    curricula,
    grades,
    subjects,
    
    // Computed
    hasCurricula,
    curriculumCount,
    
    // Methods
    fetchCurricula,
    createCurriculum,
    updateCurriculum,
    deleteCurriculum,
    setLockDate,
    isEditable,
    getCurriculumById,
    filterByGrade,
    filterBySubject,
    searchByName,
    sortBy,
    reset
  }
}
