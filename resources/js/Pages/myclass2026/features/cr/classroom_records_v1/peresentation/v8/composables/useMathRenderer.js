/**
 * Smart Math + Markdown Renderer
 * Handles: LaTeX (\\frac, x^2, \\sqrt), Markdown (**bold**, *italic*), all delimiters
 * Normalizes AI double-escaped backslashes
 * No external library dependency
 */

export function useMathRenderer() {
  /**
   * Main entry point: renders both Markdown and LaTeX math in text
   */
  function renderMath(text) {
    if (!text) return ''
    if (typeof text !== 'string') return String(text)

    // Step 1: Normalize AI double-escaping (\\frac → \frac)
    let html = normalizeEscapes(text)

    // Step 2: Escape HTML first to prevent XSS
    html = escapeHtml(html)

    // Step 3: Handle delimited LaTeX ($...$, $$...$$, \(...\))
    // Replace delimiters with rendered content (delimiters themselves are removed)
    html = html.replace(/\\\((.*?)\\\)/g, (_, inner) => `<span class="math-inline">${renderLaTeX(inner)}</span>`)
    html = html.replace(/\$\$(.*?)\$\$/g, (_, inner) => `<span class="math-display">${renderLaTeX(inner)}</span>`)
    html = html.replace(/\$(.*?)\$/g, (_, inner) => `<span class="math-inline">${renderLaTeX(inner)}</span>`)

    // Step 4: Handle plain LaTeX commands (no delimiters) still in text
    html = renderLaTeXCommands(html)

    // Step 5: Render Markdown (bold, italic) — do this last so math isn't broken
    html = renderMarkdown(html)

    return html
  }

  /**
   * Normalize double backslashes from AI JSON output
   * e.g., "Solve: $3 \\\\frac{1}{4}$" → "Solve: $3 \\frac{1}{4}$"
   */
  function normalizeEscapes(text) {
    // Replace \\ followed by a letter with \ + letter (LaTeX command)
    // But be careful: \\ in markdown means line break, so only do it for known commands
    return text.replace(/\\\\(frac|sqrt|times|div|pm|neq|leq|geq|infty|pi|alpha|beta|theta|sum|prod|int|left|right|left\{|\}|\[|\])/g, '\\$1')
  }

  /**
   * Render LaTeX content (inside delimiters)
   */
  function renderLaTeX(latex) {
    let result = latex.trim()

    // Mixed numbers: "3 \frac{1}{4}" → render fraction part
    // Whole numbers before \frac stay as-is

    // \frac{a}{b} → superscript a / subscript b
    result = result.replace(/\\frac\{([^}]+)\}\{([^}]+)\}/g, '<span style="display:inline-block;text-align:center;vertical-align:middle;font-size:0.85em;line-height:1"><span style="display:block;border-bottom:1px solid;padding:0 2px">$1</span><span style="display:block">$2</span></span>')

    // \sqrt{x}
    result = result.replace(/\\sqrt\{([^}]+)\}/g, '<span style="display:inline-flex;align-items:flex-start;gap:1px"><span style="font-size:1.1em">&radic;</span><span style="border-top:1.5px solid;padding:0 3px;padding-top:2px">$1</span></span>')

    // Superscripts: x^{abc} or x^2
    result = result.replace(/\^\{([^}]+)\}/g, '<sup>$1</sup>')
    result = result.replace(/\^([0-9a-zA-Z])/g, '<sup>$1</sup>')

    // Subscripts: x_{abc} or x_2
    result = result.replace(/_\{([^}]+)\}/g, '<sub>$1</sub>')
    result = result.replace(/_([0-9a-zA-Z])/g, '<sub>$1</sub>')

    // Greek & operators
    result = result.replace(/\\times/g, '&times;')
    result = result.replace(/\\div/g, '&divide;')
    result = result.replace(/\\pm/g, '&plusmn;')
    result = result.replace(/\\neq/g, '&ne;')
    result = result.replace(/\\leq/g, '&le;')
    result = result.replace(/\\geq/g, '&ge;')
    result = result.replace(/\\infty/g, '&infin;')
    result = result.replace(/\\pi/g, '&pi;')
    result = result.replace(/\\alpha/g, '&alpha;')
    result = result.replace(/\\beta/g, '&beta;')
    result = result.replace(/\\theta/g, '&theta;')
    result = result.replace(/\\sum/g, '&Sigma;')
    result = result.replace(/\\prod/g, '&Pi;')
    result = result.replace(/\\int/g, '&int;')

    return result
  }

  /**
   * Render plain LaTeX commands (not inside delimiters)
   * These are already HTML-escaped, so backslash is \
   */
  function renderLaTeXCommands(html) {
    // \frac{a}{b} — proper fraction bar
    html = html.replace(/\\frac\{([^}]+)\}\{([^}]+)\}/g, '<span style="display:inline-block;text-align:center;vertical-align:middle;font-size:0.85em;line-height:1"><span style="display:block;border-bottom:1px solid;padding:0 2px">$1</span><span style="display:block">$2</span></span>')

    // \sqrt{x}
    html = html.replace(/\\sqrt\{([^}]+)\}/g, '<span style="display:inline-flex;align-items:flex-start;gap:1px"><span style="font-size:1.1em">&radic;</span><span style="border-top:1.5px solid;padding:0 3px;padding-top:2px">$1</span></span>')

    // Superscripts: x^{n} or x^n (only when preceded by word char)
    html = html.replace(/([0-9a-zA-Z])\^\{([^}]+)\}/g, '$1<sup>$2</sup>')
    html = html.replace(/([0-9a-zA-Z])\^([0-9a-zA-Z])/g, '$1<sup>$2</sup>')

    // Subscripts: x_{n} or x_n
    html = html.replace(/([0-9a-zA-Z])_\{([^}]+)\}/g, '$1<sub>$2</sub>')
    html = html.replace(/([0-9a-zA-Z])_([0-9a-zA-Z])/g, '$1<sub>$2</sub>')

    // Greek & operators
    html = html.replace(/\\times/g, '&times;')
    html = html.replace(/\\div/g, '&divide;')
    html = html.replace(/\\pm/g, '&plusmn;')
    html = html.replace(/\\neq/g, '&ne;')
    html = html.replace(/\\leq/g, '&le;')
    html = html.replace(/\\geq/g, '&ge;')
    html = html.replace(/\\sqrt\{([^}]+)\}/g, '<span style="display:inline-flex;align-items:flex-start;gap:1px"><span style="font-size:1.1em">&radic;</span><span style="border-top:1.5px solid;padding:0 3px;padding-top:2px">$1</span></span>')
    html = html.replace(/\\infty/g, '&infin;')
    html = html.replace(/\\pi/g, '&pi;')
    html = html.replace(/\\alpha/g, '&alpha;')
    html = html.replace(/\\beta/g, '&beta;')
    html = html.replace(/\\theta/g, '&theta;')
    html = html.replace(/\\sum/g, '&Sigma;')
    html = html.replace(/\\prod/g, '&Pi;')
    html = html.replace(/\\int/g, '&int;')

    return html
  }

  /**
   * Render simple Markdown: **bold**, *italic*, ***bold+italic***
   * Processed AFTER LaTeX so math content doesn't get broken
   */
  function renderMarkdown(html) {
    // ***text*** → bold + italic
    html = html.replace(/\*\*\*(.+?)\*\*\*/g, '<strong><em>$1</em></strong>')
    // **text** → bold
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    // *text* → italic (but not inside <sup>/<sub> which are already processed)
    html = html.replace(/\*(.+?)\*/g, '<em>$1</em>')

    return html
  }

  function escapeHtml(text) {
    const div = document.createElement('div')
    div.textContent = text
    return div.innerHTML
  }

  return {
    renderMath,
    renderLaTeX
  }
}
