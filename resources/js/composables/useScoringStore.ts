/**
 * Scoring Store Composable
 * 
 * Manages point calculations and scoring logic for the quiz builder.
 * Handles default point assignment, custom overrides, and total calculations.
 */

import { ref, computed, readonly } from 'vue'
import type { QuizQuestion, ScoringConfig, LiveStats } from '@/types/quiz-builder'
import { DEFAULTS } from '@/types/quiz-builder'

const scoringConfig = ref<ScoringConfig>({
  defaultPoints: {
    easy: DEFAULTS.SCORING.EASY_POINTS,
    medium: DEFAULTS.SCORING.MEDIUM_POINTS,
    hard: DEFAULTS.SCORING.HARD_POINTS
  },
  passingScoreThreshold: DEFAULTS.SCORING.PASSING_THRESHOLD,
  thresholdIsPercentage: DEFAULTS.SCORING.THRESHOLD_IS_PERCENTAGE,
  totalPossiblePoints: 0
})

export function useScoringStore() {
  /**
   * Calculate default points for a question based on difficulty
   */
  const getDefaultPoints = (difficulty: 'Easy' | 'Medium' | 'Hard'): number => {
    switch (difficulty) {
      case 'Easy':
        return scoringConfig.value.defaultPoints.easy
      case 'Medium':
        return scoringConfig.value.defaultPoints.medium
      case 'Hard':
        return scoringConfig.value.defaultPoints.hard
      default:
        return scoringConfig.value.defaultPoints.medium
    }
  }

  /**
   * Apply default points to a question if not already set
   */
  const applyDefaultPoints = (question: QuizQuestion): QuizQuestion => {
    if (!question.points || question.points === 0) {
      question.points = getDefaultPoints(question.difficulty)
    }
    return question
  }

  /**
   * Update points for a specific question
   */
  const updateQuestionPoints = (questions: QuizQuestion[], questionId: string, points: number): QuizQuestion[] => {
    return questions.map(question => {
      if (question.id === questionId) {
        return { ...question, points }
      }
      return question
    })
  }

  /**
   * Calculate total points for a set of questions
   */
  const calculateTotalPoints = (questions: QuizQuestion[]): number => {
    return questions.reduce((total, question) => total + (question.points || 0), 0)
  }

  /**
   * Calculate points distribution by difficulty
   */
  const calculatePointsDistribution = (questions: QuizQuestion[]) => {
    const distribution = {
      easy: 0,
      medium: 0,
      hard: 0
    }

    questions.forEach(question => {
      const points = question.points || 0
      switch (question.difficulty) {
        case 'Easy':
          distribution.easy += points
          break
        case 'Medium':
          distribution.medium += points
          break
        case 'Hard':
          distribution.hard += points
          break
      }
    })

    return distribution
  }

  /**
   * Validate passing score threshold
   */
  const validatePassingScore = (threshold: number, totalPoints: number): { isValid: boolean; message?: string } => {
    if (threshold < 0) {
      return { isValid: false, message: 'Passing score cannot be negative' }
    }

    if (scoringConfig.value.thresholdIsPercentage) {
      if (threshold > 100) {
        return { isValid: false, message: 'Percentage cannot exceed 100%' }
      }
    } else {
      if (threshold > totalPoints) {
        return { isValid: false, message: 'Passing score cannot exceed total possible points' }
      }
    }

    return { isValid: true }
  }

  /**
   * Calculate live statistics for the quiz
   */
  const calculateLiveStats = (questions: QuizQuestion[], sections: any[] = []): LiveStats => {
    const totalPoints = calculateTotalPoints(questions)
    const pointsDistribution = calculatePointsDistribution(questions)
    
    // Calculate average difficulty
    const difficultyMap = { 'Easy': 1, 'Medium': 2, 'Hard': 3 }
    const avgDifficultyValue = questions.length > 0 
      ? questions.reduce((sum, q) => sum + difficultyMap[q.difficulty], 0) / questions.length
      : 0
    
    let averageDifficulty = 'N/A'
    if (avgDifficultyValue > 0) {
      if (avgDifficultyValue < 1.5) averageDifficulty = 'Easy'
      else if (avgDifficultyValue < 2.5) averageDifficulty = 'Medium'
      else averageDifficulty = 'Hard'
    }

    // Estimate completion time
    const estimatedTimeMinutes = Math.ceil(questions.length * DEFAULTS.TIMING.ESTIMATED_MINUTES_PER_QUESTION)

    return {
      questionCount: questions.length,
      totalPoints,
      estimatedTimeMinutes,
      averageDifficulty,
      pointsDistribution,
      sectionCount: sections.length
    }
  }

  /**
   * Update scoring configuration
   */
  const updateScoringConfig = (updates: Partial<ScoringConfig>) => {
    Object.assign(scoringConfig.value, updates)
  }

  /**
   * Reset scoring configuration to defaults
   */
  const resetScoringConfig = () => {
    scoringConfig.value = {
      defaultPoints: {
        easy: DEFAULTS.SCORING.EASY_POINTS,
        medium: DEFAULTS.SCORING.MEDIUM_POINTS,
        hard: DEFAULTS.SCORING.HARD_POINTS
      },
      passingScoreThreshold: DEFAULTS.SCORING.PASSING_THRESHOLD,
      thresholdIsPercentage: DEFAULTS.SCORING.THRESHOLD_IS_PERCENTAGE,
      totalPossiblePoints: 0
    }
  }

  /**
   * Get passing score in absolute points
   */
  const getPassingScorePoints = computed(() => {
    if (!scoringConfig.value.passingScoreThreshold) return 0
    
    if (scoringConfig.value.thresholdIsPercentage) {
      return Math.ceil((scoringConfig.value.passingScoreThreshold / 100) * scoringConfig.value.totalPossiblePoints)
    }
    
    return scoringConfig.value.passingScoreThreshold
  })

  /**
   * Get passing score as percentage
   */
  const getPassingScorePercentage = computed(() => {
    if (!scoringConfig.value.passingScoreThreshold) return 0
    
    if (scoringConfig.value.thresholdIsPercentage) {
      return scoringConfig.value.passingScoreThreshold
    }
    
    if (scoringConfig.value.totalPossiblePoints === 0) return 0
    
    return Math.round((scoringConfig.value.passingScoreThreshold / scoringConfig.value.totalPossiblePoints) * 100)
  })

  return {
    // State
    scoringConfig: readonly(scoringConfig),
    
    // Computed
    getPassingScorePoints,
    getPassingScorePercentage,
    
    // Methods
    getDefaultPoints,
    applyDefaultPoints,
    updateQuestionPoints,
    calculateTotalPoints,
    calculatePointsDistribution,
    validatePassingScore,
    calculateLiveStats,
    updateScoringConfig,
    resetScoringConfig
  }
}