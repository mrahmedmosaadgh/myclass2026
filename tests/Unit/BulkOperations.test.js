import { describe, it, expect, beforeEach, vi } from 'vitest'
import { useBulkOperations } from '@/composables/useBulkOperations'

describe('Bulk Operations Functionality', () => {
  let bulkOperations

  beforeEach(() => {
    // Clear localStorage before each test
    localStorage.clear()
    bulkOperations = useBulkOperations()
    // Reset state to ensure clean start
    bulkOperations.resetBulkState()
  })

  describe('Multi-select Mode', () => {
    it('should toggle multi-select mode', () => {
      expect(bulkOperations.bulkState.value.multiSelectMode).toBe(false)
      
      bulkOperations.toggleMultiSelectMode()
      expect(bulkOperations.bulkState.value.multiSelectMode).toBe(true)
      
      bulkOperations.toggleMultiSelectMode()
      expect(bulkOperations.bulkState.value.multiSelectMode).toBe(false)
    })

    it('should clear selection when exiting multi-select mode', () => {
      // Select some questions first
      bulkOperations.toggleQuestionSelection('1')
      bulkOperations.toggleQuestionSelection('2')
      expect(bulkOperations.bulkState.value.selectedQuestionIds).toHaveLength(2)
      
      // Enable multi-select mode
      bulkOperations.toggleMultiSelectMode()
      expect(bulkOperations.bulkState.value.multiSelectMode).toBe(true)
      
      // Exit multi-select mode should clear selection
      bulkOperations.toggleMultiSelectMode()
      expect(bulkOperations.bulkState.value.multiSelectMode).toBe(false)
      expect(bulkOperations.bulkState.value.selectedQuestionIds).toHaveLength(0)
    })
  })

  describe('Question Selection', () => {
    it('should toggle question selection', () => {
      expect(bulkOperations.isQuestionSelected('1')).toBe(false)
      
      bulkOperations.toggleQuestionSelection('1')
      expect(bulkOperations.isQuestionSelected('1')).toBe(true)
      
      bulkOperations.toggleQuestionSelection('1')
      expect(bulkOperations.isQuestionSelected('1')).toBe(false)
    })

    it('should handle multiple question selections', () => {
      bulkOperations.toggleQuestionSelection('1')
      bulkOperations.toggleQuestionSelection('2')
      bulkOperations.toggleQuestionSelection('3')
      
      expect(bulkOperations.bulkState.value.selectedQuestionIds).toEqual(['1', '2', '3'])
      expect(bulkOperations.getSelectionStats.value.selectedCount).toBe(3)
    })

    it('should clear all selections', () => {
      bulkOperations.toggleQuestionSelection('1')
      bulkOperations.toggleQuestionSelection('2')
      expect(bulkOperations.bulkState.value.selectedQuestionIds).toHaveLength(2)
      
      bulkOperations.clearSelection()
      expect(bulkOperations.bulkState.value.selectedQuestionIds).toHaveLength(0)
      expect(bulkOperations.bulkState.value.selectAllActive).toBe(false)
    })
  })

  describe('Select All Functionality', () => {
    const mockQuestions = [
      { id: 1, question_text: 'Question 1' },
      { id: 2, question_text: 'Question 2' },
      { id: 3, question_text: 'Question 3' }
    ]

    it('should select all filtered questions', () => {
      bulkOperations.selectAllFiltered(mockQuestions)
      
      expect(bulkOperations.bulkState.value.selectedQuestionIds).toEqual(['1', '2', '3'])
      expect(bulkOperations.bulkState.value.selectAllActive).toBe(true)
    })

    it('should deselect all when select all is active', () => {
      // First select all
      bulkOperations.selectAllFiltered(mockQuestions)
      expect(bulkOperations.bulkState.value.selectAllActive).toBe(true)
      expect(bulkOperations.bulkState.value.selectedQuestionIds).toHaveLength(3)
      
      // Then deselect all
      bulkOperations.selectAllFiltered(mockQuestions)
      expect(bulkOperations.bulkState.value.selectedQuestionIds).toHaveLength(0)
      expect(bulkOperations.bulkState.value.selectAllActive).toBe(false)
    })
  })

  describe('Bulk Add Validation', () => {
    const questionsToAdd = [
      { id: 1, question_text: 'Question 1' },
      { id: 2, question_text: 'Question 2' },
      { id: 3, question_text: 'Question 3' }
    ]

    const existingQuestions = [
      { id: 2, question_text: 'Question 2' },
      { id: 4, question_text: 'Question 4' }
    ]

    it('should validate bulk add operation', () => {
      const result = bulkOperations.validateBulkAdd(questionsToAdd, existingQuestions)
      
      expect(result.isValid).toBe(true)
      expect(result.duplicates).toHaveLength(1)
      expect(result.duplicates[0].id).toBe(2)
      expect(result.newQuestions).toHaveLength(2)
      expect(result.newQuestions.map(q => q.id)).toEqual([1, 3])
    })

    it('should return invalid when no new questions', () => {
      const result = bulkOperations.validateBulkAdd([existingQuestions[0]], existingQuestions)
      
      expect(result.isValid).toBe(false)
      expect(result.duplicates).toHaveLength(1)
      expect(result.newQuestions).toHaveLength(0)
    })
  })

  describe('Selected Questions Retrieval', () => {
    const allQuestions = [
      { id: 1, question_text: 'Question 1' },
      { id: 2, question_text: 'Question 2' },
      { id: 3, question_text: 'Question 3' },
      { id: 4, question_text: 'Question 4' }
    ]

    it('should get selected questions from list', () => {
      bulkOperations.toggleQuestionSelection('1')
      bulkOperations.toggleQuestionSelection('3')
      
      const selectedQuestions = bulkOperations.getSelectedQuestions(allQuestions)
      
      expect(selectedQuestions).toHaveLength(2)
      expect(selectedQuestions.map(q => q.id)).toEqual([1, 3])
    })

    it('should return empty array when no questions selected', () => {
      // Ensure no questions are selected
      bulkOperations.clearSelection()
      
      const selectedQuestions = bulkOperations.getSelectedQuestions(allQuestions)
      expect(selectedQuestions).toHaveLength(0)
    })
  })

  describe('Operation Summaries', () => {
    it('should generate correct summary for add-all operation', () => {
      const summary = bulkOperations.getBulkOperationSummary('add-all', 5)
      expect(summary).toBe('Add all 5 filtered questions to the quiz?')
    })

    it('should generate correct summary for add-selected operation', () => {
      const summary = bulkOperations.getBulkOperationSummary('add-selected', 3)
      expect(summary).toBe('Add 3 selected questions to the quiz?')
    })

    it('should generate correct summary for remove-all operation', () => {
      const summary = bulkOperations.getBulkOperationSummary('remove-all', 10)
      expect(summary).toBe('Remove all 10 questions from the quiz? This action cannot be undone.')
    })
  })

  describe('State Persistence', () => {
    it.skip('should persist bulk state to localStorage', () => {
      // Clear localStorage first
      localStorage.clear()
      
      bulkOperations.toggleQuestionSelection('1')
      bulkOperations.toggleQuestionSelection('2')
      
      // State should be persisted
      const stored = localStorage.getItem('quiz-builder-bulk-state')
      expect(stored).toBeTruthy()
      
      const parsedState = JSON.parse(stored)
      expect(parsedState.selectedQuestionIds).toEqual(['1', '2'])
    })

    it.skip('should restore bulk state from localStorage', () => {
      // Clear localStorage and reset state first
      localStorage.clear()
      bulkOperations.resetBulkState()
      
      // Manually set localStorage
      const testState = {
        multiSelectMode: false,
        selectedQuestionIds: ['1', '3', '5'],
        selectAllActive: true
      }
      localStorage.setItem('quiz-builder-bulk-state', JSON.stringify(testState))
      
      // Call restore method directly to test restoration
      bulkOperations.restoreBulkState()
      
      expect(bulkOperations.bulkState.value.selectedQuestionIds).toEqual(['1', '3', '5'])
      expect(bulkOperations.bulkState.value.selectAllActive).toBe(true)
      // Multi-select mode should always start disabled
      expect(bulkOperations.bulkState.value.multiSelectMode).toBe(false)
    })

    it('should handle localStorage errors gracefully', () => {
      // Mock localStorage to throw error
      const originalSetItem = localStorage.setItem
      localStorage.setItem = vi.fn(() => {
        throw new Error('Storage quota exceeded')
      })
      
      // Should not throw error
      expect(() => {
        bulkOperations.toggleQuestionSelection('1')
      }).not.toThrow()
      
      // Restore original localStorage
      localStorage.setItem = originalSetItem
    })
  })

  describe('State Reset', () => {
    it('should reset bulk state completely', () => {
      // Set up some state
      bulkOperations.toggleMultiSelectMode()
      bulkOperations.toggleQuestionSelection('1')
      bulkOperations.toggleQuestionSelection('2')
      
      expect(bulkOperations.bulkState.value.multiSelectMode).toBe(true)
      expect(bulkOperations.bulkState.value.selectedQuestionIds).toHaveLength(2)
      
      // Reset state
      bulkOperations.resetBulkState()
      
      expect(bulkOperations.bulkState.value.multiSelectMode).toBe(false)
      expect(bulkOperations.bulkState.value.selectedQuestionIds).toHaveLength(0)
      expect(bulkOperations.bulkState.value.selectAllActive).toBe(false)
    })
  })
})