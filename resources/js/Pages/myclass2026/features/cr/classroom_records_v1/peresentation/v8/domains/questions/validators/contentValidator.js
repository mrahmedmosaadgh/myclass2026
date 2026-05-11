/**
 * Content Validator
 * Validates content-specific rules for different question types.
 */

import { QUESTION_TYPES, allowsMultipleCorrect } from '../schema.js'

/**
 * Validate question content for logical correctness.
 * @param {Object} question — v8 canonical question
 * @returns {{ valid: boolean, warnings: Array<{field: string, message: string}> }}
 */
export function validateContent(question) {
  if (!question || !question.content) {
    return { valid: false, warnings: [{ field: 'root', message: 'Invalid question object' }] }
  }

  const type = question.type
  const content = question.content
  const warnings = []

  // Type-specific validation
  switch (type) {
    case QUESTION_TYPES.MULTIPLE_CHOICE:
      validateMultipleChoice(content, warnings)
      break
    case QUESTION_TYPES.MULTIPLE_SELECT:
      validateMultipleSelect(content, warnings)
      break
    case QUESTION_TYPES.BOOLEAN:
      validateBoolean(content, warnings)
      break
    case QUESTION_TYPES.SHORT_ANSWER:
      validateShortAnswer(content, warnings, question)
      break
    case QUESTION_TYPES.FILL_IN_BLANK:
      validateFillInBlank(content, warnings)
      break
    case QUESTION_TYPES.MATCHING:
      validateMatching(content, warnings)
      break
    case QUESTION_TYPES.ORDERING:
      validateOrdering(content, warnings)
      break
    case QUESTION_TYPES.STIMULUS_MCQ:
    case QUESTION_TYPES.EVIDENCE_BASED:
    case QUESTION_TYPES.CLAIM_EVALUATION:
    case QUESTION_TYPES.DATA_INTERPRETATION:
    case QUESTION_TYPES.SOURCE_ANALYSIS:
      validateStimulusBased(content, type, warnings)
      break
    default:
      // Generic validation for other types
      break
  }

  // Common checks
  validateCommon(content, warnings)

  return {
    valid: warnings.filter(w => w.severity === 'error').length === 0,
    warnings,
  }
}

// ============================================================================
// TYPE-SPECIFIC VALIDATORS
// ============================================================================

function validateMultipleChoice(content, warnings) {
  const options = content.options || []
  const correctOptions = options.filter(opt => opt.is_correct)

  if (correctOptions.length === 0) {
    warnings.push({
      field: 'content.options',
      message: 'Multiple choice question has no correct answer',
      severity: 'error',
    })
  }

  if (correctOptions.length > 1) {
    warnings.push({
      field: 'content.options',
      message: `Multiple choice should have exactly 1 correct answer, found ${correctOptions.length}`,
      severity: 'warning',
    })
  }

  if (options.length < 2) {
    warnings.push({
      field: 'content.options',
      message: 'Multiple choice should have at least 2 options',
      severity: 'error',
    })
  }

  // Check for duplicate option text
  const texts = options.map(opt => opt.text?.trim().toLowerCase()).filter(Boolean)
  const duplicates = texts.filter((item, index) => texts.indexOf(item) !== index)
  if (duplicates.length > 0) {
    warnings.push({
      field: 'content.options',
      message: `Duplicate option text found: "${duplicates[0]}"`,
      severity: 'warning',
    })
  }

  // Check for empty options
  const emptyOptions = options.filter(opt => !opt.text || opt.text.trim() === '')
  if (emptyOptions.length > 0) {
    warnings.push({
      field: 'content.options',
      message: `${emptyOptions.length} option(s) have empty text`,
      severity: 'error',
    })
  }

  // Check for "all of the above" / "none of the above" consistency
  const lastOption = options[options.length - 1]
  if (lastOption) {
    const text = lastOption.text?.toLowerCase() || ''
    if (text.includes('all of the above') || text.includes('all the above')) {
      // Verify all-above is actually correct if marked so
      if (lastOption.is_correct) {
        const othersCorrect = options.slice(0, -1).filter(opt => opt.is_correct)
        if (othersCorrect.length !== options.length - 1) {
          warnings.push({
            field: 'content.options',
            message: '"All of the above" is correct but not all other options are correct',
            severity: 'error',
          })
        }
      }
    }
  }
}

function validateMultipleSelect(content, warnings) {
  const options = content.options || []
  const correctOptions = options.filter(opt => opt.is_correct)

  if (correctOptions.length === 0) {
    warnings.push({
      field: 'content.options',
      message: 'Multiple select question has no correct answers',
      severity: 'error',
    })
  }

  if (correctOptions.length === options.length) {
    warnings.push({
      field: 'content.options',
      message: 'All options cannot be correct in a multiple select question',
      severity: 'warning',
    })
  }

  if (options.length < 2) {
    warnings.push({
      field: 'content.options',
      message: 'Multiple select should have at least 2 options',
      severity: 'error',
    })
  }
}

