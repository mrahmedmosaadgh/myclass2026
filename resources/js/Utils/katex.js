import { reactive } from 'vue';

// Global reactive cache for rendered math
const mathCache = reactive({});
let katexPromise = null;
let katexLib = null;

// Helper to load Katex once
const loadKatex = async () => {
    if (katexLib) return katexLib;
    if (katexPromise) return katexPromise;

    katexPromise = Promise.all([
        import('katex'),
        import('katex/dist/katex.min.css')
    ]).then(([module]) => {
        katexLib = module.default;
        return katexLib;
    }).catch(err => {
        console.error('Failed to load Katex', err);
        katexPromise = null;
        return null;
    });

    return katexPromise;
};

/**
 * Renders math expressions in a string to HTML using KaTeX.
 * Uses a reactive cache to support synchronous usage while lazy loading.
 * 
 * @param {string} text - The text containing math expressions.
 * @returns {string} - The HTML string with rendered math (or original text if loading).
 */
export const renderMath = (text) => {
    if (!text) return '';
    if (typeof text !== 'string') return String(text);

    // If already cached, return result
    if (mathCache[text]) return mathCache[text];

    // Quick check if text contains math delimiters
    if (!text.match(/(\$\$|\\\[|\$|\\\()/) && !text.includes('$$') && !text.includes('\\[')) {
        return text;
    }

    // Set temporary value (original text) to avoid loops if needed, 
    // but here we just return text if not in cache.
    // However, we need to trigger the load.

    if (!katexLib) {
        loadKatex().then(() => {
            // Trigger reactivity by updating cache for this key
            // We need to re-run the render logic once loaded
            const rendered = performRender(text, katexLib);
            mathCache[text] = rendered;
        });
        // Return placeholder (original text) while loading
        return text;
    }

    // If loaded but not in cache, render and cache
    const rendered = performRender(text, katexLib);
    mathCache[text] = rendered;
    return rendered;
};

// Actual rendering logic detached from export
const performRender = (text, katex) => {
    if (!katex) return text;

    let renderedText = text;

    // Replace display math $$...$$ or \[...\]
    renderedText = renderedText.replace(/(\$\$|\\\[)([\s\S]*?)(\$\$|\\\])/g, (match, p1, p2) => {
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
    // Fixed regex to properly handle consecutive inline math expressions
    renderedText = renderedText.replace(/\$(.*?)\$|\\\((.*?)\\\)/g, function (match, content1, content2) {
        const content = content1 || content2;  // Use content from either capture group
    
        // Check if this $...$ pair is part of a $$...$$ sequence
        if (match.startsWith('$') && match.endsWith('$')) {
            // Verify this isn't part of a double-dollar sequence by checking context
            const matchStart = arguments[arguments.length - 2];
            const prevChar = matchStart > 0 ? renderedText.charAt(matchStart - 1) : '';
            const nextChar = matchStart + match.length < renderedText.length ? renderedText.charAt(matchStart + match.length) : '';
            
            if ((prevChar === '$' || nextChar === '$') && !content.startsWith('$') && !content.endsWith('$')) {
                // This is likely part of $$...$$, so return original match to be handled by the display math regex
                return match;
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

