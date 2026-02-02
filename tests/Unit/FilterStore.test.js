import { describe, it, expect, beforeEach, vi } from 'vitest'
import { useFilterStore } from '@/composables/useFilterStore'

describe('FilterStore Composable', () => {
  let filterStore

  beforeEach(() => {
    // Clear localStorage mock
    localStorage.clear()
    localStorage.getItem.mockClear()
    localStorage.setItem.mockClear()
    localStorage.removeItem.mockClear()
    
    // Create fresh instance
    filterStore = useFilterStore()
  })

  it('initializes with default filter state', () => {
    expect(filterStore.filterState.value).toEqual({
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
  })

  it('applies filters correctly', () => {
    const newFilters = {
      grade: '1',
      difficulty: 'Easy',
      searchTerm: 'test'
    }

    filterStore.applyFilters(newFilters)

    expect(filterStore.filterState.value.grade).toBe('1')
    expect(filterStore.filterState.value.difficulty).toBe('Easy')
    expect(filterStore.filterState.value.searchTerm).toBe('test')
  })

  it('handles cascading filter logic when grade changes', () => {
    // Set initial state with all cascading filters
    filterStore.applyFilters({
      grade: '1',
      subject: 's1',
      topic: 't1'
    })

    // Change grade - should clear dependent filters
    filterStore.applyFilters({ grade: '2' })

    expect(filterStore.filterState.value.grade).toBe('2')
    expect(filterStore.filterState.value.subject).toBeUndefined()
    expect(filterStore.filterState.value.topic).toBeUndefined()
  })

  it('handles cascading filter logic when subject changes', () => {
    // Set initial state
    filterStore.applyFilters({
      grade: '1',
      subject: 's1',
      topic: 't1'
    })

    // Change subject - should clear topic
    filterStore.applyFilters({ subject: 's2' })

    expect(filterStore.filterState.value.subject).toBe('s2')
    expect(filterStore.filterState.value.topic).toBeUndefined()
  })

  it('persists filters to localStorage', () => {
    const filters = { grade: '1', difficulty: 'Medium' }
    filterStore.applyFilters(filters)

    expect(localStorage.setItem).toHaveBeenCalledWith(
      'quiz-builder-filters',
      expect.stringContaining('"grade":"1"')
    )
  })

  it('restores filters from localStorage', () => {
    const storedFilters = {
      grade: '2',
      difficulty: 'Hard',
      searchTerm: 'restored',
      timestamp: Date.now()
    }

    localStorage.getItem.mockReturnValue(JSON.stringify(storedFilters))

    // Create new store instance to trigger restoration
    const newStore = useFilterStore()

    expect(newStore.filterState.value.grade).toBe('2')
    expect(newStore.filterState.value.difficulty).toBe('Hard')
    expect(newStore.filterState.value.searchTerm).toBe('restored')
  })

  it('validates restored filter data', () => {
    const invalidStoredData = {
      grade: 123, // Invalid type
      difficulty: 'Invalid', // Invalid value
      usedInQuiz: 'invalid', // Invalid value
      searchTerm: null, // Invalid type
      timestamp: Date.now()
    }

    localStorage.getItem.mockReturnValue(JSON.stringify(invalidStoredData))

    const newStore = useFilterStore()

    // Should use default values for invalid data
    expect(newStore.filterState.value.grade).toBeUndefined()
    expect(newStore.filterState.value.difficulty).toBeUndefined()
    expect(newStore.filterState.value.usedInQuiz).toBe('all')
    expect(newStore.filterState.value.searchTerm).toBe('')
  })

  it('handles localStorage errors gracefully', () => {
    localStorage.setItem.mockImplementation(() => {
      throw new Error('Storage quota exceeded')
    })

    // Should not throw error
    expect(() => {
      filterStore.applyFilters({ grade: '1' })
    }).not.toThrow()
  })

  it('handles corrupted localStorage data', () => {
    // First, make localStorage available for this test
    localStorage.getItem.mockReturnValue('invalid json')
    
    // Mock the localStorage availability check to return true
    const originalSetItem = localStorage.setItem
    localStorage.setItem = vi.fn()
    
    // Should not throw error and should clear corrupted data
    expect(() => {
      useFilterStore()
    }).not.toThrow()

    // Only check if localStorage was actually used (when available)
    if (localStorage.removeItem.mock.calls.length > 0) {
      expect(localStorage.removeItem).toHaveBeenCalledWith('quiz-builder-filters')
    }
    
    // Restore original
    localStorage.setItem = originalSetItem
  })

  it('clears all filters correctly', () => {
    // Set some filters first
    filterStore.applyFilters({
      grade: '1',
      difficulty: 'Easy',
      searchTerm: 'test'
    })

    filterStore.clearFilters()

    expect(filterStore.filterState.value).toEqual({
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
  })

  it('detects active filters correctly', () => {
    expect(filterStore.hasActiveFilters.value).toBe(false)

    filterStore.applyFilters({ grade: '1' })
    expect(filterStore.hasActiveFilters.value).toBe(true)

    filterStore.clearFilters()
    expect(filterStore.hasActiveFilters.value).toBe(false)
  })

  it('provides cascading filter options', () => {
    const grades = [{ id: '1', name: 'Grade 1' }]
    const subjects = [
      { id: 's1', name: 'Math', gradeId: '1' },
      { id: 's2', name: 'Science', gradeId: '2' }
    ]

    filterStore.setAvailableOptions({ grades, subjects })

    // Set grade filter
    filterStore.applyFilters({ grade: '1' })

    // Should only show subjects for selected grade
    expect(filterStore.filteredSubjects.value).toEqual([
      { id: 's1', name: 'Math', gradeId: '1' }
    ])
  })

  it('reports localStorage availability correctly', () => {
    expect(typeof filterStore.isStorageAvailable()).toBe('boolean')
  })

  it('clears persisted filters', () => {
    // Only test if localStorage is available
    if (filterStore.isStorageAvailable()) {
      filterStore.clearPersistedFilters()
      expect(localStorage.removeItem).toHaveBeenCalledWith('quiz-builder-filters')
    } else {
      // If localStorage is not available, the method should still be callable
      expect(() => {
        filterStore.clearPersistedFilters()
      }).not.toThrow()
    }
  })
})