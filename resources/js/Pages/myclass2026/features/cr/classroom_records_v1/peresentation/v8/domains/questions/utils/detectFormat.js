/**
 * Format Detection Utility
 * Automatically detects which format a question is in for normalization.
 */

export const FORMATS = Object.freeze({
  V8: 'v8',
  QUIZ_ENGINE: 'quiz_engine',     // Format A - enterprise quiz engine
  OLD_LESSON: 'old_lesson',       // Format B - old lesson presentation
  READY_TO_PRINT: 'ready_to_print', // Format C - exam builder
  AI_MINIMAL: 'ai_minimal',       // AI-generated minimal format
  UNKNOWN: 'unknown',
})

/**
 * Detect the format of a raw question object
 * @param {Object} question — Raw question data
 * @returns {string} Format constant from FORMATS
 */
export function detectFormat(question) {
  if (!question || typeof question !== 'object') {
    return FORMATS.UNKNOWN
  }

  // V8: Has schema_version and structured content/options
  if (question.schema_version !== undefined) {
    return FORMATS.V8
  }

  // Format A (Quiz Engine): Has questionType, question, answerOptions with isCorrect
  if (
    question.questionType !== undefined &&
    question.question !== undefined &&
    Array.isArray(question.answerOptions) &&
    question.answerOptions.length > 0 &&
    typeof question.answerOptions[0] === 'object' &&
    'isCorrect' in question.answerOptions[0]
  ) {
    return FORMATS.QUIZ_ENGINE
  }

  // Format B (Old Lesson): Has questionNumber, question, options array of strings or objects with text/isCorrect
  if (
    question.questionNumber !== undefined &&
    question.question !== undefined &&
    Array.isArray(question.options) &&
    question.options.length > 0
  ) {
    // Check if options are strings or objects with text
    const firstOpt = question.options[0]
    if (typeof firstOpt === 'string' || (typeof firstOpt === 'object' && firstOpt.text !== undefined)) {
      return FORMATS.OLD_LESSON
    }
  }

  // Format C (ReadyToPrint): Has type, marks, content with prompt/options/correct_option_index
  if (
    question.type !== undefined &&
    question.content !== undefined &&
    typeof question.content === 'object' &&
    question.content.prompt !== undefined &&
    Array.isArray(question.content.options) &&
    question.content.correct_option_index !== undefined
  ) {
    return FORMATS.READY_TO_PRINT
  }

  // Format C variant (examReadyToPrintStore style)
  if (
    question.type !== undefined &&
    question.marks !== undefined &&
    question.content !== undefined &&
    typeof question.content === 'object' &&
    (question.content.prompt !== undefined || question.content.question !== undefined)
  ) {
    return FORMATS.READY_TO_PRINT
  }

  // AI Minimal: Has type, prompt, options array with text/correct or is_correct
  if (
    question.type !== undefined &&
    question.prompt !== undefined &&
    Array.isArray(question.options) &&
    question.options.length > 0 &&
    typeof question.options[0] === 'object' &&
    (question.options[0].correct !== undefined || question.options[0].is_correct !== undefined)
  ) {
    return FORMATS.AI_MINIMAL
  }

  // AI Minimal variant: flat structure with options array of strings + correct_index
  if (
    question.prompt !== undefined &&
    Array.isArray(question.options) &&
    typeof question.correct_index !== undefined
  ) {
    return FORMATS.AI_MINIMAL
  }

  return FORMATS.UNKNOWN
}

/**
 * Check if a format can be normalized to v8
 * @param {string} format — Format from detectFormat()
 * @returns {boolean}
 */
export function canNormalize(format) {
  return [
    FORMATS.QUIZ_ENGINE,
    FORMATS.OLD_LESSON,
    FORMATS.READY_TO_PRINT,
    FORMATS.AI_MINIMAL,
  ].includes(format)
}
