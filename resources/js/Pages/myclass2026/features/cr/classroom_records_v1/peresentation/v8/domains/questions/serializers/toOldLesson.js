/**
 * Serialize v8 Canonical → Format B (Old Lesson Presentation)
 */

/**
 * Serialize a v8 question to Old Lesson (Format B)
 * @param {Object} question — v8 canonical question
 * @param {number} [questionNumber=1] — Optional question number
 * @returns {Object} Old Lesson format question
 */
export function toOldLesson(question, questionNumber = 1) {
  if (!question) return null

  const content = question.content || {}
  const options = content.options || []
  const correctIndex = options.findIndex(opt => opt.is_correct)

  const output = {
    questionNumber,
    question: content.prompt || '',
    options: options.map(opt => opt.text || ''),
    points: question.marks || 1,
  }

  if (correctIndex >= 0) {
    output.correctOptionIndex = correctIndex
  }

  if (content.explanation) {
    output.explanation = content.explanation
  }

  if (question.meta?.difficulty) {
    output.difficulty = question.meta.difficulty
  }

  return output
}
