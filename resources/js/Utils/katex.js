import katex from 'katex';

/**
 * Renders math expressions in a string to HTML using KaTeX.
 * Supports:
 * - Block math: $$ ... $$ or \[ ... \]
 * - Inline math: $ ... $ or \( ... \)
 * 
 * @param {string} text - The text containing math expressions.
 * @returns {string} - The HTML string with rendered math.
 */
export const renderMath = (text) => {
    if (!text) return '';

    // If text is not a string, return it as is (or converted to string)
    if (typeof text !== 'string') return String(text);

    // Replace display math $$...$$ or \[...\]
    let renderedText = text.replace(/(\$\$|\\\[)([\s\S]*?)(\$\$|\\\])/g, (match, p1, p2) => {
        try {
            return katex.renderToString(p2, {
                displayMode: true,
                throwOnError: false
            });
        } catch (e) {
            console.error('KaTeX error:', e);
            return match;
        }
    });

    // Replace inline math $...$ or \(...\)
    // We use a regular function (not arrow) to access the arguments object
    renderedText = renderedText.replace(/(\$|\\\()([\s\S]*?)(\$|\\\))/g, function (match, start, content, end) {
        // basic check to ensure we didn't match empty or just signs if using $
        if (start === '$' && end === '$') {
            // Check if it's escaped
            const index = arguments[arguments.length - 2]; // offset
            if (index > 0 && renderedText[index - 1] === '\\') {
                return '$' + content + '$'; // It was escaped \$
            }
        }

        try {
            return katex.renderToString(content, {
                displayMode: false,
                throwOnError: false
            });
        } catch (e) {
            console.error('KaTeX error:', e);
            return match;
        }
    });

    return renderedText;
};
