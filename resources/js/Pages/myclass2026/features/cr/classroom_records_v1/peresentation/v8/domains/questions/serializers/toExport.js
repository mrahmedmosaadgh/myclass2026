/**
 * Serialize v8 Canonical → Clean Export JSON
 * Minimal, portable format for sharing/backup.
 */

/**
 * Serialize a v8 question to clean export format
 * @param {Object} question — v8 canonical question
 * @param {Object} options
 * @param {boolean} [options.stripRuntime=true] — Remove runtime fields (response, etc.)
 * @param {boolean} [options.pretty=false] — Return pretty JSON string instead of object
 * @returns {Object|string} Export object or JSON string
 */
export function toExport(question, { stripRuntime = true, pretty = false } = {}) {
  if (!question) return null

  const content = question.content || {}
  const meta = question.meta || {}

  // Build clean export object
  const exportObj = {
    schema_version: question.schema_version || 1,
    type: question.type,
    marks: question.marks || 1,
    content: {
      prompt: content.prompt || '',
    },
    meta: {
      difficulty: meta.difficulty || 2,
      bloom_level: meta.bloom_level || 1,
      estimated_time_sec: meta.estimated_time_sec || 60,
      source: meta.source || 'teacher',
      tags: meta.tags || [],
    },
  }

  // Options
  if (content.options && content.options.length > 0) {
    exportObj.content.options = content.options.map(opt => ({
      id: opt.id,
      text: opt.text || '',
      is_correct: Boolean(opt.is_correct),
      ...(opt.rationale && { rationale: opt.rationale }),
    }))
  }

  // Optional content fields
  if (content.explanation) exportObj.content.explanation = content.explanation
  if (content.hints && content.hints.length > 0) exportObj.content.hints = content.hints
  if (content.media && content.media.length > 0) exportObj.content.media = content.media
  if (content.stimulus) exportObj.content.stimulus = content.stimulus

  // Modern assessment meta fields
  if (meta.cognitive_demand) exportObj.meta.cognitive_demand = meta.cognitive_demand
  if (meta.assessment_mode) exportObj.meta.assessment_mode = meta.assessment_mode
  if (meta.discipline_thinking) exportObj.meta.discipline_thinking = meta.discipline_thinking
  if (meta.authenticity) exportObj.meta.authenticity = meta.authenticity
  if (meta.transfer_requirement) exportObj.meta.transfer_requirement = meta.transfer_requirement
  if (meta.distractor_design) exportObj.meta.distractor_design = meta.distractor_design

  // Evaluation settings (keep unless stripping runtime)
  if (!stripRuntime && question.evaluation) {
    exportObj.evaluation = question.evaluation
  }

  // Layout settings (keep unless stripping runtime)
  if (!stripRuntime && question.layout) {
    exportObj.layout = question.layout
  }

  if (pretty) {
    return JSON.stringify(exportObj, null, 2)
  }

  return exportObj
}

/**
 * Serialize multiple questions to an export array
 * @param {Array} questions — Array of v8 questions
 * @param {Object} options
 * @returns {Object|string}
 */
export function toExportArray(questions, options = {}) {
  const array = questions.map(q => toExport(q, { ...options, pretty: false })).filter(Boolean)

  if (options.pretty) {
    return JSON.stringify(array, null, 2)
  }

  return array
}
