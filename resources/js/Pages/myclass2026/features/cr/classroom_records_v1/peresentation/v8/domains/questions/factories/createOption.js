/**
 * Option Factory
 * Creates standardized v8 option objects with stable IDs and full metadata.
 */

import { generateShortId } from '../utils/generateId.js'

/**
 * Create a v8 option object
 * @param {Object} params
 * @param {string} [params.id] — Unique option ID (auto-generated if not provided)
 * @param {string} params.text — Option display text
 * @param {boolean} [params.is_correct=false] — Whether this option is correct
 * @param {string} [params.rationale] — Why this option is correct/incorrect
 * @param {Array} [params.media] — Media attached to this option
 * @param {number} [params.marks=0] — Partial credit for this option
 * @param {string} [params.targets_misconception] — Common error this distractor targets
 * @param {string} [params.evidence_support] — 'strong' | 'weak' | 'none'
 * @param {Object} [params.metadata] — Additional metadata
 * @returns {Object} v8 option object
 */
export function createOption({
  id = null,
  text = '',
  is_correct = false,
  rationale = '',
  media = [],
  marks = 0,
  targets_misconception = null,
  evidence_support = null,
  metadata = null,
} = {}) {
  if (!text && text !== '') {
    throw new Error('Option text is required')
  }

  const option = {
    id: id || generateShortId('opt'),
    text: String(text).trim(),
    is_correct: Boolean(is_correct),
  }

  // Only add optional fields when they have meaningful values
  if (rationale?.trim()) option.rationale = rationale.trim()
  if (Array.isArray(media) && media.length > 0) option.media = media
  if (marks > 0) option.marks = marks
  if (targets_misconception) option.targets_misconception = targets_misconception
  if (evidence_support) option.evidence_support = evidence_support
  if (metadata && Object.keys(metadata).length > 0) option.metadata = metadata

  return option
}

/**
 * Create multiple options at once
 * @param {Array<Object>} optionList — Array of option parameters
 * @returns {Array<Object>} Array of v8 option objects
 */
export function createOptions(optionList = []) {
  return optionList.map(opt => createOption(opt))
}
