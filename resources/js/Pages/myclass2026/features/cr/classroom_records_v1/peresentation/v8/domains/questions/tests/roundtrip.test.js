/**
 * Round-trip tests for question normalizers and serializers.
 * Run manually in browser console or with a test runner.
 */

import {
  normalize,
  fromQuizEngine,
  fromOldLesson,
  fromReadyToPrint,
  fromAI,
  serialize,
  toQuizEngine,
  toOldLesson,
  toReadyToPrint,
  toExport,
  validate,
  createQuestion,
  createOption,
  createMedia,
} from '../index.js'

// ============================================================================
// TEST DATA
// ============================================================================

const quizEngineQuestion = {
  questionType: 'multiple_choice',
  question: 'What is 2 + 2?',
  answerOptions: [
    { text: '3', isCorrect: false, rationale: 'Too low' },
    { text: '4', isCorrect: true, rationale: 'Correct!' },
    { text: '5', isCorrect: false, rationale: 'Too high' },
    { text: '6', isCorrect: false, rationale: 'Way too high' },
  ],
  explanation: 'Basic addition of two and two.',
  marks: 1,
  meta: { difficulty: 1, tags: ['math'] },
}

const oldLessonQuestion = {
  questionNumber: 1,
  question: 'What is the capital of France?',
  options: ['London', 'Paris', 'Berlin', 'Madrid'],
  correctOptionIndex: 1,
  explanation: 'Paris is the capital of France.',
  points: 1,
}

const readyToPrintQuestion = {
  type: 'mcq',
  marks: 2,
  content: {
    prompt: 'Which planet is closest to the Sun?',
    options: ['Venus', 'Mars', 'Mercury', 'Earth'],
    correct_option_index: 2,
    explanation: 'Mercury is the closest planet to the Sun.',
  },
}

const aiMinimalQuestion = {
  type: 'multiple_choice',
  prompt: 'What is H2O?',
  options: [
    { text: 'Hydrogen', correct: false },
    { text: 'Water', correct: true },
    { text: 'Oxygen', correct: false },
    { text: 'Helium', correct: false },
  ],
  explanation: 'H2O is the chemical formula for water.',
  hints: ['Think about chemical composition'],
}

const v8Question = {
  schema_version: 1,
  id: 'q_test_123',
  type: 'multiple_choice',
  marks: 1,
  content: {
    prompt: 'What is the largest mammal?',
    options: [
      { id: 'a', text: 'Elephant', is_correct: false, rationale: 'Large, but not the largest' },
      { id: 'b', text: 'Blue Whale', is_correct: true, rationale: 'Largest known animal' },
      { id: 'c', text: 'Giraffe', is_correct: false },
      { id: 'd', text: 'Hippopotamus', is_correct: false },
    ],
    explanation: 'The blue whale is the largest mammal and the largest animal ever known.',
  },
  meta: {
    difficulty: 1,
    bloom_level: 1,
    estimated_time_sec: 30,
    source: 'ai',
    tags: ['biology'],
  },
  evaluation: { mode: 'auto' },
}

// ============================================================================
// TEST HELPERS
// ============================================================================

function assert(condition, message) {
  if (!condition) {
    throw new Error(`ASSERTION FAILED: ${message}`)
  }
}

function logTest(name, passed, details = '') {
  const status = passed ? '✅ PASS' : '❌ FAIL'
  console.log(`  ${status} ${name}${details ? ` (${details})` : ''}`)
}

// ============================================================================
// TESTS
// ============================================================================

