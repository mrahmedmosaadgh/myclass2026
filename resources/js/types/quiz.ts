/**
 * Enterprise Quiz System - TypeScript Type Definitions
 * 
 * This file contains all TypeScript interfaces and types for the quiz system.
 * These types ensure type safety across the Vue 3 components and provide
 * clear contracts for data structures used throughout the application.
 */

/**
 * Represents a single answer option for a question.
 * 
 * @interface AnswerOption
 * @property {string | number} id - Unique identifier for the option
 * @property {string} text - The text content of the answer option
 * @property {boolean} isCorrect - Whether this option is the correct answer
 * @property {string} [rationale] - Optional explanation for why this answer is correct/incorrect
 * @property {number} [distractorStrength] - Optional metric (0-1) indicating how effective this distractor is
 */
export interface AnswerOption {
  id: string | number
  text: string
  isCorrect: boolean
  rationale?: string
  distractorStrength?: number
}

/**
 * Represents a question type classification.
 * 
 * @interface QuestionType
 * @property {number} id - Unique identifier for the question type
 * @property {string} slug - URL-friendly identifier (e.g., 'multiple_choice', 'true_false')
 * @property {string} name - Human-readable name of the question type
 * @property {boolean} hasOptions - Whether this question type uses answer options
 * @property {boolean} supportsHints - Whether this question type supports hints
 * @property {boolean} supportsExplanation - Whether this question type supports explanations
 */
export interface QuestionType {
  id: number
  slug: string
  name: string
  hasOptions: boolean
  supportsHints: boolean
  supportsExplanation: boolean
}

/**
 * Represents a complete quiz question with all associated data.
 * 
 * @interface QuizQuestion
 * @property {string | number} id - Unique identifier for the question
 * @property {number} questionNumber - The sequential number of this question in the quiz (1-based)
 * @property {number} questionTypeId - Foreign key reference to the question type
 * @property {QuestionType} questionType - The complete question type object
 * @property {string} question - The question text (may contain HTML/LaTeX)
 * @property {AnswerOption[]} answerOptions - Array of possible answer options
 * @property {string} [explanation] - Optional global explanation shown after answering
 * @property {string[]} [hints] - Optional array of hint texts
 * @property {number} [bloomLevel] - Bloom's Taxonomy level (1-6)
 * @property {number} [difficultyLevel] - Difficulty rating (1-5)
 * @property {number} [estimatedTimeSec] - Estimated time to complete in seconds
 * @property {Record<string, any>} [metadata] - Additional metadata as key-value pairs
 */
export interface QuizQuestion {
  id: string | number
  questionNumber: number
  questionTypeId: number
  questionType: QuestionType
  question: string
  answerOptions: AnswerOption[]
  explanation?: string
  hints?: string[]
  bloomLevel?: number
  difficultyLevel?: number
  estimatedTimeSec?: number
  metadata?: Record<string, any>
}

/**
 * Represents a student's answer to a specific question.
 * 
 * @interface AnswerRecord
 * @property {string | number} questionId - The ID of the question being answered
 * @property {number} questionNumber - The sequential number of the question
 * @property {number} selectedIndex - The index of the selected option in the answerOptions array
 * @property {string | number} [selectedOptionId] - Optional ID of the selected option
 * @property {string} [selectedText] - Optional text answer (for fill-in-blank or essay questions)
 * @property {boolean} correct - Whether the answer was correct
 * @property {string} question - The question text (for reference in results)
 * @property {string} correctText - The text of the correct answer
 * @property {number} timeSpentSec - Time spent on this question in seconds
 * @property {Date} answeredAt - Timestamp when the answer was submitted
 */
export interface AnswerRecord {
  questionId: string | number
  questionNumber: number
  selectedIndex: number
  selectedOptionId?: string | number
  selectedText?: string
  correct: boolean
  question: string
  correctText: string
  timeSpentSec: number
  answeredAt: Date
}

/**
 * Represents the complete results of a quiz attempt.
 * 
 * @interface QuizResult
 * @property {string | number} attemptId - Unique identifier for this quiz attempt
 * @property {number} total - Total number of questions in the quiz
 * @property {number} correct - Number of correctly answered questions
 * @property {number} percentage - Percentage score (0-100)
 * @property {AnswerRecord[]} answers - Array of all answer records
 * @property {Date} completedAt - Timestamp when the quiz was completed
 * @property {Record<string, any>} [metadata] - Additional metadata about the attempt
 */
export interface QuizResult {
  attemptId: string | number
  total: number
  correct: number
  percentage: number
  answers: AnswerRecord[]
  completedAt: Date
  metadata?: Record<string, any>
}

/**
 * Configuration options for quiz behavior.
 * 
 * @interface QuizConfig
 * @property {boolean} allowReviewMode - Whether students can navigate freely between questions
 * @property {boolean} autoAdvance - Whether to automatically advance to next question on correct answer
 * @property {boolean} showRationaleOnCorrect - Whether to show rationale even for correct answers
 * @property {number} [timeLimit] - Optional time limit in seconds for the entire quiz
 * @property {boolean} [shuffleQuestions] - Whether to randomize question order
 * @property {boolean} [shuffleOptions] - Whether to randomize answer option order
 */
export interface QuizConfig {
  allowReviewMode: boolean
  autoAdvance: boolean
  showRationaleOnCorrect: boolean
  timeLimit?: number
  shuffleQuestions?: boolean
  shuffleOptions?: boolean
}

/**
 * Props interface for the QuizEngine component.
 * 
 * @interface QuizEngineProps
 * @property {QuizQuestion[]} quiz - Array of questions to display in the quiz
 * @property {Partial<QuizConfig>} [config] - Optional configuration overrides
 * @property {string | number} [attemptId] - Optional attempt ID for tracking
 */
export interface QuizEngineProps {
  quiz: QuizQuestion[]
  config?: Partial<QuizConfig>
  attemptId?: string | number
}

/**
 * Event emitters interface for the QuizEngine component.
 * Defines all events that the QuizEngine can emit to parent components.
 * 
 * @interface QuizEngineEmits
 */
export interface QuizEngineEmits {
  /**
   * Emitted when a student selects an answer option.
   * @param record - The complete answer record with all details
   */
  'answer-selected': (record: AnswerRecord) => void
  
  /**
   * Emitted when the current question changes (navigation).
   * @param index - The new question index (0-based)
   */
  'question-changed': (index: number) => void
  
  /**
   * Emitted when the quiz is completed and all questions are answered.
   * @param result - The complete quiz result with score and answers
   */
  'quiz-completed': (result: QuizResult) => void
  
  /**
   * Emitted when entering review mode (all questions answered, review enabled).
   */
  'quiz-review-enter': () => void
  
  /**
   * Emitted when time limit is approaching (e.g., 5 minutes remaining).
   * @param remainingSeconds - Number of seconds remaining
   */
  'time-warning': (remainingSeconds: number) => void
}
