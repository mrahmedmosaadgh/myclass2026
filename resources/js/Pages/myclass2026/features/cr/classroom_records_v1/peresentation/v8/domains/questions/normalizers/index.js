/**
 * Question Normalizers - Public API
 * Main entry point for normalizing any incoming question format to v8 canonical.
 */

import { detectFormat, FORMATS, canNormalize } from '../utils/detectFormat.js'
import { fromQuizEngine } from './fromQuizEngine.js'
import { fromOldLesson } from './fromOldLesson.js'
import { fromReadyToPrint } from './fromReadyToPrint.js'
import { fromAI } from './fromAI.js'

/**
 * Normalize a raw question to v8 canonical format.
 * Auto-detects the source format and dispatches to the correct normalizer.
 *
 * @param {Object|Array} questionOrArray — Single question or array of questions
 * @param {Object} options
 * @param {string} [options.forceFormat] — Force a specific format (skip auto-detect)
 * @returns {Object|Array} Normalized v8 question(s)
 */
export function normalize(questionOrArray, { forceFormat } = {}) {
  // Handle arrays
  if (Array.isArray(questionOrArray)) {
    return questionOrArray.map(q => normalize(q, { forceFormat })).filter(Boolean)
  }

  const question = questionOrArray
  if (!question || typeof question !== 'object') {
    console.warn('[normalize] Invalid question object:', question)
    return null
  }

  // Already v8
  if (!forceFormat && question.schema_version !== undefined) {
    return normalizeV8(question)
  }

  // Auto-detect or use forced format
  const format = forceFormat || detectFormat(question)

  switch (format) {
    case FORMATS.V8:
      return normalizeV8(question)
    case FORMATS.QUIZ_ENGINE:
      return fromQuizEngine(question)
    case FORMATS.OLD_LESSON:
      return fromOldLesson(question)
    case FORMATS.READY_TO_PRINT:
      return fromReadyToPrint(question)
    case FORMATS.AI_MINIMAL:
      return fromAI(question)
    default:
      console.warn(`[normalize] Unknown format for question:`, question)
      return null
  }
}

/**
 * Normalize a v8 question (ensure schema_version and fill defaults)
 * @param {Object} question
 * @returns {Object}
 */
function normalizeV8(question) {
  // If already current version, return as-is with minor cleanup
  if (question.schema_version >= 1) {
    return {
      ...question,
      // Ensure required fields exist
      content: {
        prompt: question.content?.prompt || '',
        options: question.content?.options || [],
        ...(question.content?.explanation && { explanation: question.content.explanation }),
        ...(question.content?.hints && { hints: question.content.hints }),
        ...(question.content?.media && { media: question.content.media }),
        ...(question.content?.stimulus && { stimulus: question.content.stimulus }),
      },
    }
  }

  // Future: handle schema_version < current
  return question
}

/**
 * Check if a question can be normalized
 * @param {Object} question
 * @returns {boolean}
 */
export function isNormalizable(question) {
  return canNormalize(detectFormat(question))
}

/**
 * Get detected format name for debugging
 * @param {Object} question
 * @returns {string}
 */
export function getFormatName(question) {
  return detectFormat(question)
}

// Re-export individual normalizers for direct use
export { fromQuizEngine, fromOldLesson, fromReadyToPrint, fromAI }
