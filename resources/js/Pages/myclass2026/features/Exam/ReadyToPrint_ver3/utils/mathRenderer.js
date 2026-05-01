import { renderToString } from 'katex'

/**
 * Render math content with LaTeX support
 * Converts $...$ and $$...$$ math expressions to rendered HTML
 * Also handles basic HTML content
 *
 * @param {string} content - The content to render
 * @returns {string} - Rendered HTML string
 */
export function renderMathContent(content) {
  if (!content) return ''

  let text = String(content)
    .replace(/\[\s*cite\s*:\s*\d+\s*\]/gi, '')
    .replace(/\s{2,}/g, ' ')
    .trim()

  // KaTeX configuration
  const katexConfig = {
    throwOnError: false,
    displayMode: false
  }

  // Render display math ($$...$$)
  text = text.replace(/\$\$([^$]+)\$\$/g, (match, math) => {
    try {
      return renderToString(math, { ...katexConfig, displayMode: true })
    } catch (e) {
      return match
    }
  })

  // Render inline math ($...$)
  text = text.replace(/\$([^$]+)\$/g, (match, math) => {
    try {
      return renderToString(math, katexConfig)
    } catch (e) {
      return match
    }
  })

  return text
}

export default { renderMathContent }
