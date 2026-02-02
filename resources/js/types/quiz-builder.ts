/**
 * Quiz Builder Enhancements - TypeScript Type Definitions
 * 
 * This file contains TypeScript interfaces and types for the enhanced quiz builder system.
 * These types support advanced filtering, bulk operations, points-based scoring, 
 * and section organization features.
 */

import { QuizQuestion as BaseQuizQuestion, AnswerOption, QuestionType } from './quiz'

/**
 * Enhanced Question model with points and section properties
 * Extends the base QuizQuestion with builder-specific enhancements
 */
export interface QuizQuestion extends BaseQuizQuestion {
  /** Point value assigned to this question (overrides default) */
  points: number
  /** ID of the section this question belongs to */
  sectionId?: string
  /** Order of this question within its section */
  orderInSection: number
  /** Original question data from the question bank */
  question_text: string
  /** Question difficulty level (Easy, Medium, Hard) */
  difficulty: 'Easy' | 'Medium' | 'Hard'
  /** Question options for multiple choice questions */
  options: AnswerOption[]
  /** Question type ID reference */
  question_type_id: number
  /** Topic ID for curriculum alignment */
  topic_id?: number
  /** Subject ID for curriculum alignment */
  subject_id?: number
  /** Grade level ID for curriculum alignment */
  grade_id?: number
  /** Author/creator of the question */
  author_id?: number
  /** Usage count in other quizzes */
  usage_count?: number
  /** Average success rate across attempts */
  avg_success_rate?: number
  /** Question status (active, draft, archived) */
  status: 'active' | 'draft' | 'archived'
}

/**
 * Filter state for advanced question filtering
 * Manages all filter criteria applied to the question pool
 */
export interface FilterState {
  /** Selected grade level */
  grade?: string
  /** Selected subject (filtered by grade) */
  subject?: string
  /** Selected topic (filtered by subject) */
  topic?: string
  /** Selected Bloom's Taxonomy level (1-6) */
  bloomsLevel?: string
  /** Selected question author/creator */
  author?: string
  /** Filter by quiz usage status */
  usedInQuiz?: 'used' | 'unused' | 'all'
  /** Text search term */
  searchTerm?: string
  /** Selected question type */
  questionType?: string
  /** Selected difficulty level */
  difficulty?: 'Easy' | 'Medium' | 'Hard'
}

/**
 * Section model for organizing questions into logical groups
 */
export interface Section {
  /** Unique identifier for the section */
  id: string
  /** Display name of the section */
  name: string
  /** Optional instructions shown at the beginning of the section */
  instructions?: string
  /** Order index for section positioning */
  orderIndex: number
  /** Whether the section is collapsed in the UI */
  collapsed: boolean
  /** Questions assigned to this section */
  questions: QuizQuestion[]
  /** Total points for all questions in this section */
  totalPoints: number
}

/**
 * Scoring configuration for the quiz
 */
export interface ScoringConfig {
  /** Default point values by difficulty level */
  defaultPoints: {
    easy: number
    medium: number
    hard: number
  }
  /** Passing score threshold (percentage or absolute points) */
  passingScoreThreshold?: number
  /** Whether threshold is percentage (true) or absolute points (false) */
  thresholdIsPercentage: boolean
  /** Total possible points for the entire quiz */
  totalPossiblePoints: number
}

/**
 * Bulk operation state for multi-question selection
 */
export interface BulkOperationState {
  /** Whether multi-select mode is enabled */
  multiSelectMode: boolean
  /** IDs of currently selected questions */
  selectedQuestionIds: string[]
  /** Whether "select all" is active */
  selectAllActive: boolean
}

/**
 * Smart selection criteria for random/balanced selection
 */
export interface SmartSelectionCriteria {
  /** Number of questions to select */
  count: number
  /** Selection algorithm type */
  algorithm: 'random' | 'balanced'
  /** Filters to apply during selection */
  filters: FilterState
  /** For balanced selection: target distribution */
  targetDistribution?: {
    easy: number
    medium: number
    hard: number
  }
}

/**
 * Question pool statistics for display
 */
export interface QuestionPoolStats {
  /** Total questions in current filtered pool */
  totalQuestions: number
  /** Distribution by difficulty level */
  difficultyDistribution: {
    easy: number
    medium: number
    hard: number
  }
  /** Distribution by topic */
  topicDistribution: Record<string, number>
  /** Distribution by author */
  authorDistribution: Record<string, number>
  /** Distribution by Bloom's taxonomy level */
  bloomsDistribution: Record<number, number>
}

/**
 * Live statistics for the quiz canvas
 */
