import { ref, computed, Ref } from 'vue'

export interface ValidationError {
  field: string
  message: string
}

export interface QuestionFormData {
  question_type_id: number | null
  question_text: string
  grade_level_id: number | null
  subject_id: number | null
  topic_id: number | null
  bloom_level: number | null
  difficulty_level: number | null
  estimated_time_sec: number | null
  status: string
  hints: string[]
  explanation: {
    text: string
    revealed_after_attempt: boolean
  } | null
  options: Array<{
    id?: number
    option_key: string
    option_text: string
    is_correct: boolean
    distractor_strength: number | null
    order_index: number
  }>
}

export interface QuestionType {
  id: number
  slug: string
  name: string
  has_options: boolean
  supports_hints: boolean
  supports_explanation: boolean
}

/**
 * Composable for client-side question form validation
 */
export function useQuestionValidation(questionType: Ref<QuestionType | null>) {
  const errors = ref<ValidationError[]>([])

  /**
   * Validate question text
   */
  const validateQuestionText = (text: string): string | null => {
    if (!text || text.trim().length === 0) {
      return 'Question text is required.'
    }
    if (text.length < 10) {
      return 'Question text must be at least 10 characters.'
    }
    if (text.length > 5000) {
      return 'Question text cannot exceed 5000 characters.'
    }
    return null
  }

  /**
   * Validate required field
   */
  const validateRequired = (value: any, fieldName: string): string | null => {
    if (value === null || value === undefined || value === '') {
      return `${fieldName} is required.`
    }
    return null
  }

  /**
   * Validate Bloom level
   */
  const validateBloomLevel = (level: number | null): string | null => {
    if (level === null) return null
    if (level < 1 || level > 6) {
      return 'Bloom level must be between 1 and 6.'
    }
    return null
  }

  /**
   * Validate difficulty level
   */
  const validateDifficultyLevel = (level: number | null): string | null => {
    if (level === null) return null
    if (level < 1 || level > 5) {
      return 'Difficulty level must be between 1 and 5.'
    }
    return null
  }

  /**
   * Validate estimated time
   */
  const validateEstimatedTime = (seconds: number | null): string | null => {
    if (seconds === null) return null
    if (seconds < 1) {
      return 'Estimated time must be at least 1 second.'
    }
    if (seconds > 3600) {
      return 'Estimated time cannot exceed 1 hour (3600 seconds).'
    }
    return null
  }

  /**
   * Validate hints
   */
  const validateHints = (hints: string[]): string | null => {
    if (!questionType.value?.supports_hints && hints.length > 0) {
      return 'This question type does not support hints.'
    }
    if (hints.length > 5) {
      return 'You can provide a maximum of 5 hints.'
    }
    for (let i = 0; i < hints.length; i++) {
      const hint = hints[i]
      if (hint.length < 5) {
        return `Hint ${i + 1} must be at least 5 characters.`
      }
      if (hint.length > 1000) {
        return `Hint ${i + 1} cannot exceed 1000 characters.`
      }
    }
    return null
  }

  /**
   * Validate explanation
   */
  const validateExplanation = (explanation: { text: string } | null): string | null => {
    if (!explanation || !explanation.text) return null
    
    if (!questionType.value?.supports_explanation) {
      return 'This question type does not support explanations.'
    }
    if (explanation.text.length < 10) {
      return 'Explanation must be at least 10 characters.'
    }
    if (explanation.text.length > 5000) {
      return 'Explanation cannot exceed 5000 characters.'
    }
    return null
  }

  /**
   * Validate options
   */
  const validateOptions = (options: QuestionFormData['options']): string | null => {
    if (!questionType.value) return null

    // Check if question type requires options
    if (questionType.value.has_options && options.length === 0) {
      return 'This question type requires options.'
    }

    // Check if question type doesn't support options
    if (!questionType.value.has_options && options.length > 0) {
      return 'This question type does not support options.'
    }

    // Validate minimum options
    if (options.length > 0 && options.length < 2) {
      return 'At least 2 options are required.'
    }

    // Validate maximum options
    if (options.length > 10) {
      return 'Maximum 10 options allowed.'
    }

    // Validate at least one correct option
    const hasCorrectOption = options.some(opt => opt.is_correct)
    if (options.length > 0 && !hasCorrectOption) {
      return 'At least one option must be marked as correct.'
    }

    // Validate unique option keys
    const optionKeys = options.map(opt => opt.option_key)
    const uniqueKeys = new Set(optionKeys)
    if (optionKeys.length !== uniqueKeys.size) {
      return 'Option keys must be unique.'
    }

    // Validate each option
    for (let i = 0; i < options.length; i++) {
      const option = options[i]
      
      if (!option.option_key || !/^[A-Z]$/.test(option.option_key)) {
        return `Option ${i + 1}: Key must be a single uppercase letter (A-Z).`
      }
      
      if (!option.option_text || option.option_text.trim().length === 0) {
        return `Option ${i + 1}: Text is required.`
      }
      
      if (option.option_text.length > 1000) {
        return `Option ${i + 1}: Text cannot exceed 1000 characters.`
      }
      
      if (option.distractor_strength !== null && 
          (option.distractor_strength < 0 || option.distractor_strength > 1)) {
        return `Option ${i + 1}: Distractor strength must be between 0 and 1.`
      }
    }

    return null
  }

  /**
   * Validate entire form
   */
  const validateForm = (formData: QuestionFormData): boolean => {
    errors.value = []

    // Validate question type
    const questionTypeError = validateRequired(formData.question_type_id, 'Question type')
    if (questionTypeError) {
      errors.value.push({ field: 'question_type_id', message: questionTypeError })
    }

    // Validate question text
    const questionTextError = validateQuestionText(formData.question_text)
    if (questionTextError) {
      errors.value.push({ field: 'question_text', message: questionTextError })
    }

    // Validate grade level
    const gradeLevelError = validateRequired(formData.grade_level_id, 'Grade level')
    if (gradeLevelError) {
      errors.value.push({ field: 'grade_level_id', message: gradeLevelError })
    }

    // Validate subject
    const subjectError = validateRequired(formData.subject_id, 'Subject')
    if (subjectError) {
      errors.value.push({ field: 'subject_id', message: subjectError })
    }

    // Validate status
    const statusError = validateRequired(formData.status, 'Status')
    if (statusError) {
      errors.value.push({ field: 'status', message: statusError })
    }

    // Validate Bloom level
    const bloomLevelError = validateBloomLevel(formData.bloom_level)
    if (bloomLevelError) {
      errors.value.push({ field: 'bloom_level', message: bloomLevelError })
    }

    // Validate difficulty level
    const difficultyLevelError = validateDifficultyLevel(formData.difficulty_level)
    if (difficultyLevelError) {
      errors.value.push({ field: 'difficulty_level', message: difficultyLevelError })
    }

    // Validate estimated time
    const estimatedTimeError = validateEstimatedTime(formData.estimated_time_sec)
    if (estimatedTimeError) {
      errors.value.push({ field: 'estimated_time_sec', message: estimatedTimeError })
    }

    // Validate hints
    const hintsError = validateHints(formData.hints)
    if (hintsError) {
      errors.value.push({ field: 'hints', message: hintsError })
    }

    // Validate explanation
    const explanationError = validateExplanation(formData.explanation)
    if (explanationError) {
      errors.value.push({ field: 'explanation', message: explanationError })
    }

    // Validate options
    const optionsError = validateOptions(formData.options)
    if (optionsError) {
      errors.value.push({ field: 'options', message: optionsError })
    }

    return errors.value.length === 0
  }

  /**
   * Get error message for a specific field
   */
  const getFieldError = (fieldName: string): string | null => {
    const error = errors.value.find(e => e.field === fieldName)
    return error ? error.message : null
  }

  /**
   * Check if a field has an error
   */
  const hasFieldError = (fieldName: string): boolean => {
    return errors.value.some(e => e.field === fieldName)
  }

  /**
   * Clear all errors
   */
  const clearErrors = () => {
    errors.value = []
  }

  /**
   * Clear error for a specific field
   */
  const clearFieldError = (fieldName: string) => {
    errors.value = errors.value.filter(e => e.field !== fieldName)
  }

  /**
   * Set errors from server response
   */
  const setServerErrors = (serverErrors: Record<string, string[]>) => {
    errors.value = []
    Object.entries(serverErrors).forEach(([field, messages]) => {
      messages.forEach(message => {
        errors.value.push({ field, message })
      })
    })
  }

  return {
    errors: computed(() => errors.value),
    validateForm,
    validateQuestionText,
    validateRequired,
    validateBloomLevel,
    validateDifficultyLevel,
    validateEstimatedTime,
    validateHints,
    validateExplanation,
    validateOptions,
    getFieldError,
    hasFieldError,
    clearErrors,
    clearFieldError,
    setServerErrors,
  }
}
