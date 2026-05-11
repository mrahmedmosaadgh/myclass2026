/**
 * Question Factory
 * Creates standardized v8 canonical question objects with defaults and validation.
 */

import {
  CURRENT_SCHEMA_VERSION,
  QUESTION_TYPES,
  COGNITIVE_DEMAND,
  ASSESSMENT_MODE,
  EVALUATION_MODE,
  QUESTION_SOURCE,
  DEFAULTS,
} from '../schema.js'
import { generateUUID } from '../utils/generateId.js'

/**
 * Create a v8 canonical question object
 * @param {Object} params
 * @param {string} [params.id] — Question UUID (auto-generated if not provided)
 * @param {string} params.type — Question type (see QUESTION_TYPES)
 * @param {number} [params.marks=1] — Points/mark value
 * @param {Object} params.content — Question content
 * @param {string} [params.content.prompt] — Question text
 * @param {Array} [params.content.media] — Media array for prompt
 * @param {Array} [params.content.options] — Answer options (for MCQ types)
 * @param {string} [params.content.explanation] — Correct answer explanation
 * @param {Array} [params.content.hints] — Array of hint strings
 * @param {Object} [params.content.stimulus] — Stimulus object (for advanced types)
 * @param {Object} [params.meta] — Metadata
 * @param {Object} [params.response] — Student response data
 * @param {Object} [params.evaluation] — Evaluation settings
 * @param {Object} [params.layout] — Layout/display settings
 * @returns {Object} v8 canonical question object
 */
export function createQuestion({
  id = null,
  type = QUESTION_TYPES.MULTIPLE_CHOICE,
  marks = DEFAULTS.marks,
  content = {},
  meta = {},
  response = {},
  evaluation = {},
  layout = {},
} = {}) {
  if (!type) {
    throw new Error('Question type is required')
  }

  const question = {
    schema_version: CURRENT_SCHEMA_VERSION,
    id: id || generateUUID('q'),
    type,
    marks: Number(marks) || DEFAULTS.marks,
  }

  // Content
  question.content = buildContent(type, content)

  // Meta with defaults
  question.meta = buildMeta(type, meta)

  // Response (empty by default)
  if (Object.keys(response).length > 0) {
    question.response = response
  }

  // Evaluation settings
  question.evaluation = buildEvaluation(type, evaluation)

  // Layout settings
  if (Object.keys(layout).length > 0) {
    question.layout = layout
  }

  return question
}

// ============================================================================
// BUILDERS
// ============================================================================

function buildContent(type, content) {
  const result = {
    prompt: content.prompt || '',
  }

  // Media on prompt level
  if (Array.isArray(content.media) && content.media.length > 0) {
    result.media = content.media
  }

  // Options (for types that use them)
  if (content.options && Array.isArray(content.options)) {
    result.options = content.options
  }

  // Explanation
  if (content.explanation) {
    result.explanation = String(content.explanation).trim()
  }

  // Hints
  if (Array.isArray(content.hints) && content.hints.length > 0) {
    result.hints = content.hints.map(h => String(h)).filter(Boolean)
  }

  // Stimulus (for advanced types)
  if (content.stimulus && typeof content.stimulus === 'object') {
    result.stimulus = content.stimulus
  }

  // For fill-in-the-blank: extract blank positions
  if (type === QUESTION_TYPES.FILL_IN_BLANK && content.blanks) {
    result.blanks = content.blanks
  }

  // For matching: pairs
  if (type === QUESTION_TYPES.MATCHING && content.pairs) {
    result.pairs = content.pairs
  }

  // For ordering: items
  if (type === QUESTION_TYPES.ORDERING && content.items) {
    result.items = content.items
  }

  // For justification_required: justification prompt
  if (type === QUESTION_TYPES.JUSTIFICATION_REQUIRED && content.justification_prompt) {
    result.justification_prompt = String(content.justification_prompt)
  }

  return result
}

function buildMeta(type, meta) {
  const result = {
    difficulty: Number(meta.difficulty) || DEFAULTS.difficulty,
    bloom_level: Number(meta.bloom_level) || DEFAULTS.bloom_level,
    estimated_time_sec: Number(meta.estimated_time_sec) || DEFAULTS.estimated_time_sec,
    source: meta.source || DEFAULTS.source,
    tags: Array.isArray(meta.tags) ? meta.tags : [],
  }

  // Modern assessment fields (optional)
  if (meta.cognitive_demand && Object.values(COGNITIVE_DEMAND).includes(meta.cognitive_demand)) {
    result.cognitive_demand = meta.cognitive_demand
  } else {
    result.cognitive_demand = DEFAULTS.cognitive_demand
  }

  if (meta.assessment_mode && Object.values(ASSESSMENT_MODE).includes(meta.assessment_mode)) {
    result.assessment_mode = meta.assessment_mode
  } else {
    result.assessment_mode = DEFAULTS.assessment_mode
  }

  // Discipline thinking
  if (meta.discipline_thinking && typeof meta.discipline_thinking === 'object') {
    result.discipline_thinking = meta.discipline_thinking
  }

  // Authenticity
  if (meta.authenticity && typeof meta.authenticity === 'object') {
    result.authenticity = meta.authenticity
  }

  // Transfer
  if (meta.transfer_requirement === true) {
    result.transfer_requirement = true
  }

  // Distractor design
  if (meta.distractor_design && typeof meta.distractor_design === 'object') {
    result.distractor_design = meta.distractor_design
  }

  return result
}

function buildEvaluation(type, evaluation) {
  const result = {
    mode: evaluation.mode || EVALUATION_MODE.AUTO,
  }

  if (evaluation.partial_credit !== undefined) {
    result.partial_credit = Boolean(evaluation.partial_credit)
  }

  if (evaluation.case_sensitive !== undefined) {
    result.case_sensitive = Boolean(evaluation.case_sensitive)
  }

  if (evaluation.tolerance !== undefined) {
    result.tolerance = Number(evaluation.tolerance)
  }

  if (evaluation.keywords && Array.isArray(evaluation.keywords)) {
    result.keywords = evaluation.keywords
  }

  return result
}
