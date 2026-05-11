/**
 * Normalize AI Minimal Format → v8 Canonical
 *
 * AI Minimal structures (various forms AI might output):
 * {
 *   type: "multiple_choice",
 *   prompt: "What is 2 + 2?",
 *   options: [
 *     { text: "3", correct: false },
 *     { text: "4", correct: true }
 *   ],
 *   explanation: "...",
 *   hints: ["..."]
 * }
 *
 * OR:
 * {
 *   question: "What is 2 + 2?",
 *   options: ["3", "4", "5", "6"],
 *   correct_index: 1,
 *   explanation: "..."
 * }
 */

import { QUESTION_TYPES, DEFAULTS } from '../schema.js'
import { generateUUID, generateLetterId } from '../utils/generateId.js'

/**
 * Normalize an AI-generated minimal question to v8
 * @param {Object} question — AI minimal format question
 * @returns {Object} v8 canonical question
 */
export function fromAI(question) {
  if (!question) return null

  const v8 = {
    schema_version: 1,
    id: generateUUID('q'),
    type: inferType(question),
    marks: 1,
    content: {
      prompt: question.prompt || question.question || '',
      options: normalizeOptions(question),
    },
    meta: {
      difficulty: DEFAULTS.difficulty,
      bloom_level: DEFAULTS.bloom_level,
      estimated_time_sec: DEFAULTS.estimated_time_sec,
      source: 'ai',
      tags: question.tags || [],
      cognitive_demand: inferCognitiveDemand(question),
      assessment_mode: question.stimulus ? 'stimulus_based' : 'traditional',
    },
    evaluation: { mode: 'auto' },
  }

  if (question.explanation || question.rationale) {
    v8.content.explanation = String(question.explanation || question.rationale)
  }

  if (question.hints && Array.isArray(question.hints)) {
    v8.content.hints = question.hints.map(h => String(h)).filter(Boolean)
  }

  if (question.stimulus) {
    v8.content.stimulus = question.stimulus
  }

  return v8
}

function normalizeOptions(question) {
  const rawOptions = question.options || []
  const correctIndex = question.correct_index ?? -1

  return rawOptions.map((opt, index) => {
    // String option
    if (typeof opt === 'string') {
      return {
        id: generateLetterId(index),
        text: opt,
        is_correct: index === correctIndex,
      }
    }

    // Object option (AI format with 'correct' or 'is_correct')
    const isCorrect =
      opt.correct !== undefined ? Boolean(opt.correct)
        : opt.is_correct !== undefined ? Boolean(opt.is_correct)
          : opt.isCorrect !== undefined ? Boolean(opt.isCorrect)
            : index === correctIndex

    const option = {
      id: opt.id || generateLetterId(index),
      text: opt.text || opt.label || '',
      is_correct: isCorrect,
    }

    if (opt.rationale || opt.feedback) {
      option.rationale = String(opt.rationale || opt.feedback)
    }

    return option
  })
}

function inferType(question) {
  const rawType = question.type || ''

  const typeMap = {
    mcq: QUESTION_TYPES.MULTIPLE_CHOICE,
    multiple_choice: QUESTION_TYPES.MULTIPLE_CHOICE,
    multiple: QUESTION_TYPES.MULTIPLE_SELECT,
    multi_select: QUESTION_TYPES.MULTIPLE_SELECT,
    true_false: QUESTION_TYPES.BOOLEAN,
    boolean: QUESTION_TYPES.BOOLEAN,
    short_answer: QUESTION_TYPES.SHORT_ANSWER,
    text: QUESTION_TYPES.SHORT_ANSWER,
    essay: QUESTION_TYPES.LONG_ANSWER,
    fill_blank: QUESTION_TYPES.FILL_IN_BLANK,
    matching: QUESTION_TYPES.MATCHING,
    ordering: QUESTION_TYPES.ORDERING,
    sequence: QUESTION_TYPES.ORDERING,
    stimulus_mcq: QUESTION_TYPES.STIMULUS_MCQ,
    evidence_based: QUESTION_TYPES.EVIDENCE_BASED,
    code_tracing: QUESTION_TYPES.CODE_TRACING,
  }

  if (typeMap[rawType]) return typeMap[rawType]

  // Infer from structure
  const options = question.options || []
  if (options.length === 0) return QUESTION_TYPES.SHORT_ANSWER

  const correctCount = options.filter(o =>
    o.correct || o.isCorrect || o.is_correct
  ).length

  if (correctCount > 1) return QUESTION_TYPES.MULTIPLE_SELECT
  if (question.correct_index !== undefined) return QUESTION_TYPES.MULTIPLE_CHOICE

  return QUESTION_TYPES.MULTIPLE_CHOICE
}

function inferCognitiveDemand(question) {
  if (question.stimulus || question.context) return 'analysis'
  if (question.hints && question.hints.length > 0) return 'application'
  return 'recall'
}
