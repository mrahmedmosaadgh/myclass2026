export function numberToLetters(num, letterCase = 'upper') {
  const n = Number(num)
  if (!Number.isFinite(n) || n <= 0) return ''

  let x = Math.floor(n)
  let result = ''
  while (x > 0) {
    x -= 1
    result = String.fromCharCode(65 + (x % 26)) + result
    x = Math.floor(x / 26)
  }

  return letterCase === 'lower' ? result.toLowerCase() : result
}

export function formatQuestionLabel(index, options = {}) {
  const style = options?.style || 'question'
  const prefix = options?.prefix ?? ''
  const suffix = options?.suffix ?? ''
  const startAt = Number.isFinite(Number(options?.startAt)) ? Number(options.startAt) : 1
  const letterCase = options?.letterCase || 'upper'

  const n = Math.max(0, Number(index) - startAt + 1)
  const letter = numberToLetters(n, letterCase)

  if (style === 'number') return `${prefix}${n}${suffix}`
  if (style === 'letter') return `${prefix}${letter}${suffix}`

  if (style === 'custom') {
    const template = options?.customTemplate || '{n}'
    return template
      .replaceAll('{n}', String(n))
      .replaceAll('{letter}', String(letter))
  }

  const questionWord = options?.questionWord ?? 'Question'
  const spacer = options?.spacer ?? ' '
  return `${questionWord}${spacer}${n}${suffix}`
}
