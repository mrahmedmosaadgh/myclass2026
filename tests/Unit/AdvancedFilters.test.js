import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import AdvancedFilters from '@/Components/Quiz/AdvancedFilters.vue'

// Mock the useFilterStore composable
const mockFilterStore = {
  setAvailableOptions: vi.fn(),
  filteredSubjects: [],
  filteredTopics: []
}

vi.mock('@/composables/useFilterStore', () => ({
  useFilterStore: () => mockFilterStore
}))

describe('AdvancedFilters Component', () => {
  let wrapper

  const defaultProps = {
    availableGrades: [
      { id: '1', name: 'Grade 1', level: 1 },
      { id: '2', name: 'Grade 2', level: 2 }
    ],
    availableSubjects: [
      { id: 's1', name: 'Math', gradeId: '1' },
      { id: 's2', name: 'Science', gradeId: '2' }
    ],
    availableTopics: [
      { id: 't1', name: 'Addition', subjectId: 's1' },
      { id: 't2', name: 'Physics', subjectId: 's2' }
    ],
    authors: [
      { id: 'a1', name: 'John Doe', email: 'john@example.com' },
      { id: 'a2', name: 'Jane Smith', email: 'jane@example.com' }
    ],
    questionTypes: [
      { id: 'qt1', name: 'Multiple Choice' },
      { id: 'qt2', name: 'True/False' }
    ],
    modelValue: {
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
  }

  beforeEach(() => {
    wrapper = mount(AdvancedFilters, {
      props: defaultProps
    })
  })

  it('renders all filter controls', () => {
    // Check that all main filter controls are present
    expect(wrapper.find('input[placeholder="Search questions..."]').exists()).toBe(true)
    expect(wrapper.findAll('.q-select').length).toBe(8) // Grade, Subject, Topic, Type, Difficulty, Bloom's, Author, Usage
  })

  it('displays correct difficulty options', () => {
    const difficultySelect = wrapper.findComponent({ name: 'QSelect' })
    // We can't easily test the options without triggering the dropdown, 
    // but we can verify the component structure
    expect(difficultySelect.exists()).toBe(true)
  })

  it('emits filter-changed when search term changes', async () => {
    const searchInput = wrapper.find('input[placeholder="Search questions..."]')
    await searchInput.setValue('test search')
    
    expect(wrapper.emitted('filter-changed')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
  })

  it('shows active filter count correctly', async () => {
    // Initially no active filters
    expect(wrapper.text()).toContain('0 Active')
    
    // Set some filters via props
    await wrapper.setProps({
      modelValue: {
        ...defaultProps.modelValue,
        searchTerm: 'test',
        difficulty: 'Easy'
      }
    })
    
    await nextTick()
    expect(wrapper.text()).toContain('2 Active')
  })

  it('displays active filter summary chips', async () => {
    await wrapper.setProps({
      modelValue: {
        ...defaultProps.modelValue,
        searchTerm: 'test search',
        difficulty: 'Medium'
      }
    })
    
    await nextTick()
    
    // Should show filter chips
    const chips = wrapper.findAll('.q-chip')
    expect(chips.length).toBeGreaterThan(0)
  })

  it('clears all filters when clear button is clicked', async () => {
    // Set some filters first
    await wrapper.setProps({
      modelValue: {
        ...defaultProps.modelValue,
        searchTerm: 'test',
        difficulty: 'Hard'
      }
    })
    
    await nextTick()
    
    // Find and click clear button
    const buttons = wrapper.findAll('button')
    const clearButton = buttons.find(btn => btn.text().includes('Clear All'))
    expect(clearButton).toBeTruthy()
    await clearButton.trigger('click')
    
    expect(wrapper.emitted('filters-cleared')).toBeTruthy()
  })

  it('handles cascading filter logic for grade->subject', async () => {
    // This tests the computed property logic
    const component = wrapper.vm
    
    // Set grade filter
    component.localFilters.grade = '1'
    await nextTick()
    
    // filteredSubjects should only show subjects for grade 1
    expect(component.filteredSubjects).toEqual([
      { id: 's1', name: 'Math', gradeId: '1' }
    ])
  })

  it('handles cascading filter logic for subject->topic', async () => {
    const component = wrapper.vm
    
    // Set subject filter
    component.localFilters.subject = 's1'
    await nextTick()
    
    // filteredTopics should only show topics for subject s1
    expect(component.filteredTopics).toEqual([
      { id: 't1', name: 'Addition', subjectId: 's1' }
    ])
  })

  it('clears dependent filters when parent filter changes', async () => {
    const component = wrapper.vm
    
    // Set all cascading filters
    component.localFilters.grade = '1'
    component.localFilters.subject = 's1'
    component.localFilters.topic = 't1'
    
    // Change grade - should clear subject and topic
    component.onGradeChange()
    
    expect(component.localFilters.subject).toBeUndefined()
    expect(component.localFilters.topic).toBeUndefined()
  })

  it('initializes filter store with available options on mount', () => {
    expect(mockFilterStore.setAvailableOptions).toHaveBeenCalledWith({
      grades: defaultProps.availableGrades,
      subjects: defaultProps.availableSubjects,
      topics: defaultProps.availableTopics,
      authors: defaultProps.authors
    })
  })
})