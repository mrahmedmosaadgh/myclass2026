/**
 * Smart Selection Composable
 * 
 * Provides intelligent question selection algorithms for the quiz builder.
 * Includes random selection and balanced selection based on difficulty distribution.
 */

import { computed } from 'vue'
import type { QuizQuestion, SmartSelectionCriteria, QuestionPoolStats } from '@/types/quiz-builder'

export function useSmartSelection() {
  /**
   * Calculate question pool statistics
   */
  const calculatePoolStats = (questions: QuizQuestion[]): QuestionPoolStats => {
    const stats: QuestionPoolStats = {
      totalQuestions: questions.length,
      difficultyDistribution: { easy: 0, medium: 0, hard: 0 },
      topicDistribution: {},
      authorDistribution: {},
      bloomsDistribution: {}
    }

    questions.forEach(question => {
      // Difficulty distribution
      switch (question.difficulty) {
        case 'Easy':
          stats.difficultyDistribution.easy++
          break
        case 'Medium':
          stats.difficultyDistribution.medium++
          break
        case 'Hard':
          stats.difficultyDistribution.hard++
          break
      }

      // Topic distribution
      if (question.topic_id) {
        const topicKey = question.topic_id.toString()
        stats.topicDistribution[topicKey] = (stats.topicDistribution[topicKey] || 0) + 1
      }

      // Author distribution
      if (question.author_id) {
        const authorKey = question.author_id.toString()
        stats.authorDistribution[authorKey] = (stats.authorDistribution[authorKey] || 0) + 1
      }

      // Bloom's taxonomy distribution
      if (question.bloomLevel) {
        stats.bloomsDistribution[question.bloomLevel] = (stats.bloomsDistribution[question.bloomLevel] || 0) + 1
      }
    })

    return stats
  }

  /**
   * Random selection algorithm
   */
  const randomSelection = (
    availableQuestions: QuizQuestion[],
    count: number
  ): QuizQuestion[] => {
    if (count >= availableQuestions.length) {
      return [...availableQuestions]
    }

    const shuffled = [...availableQuestions].sort(() => Math.random() - 0.5)
    return shuffled.slice(0, count)
  }

  /**
   * Balanced selection algorithm
   * Attempts to maintain a balanced distribution of difficulty levels
   */
  const balancedSelection = (
    availableQuestions: QuizQuestion[],
    count: number,
    targetDistribution?: { easy: number; medium: number; hard: number }
  ): QuizQuestion[] => {
    if (count >= availableQuestions.length) {
      return [...availableQuestions]
    }

    // Default balanced distribution (roughly equal)
    const defaultDistribution = {
      easy: Math.floor(count * 0.33),
      medium: Math.floor(count * 0.34),
      hard: Math.floor(count * 0.33)
    }

    // Adjust for exact count
    const totalDefault = defaultDistribution.easy + defaultDistribution.medium + defaultDistribution.hard
    if (totalDefault < count) {
      defaultDistribution.medium += count - totalDefault
    }

    const distribution = targetDistribution || defaultDistribution

    // Group questions by difficulty
    const questionsByDifficulty = {
      easy: availableQuestions.filter(q => q.difficulty === 'Easy'),
      medium: availableQuestions.filter(q => q.difficulty === 'Medium'),
      hard: availableQuestions.filter(q => q.difficulty === 'Hard')
    }

    const selected: QuizQuestion[] = []

    // Select from each difficulty level
    Object.entries(distribution).forEach(([difficulty, targetCount]) => {
      const difficultyKey = difficulty as keyof typeof questionsByDifficulty
      const availableForDifficulty = questionsByDifficulty[difficultyKey]
      
      if (availableForDifficulty.length > 0) {
        const toSelect = Math.min(targetCount, availableForDifficulty.length)
        const shuffled = [...availableForDifficulty].sort(() => Math.random() - 0.5)
        selected.push(...shuffled.slice(0, toSelect))
      }
    })

    // If we haven't selected enough questions, fill from remaining questions
    if (selected.length < count) {
      const selectedIds = new Set(selected.map(q => q.id.toString()))
      const remaining = availableQuestions.filter(q => !selectedIds.has(q.id.toString()))
      const shuffledRemaining = remaining.sort(() => Math.random() - 0.5)
      const needed = count - selected.length
      selected.push(...shuffledRemaining.slice(0, needed))
    }

    return selected
  }

  /**
   * Smart selection based on criteria
   */
  const smartSelection = (
    availableQuestions: QuizQuestion[],
    criteria: SmartSelectionCriteria
  ): { selected: QuizQuestion[]; stats: QuestionPoolStats } => {
    let selected: QuizQuestion[]

    switch (criteria.algorithm) {
      case 'random':
        selected = randomSelection(availableQuestions, criteria.count)
        break
      case 'balanced':
        selected = balancedSelection(availableQuestions, criteria.count, criteria.targetDistribution)
        break
      default:
        selected = randomSelection(availableQuestions, criteria.count)
    }

    const stats = calculatePoolStats(selected)

    return { selected, stats }
  }

  /**
   * Validate selection criteria
   */
  const validateSelectionCriteria = (
    criteria: SmartSelectionCriteria,
    availableQuestions: QuizQuestion[]
  ): { isValid: boolean; warnings: string[] } => {
    const warnings: string[] = []

    if (criteria.count <= 0) {
      return { isValid: false, warnings: ['Selection count must be greater than 0'] }
    }

    if (criteria.count > availableQuestions.length) {
      warnings.push(`Requested ${criteria.count} questions but only ${availableQuestions.length} available`)
    }

    if (criteria.algorithm === 'balanced' && criteria.targetDistribution) {
      const totalTarget = criteria.targetDistribution.easy + 
                         criteria.targetDistribution.medium + 
                         criteria.targetDistribution.hard

      if (totalTarget !== criteria.count) {
        warnings.push('Target distribution does not match selection count')
      }
    }

    // Check if balanced selection is possible with current pool
    if (criteria.algorithm === 'balanced') {
      const poolStats = calculatePoolStats(availableQuestions)
      
      if (poolStats.difficultyDistribution.easy === 0 && criteria.count > 2) {
        warnings.push('No easy questions available for balanced selection')
      }
      if (poolStats.difficultyDistribution.medium === 0 && criteria.count > 2) {
        warnings.push('No medium questions available for balanced selection')
      }
      if (poolStats.difficultyDistribution.hard === 0 && criteria.count > 2) {
        warnings.push('No hard questions available for balanced selection')
      }
    }

    return { isValid: true, warnings }
  }

  /**
   * Get recommended selection count based on pool size
   */
  const getRecommendedCount = (poolSize: number): number => {
    if (poolSize <= 5) return poolSize
    if (poolSize <= 20) return Math.floor(poolSize * 0.5)
    if (poolSize <= 50) return Math.floor(poolSize * 0.3)
    return Math.floor(poolSize * 0.2)
  }

  /**
   * Get selection algorithm recommendations
   */
  const getAlgorithmRecommendations = (
    poolStats: QuestionPoolStats
  ): { algorithm: 'random' | 'balanced'; reason: string } => {
    const { difficultyDistribution } = poolStats
    const total = difficultyDistribution.easy + difficultyDistribution.medium + difficultyDistribution.hard

    // If pool is too small or heavily skewed, recommend random
    if (total < 6) {
      return { algorithm: 'random', reason: 'Pool too small for balanced selection' }
    }

    const easyRatio = difficultyDistribution.easy / total
    const mediumRatio = difficultyDistribution.medium / total
    const hardRatio = difficultyDistribution.hard / total

    // Check if distribution is heavily skewed (>80% in one difficulty)
    if (easyRatio > 0.8 || mediumRatio > 0.8 || hardRatio > 0.8) {
      return { algorithm: 'random', reason: 'Pool heavily skewed toward one difficulty level' }
    }

    // Check if any difficulty level is completely missing
    if (easyRatio === 0 || mediumRatio === 0 || hardRatio === 0) {
      return { algorithm: 'random', reason: 'Missing questions in one or more difficulty levels' }
    }

    return { algorithm: 'balanced', reason: 'Good distribution across difficulty levels' }
  }

  /**
   * Preview selection results without actually selecting
   */
  const previewSelection = (
    availableQuestions: QuizQuestion[],
    criteria: SmartSelectionCriteria
  ): {
    wouldSelect: number
    distribution: { easy: number; medium: number; hard: number }
    warnings: string[]
  } => {
    const validation = validateSelectionCriteria(criteria, availableQuestions)
    const actualCount = Math.min(criteria.count, availableQuestions.length)

    let distribution = { easy: 0, medium: 0, hard: 0 }

    if (criteria.algorithm === 'balanced') {
      const defaultDistribution = {
        easy: Math.floor(actualCount * 0.33),
        medium: Math.floor(actualCount * 0.34),
        hard: Math.floor(actualCount * 0.33)
      }

      const totalDefault = defaultDistribution.easy + defaultDistribution.medium + defaultDistribution.hard
      if (totalDefault < actualCount) {
        defaultDistribution.medium += actualCount - totalDefault
      }

      distribution = criteria.targetDistribution || defaultDistribution
    } else {
      // For random selection, estimate based on current pool distribution
      const poolStats = calculatePoolStats(availableQuestions)
      const total = poolStats.totalQuestions

      if (total > 0) {
        distribution = {
          easy: Math.round((poolStats.difficultyDistribution.easy / total) * actualCount),
          medium: Math.round((poolStats.difficultyDistribution.medium / total) * actualCount),
          hard: Math.round((poolStats.difficultyDistribution.hard / total) * actualCount)
        }
      }
    }

    return {
      wouldSelect: actualCount,
      distribution,
      warnings: validation.warnings
    }
  }

  return {
    // Methods
    calculatePoolStats,
    randomSelection,
    balancedSelection,
    smartSelection,
    validateSelectionCriteria,
    getRecommendedCount,
    getAlgorithmRecommendations,
    previewSelection
  }
}