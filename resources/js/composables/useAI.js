import { ref } from 'vue';
import aiService from '@/Services/aiService';

/**
 * Vue composable for AI functionality
 * @returns {Object} AI methods and state
 */
export function useAI() {
    const loading = ref(false);
    const error = ref(null);
    const lastResponse = ref('');

    /**
     * Ask AI a question
     * @param {string} prompt - The question/prompt
     * @param {Object} options - Additional options
     * @returns {Promise<string>} AI response content
     */
    const ask = async (prompt, options = {}) => {
        if (!prompt || !prompt.trim()) {
            error.value = 'Prompt cannot be empty';
            return '';
        }

        loading.value = true;
        error.value = null;

        try {
            const response = await aiService.complete(prompt, options);

            if (response.success) {
                lastResponse.value = response.content;
                return response.content;
            } else {
                error.value = response.error || 'Failed to get AI response';
                return '';
            }
        } catch (e) {
            error.value = e.message || 'An unexpected error occurred';
            return '';
        } finally {
            loading.value = false;
        }
    };

    /**
     * Improve text using AI
     * @param {string} text - Text to improve
     * @returns {Promise<string>} Improved text
     */
    const improveText = async (text) => {
        if (!text || !text.trim()) {
            error.value = 'Text cannot be empty';
            return '';
        }

        loading.value = true;
        error.value = null;

        try {
            const response = await aiService.improveText(text);

            if (response.success) {
                lastResponse.value = response.content;
                return response.content;
            } else {
                error.value = response.error || 'Failed to improve text';
                return '';
            }
        } catch (e) {
            error.value = e.message || 'An unexpected error occurred';
            return '';
        } finally {
            loading.value = false;
        }
    };

    /**
     * Generate similar problems
     * @param {string} problem - Original problem
     * @param {number} count - Number of problems to generate
     * @returns {Promise<string>} Generated problems
     */
    const generateSimilarProblems = async (problem, count = 3) => {
        if (!problem || !problem.trim()) {
            error.value = 'Problem cannot be empty';
            return '';
        }

        loading.value = true;
        error.value = null;

        try {
            const response = await aiService.generateSimilarProblems(problem, count);

            if (response.success) {
                lastResponse.value = response.content;
                return response.content;
            } else {
                error.value = response.error || 'Failed to generate problems';
                return '';
            }
        } catch (e) {
            error.value = e.message || 'An unexpected error occurred';
            return '';
        } finally {
            loading.value = false;
        }
    };

    /**
     * Clear error state
     */
    const clearError = () => {
        error.value = null;
    };

    return {
        // State
        loading,
        error,
        lastResponse,

        // Methods
        ask,
        improveText,
        generateSimilarProblems,
        clearError,
    };
}
