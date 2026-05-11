/**
 * V8 Canonical Question Schema Definitions
 * Core types and constants for the v8 question format.
 */

// ============================================================================
// CONSTANTS
// ============================================================================

/** Supported question types in v8 */
export const QUESTION_TYPES = Object.freeze({
  // Basic types
  MULTIPLE_CHOICE: 'multiple_choice',
  MULTIPLE_SELECT: 'multiple_select',
  BOOLEAN: 'boolean',
  SHORT_ANSWER: 'short_answer',
  LONG_ANSWER: 'long_answer',
  FILL_IN_BLANK: 'fill_in_blank',
  MATCHING: 'matching',
  ORDERING: 'ordering',
  // Modern assessment types
  STIMULUS_MCQ: 'stimulus_mcq',
  EVIDENCE_BASED: 'evidence_based',
  CLAIM_EVALUATION: 'claim_evaluation',
  DATA_INTERPRETATION: 'data_interpretation',
  SOURCE_ANALYSIS: 'source_analysis',
  SCENARIO_APPLICATION: 'scenario_application',
  CODE_TRACING: 'code_tracing',
  DEBUGGING: 'debugging',
  MULTI_STEP_REASONING: 'multi_step_reasoning',
  JUSTIFICATION_REQUIRED: 'justification_required',
})

/** Media types supported in question content */
export const MEDIA_TYPES = Object.freeze({
  IMAGE: 'image',
  AUDIO: 'audio',
  VIDEO: 'video',
  LINK: 'link',
  EMBED: 'embed',
})

/** Cognitive demand levels (Bloom's taxonomy + modern extensions) */
export const COGNITIVE_DEMAND = Object.freeze({
  RECALL: 'recall',
  APPLICATION: 'application',
  ANALYSIS: 'analysis',
  EVALUATION: 'evaluation',
  SYNTHESIS: 'synthesis',
  CREATION: 'creation',
})

/** Assessment modes */
export const ASSESSMENT_MODE = Object.freeze({
  TRADITIONAL: 'traditional',
  STIMULUS_BASED: 'stimulus_based',
  CRITICAL_THINKING: 'critical_thinking',
  PERFORMANCE_TASK: 'performance_task',
})

/** Discipline-specific thinking types */
export const DISCIPLINE_THINKING = Object.freeze({
  SCIENTIFIC_INQUIRY: 'scientific_inquiry',
  HISTORICAL_ANALYSIS: 'historical_analysis',
  COMPUTATIONAL_THINKING: 'computational_thinking',
  DATA_LITERACY: 'data_literacy',
  ARTISTIC_CRITIQUE: 'artistic_critique',
  ETHICAL_REASONING: 'ethical_reasoning',
})

/** Stimulus types for advanced question types */
export const STIMULUS_TYPES = Object.freeze({
  TEXT: 'text',
  IMAGE: 'image',
  DATA_TABLE: 'data_table',
  GRAPH: 'graph',
  PRIMARY_SOURCE: 'primary_source',
  SCIENTIFIC_EXPERIMENT: 'scientific_experiment',
  CODE_SNIPPET: 'code_snippet',
  SCENARIO: 'scenario',
})

/** Evaluation modes */
export const EVALUATION_MODE = Object.freeze({
  AUTO: 'auto',
  MANUAL: 'manual',
  HYBRID: 'hybrid',
})

/** Question sources */
export const QUESTION_SOURCE = Object.freeze({
  AI: 'ai',
  TEACHER: 'teacher',
  TEXTBOOK: 'textbook',
  STANDARDIZED: 'standardized',
})

// ============================================================================
// SCHEMA VERSION
// ============================================================================

export const CURRENT_SCHEMA_VERSION = 1

// ============================================================================
// DEFAULTS
// ============================================================================

export const DEFAULTS = Object.freeze({
  schema_version: CURRENT_SCHEMA_VERSION,
  marks: 1,
  difficulty: 2,
  bloom_level: 1,
  estimated_time_sec: 60,
  source: QUESTION_SOURCE.TEACHER,
  cognitive_demand: COGNITIVE_DEMAND.RECALL,
  assessment_mode: ASSESSMENT_MODE.TRADITIONAL,
})

// ============================================================================
// TYPE HELPERS
// ============================================================================

/**
 * Check if a string is a valid v8 question type
 * @param {string} type
 * @returns {boolean}
 */
export function isValidQuestionType(type) {
  return Object.values(QUESTION_TYPES).includes(type)
}

/**
 * Check if a string is a valid media type
 * @param {string} type
 * @returns {boolean}
 */
export function isValidMediaType(type) {
  return Object.values(MEDIA_TYPES).includes(type)
}

/**
 * Check if a question type uses options (has answer choices)
 * @param {string} type
 * @returns {boolean}
 */
export function hasOptions(type) {
  return [
    QUESTION_TYPES.MULTIPLE_CHOICE,
    QUESTION_TYPES.MULTIPLE_SELECT,
    QUESTION_TYPES.BOOLEAN,
    QUESTION_TYPES.STIMULUS_MCQ,
    QUESTION_TYPES.EVIDENCE_BASED,
    QUESTION_TYPES.CLAIM_EVALUATION,
    QUESTION_TYPES.DATA_INTERPRETATION,
    QUESTION_TYPES.SOURCE_ANALYSIS,
    QUESTION_TYPES.SCENARIO_APPLICATION,
    QUESTION_TYPES.CODE_TRACING,
    QUESTION_TYPES.DEBUGGING,
    QUESTION_TYPES.MULTI_STEP_REASONING,
    QUESTION_TYPES.JUSTIFICATION_REQUIRED,
    QUESTION_TYPES.MATCHING,
    QUESTION_TYPES.ORDERING,
  ].includes(type)
}

/**
 * Check if a question type supports multiple correct answers
 * @param {string} type
 * @returns {boolean}
 */
export function allowsMultipleCorrect(type) {
  return [
    QUESTION_TYPES.MULTIPLE_SELECT,
    QUESTION_TYPES.MATCHING,
    QUESTION_TYPES.ORDERING,
  ].includes(type)
}

/**
 * Check if a question type requires text input (no options)
 * @param {string} type
 * @returns {boolean}
 */
export function requiresTextInput(type) {
  return [
    QUESTION_TYPES.SHORT_ANSWER,
    QUESTION_TYPES.LONG_ANSWER,
    QUESTION_TYPES.FILL_IN_BLANK,
  ].includes(type)
}

/**
 * Check if a question type uses a stimulus (advanced assessment)
 * @param {string} type
 * @returns {boolean}
 */
export function usesStimulus(type) {
  return [
    QUESTION_TYPES.STIMULUS_MCQ,
    QUESTION_TYPES.EVIDENCE_BASED,
    QUESTION_TYPES.CLAIM_EVALUATION,
    QUESTION_TYPES.DATA_INTERPRETATION,
    QUESTION_TYPES.SOURCE_ANALYSIS,
    QUESTION_TYPES.SCENARIO_APPLICATION,
    QUESTION_TYPES.CODE_TRACING,
    QUESTION_TYPES.DEBUGGING,
    QUESTION_TYPES.MULTI_STEP_REASONING,
  ].includes(type)
}
