/**
 * Question Serializers - Public API
 * Main entry point for serializing v8 canonical questions to various output formats.
 */

import { toQuizEngine } from './toQuizEngine.js'
import { toOldLesson } from './toOldLesson.js'
import { toReadyToPrint } from './toReadyToPrint.js'
import { toExport, toExportArray } from './toExport.js'

export const OUTPUT_FORMATS = Object.freeze({
  V8: 'v8',
  V8_EXPORT: 'v8_export',
  QUIZ_ENGINE: 'quiz_engine',
  OLD_LESSON: 'old_lesson',
  READY_TO_PRINT: 'ready_to_print',
})

/**
 * Serialize a v8 question to a specific output format.
 *
 * @param {Object|Array} questionOrArray — v8 canonical question(s)
 * @param {Object} options
 * @param {string} [options.format='v8_export'] — Target format from OUTPUT_FORMATS
 * @param {boolean} [options.pretty=false] — Return pretty JSON string (for export)
 * @param {boolean} [options.stripRuntime=true] — Remove runtime fields
 * @returns {Object|string} Serialized question(s)
 */
export function serialize(questionOrArray, { format = OUTPUT_FORMATS.V8_EXPORT, pretty = false, stripRuntime = true } = {}) {
  // Handle arrays
  if (Array.isArray(questionOrArray)) {
    if (format === OUTPUT_FORMATS.V8_EXPORT) {
      return toExportArray(questionOrArray, { pretty, stripRuntime })
    }
    return questionOrArray.map(q => serializeSingle(q, format)).filter(Boolean)
  }

  return serializeSingle(questionOrArray, format, { pretty, stripRuntime })
}

function serializeSingle(question, format, { pretty, stripRuntime }) {
  switch (format) {
    case OUTPUT_FORMATS.V8:
      // Return as-is (v8 is already canonical)
      return question

    case OUTPUT_FORMATS.V8_EXPORT:
      return toExport(question, { pretty, stripRuntime })

    case OUTPUT_FORMATS.QUIZ_ENGINE:
      return toQuizEngine(question)

    case OUTPUT_FORMATS.OLD_LESSON:
      return toOldLesson(question)

    case OUTPUT_FORMATS.READY_TO_PRINT:
      return toReadyToPrint(question)

    default:
      console.warn(`[serialize] Unknown format: ${format}`)
      return null
  }
}

// Re-export individual serializers for direct use
export { toQuizEngine, toOldLesson, toReadyToPrint, toExport, toExportArray }
