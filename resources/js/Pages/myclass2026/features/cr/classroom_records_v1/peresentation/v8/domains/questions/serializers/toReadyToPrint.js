/**
 * Serialize v8 Canonical → Format C (ReadyToPrint / Exam Builder)
 */

import { QUESTION_TYPES } from '../schema.js'

const typeMap = {
  [QUESTION_TYPES.MULTIPLE_CHOICE]: 'mcq',
  [QUESTION_TYPES.MULTIPLE_SELECT]: 'mcq', // Exam builder may not support multi-select
  [QUESTION_TYPES.BOOLEAN]: 'boolean',
  [QUESTION_TYPES.SHORT_ANSWER]: 'short_answer',
  [QUESTION_TYPES.LONG_ANSWER]: 'long_answer',
  [QUESTION_TYPES.FILL_IN_BLANK]: 'fill_blank',
  [QUESTION_TYPES.MATCHING]: 'matching',
  [QUESTION_TYPES.ORDERING]: 'ordering',
}

/**
 * Serialize a v8 question to ReadyToPrint (Format C)
 * @param {Object} question — v8 canonical question
 * @returns {Object} ReadyToPrint format question
 */
export function toReadyToPrint(question) {
  if (!question) return null

  const v8Type = question.type || QUESTION_TYPES.MULTIPLE_CHOICE
  const content = question.content || {}
  const options = content.options || []
  const correctIndex = options.findIndex(opt => opt.is_correct)

  const output = {
    type: typeMap[v8Type] || v8Type,
    marks: question.marks || 1,
    content: {
      prompt: content.prompt || '',
      options: options.map(opt => opt.text || ''),
      correct_option_index: correctIndex >= 0 ? correctIndex : 0,
    },
  }

  if (content.explanation) {
    output.content.explanation = content.explanation
  }

  if (content.hints && content.hints.length > 0) {
    output.content.hints = content.hints
  }

  if (question.meta?.difficulty) {
    output.content.difficulty = question.meta.difficulty
  }

  if (question.meta?.tags && question.meta.tags.length > 0) {
    output.content.tags = question.meta.tags
  }

  // Response, evaluation, layout passthrough
  if (question.response && Object.keys(question.response).length > 0) {
    output.response = question.response
  }

  if (question.evaluation && Object.keys(question.evaluation).length > 0) {
    output.evaluation = question.evaluation
  }

  if (question.layout && Object.keys(question.layout).length > 0) {
    output.layout = question.layout
  }

  return output
}
