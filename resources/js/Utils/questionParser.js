import katex from 'katex';
import DOMPurify from 'dompurify';

export const renderKaTeX = (text) => {
    if (!text) return ''; // Add this null check

    try {
        // First, handle markdown-style bold text
        let processed = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // Then handle LaTeX expressions
        processed = processed.replace(/\\\((.*?)\\\)/g, (match, latex) => {
            try {
                return katex.renderToString(latex.trim(), {
                    throwOnError: false,
                    displayMode: false,
                    strict: false
                });
            } catch (e) {
                console.warn('KaTeX rendering error:', e);
                return match;
            }
        });

        // Add line breaks
        processed = processed.replace(/\n/g, '<br>');

        return DOMPurify.sanitize(processed);
    } catch (error) {
        console.error('Error in renderKaTeX:', error);
        return text || ''; // Add null check here too
    }
};

export const parseQuestionText = (text) => {
    try {
        if (!text?.trim()) {
            throw new Error('Please provide question text');
        }

        // Extract the question body (everything before the first option)
        const bodyMatch = text.match(/(.*?)(?=\s*[A-D]\))/s);
        const body = bodyMatch ? bodyMatch[1].trim() : '';

        // Extract options using a more flexible regex that handles both formats:
        // "A) ..." and "**A)** ..."
        const optionRegex = /(?:^|\n)\s*([A-D]\))\s*(.*?)(?=(?:\n\s*[A-D]\)|$))/gs;
        const options = [];
        let match;

        while ((match = optionRegex.exec(text)) !== null) {
            options.push({
                option: match[2].trim(),
                feedback: '',
                isCorrect: false
            });
        }

        // Validate parsed data
        if (!body) {
            throw new Error('Could not extract question body');
        }
        if (options.length === 0) {
            throw new Error('Could not extract any options');
        }

        return {
            success: true,
            data: {
                body,
                options
            }
        };
    } catch (error) {
        return {
            success: false,
            error: error.message || 'Error parsing question. Please check the format.'
        };
    }
};

// Helper function to get both parsed and rendered versions
export const parseAndRenderQuestion = (text) => {
    const parsed = parseQuestionText(text);

    if (!parsed.success) {
        return parsed;
    }

    return {
        success: true,
        data: {
            ...parsed.data,
            renderedBody: renderKaTeX(parsed.data.body),
            renderedOptions: parsed.data.options.map(opt => ({
                ...opt,
                renderedOption: renderKaTeX(opt.option)
            }))
        }
    };
};

