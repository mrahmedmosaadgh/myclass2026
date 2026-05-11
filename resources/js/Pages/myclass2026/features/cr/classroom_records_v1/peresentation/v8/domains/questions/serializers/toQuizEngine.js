/**
 * Serialize v8 Canonical → Format A (QuizEngine)
 */

import { QUESTION_TYPES } from '../schema.js'

const typeMap = {
  [QUESTION_TYPES.MULTIPLE_CHOICE]: 'multiple_choice',
  [QUESTION_TYPES.MULTIPLE_SELECT]: 'multiple select',
  [QUESTION_TYPES.BOOLEAN]: 'true_false',
  [QUESTION_TYPES.SHORT_ANSWER]: 'short_answer',
  [QUESTION_TYPES.LONG_ANSWER]: 'essay',
  [QUESTION_TYPES.FILL_IN_BLANK]: 'fill_in_the_blank',
  [QUESTION_TYPES.MATCHING]: 'matching',
  [QUESTION_TYPES.ORDERING]: 'ordering',
}

/**
 * Serialize a v8 question to QuizEngine (Format A)
 * @param {Object} question — v8 canonical question
 * @returns {Object} QuizEngine format question
 */
export function toQuizEngine(question) {
  if (!question) return null

  const v8Type = question.type || QUESTION_TYPES.MULTIPLE_CHOICE
  const content = question.content || {}

  const output = {
    questionType: typeMap[v8Type] || v8Type,
    question: content.prompt || '',
    answerOptions: serializeOptions(content.options || []),
    marks: question.marks || 1,
    meta: {
      difficulty: question.meta?.difficulty || 2,
      bloomLevel: question.meta?.bloom_level || 1,
      estimatedTimeSec: question.meta?.estimated_time_sec || 60,
      tags: question.meta?.tags || [],
    },
  }

  if (content.explanation) {
    output.explanation = content.explanation
  }

  if (content.hints && content.hints.length > 0) {
    output.hints = content.hints
  }

  return output
}

function serializeOptions(options) {
  return options.map(opt => ({
    text: opt.text || '',
    isCorrect: Boolean(opt.is_correct),
    ...(opt.rationale && { rationale: opt.rationale }),
    ...(opt.media && { media: opt.media }),
  }))
}