function validateBoolean(content, warnings) {
  const options = content.options || []

  if (options.length !== 2) {
    warnings.push({
      field: 'content.options',
      message: `Boolean question should have exactly 2 options, found ${options.length}`,
      severity: 'warning',
    })
  }

  const hasCorrect = options.some(opt => opt.is_correct)
  if (!hasCorrect) {
    warnings.push({
      field: 'content.options',
      message: 'Boolean question has no correct answer',
      severity: 'error',
    })
  }
}

function validateShortAnswer(content, warnings, question = {}) {
  if (content.options && content.options.length > 0) {
    warnings.push({
      field: 'content.options',
      message: 'Short answer question should not have options',
      severity: 'warning',
    })
  }

  // Check for expected answer or keywords
  if (question.evaluation?.keywords && question.evaluation.keywords.length === 0) {
    warnings.push({
      field: 'evaluation.keywords',
      message: 'Short answer question has no evaluation keywords for auto-grading',
      severity: 'info',
    })
  }
}

function validateFillInBlank(content, warnings) {
  if (!content.blanks || content.blanks.length === 0) {
    warnings.push({
      field: 'content.blanks',
      message: 'Fill-in-the-blank question should define blanks',
      severity: 'warning',
    })
  }

  const prompt = content.prompt || ''
  const blankCount = (prompt.match(/_{2,}/g) || []).length + (prompt.match(/\[blank\]/g) || []).length

  if (content.blanks && blankCount > 0 && content.blanks.length !== blankCount) {
    warnings.push({
      field: 'content.blanks',
      message: `Found ${blankCount} blanks in prompt but ${content.blanks.length} blank answers defined`,
      severity: 'warning',
    })
  }
}

function validateMatching(content, warnings) {
  if (!content.pairs || content.pairs.length < 2) {
    warnings.push({
      field: 'content.pairs',
      message: 'Matching question should have at least 2 pairs',
      severity: 'error',
    })
  }

  if (content.pairs) {
    const leftItems = content.pairs.map(p => p.left?.toLowerCase?.())
    const rightItems = content.pairs.map(p => p.right?.toLowerCase?.())

    if (new Set(leftItems).size !== leftItems.length) {
      warnings.push({
        field: 'content.pairs',
        message: 'Duplicate left-side items in matching pairs',
        severity: 'warning',
      })
    }

    if (new Set(rightItems).size !== rightItems.length) {
      warnings.push({
        field: 'content.pairs',
        message: 'Duplicate right-side items in matching pairs',
        severity: 'warning',
      })
    }
  }
}

function validateOrdering(content, warnings) {
  if (!content.items || content.items.length < 2) {
    warnings.push({
      field: 'content.items',
      message: 'Ordering question should have at least 2 items',
      severity: 'error',
    })
  }
}

function validateStimulusBased(content, type, warnings) {
  if (!content.stimulus || typeof content.stimulus !== 'object') {
    warnings.push({
      field: 'content.stimulus',
      message: `${type} question requires a stimulus object`,
      severity: 'error',
    })
  } else {
    if (!content.stimulus.type) {
      warnings.push({
        field: 'content.stimulus.type',
        message: 'Stimulus type is required',
        severity: 'warning',
      })
    }

    if (!content.stimulus.content && !content.stimulus.title && (!content.stimulus.media || content.stimulus.media.length === 0)) {
      warnings.push({
        field: 'content.stimulus',
        message: 'Stimulus should have content, title, or media',
        severity: 'warning',
      })
    }
  }

  // Also validate options (stimulus-based types use options)
  if (content.options) {
    validateMultipleChoice(content, warnings)
  }
}

// ============================================================================
// COMMON VALIDATORS
// ============================================================================

function validateCommon(content, warnings) {
  // Check prompt for empty/whitespace only
  if (content.prompt && content.prompt.trim().length === 0) {
    warnings.push({
      field: 'content.prompt',
      message: 'Question prompt is empty or whitespace only',
      severity: 'error',
    })
  }

  // Check explanation consistency
  if (content.explanation && content.explanation.trim().length < 10) {
    warnings.push({
      field: 'content.explanation',
      message: 'Explanation is very short (less than 10 characters)',
      severity: 'info',
    })
  }

  // Check hints quality
  if (content.hints) {
    content.hints.forEach((hint, i) => {
      if (hint.trim().length < 5) {
        warnings.push({
          field: `content.hints[${i}]`,
          message: `Hint ${i + 1} is very short`,
          severity: 'info',
        })
      }
    })
  }

  // Check media accessibility
  if (content.media) {
    content.media.forEach((media, i) => {
      if (media.type === 'image' && !media.alt) {
        warnings.push({
          field: `content.media[${i}].alt`,
          message: `Image media is missing alt text for accessibility`,
          severity: 'warning',
        })
      }
    })
  }
}
