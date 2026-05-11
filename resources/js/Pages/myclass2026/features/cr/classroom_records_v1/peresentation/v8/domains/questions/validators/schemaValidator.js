/**
 * Schema Validator
 * Validates that a question conforms to the v8 canonical schema structure.
 */

import { CURRENT_SCHEMA_VERSION, isValidQuestionType, hasOptions, requiresTextInput } from '../schema.js'

/**
 * Validate a question against the v8 schema structure.
 * @param {Object} question — v8 question to validate
 * @returns {{ valid: boolean, errors: Array<{field: string, message: string}> }}
 */
export function validateSchema(question) {
  const errors = []

  if (!question || typeof question !== 'object') {
    return { valid: false, errors: [{ field: 'root', message: 'Question must be an object' }] }
  }

  // Required top-level fields
  if (!question.schema_version) {
    errors.push({ field: 'schema_version', message: 'schema_version is required' })
  } else if (question.schema_version !== CURRENT_SCHEMA_VERSION) {
    errors.push({
      field: 'schema_version',
      message: `Expected schema_version ${CURRENT_SCHEMA_VERSION}, got ${question.schema_version}`,
    })
  }

  if (!question.id) {
    errors.push({ field: 'id', message: 'id is required' })
  }

  if (!question.type) {
    errors.push({ field: 'type', message: 'type is required' })
  } else if (!isValidQuestionType(question.type)) {
    errors.push({ field: 'type', message: `Unknown question type: ${question.type}` })
  }

  if (question.marks === undefined || question.marks === null) {
    errors.push({ field: 'marks', message: 'marks is required' })
  } else if (!Number.isFinite(question.marks) || question.marks < 0) {
    errors.push({ field: 'marks', message: 'marks must be a non-negative number' })
  }

  // Content validation
  const contentErrors = validateContentStructure(question.content, question.type)
  errors.push(...contentErrors)

  // Meta validation (optional but if present must be valid)
  if (question.meta) {
    const metaErrors = validateMeta(question.meta)
    errors.push(...metaErrors)
  }

  return {
    valid: errors.length === 0,
    errors,
  }
}

function validateContentStructure(content, type) {
  const errors = []

  if (!content || typeof content !== 'object') {
    errors.push({ field: 'content', message: 'content object is required' })
    return errors
  }

  // Prompt
  if (content.prompt === undefined || content.prompt === null) {
    errors.push({ field: 'content.prompt', message: 'content.prompt is required' })
  } else if (typeof content.prompt !== 'string') {
    errors.push({ field: 'content.prompt', message: 'content.prompt must be a string' })
  } else if (content.prompt.trim().length === 0) {
    errors.push({ field: 'content.prompt', message: 'content.prompt cannot be empty' })
  }

  // Options validation for question types that require them
  if (hasOptions(type)) {
    if (!Array.isArray(content.options)) {
      errors.push({ field: 'content.options', message: `content.options array is required for type '${type}'` })
    } else if (content.options.length < 2) {
      errors.push({ field: 'content.options', message: `At least 2 options required for type '${type}'` })
    } else {
      // Validate each option
      content.options.forEach((opt, i) => {
        if (!opt || typeof opt !== 'object') {
          errors.push({ field: `content.options[${i}]`, message: 'Each option must be an object' })
          return
        }
        if (!opt.id) {
          errors.push({ field: `content.options[${i}].id`, message: 'Option id is required' })
        }
        if (opt.text === undefined || opt.text === null) {
          errors.push({ field: `content.options[${i}].text`, message: 'Option text is required' })
        }
        if (opt.is_correct === undefined || opt.is_correct === null) {
          errors.push({ field: `content.options[${i}].is_correct`, message: 'Option is_correct is required' })
        }
      })
    }
  }

  // Media validation (if present)
  if (content.media) {
    if (!Array.isArray(content.media)) {
      errors.push({ field: 'content.media', message: 'content.media must be an array' })
    }
  }

  // Hints validation
  if (content.hints) {
    if (!Array.isArray(content.hints)) {
      errors.push({ field: 'content.hints', message: 'content.hints must be an array' })
    }
  }

  return errors
}

function validateMeta(meta) {
  const errors = []

  if (meta.difficulty !== undefined && (!Number.isInteger(meta.difficulty) || meta.difficulty < 1 || meta.difficulty > 5)) {
    errors.push({ field: 'meta.difficulty', message: 'meta.difficulty must be an integer from 1 to 5' })
  }

  if (meta.bloom_level !== undefined && (!Number.isInteger(meta.bloom_level) || meta.bloom_level < 1 || meta.bloom_level > 6)) {
    errors.push({ field: 'meta.bloom_level', message: 'meta.bloom_level must be an integer from 1 to 6' })
  }

  if (meta.estimated_time_sec !== undefined && (!Number.isInteger(meta.estimated_time_sec) || meta.estimated_time_sec < 0)) {
    errors.push({ field: 'meta.estimated_time_sec', message: 'meta.estimated_time_sec must be a non-negative integer' })
  }

  if (meta.source && typeof meta.source !== 'string') {
    errors.push({ field: 'meta.source', message: 'meta.source must be a string' })
  }

  if (meta.tags && !Array.isArray(meta.tags)) {
    errors.push({ field: 'meta.tags', message: 'meta.tags must be an array of strings' })
  }

  return errors
}