export function runTests() {
  console.log('🧪 Running Question Domain Layer Tests...\n')

  let totalPassed = 0
  let totalFailed = 0

  // === NORMALIZER TESTS ===
  console.log('📥 NORMALIZER TESTS:')

  try {
    // QuizEngine → v8
    const fromQE = fromQuizEngine(quizEngineQuestion)
    assert(fromQE.schema_version === 1, 'Schema version set')
    assert(fromQE.type === 'multiple_choice', 'Type normalized')
    assert(fromQE.content.prompt === 'What is 2 + 2?', 'Prompt preserved')
    assert(fromQE.content.options.length === 4, '4 options')
    assert(fromQE.content.options[1].is_correct === true, 'Correct answer flagged')
    assert(fromQE.content.options[1].text === '4', 'Correct text preserved')
    assert(fromQE.meta.tags.includes('math'), 'Tags preserved')
    logTest('QuizEngine → v8', true)
    totalPassed++
  } catch (e) {
    logTest('QuizEngine → v8', false, e.message)
    totalFailed++
  }

  try {
    // Old Lesson → v8
    const fromOL = fromOldLesson(oldLessonQuestion)
    assert(fromOL.type === 'multiple_choice', 'Type is multiple_choice')
    assert(fromOL.content.prompt === 'What is the capital of France?', 'Prompt preserved')
    assert(fromOL.content.options[1].is_correct === true, 'Correct option at index 1')
    assert(fromOL.content.options[1].text === 'Paris', 'Option text preserved')
    assert(fromOL.marks === 1, 'Marks preserved')
    logTest('OldLesson → v8', true)
    totalPassed++
  } catch (e) {
    logTest('OldLesson → v8', false, e.message)
    totalFailed++
  }

  try {
    // ReadyToPrint → v8
    const fromRTP = fromReadyToPrint(readyToPrintQuestion)
    assert(fromRTP.type === 'multiple_choice', 'mcq mapped to multiple_choice')
    assert(fromRTP.content.prompt === 'Which planet is closest to the Sun?', 'Prompt preserved')
    assert(fromRTP.content.options[2].is_correct === true, 'Correct option at index 2')
    assert(fromRTP.content.options[2].text === 'Mercury', 'Correct text preserved')
    assert(fromRTP.marks === 2, 'Marks preserved')
    logTest('ReadyToPrint → v8', true)
    totalPassed++
  } catch (e) {
    logTest('ReadyToPrint → v8', false, e.message)
    totalFailed++
  }

  try {
    // AI Minimal → v8
    const fromAIResult = fromAI(aiMinimalQuestion)
    assert(fromAIResult.type === 'multiple_choice', 'Type inferred')
    assert(fromAIResult.content.prompt === 'What is H2O?', 'Prompt preserved')
    assert(fromAIResult.content.options[1].is_correct === true, 'Correct using "correct" flag')
    assert(fromAIResult.meta.source === 'ai', 'Source set to ai')
    assert(fromAIResult.content.hints.length === 1, 'Hints preserved')
    logTest('AI Minimal → v8', true)
    totalPassed++
  } catch (e) {
    logTest('AI Minimal → v8', false, e.message)
    totalFailed++
  }

  try {
    // Auto-detect normalize
    const normalized = normalize(readyToPrintQuestion)
    assert(normalized.schema_version === 1, 'Auto-detect worked')
    assert(normalized.content.options.length === 4, 'Options preserved')
    logTest('Auto-detect normalize', true)
    totalPassed++
  } catch (e) {
    logTest('Auto-detect normalize', false, e.message)
    totalFailed++
  }

  try {
    // v8 passthrough
    const v8Norm = normalize(v8Question)
    assert(v8Norm.schema_version === 1, 'v8 passthrough works')
    assert(v8Norm.id === 'q_test_123', 'ID preserved')
    logTest('v8 passthrough', true)
    totalPassed++
  } catch (e) {
    logTest('v8 passthrough', false, e.message)
    totalFailed++
  }

  // === SERIALIZER TESTS ===
  console.log('\n📤 SERIALIZER TESTS:')

  try {
    // v8 → QuizEngine
    const v8 = fromQuizEngine(quizEngineQuestion)
    const backToQE = toQuizEngine(v8)
    assert(backToQE.questionType === 'multiple_choice', 'Type serialized')
    assert(backToQE.question === 'What is 2 + 2?', 'Prompt preserved')
    assert(backToQE.answerOptions[1].isCorrect === true, 'Correct answer preserved')
    logTest('v8 → QuizEngine', true)
    totalPassed++
  } catch (e) {
    logTest('v8 → QuizEngine', false, e.message)
    totalFailed++
  }

  try {
    // v8 → ReadyToPrint
    const v8 = fromReadyToPrint(readyToPrintQuestion)
    const backToRTP = toReadyToPrint(v8)
    assert(backToRTP.type === 'mcq', 'Type mapped back to mcq')
    assert(backToRTP.content.prompt === 'Which planet is closest to the Sun?', 'Prompt preserved')
    assert(backToRTP.content.correct_option_index === 2, 'Correct index preserved')
    logTest('v8 → ReadyToPrint', true)
    totalPassed++
  } catch (e) {
    logTest('v8 → ReadyToPrint', false, e.message)
    totalFailed++
  }

  try {
    // v8 → Export
    const exportObj = toExport(v8Question)
    assert(exportObj.schema_version === 1, 'Export has schema_version')
    assert(exportObj.type === 'multiple_choice', 'Export has type')
    assert(exportObj.content.options.length === 4, 'Export has options')
    assert(!exportObj.response, 'Runtime fields stripped')
    logTest('v8 → Export (clean)', true)
    totalPassed++
  } catch (e) {
    logTest('v8 → Export (clean)', false, e.message)
    totalFailed++
  }

  // === VALIDATOR TESTS ===
  console.log('\n✅ VALIDATOR TESTS:')

  try {
    const valid = validate(v8Question)
    assert(valid.valid === true, 'Valid question passes')
    assert(valid.errors.length === 0, 'No errors')
    logTest('Validate valid question', true)
    totalPassed++
  } catch (e) {
    logTest('Validate valid question', false, e.message)
    totalFailed++
  }

  try {
    const badQuestion = { ...v8Question, content: { ...v8Question.content, options: [] } }
    const invalid = validate(badQuestion)
    assert(invalid.valid === false, 'Invalid question fails')
    assert(invalid.errors.length > 0, 'Has errors')
    logTest('Validate invalid question', true)
    totalPassed++
  } catch (e) {
    logTest('Validate invalid question', false, e.message)
    totalFailed++
  }

  // === FACTORY TESTS ===
  console.log('\n🏭 FACTORY TESTS:')

  try {
    const opt = createOption({ text: 'Test', is_correct: true })
    assert(opt.id.startsWith('opt_'), 'Option ID generated')
    assert(opt.text === 'Test', 'Text set')
    assert(opt.is_correct === true, 'is_correct set')
    logTest('createOption', true)
    totalPassed++
  } catch (e) {
    logTest('createOption', false, e.message)
    totalFailed++
  }

  try {
    const q = createQuestion({
      type: 'multiple_choice',
      content: {
        prompt: 'Test question?',
        options: [
          { text: 'A', is_correct: false },
          { text: 'B', is_correct: true },
        ],
      },
    })
    assert(q.schema_version === 1, 'Schema version set')
    assert(q.id.startsWith('q_'), 'ID generated')
    assert(q.type === 'multiple_choice', 'Type set')
    assert(q.content.prompt === 'Test question?', 'Prompt set')
    assert(q.content.options.length === 2, 'Options created')
    logTest('createQuestion', true)
    totalPassed++
  } catch (e) {
    logTest('createQuestion', false, e.message)
    totalFailed++
  }

  // === SUMMARY ===
  console.log(`\n📊 Results: ${totalPassed} passed, ${totalFailed} failed out of ${totalPassed + totalFailed} tests`)
  return { passed: totalPassed, failed: totalFailed }
}

// Auto-run if in browser console context
if (typeof window !== 'undefined') {
  window.runQuestionTests = runTests
  console.log('💡 Call runQuestionTests() in console to run tests')
}
