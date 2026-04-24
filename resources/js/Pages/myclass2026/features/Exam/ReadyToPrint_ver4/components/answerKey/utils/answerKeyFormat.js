export function getCorrectOptionIndex(question) {
  if (question?.type !== 'multiple_choice') return null

  const v = question?.content?.correct_option_index
    ?? question?.content?.correct_answer
    ?? question?.correct_answer

  if (typeof v === 'number' && Number.isFinite(v)) return v
  if (typeof v === 'string' && v.trim() !== '' && !Number.isNaN(Number(v))) return Number(v)

  if (typeof v === 'string' && Array.isArray(question?.content?.options)) {
    const idx = question.content.options.findIndex(o => String(o).trim() === v.trim())
    if (idx >= 0) return idx
  }

  return null
}

export function formatChoiceLabelFromIndex(idx, mcqOptions) {
  if (idx == null) return '-'

  const labelStyle = mcqOptions?.labelStyle || 'letter'
  const customTpl = mcqOptions?.customLabelTemplate || '{letter})'

  const n = idx + 1
  const letter = String.fromCharCode('A'.charCodeAt(0) + idx)

  if (labelStyle === 'number') return String(n)
  if (labelStyle === 'custom') {
    return String(customTpl)
      .replaceAll('{i}', String(idx))
      .replaceAll('{n}', String(n))
      .replaceAll('{letter}', String(letter))
      // keep it compact for answer key (no trailing punctuation/spaces)
      .replace(/[)\.\s]+$/g, '')
  }

  // default 'letter'
  return String(letter)
}

export function getAnswerKeyChoiceLabel(question, mcqOptions) {
  const idx = getCorrectOptionIndex(question)
  return formatChoiceLabelFromIndex(idx, mcqOptions)
}
