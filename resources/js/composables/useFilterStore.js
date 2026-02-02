/**
 * Filter Store Composable
 * 
 * Manages filter state and persistence for the quiz builder's advanced filtering system.
 * Provides reactive state management with localStorage persistence.
 */

import { ref, computed, watch, readonly } from 'vue'
import { STORAGE_KEYS } from '@/types/quiz-builder'

const filterState = ref({
  grade: undefined,
  subject: undefined,
  topic: undefined,
  bloomsLevel: undefined,
  author: undefined,
  usedInQuiz: 'all',
  searchTerm: '',
  questionType: undefined,
  difficulty: undefined
})

const availableGrades = ref([])
const availableSubjects = ref([])
const availableTopics = ref([])
const availableAuthors = ref([])

// Track localStorage availability
let isLocalStorageAvailable = true

// Test localStorage availability on module load
try {
  const testKey = '__localStorage_test__'
  localStorage.setItem(testKey, 'test')
  localStorage.removeItem(testKey)
} catch (error) {
  isLocalStorageAvailable = false
  console.warn('localStorage is not available, filter persistence will be disabled:', error)
}

export function useFilterStore() {
  /**
   * Computed properties for cascading filters
   */
  const filteredSubjects = computed(() => {
    if (!filterState.value.grade) return availableSubjects.value
    return availableSubjects.value.filter(subject => subject.gradeId === filterState.value.grade)
  })

  const filteredTopics = computed(() => {
    if (!filterState.value.subject) return availableTopics.value
    return availableTopics.value.filter(topic => topic.subjectId === filterState.value.subject)
  })

  /**
   * Check if any filters are active
   */
  const hasActiveFilters = computed(() => {
    return !!(
      filterState.value.grade ||
      filterState.value.subject ||
      filterState.value.topic ||
      filterState.value.bloomsLevel ||
      filterState.value.author ||
      filterState.value.searchTerm ||
      filterState.value.questionType ||
      filterState.value.difficulty ||
      (filterState.value.usedInQuiz && filterState.value.usedInQuiz !== 'all')
    )
  })

  /**
   * Apply filters to the filter state
   */
  const applyFilters = (filters) => {
    // Handle cascading logic
    if (filters.grade !== filterState.value.grade) {
      // Grade changed, clear dependent filters
      filterState.value.subject = undefined
      filterState.value.topic = undefined
    }
    
    if (filters.subject !== filterState.value.subject) {
      // Subject changed, clear dependent filters
      filterState.value.topic = undefined
    }

    // Apply the new filters
    Object.assign(filterState.value, filters)
    
    // Persist to localStorage
    persistFilters()
  }

  /**
   * Clear all filters
   */
  const clearFilters = () => {
    filterState.value = {
      grade: undefined,
      subject: undefined,
      topic: undefined,
      bloomsLevel: undefined,
      author: undefined,
      usedInQuiz: 'all',
      searchTerm: '',
      questionType: undefined,
      difficulty: undefined
    }
    persistFilters()
  }

  /**
   * Persist filters to localStorage
   */
  const persistFilters = () => {
    if (!isLocalStorageAvailable) {
      console.debug('localStorage not available, skipping filter persistence')
      return
    }

    try {
      const dataToStore = {
        ...filterState.value,
        timestamp: Date.now() // Add timestamp for potential expiration logic
      }
      localStorage.setItem(STORAGE_KEYS.FILTER_STATE, JSON.stringify(dataToStore))
    } catch (error) {
      console.warn('Failed to persist filter state to localStorage:', error)
      // If localStorage fails, disable it for this session
      isLocalStorageAvailable = false
    }
  }

  /**
   * Restore filters from localStorage
   */
  const restoreFilters = () => {
    if (!isLocalStorageAvailable) {
      console.debug('localStorage not available, skipping filter restoration')
      return
    }

    try {
      const stored = localStorage.getItem(STORAGE_KEYS.FILTER_STATE)
      if (stored) {
        const parsed = JSON.parse(stored)
        
        // Validate the stored data structure
        if (typeof parsed === 'object' && parsed !== null) {
          // Remove timestamp before applying filters
          const { timestamp, ...filterData } = parsed
          
          // Validate each filter property
          const validatedFilters = {
            grade: typeof filterData.grade === 'string' ? filterData.grade : undefined,
            subject: typeof filterData.subject === 'string' ? filterData.subject : undefined,
            topic: typeof filterData.topic === 'string' ? filterData.topic : undefined,
            bloomsLevel: typeof filterData.bloomsLevel === 'string' ? filterData.bloomsLevel : undefined,
            author: typeof filterData.author === 'string' ? filterData.author : undefined,
            usedInQuiz: ['used', 'unused', 'all'].includes(filterData.usedInQuiz) ? filterData.usedInQuiz : 'all',
            searchTerm: typeof filterData.searchTerm === 'string' ? filterData.searchTerm : '',
            questionType: typeof filterData.questionType === 'string' ? filterData.questionType : undefined,
            difficulty: ['Easy', 'Medium', 'Hard'].includes(filterData.difficulty) ? filterData.difficulty : undefined
          }
          
          Object.assign(filterState.value, validatedFilters)
        }
      }
    } catch (error) {
      console.warn('Failed to restore filter state from localStorage:', error)
      // Clear corrupted data
      try {
        localStorage.removeItem(STORAGE_KEYS.FILTER_STATE)
      } catch (clearError) {
        console.warn('Failed to clear corrupted filter state:', clearError)
      }
    }
  }

  /**
   * Check if localStorage is available for filter persistence
   */
  const isStorageAvailable = () => isLocalStorageAvailable

  /**
   * Clear persisted filter data from localStorage
   */
  const clearPersistedFilters = () => {
    if (!isLocalStorageAvailable) return

    try {
      localStorage.removeItem(STORAGE_KEYS.FILTER_STATE)
    } catch (error) {
      console.warn('Failed to clear persisted filter state:', error)
    }
  }

  /**
   * Set available options for cascading filters
   */
  const setAvailableOptions = (options) => {
    if (options.grades) availableGrades.value = options.grades
    if (options.subjects) availableSubjects.value = options.subjects
    if (options.topics) availableTopics.value = options.topics
    if (options.authors) availableAuthors.value = options.authors
  }

  /**
   * Get filter summary for display
   */
  const getFilterSummary = computed(() => {
    const active = []
    
    if (filterState.value.grade) {
      const grade = availableGrades.value.find(g => g.id === filterState.value.grade)
      if (grade) active.push(`Grade: ${grade.name}`)
    }
    
    if (filterState.value.subject) {
      const subject = availableSubjects.value.find(s => s.id === filterState.value.subject)
      if (subject) active.push(`Subject: ${subject.name}`)
    }
    
    if (filterState.value.topic) {
      const topic = availableTopics.value.find(t => t.id === filterState.value.topic)
      if (topic) active.push(`Topic: ${topic.name}`)
    }
    
    if (filterState.value.difficulty) {
      active.push(`Difficulty: ${filterState.value.difficulty}`)
    }
    
    if (filterState.value.bloomsLevel) {
      active.push(`Bloom's Level: ${filterState.value.bloomsLevel}`)
    }
    
    if (filterState.value.author) {
      const author = availableAuthors.value.find(a => a.id === filterState.value.author)
      if (author) active.push(`Author: ${author.name}`)
    }
    
    if (filterState.value.usedInQuiz && filterState.value.usedInQuiz !== 'all') {
      active.push(`Usage: ${filterState.value.usedInQuiz}`)
    }
    
    if (filterState.value.searchTerm) {
      active.push(`Search: "${filterState.value.searchTerm}"`)
    }

    return active
  })

  /**
   * Watch for filter changes and auto-persist
   */
  watch(
    filterState,
    () => {
      persistFilters()
    },
    { deep: true }
  )

  // Initialize by restoring from localStorage
  restoreFilters()

  return {
    // State
    filterState: readonly(filterState),
    availableGrades: readonly(availableGrades),
    availableSubjects: readonly(availableSubjects),
    availableTopics: readonly(availableTopics),
    availableAuthors: readonly(availableAuthors),
    
    // Computed
    filteredSubjects,
    filteredTopics,
    hasActiveFilters,
    getFilterSummary,
    
    // Methods
    applyFilters,
    clearFilters,
    persistFilters,
    restoreFilters,
    setAvailableOptions,
    isStorageAvailable,
    clearPersistedFilters
  }
}