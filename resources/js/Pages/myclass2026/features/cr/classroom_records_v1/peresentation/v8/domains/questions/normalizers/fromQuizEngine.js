/**
 * Normalize Format A (QuizEngine) → v8 Canonical
 *
 * Format A structure:
 * {
 *   questionType: string,
 *   question: string,
 *   answerOptions: [
 *     { text: string, isCorrect: boolean, rationale: string, feedback?: string }
 *   ],
 *   explanation?: string,
 *   hints?: string[],
 *   meta?: { difficulty?, bloomLevel?, tags? }
 * }
 */

import { QUESTION_TYPES } from '../schema.js'
import { generateUUID } from '../utils/generateId.js'

/**
 * Normalize a QuizEngine (Format A) question to v8
 * @param {Object} question — QuizEngine format question
 * @returns {Object} v8 canonical question
 */
export function fromQuizEngine(question) {
  if (!question) return null

  const v8 = {
    schema_version: 1,
    id: generateUUID('q'),
    type: normalizeType(question.questionType),
    marks: question.marks || 1,
    content: {
      prompt: question.question || '',
      options: normalizeOptions(question.answerOptions || []),
    },
    meta: {
      difficulty: question.meta?.difficulty || 2,
      bloom_level: question.meta?.bloomLevel || 1,
      estimated_time_sec: question.meta?.estimatedTimeSec || 60,
      source: 'teacher',
      tags: question.meta?.tags || [],
      cognitive_demand: 'recall',
      assessment_mode: 'traditional',
    },
    evaluation: { mode: 'auto' },
  }

  // Optional fields
  if (question.explanation) {
    v8.content.explanation = String(question.explanation)
  }

  if (question.hints && Array.isArray(question.hints)) {
    v8.content.hints = question.hints.map(h => String(h)).filter(Boolean)
  }

  if (question.rationale) {
    v8.content.explanation = String(question.rationale)
  }

  // Modern assessment detection
  if (question.stimulus || question.context || question.scenario) {
    v8.meta.assessment_mode = 'stimulus_based'
    v8.meta.cognitive_demand = 'analysis'
  }

  return v8
}

function normalizeType(type) {
  const typeMap = {
    'multiple_choice': QUESTION_TYPES.MULTIPLE_CHOICE,
    'multiple select': QUESTION_TYPES.MULTIPLE_SELECT,
    'true_false': QUESTION_TYPES.BOOLEAN,
    'short_answer': QUESTION_TYPES.SHORT_ANSWER,
    'essay': QUESTION_TYPES.LONG_ANSWER,
    'fill_in_the_blank': QUESTION_TYPES.FILL_IN_BLANK,
    'matching': QUESTION_TYPES.MATCHING,
    'ordering': QUESTION_TYPES.ORDERING,
  }
  return typeMap[type] || type || QUESTION_TYPES.MULTIPLE_CHOICE
}

function normalizeOptions(answerOptions) {
  return answerOptions.map((opt, index) => {
    const option = {
      id: String.fromCharCode(97 + index), // a, b, c, ...
      text: opt.text || opt.label || '',
      is_correct: Boolean(opt.isCorrect),
    }

    if (opt.rationale) option.rationale = String(opt.rationale)
    if (opt.feedback) option.rationale = String(opt.feedback)
    if (opt.media) option.media = opt.media

    return option
  })
}
