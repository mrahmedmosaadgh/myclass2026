/**
 * Normalize Format C (ReadyToPrint / Exam Builder) → v8 Canonical
 *
 * Format C structure:
 * {
 *   type: string,
 *   marks: number,
 *   content: {
 *     prompt: string,
 *     options: string[] | [{ text: string }],
 *     correct_option_index: number
 *   },
 *   response?: {},
 *   evaluation?: {},
 *   layout?: {}
 * }
 *
 * Also handles the examReadyToPrintStore style:
 * {
 *   type: 'mcq',
 *   marks: 1,
 *   content: { prompt: '', options: ['A', 'B', 'C', 'D'], correct_option_index: 0 },
 *   response: {},
 *   evaluation: {},
 *   layout: {}
 * }
 */

import { QUESTION_TYPES } from '../schema.js'
import { generateUUID, generateLetterId } from '../utils/generateId.js'

/**
 * Normalize a ReadyToPrint (Format C) question to v8
 * @param {Object} question — ReadyToPrint format question
 * @returns {Object} v8 canonical question
 */
export function fromReadyToPrint(question) {
  if (!question) return null

  const content = question.content || {}
  const rawOptions = content.options || []
  const correctIndex = content.correct_option_index ?? -1

  const v8 = {
    schema_version: 1,
    id: generateUUID('q'),
    type: normalizeType(content.type || question.type || 'mcq'),
    marks: question.marks || 1,
    content: {
      prompt: content.prompt || content.question || '',
      options: normalizeOptions(rawOptions, correctIndex),
    },
    meta: {
      difficulty: content.difficulty || question.difficulty || 2,
      bloom_level: content.bloom_level || 1,
      estimated_time_sec: 60,
      source: 'teacher',
      tags: content.tags || question.tags || [],
      cognitive_demand: 'recall',
      assessment_mode: 'traditional',
    },
    evaluation: {
      mode: 'auto',
      ...(question.evaluation || {}),
    },
  }

  if (content.explanation || question.explanation) {
    v8.content.explanation = String(content.explanation || question.explanation)
  }

  if (content.hints && Array.isArray(content.hints)) {
    v8.content.hints = content.hints.map(h => String(h)).filter(Boolean)
  }

  if (question.layout && Object.keys(question.layout).length > 0) {
    v8.layout = question.layout
  }

  return v8
}

function normalizeType(type) {
  const typeMap = {
    mcq: QUESTION_TYPES.MULTIPLE_CHOICE,
    multiple_choice: QUESTION_TYPES.MULTIPLE_CHOICE,
    boolean: QUESTION_TYPES.BOOLEAN,
    true_false: QUESTION_TYPES.BOOLEAN,
    short_answer: QUESTION_TYPES.SHORT_ANSWER,
    long_answer: QUESTION_TYPES.LONG_ANSWER,
    fill_in_blank: QUESTION_TYPES.FILL_IN_BLANK,
    fill_blank: QUESTION_TYPES.FILL_IN_BLANK,
    matching: QUESTION_TYPES.MATCHING,
    ordering: QUESTION_TYPES.ORDERING,
    order: QUESTION_TYPES.ORDERING,
  }
  return typeMap[type] || type || QUESTION_TYPES.MULTIPLE_CHOICE
}

function normalizeOptions(rawOptions, correctIndex) {
  return rawOptions.map((opt, index) => {
    // String option
    if (typeof opt === 'string') {
      return {
        id: generateLetterId(index),
        text: opt,
        is_correct: index === correctIndex,
      }
    }

    // Object option
    return {
      id: opt.id || generateLetterId(index),
      text: opt.text || opt.label || '',
      is_correct: index === correctIndex,
      ...(opt.rationale && { rationale: String(opt.rationale) }),
    }
  })
}
