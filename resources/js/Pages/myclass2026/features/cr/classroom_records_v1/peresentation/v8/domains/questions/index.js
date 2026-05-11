/**
 * V8 Question Domain Layer - Public API
 * Canonical question format: normalization, serialization, validation, and factories.
 */

// Schema definitions and type helpers
export {
  QUESTION_TYPES,
  MEDIA_TYPES,
  COGNITIVE_DEMAND,
  ASSESSMENT_MODE,
  DISCIPLINE_THINKING,
  STIMULUS_TYPES,
  EVALUATION_MODE,
  QUESTION_SOURCE,
  CURRENT_SCHEMA_VERSION,
  DEFAULTS,
  isValidQuestionType,
  isValidMediaType,
  hasOptions,
  allowsMultipleCorrect,
  requiresTextInput,
  usesStimulus,
} from './schema.js'

// Factories for creating v8 questions
export {
  createQuestion,
  createOption,
  createOptions,
  createMedia,
} from './factories/index.js'

// Normalizers: any format → v8
export {
  normalize,
  fromQuizEngine,
  fromOldLesson,
  fromReadyToPrint,
  fromAI,
  isNormalizable,
  getFormatName,
} from './normalizers/index.js'

// Serializers: v8 → any format
export {
  serialize,
  toQuizEngine,
  toOldLesson,
  toReadyToPrint,
  toExport,
  toExportArray,
  OUTPUT_FORMATS,
} from './serializers/index.js'

// Validators: three-level validation
export {
  validate,
  validateSchema,
  validateContent,
  isValid,
  validateAll,
} from './validators/index.js'

// Utilities
export {
  generateUUID,
  generateShortId,
  generateLetterId,
  generateStableId,
  detectFormat,
  FORMATS,
  canNormalize,
  enrichQuestion,
} from './utils/index.js'
