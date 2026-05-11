/**
 * Normalize Format B (Old Lesson Presentation) → v8 Canonical
 *
 * Format B structure:
 * {
 *   questionNumber: number,
 *   question: string,
 *   options: string[] | [{ text: string, isCorrect: boolean }],
 *   correctOptionIndex?: number,
 *   explanation?: string,
 *   points?: number,
 *   difficulty?: number
 * }
 */

import { QUESTION_TYPES } from '../schema.js'
import { generateUUID, generateLetterId } from '../utils/generateId.js'

/**
 * Normalize an Old Lesson (Format B) question to v8
 * @param {Object} question — Old lesson format question
 * @returns {Object} v8 canonical question
 */
export function fromOldLesson(question) {
  if (!question) return null

  const rawOptions = question.options || []
  const correctIndex = question.correctOptionIndex ?? -1

  const v8 = {
    schema_version: 1,
    id: generateUUID('q'),
    type: QUESTION_TYPES.MULTIPLE_CHOICE,
    marks: question.points || 1,
    content: {
      prompt: question.question || '',
      options: normalizeOptions(rawOptions, correctIndex),
    },
    meta: {
      difficulty: question.difficulty || 2,
      bloom_level: 1,
      estimated_time_sec: 60,
      source: 'teacher',
      tags: [],
      cognitive_demand: 'recall',
      assessment_mode: 'traditional',
    },
    evaluation: { mode: 'auto' },
  }

  if (question.explanation) {
    v8.content.explanation = String(question.explanation)
  }

  return v8
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
      is_correct: opt.isCorrect !== undefined ? Boolean(opt.isCorrect) : index === correctIndex,
      ...(opt.rationale && { rationale: String(opt.rationale) }),
      ...(opt.media && { media: opt.media }),
    }
  })
}
