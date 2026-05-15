const optionLabels = ['A', 'B', 'C', 'D', 'E', 'F']

function normalizeOption(option, index) {
  if (typeof option === 'string') {
    return {
      id: optionLabels[index] || String(index + 1),
      text: option
    }
  }

  return {
    id: String(option?.id || optionLabels[index] || index + 1),
    text: String(option?.text || option?.label || option || '')
  }
}

function extractCorrectOptionId(source, options) {
  if (source.correctOptionId) return source.correctOptionId
  if (source.correctId) return source.correctId
  if (source.answerId) return source.answerId

  const answerStr = String(source.answer || '')
  if (answerStr && options.length) {
    const match = options.find((option) => answerStr.includes(option.id) || answerStr.includes(option.text))
    if (match) return match.id
  }

  return options[0]?.id || null
}

export function normalizeGroupQuizQuestion(rawQuestion, index = 0) {
  const source = rawQuestion?.questionData || rawQuestion || {}
  const options = Array.isArray(source.options) ? source.options.map(normalizeOption) : []
  const correctOptionId = extractCorrectOptionId(source, options)

  return {
    id: String(rawQuestion?.id || source.id || `question-${index + 1}`),
    question: String(source.question || source.prompt || `Question ${index + 1}`),
    options,
    correctOptionId
  }
}

export function extractGroupQuizQuestionsFromSlides(slides = []) {
  const questions = []

  slides.forEach((slide) => {
    const elements = Array.isArray(slide?.elements) ? slide.elements : []

    elements.forEach((element) => {
      if (element?.type === 'group-mcq') {
        questions.push(normalizeGroupQuizQuestion(element, questions.length))
      }
    })
  })

  return questions
}
