/**
 * Bulk Operations Composable
 * 
 * Manages multi-question selection and batch operations for the quiz builder.
 * Handles selection state, bulk add operations, and multi-select mode.
 */

import { ref, computed, readonly } from 'vue'
import type { QuizQuestion, BulkOperationState } from '@/types/quiz-builder'
import { STORAGE_KEYS } from '@/types/quiz-builder'

const bulkState = ref<BulkOperationState>({
  multiSelectMode: false,
  selectedQuestionIds: [],
  selectAllActive: false
})

export function useBulkOperations() {
  /**
   * Toggle multi-select mode
   */
  const toggleMultiSelectMode = () => {
    bulkState.value.multiSelectMode = !bulkState.value.multiSelectMode
    
    // Clear selection when exiting multi-select mode
    if (!bulkState.value.multiSelectMode) {
      clearSelection()
    }
    
    persistBulkState()
  }

  /**
   * Toggle question selection
   */
  const toggleQuestionSelection = (questionId: string) => {
    const index = bulkState.value.selectedQuestionIds.indexOf(questionId)
    
    if (index === -1) {
      bulkState.value.selectedQuestionIds.push(questionId)
    } else {
      bulkState.value.selectedQuestionIds.splice(index, 1)
    }
    
    updateSelectAllState()
    persistBulkState()
  }

  /**
   * Select all filtered questions
   */
  const selectAllFiltered = (filteredQuestions: QuizQuestion[]) => {
    if (bulkState.value.selectAllActive) {
      // Deselect all
      bulkState.value.selectedQuestionIds = []
      bulkState.value.selectAllActive = false
    } else {
      // Select all filtered questions
      bulkState.value.selectedQuestionIds = filteredQuestions.map(q => q.id.toString())
      bulkState.value.selectAllActive = true
    }
    
    persistBulkState()
  }

  /**
   * Clear all selections
   */
  const clearSelection = () => {
    bulkState.value.selectedQuestionIds = []
    bulkState.value.selectAllActive = false
    persistBulkState()
  }

  /**
   * Update select all state based on current selection
   */
  const updateSelectAllState = () => {
    // This will be called after individual selections to update the "select all" checkbox state
    // The actual logic depends on the current filtered questions, so this is handled in the component
  }

  /**
   * Check if a question is selected
   */
  const isQuestionSelected = (questionId: string): boolean => {
    return bulkState.value.selectedQuestionIds.includes(questionId)
  }

  /**
   * Get selected questions from a list
   */
  const getSelectedQuestions = (allQuestions: QuizQuestion[]): QuizQuestion[] => {
    return allQuestions.filter(q => 
      bulkState.value.selectedQuestionIds.includes(q.id.toString())
    )
  }

  /**
   * Get selection statistics
   */
  const getSelectionStats = computed(() => {
    return {
      selectedCount: bulkState.value.selectedQuestionIds.length,
      multiSelectActive: bulkState.value.multiSelectMode,
      selectAllActive: bulkState.value.selectAllActive
    }
  })

  /**
   * Validate bulk add operation
   */
  const validateBulkAdd = (
    questionsToAdd: QuizQuestion[],
    existingQuestions: QuizQuestion[]
  ): { isValid: boolean; duplicates: QuizQuestion[]; newQuestions: QuizQuestion[] } => {
    const existingIds = new Set(existingQuestions.map(q => q.id.toString()))
    const duplicates: QuizQuestion[] = []
    const newQuestions: QuizQuestion[] = []

    questionsToAdd.forEach(question => {
      if (existingIds.has(question.id.toString())) {
        duplicates.push(question)
      } else {
        newQuestions.push(question)
      }
    })

    return {
      isValid: newQuestions.length > 0,
      duplicates,
      newQuestions
    }
  }

  /**
   * Prepare questions for bulk add (apply default points, etc.)
   */
  const prepareBulkQuestions = (
    questions: QuizQuestion[],
    applyDefaultPoints: (question: QuizQuestion) => QuizQuestion
  ): QuizQuestion[] => {
    return questions.map(question => {
      const prepared = { ...question }
      
      // Apply default points if not set
      if (!prepared.points || prepared.points === 0) {
        prepared.points = applyDefaultPoints(prepared).points
      }
      
      // Reset section assignment (will be assigned to default section)
      prepared.sectionId = undefined
      prepared.orderInSection = 0
      
      return prepared
    })
  }

  /**
   * Persist bulk state to localStorage
   */
  const persistBulkState = () => {
    try {
      localStorage.setItem(STORAGE_KEYS.BULK_STATE, JSON.stringify(bulkState.value))
    } catch (error) {
      console.warn('Failed to persist bulk state to localStorage:', error)
    }
  }

  /**
   * Restore bulk state from localStorage
   */
  const restoreBulkState = () => {
    try {
      const stored = localStorage.getItem(STORAGE_KEYS.BULK_STATE)
      if (stored) {
        const parsed = JSON.parse(stored)
        if (typeof parsed === 'object' && parsed !== null) {
          // Only restore selection state, not multi-select mode (should start disabled)
          bulkState.value.selectedQuestionIds = parsed.selectedQuestionIds || []
          bulkState.value.selectAllActive = parsed.selectAllActive || false
          // Always start with multi-select mode disabled
          bulkState.value.multiSelectMode = false
        }
      }
    } catch (error) {
      console.warn('Failed to restore bulk state from localStorage:', error)
    }
  }

  /**
   * Get bulk operation summary for confirmation dialogs
   */
  const getBulkOperationSummary = (
    operation: 'add-all' | 'add-selected' | 'remove-all',
    questionCount: number
  ): string => {
    switch (operation) {
      case 'add-all':
        return `Add all ${questionCount} filtered questions to the quiz?`
      case 'add-selected':
        return `Add ${questionCount} selected questions to the quiz?`
      case 'remove-all':
        return `Remove all ${questionCount} questions from the quiz? This action cannot be undone.`
      default:
        return 'Perform bulk operation?'
    }
  }

  /**
   * Reset bulk operations state
   */
  const resetBulkState = () => {
    bulkState.value = {
      multiSelectMode: false,
      selectedQuestionIds: [],
      selectAllActive: false
    }
    persistBulkState()
  }

  // Initialize by restoring from localStorage
  restoreBulkState()

  return {
    // State
    bulkState: readonly(bulkState),
    
    // Computed
    getSelectionStats,
    
    // Methods
    toggleMultiSelectMode,
    toggleQuestionSelection,
    selectAllFiltered,
    clearSelection,
    isQuestionSelected,
    getSelectedQuestions,
    validateBulkAdd,
    prepareBulkQuestions,
    getBulkOperationSummary,
    persistBulkState,
    restoreBulkState,
    resetBulkState
  }
}