export interface LiveStats {
  /** Total number of questions selected */
  questionCount: number
  /** Total points for all selected questions */
  totalPoints: number
  /** Estimated completion time in minutes */
  estimatedTimeMinutes: number
  /** Average difficulty level */
  averageDifficulty: string
  /** Points distribution by difficulty */
  pointsDistribution: {
    easy: number
    medium: number
    hard: number
  }
  /** Number of sections */
  sectionCount: number
}

/**
 * Grade level for curriculum alignment
 */
export interface Grade {
  id: string
  name: string
  level: number
}

/**
 * Subject for curriculum alignment
 */
export interface Subject {
  id: string
  name: string
  gradeId: string
}

/**
 * Topic for curriculum alignment
 */
export interface Topic {
  id: string
  name: string
  subjectId: string
}

/**
 * Author/creator information
 */
export interface Author {
  id: string
  name: string
  email?: string
}

/**
 * Enhanced quiz model with builder-specific properties
 */
export interface EnhancedQuiz {
  /** Basic quiz properties */
  id?: string
  name: string
  description?: string
  time_limit_minutes?: number
  status: 'draft' | 'active' | 'archived'
  shuffle_questions: boolean
  shuffle_options: boolean
  allow_review: boolean
  
  /** Enhanced properties */
  sections: Section[]
  scoringConfig: ScoringConfig
  selectedQuestions: QuizQuestion[]
  
  /** Metadata */
  created_at?: string
  updated_at?: string
  author_id?: string
}

/**
 * Props for AdvancedFilters component
 */
export interface AdvancedFiltersProps {
  availableGrades: Grade[]
  availableSubjects: Subject[]
  availableTopics: Topic[]
  authors: Author[]
  questionTypes: QuestionType[]
  modelValue: FilterState
}

/**
 * Events emitted by AdvancedFilters component
 */
export interface AdvancedFiltersEmits {
  'update:modelValue': (filters: FilterState) => void
  'filter-changed': (filters: FilterState) => void
  'filters-cleared': () => void
}

/**
 * Props for BulkOperations component
 */
export interface BulkOperationsProps {
  filteredQuestions: QuizQuestion[]
  selectedQuestions: QuizQuestion[]
  bulkState: BulkOperationState
}

/**
 * Events emitted by BulkOperations component
 */
export interface BulkOperationsEmits {
  'add-all-filtered': () => void
  'add-selected': (questions: QuizQuestion[]) => void
  'toggle-multi-select': (enabled: boolean) => void
  'clear-selection': () => void
}

/**
 * Props for ScoringSettings component
 */
export interface ScoringSettingsProps {
  questions: QuizQuestion[]
  scoringConfig: ScoringConfig
}

/**
 * Events emitted by ScoringSettings component
 */
export interface ScoringSettingsEmits {
  'points-updated': (questionId: string, points: number) => void
  'passing-score-changed': (threshold: number) => void
  'scoring-config-updated': (config: ScoringConfig) => void
}

/**
 * Props for SectionManager component
 */
export interface SectionManagerProps {
  sections: Section[]
  questions: QuizQuestion[]
}

/**
 * Events emitted by SectionManager component
 */
export interface SectionManagerEmits {
  'section-added': (section: Section) => void
  'section-updated': (sectionId: string, updates: Partial<Section>) => void
  'section-deleted': (sectionId: string) => void
  'questions-reordered': (newOrder: QuizQuestion[]) => void
}

/**
 * Validation result for quiz builder operations
 */
export interface ValidationResult {
  isValid: boolean
  errors: string[]
  warnings: string[]
}

/**
 * API response structure for questions endpoint
 */
export interface QuestionsApiResponse {
  success: boolean
  data: {
    data: QuizQuestion[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  message?: string
}

/**
 * API response structure for metadata endpoints
 */
export interface MetadataApiResponse<T> {
  success: boolean
  data: T[]
  message?: string
}

/**
 * Local storage keys for filter persistence
 */
export const STORAGE_KEYS = {
  FILTER_STATE: 'quiz-builder-filters',
  BULK_STATE: 'quiz-builder-bulk-state',
  UI_PREFERENCES: 'quiz-builder-ui-prefs'
} as const

/**
 * Default values for various configurations
 */
export const DEFAULTS = {
  SCORING: {
    EASY_POINTS: 1,
    MEDIUM_POINTS: 2,
    HARD_POINTS: 3,
    PASSING_THRESHOLD: 70,
    THRESHOLD_IS_PERCENTAGE: true
  },
  TIMING: {
    ESTIMATED_MINUTES_PER_QUESTION: 1.5
  },
  PAGINATION: {
    QUESTIONS_PER_PAGE: 20
  }
} as const