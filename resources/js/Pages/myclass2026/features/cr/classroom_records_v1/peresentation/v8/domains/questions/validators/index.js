/**
 * Question Validators - Public API
 * Three-level validation: schema structure → content logic → cross-question consistency.
 */

import { validateSchema } from './schemaValidator.js'
import { validateContent } from './contentValidator.js'

/**
 * Validate a v8 question through all three levels.
 *
 * @param {Object} question — v8 canonical question
 * @param {Object} options
 * @param {boolean} [options.schema=true] — Run schema validation
 * @param {boolean} [options.content=true] — Run content validation
 * @param {boolean} [options.strict=false] — Treat warnings as errors
 * @returns {{ valid: boolean, errors: Array, warnings: Array, info: Array }}
 */
export function validate(question, { schema = true, content = true, strict = false } = {}) {
  const errors = []
  const warnings = []
  const info = []

  // Level 1: Schema Structure
  if (schema) {
    const schemaResult = validateSchema(question)
    if (!schemaResult.valid) {
      errors.push(...schemaResult.errors.map(e => ({ level: 'schema', ...e })))
    }
  }

  // Level 2: Content Logic
  if (content && errors.length === 0) {
    const contentResult = validateContent(question)
    if (!contentResult.valid) {
      errors.push(...contentResult.warnings.filter(w => w.severity === 'error').map(w => ({
        level: 'content',
        field: w.field,
        message: w.message,
      })))
    }

    // Separate warnings and info
    contentResult.warnings.forEach(w => {
      const entry = { level: 'content', field: w.field, message: w.message }
      if (w.severity === 'warning') {
        warnings.push(entry)
      } else if (w.severity === 'info') {
        info.push(entry)
      }
    })
  }

  // In strict mode, warnings become errors
  if (strict) {
    errors.push(...warnings.map(w => ({ ...w, wasWarning: true })))
    warnings.length = 0
  }

  return {
    valid: errors.length === 0,
    errors,
    warnings,
    info,
  }
}

/**
 * Quick check: is the question valid?
 * @param {Object} question
 * @returns {boolean}
 */
export function isValid(question) {
  return validate(question).valid
}

/**
 * Validate an array of questions
 * @param {Array} questions
 * @param {Object} options
 * @returns {{ valid: boolean, results: Array }}
 */
export function validateAll(questions, options = {}) {
  const results = questions.map((q, i) => ({
    index: i,
    id: q?.id || `question_${i}`,
    ...validate(q, options),
  }))

  return {
    valid: results.every(r => r.valid),
    results,
  }
}

// Re-export individual validators for direct use
export { validateSchema, validateContent }
