import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import { Quasar } from 'quasar'
import LiveStats from '@/Components/Quiz/LiveStats.vue'

describe('LiveStats', () => {
  const mockStats = {
    questionCount: 5,
    totalPoints: 12,
    estimatedTimeMinutes: 8,
    averageDifficulty: 'Medium',
    pointsDistribution: {
      easy: 3,
      medium: 6,
      hard: 3
    },
    sectionCount: 2
  }

  const createWrapper = (props = {}) => {
    return mount(LiveStats, {
      props: {
        stats: mockStats,
        passingScore: 70,
        passingScoreIsPercentage: true,
        ...props
      },
      global: {
        plugins: [Quasar]
      }
    })
  }

  it('renders live stats component', () => {
    const wrapper = createWrapper()
    expect(wrapper.find('.live-stats').exists()).toBe(true)
    expect(wrapper.text()).toContain('Live Statistics')
  })

  it('displays main statistics correctly', () => {
    const wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('5') // question count
    expect(wrapper.text()).toContain('12') // total points
    expect(wrapper.text()).toContain('8m') // estimated time
    expect(wrapper.text()).toContain('Medium') // difficulty
  })

  it('shows points distribution', () => {
    const wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('Points Distribution')
    expect(wrapper.text()).toContain('Easy: 3')
    expect(wrapper.text()).toContain('Medium: 6')
    expect(wrapper.text()).toContain('Hard: 3')
  })

  it('displays passing score information when provided', () => {
    const wrapper = createWrapper({
      passingScore: 70,
      passingScoreIsPercentage: true
    })
    
    expect(wrapper.text()).toContain('Passing Score Threshold')
    expect(wrapper.text()).toContain('70%')
  })

  it('calculates passing score points correctly for percentage', () => {
    const wrapper = createWrapper({
      passingScore: 50,
      passingScoreIsPercentage: true
    })
    
    // 50% of 12 points = 6 points
    expect(wrapper.vm.passingScorePoints).toBe(6)
  })

  it('handles absolute passing score correctly', () => {
    const wrapper = createWrapper({
      passingScore: 8,
      passingScoreIsPercentage: false
    })
    
    expect(wrapper.vm.passingScorePoints).toBe(8)
    expect(wrapper.vm.passingScorePercentage).toBe(67) // 8/12 * 100 = 67%
  })

  it('shows section information when sections exist', () => {
    const wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('Organization')
    expect(wrapper.text()).toContain('2 sections')
  })

  it('displays empty state when no questions', () => {
    const emptyStats = {
      questionCount: 0,
      totalPoints: 0,
      estimatedTimeMinutes: 0,
      averageDifficulty: 'N/A',
      pointsDistribution: { easy: 0, medium: 0, hard: 0 },
      sectionCount: 0
    }
    
    const wrapper = createWrapper({ stats: emptyStats })
    
    expect(wrapper.text()).toContain('No Questions Yet')
    expect(wrapper.text()).toContain('Add questions to see live statistics')
  })

  it('provides appropriate passing score status messages', () => {
    // Test low threshold
    const lowThresholdWrapper = createWrapper({
      passingScore: 40,
      passingScoreIsPercentage: true
    })
    expect(lowThresholdWrapper.vm.passingScoreStatus).toContain('Most students should pass')

    // Test high threshold
    const highThresholdWrapper = createWrapper({
      passingScore: 90,
      passingScoreIsPercentage: true
    })
    expect(highThresholdWrapper.vm.passingScoreStatus).toContain('Only top performers will pass')
  })

  it('calculates percentages correctly', () => {
    const wrapper = createWrapper()
    
    expect(wrapper.vm.getPercentage(3, 12)).toBe(25) // 3/12 = 25%
    expect(wrapper.vm.getPercentage(6, 12)).toBe(50) // 6/12 = 50%
    expect(wrapper.vm.getPercentage(0, 0)).toBe(0) // Handle division by zero
  })
})