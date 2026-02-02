/**
 * Section Store Composable
 * 
 * Manages section organization and numbering for the quiz builder.
 * Handles section creation, question assignment, and organization logic.
 */

import { ref, computed, readonly } from 'vue'
import type { Section, QuizQuestion } from '@/types/quiz-builder'

const sections = ref<Section[]>([])

export function useSectionStore() {
  /**
   * Generate a unique section ID
   */
  const generateSectionId = (): string => {
    return `section-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
  }

  /**
   * Create a new section
   */
  const createSection = (name: string, instructions?: string): Section => {
    const newSection: Section = {
      id: generateSectionId(),
      name,
      instructions,
      orderIndex: sections.value.length,
      collapsed: false,
      questions: [],
      totalPoints: 0
    }

    sections.value.push(newSection)
    return newSection
  }

  /**
   * Update an existing section
   */
  const updateSection = (sectionId: string, updates: Partial<Section>) => {
    const index = sections.value.findIndex(s => s.id === sectionId)
    if (index !== -1) {
      sections.value[index] = { ...sections.value[index], ...updates }
      
      // Recalculate total points if questions were updated
      if (updates.questions) {
        sections.value[index].totalPoints = calculateSectionPoints(sections.value[index])
      }
    }
  }

  /**
   * Delete a section and handle question reassignment
   */
  const deleteSection = (sectionId: string): QuizQuestion[] => {
    const sectionIndex = sections.value.findIndex(s => s.id === sectionId)
    if (sectionIndex === -1) return []

    const deletedSection = sections.value[sectionIndex]
    const orphanedQuestions = [...deletedSection.questions]

    // Remove the section
    sections.value.splice(sectionIndex, 1)

    // Update order indices for remaining sections
    sections.value.forEach((section, index) => {
      section.orderIndex = index
    })

    return orphanedQuestions
  }

  /**
   * Assign questions to a section
   */
  const assignQuestionsToSection = (sectionId: string, questions: QuizQuestion[]) => {
    const section = sections.value.find(s => s.id === sectionId)
    if (!section) return

    // Update questions with section assignment
    const updatedQuestions = questions.map((question, index) => ({
      ...question,
      sectionId,
      orderInSection: section.questions.length + index
    }))

    section.questions.push(...updatedQuestions)
    section.totalPoints = calculateSectionPoints(section)
  }

  /**
   * Remove questions from a section
   */
  const removeQuestionsFromSection = (sectionId: string, questionIds: string[]) => {
    const section = sections.value.find(s => s.id === sectionId)
    if (!section) return

    section.questions = section.questions.filter(q => !questionIds.includes(q.id.toString()))
    
    // Update order indices
    section.questions.forEach((question, index) => {
      question.orderInSection = index
    })

    section.totalPoints = calculateSectionPoints(section)
  }

  /**
   * Move questions between sections
   */
  const moveQuestionsBetweenSections = (
    questionIds: string[],
    fromSectionId: string,
    toSectionId: string
  ) => {
    const fromSection = sections.value.find(s => s.id === fromSectionId)
    const toSection = sections.value.find(s => s.id === toSectionId)
    
    if (!fromSection || !toSection) return

    // Find questions to move
    const questionsToMove = fromSection.questions.filter(q => 
      questionIds.includes(q.id.toString())
    )

    // Remove from source section
    removeQuestionsFromSection(fromSectionId, questionIds)

    // Add to target section
    assignQuestionsToSection(toSectionId, questionsToMove)
  }

  /**
   * Reorder sections
   */
  const reorderSections = (newOrder: Section[]) => {
    sections.value = newOrder.map((section, index) => ({
      ...section,
      orderIndex: index
    }))
  }

  /**
   * Reorder questions within a section
   */
  const reorderQuestionsInSection = (sectionId: string, newOrder: QuizQuestion[]) => {
    const section = sections.value.find(s => s.id === sectionId)
    if (!section) return

    section.questions = newOrder.map((question, index) => ({
      ...question,
      orderInSection: index
    }))
  }

  /**
   * Calculate total points for a section
   */
  const calculateSectionPoints = (section: Section): number => {
    return section.questions.reduce((total, question) => total + (question.points || 0), 0)
  }

  /**
   * Toggle section collapse state
   */
  const toggleSectionCollapse = (sectionId: string) => {
    const section = sections.value.find(s => s.id === sectionId)
    if (section) {
      section.collapsed = !section.collapsed
    }
  }

  /**
   * Get all questions organized by sections
   */
  const getAllQuestionsOrganized = computed(() => {
    const organized: QuizQuestion[] = []
    
    sections.value
      .sort((a, b) => a.orderIndex - b.orderIndex)
      .forEach(section => {
        section.questions
          .sort((a, b) => a.orderInSection - b.orderInSection)
          .forEach(question => {
            organized.push(question)
          })
      })

    return organized
  })

  /**
   * Get section statistics
   */
  const getSectionStats = computed(() => {
    return sections.value.map(section => ({
      id: section.id,
      name: section.name,
      questionCount: section.questions.length,
      totalPoints: section.totalPoints,
      averageDifficulty: calculateAverageDifficulty(section.questions),
      collapsed: section.collapsed
    }))
  })

  /**
   * Calculate average difficulty for questions
   */
  const calculateAverageDifficulty = (questions: QuizQuestion[]): string => {
    if (questions.length === 0) return 'N/A'

    const difficultyMap = { 'Easy': 1, 'Medium': 2, 'Hard': 3 }
    const sum = questions.reduce((acc, q) => acc + difficultyMap[q.difficulty], 0)
    const avg = sum / questions.length

    if (avg < 1.5) return 'Easy'
    if (avg < 2.5) return 'Medium'
    return 'Hard'
  }

  /**
   * Find section by question ID
   */
  const findSectionByQuestionId = (questionId: string): Section | undefined => {
    return sections.value.find(section => 
      section.questions.some(q => q.id.toString() === questionId)
    )
  }

  /**
   * Get question numbering with section context
   */
  const getQuestionNumbering = (questionId: string): { sectionNumber: number; questionNumber: number; globalNumber: number } | null => {
    let globalNumber = 0
    
    for (let sectionIndex = 0; sectionIndex < sections.value.length; sectionIndex++) {
      const section = sections.value[sectionIndex]
      
      for (let questionIndex = 0; questionIndex < section.questions.length; questionIndex++) {
        globalNumber++
        
        if (section.questions[questionIndex].id.toString() === questionId) {
          return {
            sectionNumber: sectionIndex + 1,
            questionNumber: questionIndex + 1,
            globalNumber
          }
        }
      }
    }
    
    return null
  }

  /**
   * Clear all sections
   */
  const clearAllSections = (): QuizQuestion[] => {
    const allQuestions: QuizQuestion[] = []
    
    sections.value.forEach(section => {
      allQuestions.push(...section.questions)
    })
    
    sections.value = []
    return allQuestions
  }

  /**
   * Initialize with a default section if none exist
   */
  const ensureDefaultSection = () => {
    if (sections.value.length === 0) {
      createSection('Main Section', 'Questions for this quiz')
    }
  }

  return {
    // State
    sections: readonly(sections),
    
    // Computed
    getAllQuestionsOrganized,
    getSectionStats,
    
    // Methods
    createSection,
    updateSection,
    deleteSection,
    assignQuestionsToSection,
    removeQuestionsFromSection,
    moveQuestionsBetweenSections,
    reorderSections,
    reorderQuestionsInSection,
    toggleSectionCollapse,
    findSectionByQuestionId,
    getQuestionNumbering,
    clearAllSections,
    ensureDefaultSection
  }
}