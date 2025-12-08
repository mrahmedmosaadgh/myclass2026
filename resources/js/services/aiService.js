import axios from 'axios';

/**
 * AI Service - Wrapper for AI API calls
 */
class AIService {
    /**
     * Send a completion request to the AI
     * @param {string} prompt - The user's prompt
     * @param {Object} options - Additional options
     * @param {Array} options.previousMessages - Previous conversation messages
     * @param {string} options.systemMessage - Custom system message
     * @param {number} options.temperature - Temperature (0-2)
     * @param {number} options.maxTokens - Max tokens to generate
     * @returns {Promise<Object>} AI response
     */
    async complete(prompt, options = {}) {
        try {
            const response = await axios.post('/api/ai/complete', {
                prompt,
                context: {
                    previousMessages: options.previousMessages || [],
                },
                systemMessage: options.systemMessage,
                temperature: options.temperature || 0.7,
                maxTokens: options.maxTokens || 2000,
                model: options.model || 'deepseek',
            });

            return {
                success: true,
                content: response.data.content,
                usage: response.data.usage,
            };
        } catch (error) {
            console.error('AI Service Error:', error);

            return {
                success: false,
                error: error.response?.data?.error || 'Failed to get AI response',
                message: error.response?.data?.message || error.message,
            };
        }
    }

    /**
     * Improve text using AI
     * @param {string} text - Text to improve
     * @returns {Promise<Object>} AI response
     */
    async improveText(text) {
        return this.complete(
            `Improve the following text for clarity, grammar, and style:\n\n${text}`,
            {
                systemMessage: 'You are an expert editor. Improve the text while maintaining its original meaning and tone.',
                temperature: 0.5,
            }
        );
    }

    /**
     * Generate similar math problems
     * @param {string} problem - Original problem
     * @param {number} count - Number of similar problems to generate
     * @returns {Promise<Object>} AI response
     */
    async generateSimilarProblems(problem, count = 3) {
        return this.complete(
            `Generate ${count} similar math problems to this one:\n\n${problem}\n\nProvide only the problems, one per line.`,
            {
                systemMessage: 'You are a math teacher creating practice problems. Generate similar problems with different numbers but the same concept.',
                temperature: 0.8,
            }
        );
    }

    /**
     * Explain a concept
     * @param {string} concept - Concept to explain
     * @param {string} level - Education level (elementary, middle, high)
     * @returns {Promise<Object>} AI response
     */
    async explainConcept(concept, level = 'middle') {
        return this.complete(
            `Explain "${concept}" in simple terms for ${level} school students.`,
            {
                systemMessage: 'You are an experienced teacher. Explain concepts clearly and engagingly for students.',
                temperature: 0.6,
            }
        );
    }
}

export default new AIService();
