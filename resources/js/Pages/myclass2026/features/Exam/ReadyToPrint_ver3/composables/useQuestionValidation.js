import { ref } from 'vue'

export function useQuestionValidation() {
  const questionErrors = ref([])

  /**
   * Detect errors in questions
   * @param {Array} questions - Array of questions to validate
   * @returns {Array} Array of error objects
   */
  function detectQuestionErrors(questions) {
    const errors = []

    if (!questions || !Array.isArray(questions)) {
      return errors
    }

    questions.forEach((q, index) => {
      const questionId = q.id || index + 1

      // Check for missing required fields
      if (!q.type) {
        errors.push({ questionId, message: 'Missing question type' })
      }

      if (!q.marks) {
        errors.push({ questionId, message: 'Missing marks' })
      }

      if (!q.content) {
        errors.push({ questionId, message: 'Missing content' })
      } else {
        if (!q.content.prompt) {
          errors.push({ questionId, message: 'Missing prompt' })
        }
      }

      // Check for version field
      if (!q.ver) {
        errors.push({ questionId, message: 'Missing version field (ver)' })
      }

      // Multiple choice specific validation
      if (q.type === 'multiple_choice') {
        if (!q.content.options || !Array.isArray(q.content.options)) {
          errors.push({ questionId, message: 'Missing or invalid options array' })
        } else {
          // Check for duplicate correct answers
          const correctAnswer = q.content.correct_option_index
          if (correctAnswer === undefined || correctAnswer === null) {
            errors.push({ questionId, message: 'Missing correct_option_index' })
          } else {
            if (correctAnswer < 0 || correctAnswer >= q.content.options.length) {
              errors.push({ questionId, message: `correct_option_index (${correctAnswer}) is out of range (0-${q.content.options.length - 1})` })
            }
          }

          // Check for duplicate options
          const options = q.content.options
          const seen = new Set()
          options.forEach((opt, optIndex) => {
            if (seen.has(opt)) {
              errors.push({ questionId, message: `Duplicate option at index ${optIndex}: "${opt}"` })
            }
            seen.add(opt)
          })
        }
      }

      // Check for LaTeX formatting issues (basic check)
      if (q.content && q.content.prompt) {
        const prompt = q.content.prompt
        // Check for unclosed math expressions
        const mathCount = (prompt.match(/\$/g) || []).length
        if (mathCount % 2 !== 0) {
          errors.push({ questionId, message: 'Unclosed LaTeX math expression (odd number of $ signs)' })
        }
      }
    })

    return errors
  }

  /**
   * Check if questions have valid structure
   * @param {Array} questions - Array of questions to validate
   * @returns {boolean} True if all questions are valid
   */
  function areQuestionsValid(questions) {
    const errors = detectQuestionErrors(questions)
    return errors.length === 0
  }

  /**
   * Get validation statistics
   * @param {Array} questions - Array of questions to validate
   * @returns {Object} Validation statistics
   */
  function getValidationStats(questions) {
    const errors = detectQuestionErrors(questions)
    const total = questions?.length || 0

    // Count errors by type
    const errorTypes = {}
    errors.forEach(error => {
      const type = error.message.split(':')[0] || error.message
      errorTypes[type] = (errorTypes[type] || 0) + 1
    })

    return {
      total,
      valid: total - errors.length,
      invalid: errors.length,
      errorTypes,
      errors
    }
  }

  /**
   * Fix common errors in questions
   * @param {Array} questions - Array of questions to fix
   * @returns {Array} Fixed questions
   */
  function fixCommonErrors(questions) {
    if (!questions || !Array.isArray(questions)) {
      return questions
    }

    return questions.map((q, index) => {
      const fixed = { ...q }

      // Ensure id exists
      if (!fixed.id) {
        fixed.id = index + 1
      }

      // Ensure version field
      if (!fixed.ver) {
        fixed.ver = 3
      }

      // Ensure marks is a number
      if (typeof fixed.marks !== 'number') {
        fixed.marks = Number(fixed.marks) || 1
      }

      // Fix multiple choice issues
      if (fixed.type === 'multiple_choice' && fixed.content) {
        if (!fixed.content.options || !Array.isArray(fixed.content.options)) {
          fixed.content.options = []
        }

        // Ensure correct_option_index is valid
        if (fixed.content.correct_option_index === undefined || fixed.content.correct_option_index === null) {
          fixed.content.correct_option_index = 0
        }

        if (fixed.content.correct_option_index < 0) {
          fixed.content.correct_option_index = 0
        }

        if (fixed.content.correct_option_index >= fixed.content.options.length) {
          fixed.content.correct_option_index = Math.max(0, fixed.content.options.length - 1)
        }
      }

      return fixed
    })
  }

  return {
    questionErrors,
    detectQuestionErrors,
    areQuestionsValid,
    getValidationStats,
    fixCommonErrors
  }
}
