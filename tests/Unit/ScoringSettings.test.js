import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { Quasar } from 'quasar'
import ScoringSettings from '@/Components/Quiz/ScoringSettings.vue'

// Mock the scoring store
vi.mock('@/composables/useScoringStore', () => ({
  useScoringStore: () => ({
    calculateTotalPoints: vi.fn((questions) => questions.reduce((sum, q) => sum + (q.points || 0), 0)),
    calculatePointsDistribution: vi.fn((questions) => ({
      easy: questions.filter(q => q.difficulty === 'Easy').reduce((sum, q) => sum + (q.points || 0), 0),
      medium: questions.filter(q => q.difficulty === 'Medium').reduce((sum, q) => sum + (q.points || 0), 0),
      hard: questions.filter(q => q.difficulty === 'Hard').reduce((sum, q) => sum + (q.points || 0), 0)
    })),
    validatePassingScore: vi.fn((threshold, total) => ({ isValid: threshold <= total })),
    getDefaultPoints: vi.fn((difficulty) => {
      const defaults = { 'Easy': 1, 'Medium': 2, 'Hard': 3 }
      return defaults[difficulty] || 2
    })
  })
}))

describe('ScoringSettings', () => {
  const mockQuestions = [
    { id: '1', question_text: 'Easy question', difficulty: 'Easy', points: 1 },
    { id: '2', question_text: 'Medium question', difficulty: 'Medium', points: 2 },
    { id: '3', question_text: 'Hard question', difficulty: 'Hard', points: 3 }
  ]

  const mockScoringConfig = {
    defaultPoints: { easy: 1, medium: 2, hard: 3 },
    passingScoreThreshold: 70,
    thresholdIsPercentage: true,
    totalPossiblePoints: 6
  }

  const createWrapper = (props = {}) => {
    return mount(ScoringSettings, {
      props: {
        questions: mockQuestions,
        scoringConfig: mockScoringConfig,
        ...props
      },
      global: {
        plugins: [Quasar]
      }
    })
  }

  it('renders scoring settings component', () => {
    const wrapper = createWrapper()
    expect(wrapper.find('.scoring-settings').exists()).toBe(true)
    expect(wrapper.text()).toContain('Scoring Settings')
  })

  it('displays default points configuration', () => {
    const wrapper = createWrapper()
    
    // Check that default points inputs are rendered
    const easyInput = wrapper.find('input[label="Easy"]')
    const mediumInput = wrapper.find('input[label="Medium"]')
    const hardInput = wrapper.find('input[label="Hard"]')
    
    expect(wrapper.text()).toContain('Default Points by Difficulty')
  })

  it('displays individual question points', () => {
    const wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('Question Points')
    expect(wrapper.text()).toContain('Q1')
    expect(wrapper.text()).toContain('Q2')
    expect(wrapper.text()).toContain('Q3')
  })

  it('calculates total points correctly', () => {
    const wrapper = createWrapper()
    
    // Total should be 1 + 2 + 3 = 6
    expect(wrapper.text()).toContain('6')
    expect(wrapper.text()).toContain('Total Points')
  })

  it('emits points-updated event when question points change', async () => {
    const wrapper = createWrapper()
    
    // Simulate updating points for a question
    await wrapper.vm.updateQuestionPoints('1', 5)
    
    expect(wrapper.emitted('points-updated')).toBeTruthy()
    expect(wrapper.emitted('points-updated')[0]).toEqual(['1', 5])
  })

  it('emits passing-score-changed event when threshold changes', async () => {
    const wrapper = createWrapper()
    
    // Simulate updating passing score
    await wrapper.vm.updatePassingScore(80)
    
    expect(wrapper.emitted('passing-score-changed')).toBeTruthy()
    expect(wrapper.emitted('passing-score-changed')[0]).toEqual([80])
  })

  it('displays passing score configuration', () => {
    const wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('Passing Score Threshold')
    expect(wrapper.text()).toContain('Use percentage')
  })

  it('shows quiz summary with total points', () => {
    const wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('Quiz Summary')
    expect(wrapper.text()).toContain('Total Points')
    expect(wrapper.text()).toContain('Passing Score')
  })
})