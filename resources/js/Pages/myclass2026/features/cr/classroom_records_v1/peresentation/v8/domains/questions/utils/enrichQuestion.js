/**
 * Question Enrichment Utility
 * Enriches AI-minimal or sparse questions to full v8 canonical format.
 */

import { CURRENT_SCHEMA_VERSION, DEFAULTS, QUESTION_TYPES } from '../schema.js'
import { generateUUID, generateShortId, generateLetterId } from './generateId.js'

/**
 * Enrich a minimal/question object to full v8 canonical format.
 * Adds missing fields, generates IDs, sets defaults.
 * @param {Object} question — Input question (any format)
 * @param {Object} options
 * @param {boolean} [options.autoType=true] — Auto-detect question type if missing
 * @returns {Object} Enriched v8 question (not yet fully validated)
 */
export function enrichQuestion(question, { autoType = true } = {}) {
  if (!question || typeof question !== 'object') {
    throw new Error('Cannot enrich: invalid question object')
  }

  const enriched = {
    schema_version: CURRENT_SCHEMA_VERSION,
    id: question.id || generateUUID('q'),
    type: question.type || (autoType ? inferType(question) : QUESTION_TYPES.MULTIPLE_CHOICE),
    marks: Number(question.marks) || DEFAULTS.marks,
  }

  // Content
  enriched.content = enrichContent(question)

  // Meta
  enriched.meta = enrichMeta(question)

  // Evaluation
  if (question.evaluation && typeof question.evaluation === 'object') {
    enriched.evaluation = question.evaluation
  }

  // Layout
  if (question.layout && typeof question.layout === 'object') {
    enriched.layout = question.layout
  }

  return enriched
}

// ============================================================================
// PRIVATE HELPERS
// ============================================================================

function enrichContent(question) {
  const content = {}

  // Prompt
  content.prompt = question.prompt || question.question || question.content?.prompt || ''

  // Options
  const rawOptions = question.options || question.answerOptions || question.content?.options || []
  if (rawOptions.length > 0) {
    content.options = enrichOptions(rawOptions)
  }

  // Explanation
  const explanation = question.explanation || question.rationale || question.content?.explanation || ''
  if (explanation) {
    content.explanation = String(explanation).trim()
  }

  // Hints
  const hints = question.hints || question.content?.hints || []
  if (Array.isArray(hints) && hints.length > 0) {
    content.hints = hints.map(h => String(h)).filter(Boolean)
  }

  // Media
  const media = question.media || question.content?.media || []
  if (Array.isArray(media) && media.length > 0) {
    content.media = media.map((m, i) => ({
      id: m.id || generateShortId(`media_${i}`),
      ...m,
    }))
  }

  // Stimulus (for advanced types)
  if (question.stimulus || question.content?.stimulus) {
    content.stimulus = question.stimulus || question.content?.stimulus
  }

  return content
}

function enrichOptions(rawOptions) {
  return rawOptions.map((opt, index) => {
    // Already a v8 option object
    if (opt.id && (opt.is_correct !== undefined || opt.isCorrect !== undefined)) {
      return {
        id: opt.id,
        text: opt.text || opt.label || '',
        is_correct: opt.is_correct !== undefined ? Boolean(opt.is_correct) : Boolean(opt.isCorrect),
        ...(opt.rationale && { rationale: opt.rationale }),
        ...(opt.media && { media: opt.media }),
      }
    }

    // String option (legacy format)
    if (typeof opt === 'string') {
      return {
        id: generateLetterId(index),
        text: opt,
        is_correct: false,
      }
    }

    // Object with text and correct flag (AI minimal)
    const text = opt.text || opt.label || ''
    const isCorrect = opt.correct || opt.isCorrect || opt.is_correct || false

    const enriched = {
      id: opt.id || generateLetterId(index),
      text: String(text),
      is_correct: Boolean(isCorrect),
    }

    if (opt.rationale) enriched.rationale = String(opt.rationale)
    if (opt.feedback) enriched.rationale = String(opt.feedback) // Legacy: feedback -> rationale
    if (opt.media) enriched.media = opt.media

    return enriched
  })
}

function enrichMeta(question) {
  const meta = {
    difficulty: Number(question.difficulty) || DEFAULTS.difficulty,
    bloom_level: Number(question.bloom_level) || DEFAULTS.bloom_level,
    estimated_time_sec: Number(question.estimated_time_sec) || DEFAULTS.estimated_time_sec,
    source: question.source || DEFAULTS.source,
    tags: Array.isArray(question.tags) ? question.tags : [],
    cognitive_demand: question.cognitive_demand || DEFAULTS.cognitive_demand,
    assessment_mode: question.assessment_mode || DEFAULTS.assessment_mode,
  }

  if (question.meta && typeof question.meta === 'object') {
    Object.assign(meta, question.meta)
  }

  return meta
}

function inferType(question) {
  // Check explicit type
  if (question.type) {
    const typeMap = {
      mcq: 'multiple_choice',
      single_choice: 'multiple_choice',
      multiple: 'multiple_select',
      multi_select: 'multiple_select',
      true_false: 'boolean',
      tf: 'boolean',
      text: 'short_answer',
      short_text: 'short_answer',
      essay: 'long_answer',
      long_text: 'long_answer',
      fill_blank: 'fill_in_blank',
      fill_in: 'fill_in_blank',
      match: 'matching',
      order: 'ordering',
      sequence: 'ordering',
    }
    return typeMap[question.type] || question.type
  }

  // Infer from options
  const options = question.options || question.answerOptions || question.content?.options || []
  if (options.length > 0) {
    // Check if multiple can be correct
    const correctCount = options.filter(o =>
      o.correct || o.isCorrect || o.is_correct
    ).length
    return correctCount > 1 ? 'multiple_select' : 'multiple_choice'
  }

  // Default
  return 'short_answer'
}
