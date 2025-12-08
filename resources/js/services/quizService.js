// Quiz API Service
// Centralized API calls for quiz management

import axios from 'axios';

const API_BASE = '/api';

export const quizApi = {
    // Quiz CRUD operations
    async getQuizzes(params = {}) {
        const response = await axios.get(`${API_BASE}/quizzes`, { params });
        return response.data;
    },

    async getQuiz(id) {
        const response = await axios.get(`${API_BASE}/quizzes/${id}`);
        return response.data;
    },

    async createQuiz(data) {
        const response = await axios.post(`${API_BASE}/quizzes`, data);
        return response.data;
    },

    async updateQuiz(id, data) {
        const response = await axios.put(`${API_BASE}/quizzes/${id}`, data);
        return response.data;
    },

    async deleteQuiz(id) {
        const response = await axios.delete(`${API_BASE}/quizzes/${id}`);
        return response.data;
    },

    async duplicateQuiz(id) {
        const response = await axios.post(`${API_BASE}/quizzes/${id}/duplicate`);
        return response.data;
    },

    async exportQuiz(id) {
        const response = await axios.get(`${API_BASE}/quizzes/${id}/export`, {
            responseType: 'blob'
        });
        return response.data;
    },

    // Quiz analytics
    async getQuizAnalytics(id) {
        const response = await axios.get(`${API_BASE}/quizzes/${id}/analytics`);
        return response.data;
    },

    // Quiz attempts
    async startQuizAttempt(quizId, userId) {
        const response = await axios.post(`${API_BASE}/quiz-attempts`, {
            quiz_id: quizId,
            user_id: userId
        });
        return response.data;
    },

    async submitQuizAnswer(attemptId, questionId, answerId) {
        const response = await axios.post(`${API_BASE}/quiz-attempts/${attemptId}/answers`, {
            question_id: questionId,
            selected_option_id: answerId
        });
        return response.data;
    },

    async completeQuizAttempt(attemptId) {
        const response = await axios.post(`${API_BASE}/quiz-attempts/${attemptId}/complete`);
        return response.data;
    },

    // Questions
    async getQuestions(params = {}) {
        const response = await axios.get(`${API_BASE}/questions`, { params });
        return response.data;
    },

    async getQuestion(id) {
        const response = await axios.get(`${API_BASE}/questions/${id}`);
        return response.data;
    },

    // Metadata
    async getQuestionTypes() {
        const response = await axios.get(`${API_BASE}/question-types`);
        return response.data;
    },

    async getTopics(params = {}) {
        const response = await axios.get(`${API_BASE}/topics`, { params });
        return response.data;
    },

    async getGrades() {
        const response = await axios.get(`${API_BASE}/grades`);
        return response.data;
    },

    async getSubjects(params = {}) {
        const response = await axios.get(`${API_BASE}/subjects`, { params });
        return response.data;
    }
};

// Quiz utilities
export const quizUtils = {
    // Calculate quiz statistics
    calculateStats(quiz) {
        const totalQuestions = quiz.questions?.length || 0;
        const estimatedTime = Math.ceil(totalQuestions * 1.5); // 1.5 min per question

        const difficulties = quiz.questions?.map(q => q.difficulty) || [];
        const difficultyMap = { 'Easy': 1, 'Medium': 2, 'Hard': 3 };
        const avgDifficulty = difficulties.length > 0
            ? difficulties.reduce((sum, d) => sum + (difficultyMap[d] || 2), 0) / difficulties.length
            : 2;

        let avgDifficultyLabel = 'Medium';
        if (avgDifficulty < 1.5) avgDifficultyLabel = 'Easy';
        else if (avgDifficulty > 2.5) avgDifficultyLabel = 'Hard';

        return {
            totalQuestions,
            estimatedTime,
            avgDifficulty: avgDifficultyLabel
        };
    },

    // Format time in seconds to readable string
    formatTime(seconds) {
        if (!seconds) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${String(secs).padStart(2, '0')}`;
    },

    // Get difficulty color
    getDifficultyColor(difficulty) {
        const colors = {
            'Easy': 'positive',
            'Medium': 'warning',
            'Hard': 'negative'
        };
        return colors[difficulty] || 'info';
    },

    // Get status color
    getStatusColor(status) {
        const colors = {
            'active': 'positive',
            'draft': 'warning',
            'archived': 'grey'
        };
        return colors[status] || 'grey';
    },

    // Truncate HTML text
    truncateHtml(html, maxLength) {
        if (!html) return '';
        const text = html.replace(/<[^>]*>/g, '');
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },

    // Shuffle array (Fisher-Yates)
    shuffleArray(array) {
        const shuffled = [...array];
        for (let i = shuffled.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }
        return shuffled;
    },

    // Calculate score percentage
    calculateScore(correctAnswers, totalQuestions) {
        if (totalQuestions === 0) return 0;
        return Math.round((correctAnswers / totalQuestions) * 100);
    },

    // Get grade letter from percentage
    getGradeLetter(percentage) {
        if (percentage >= 90) return 'A';
        if (percentage >= 80) return 'B';
        if (percentage >= 70) return 'C';
        if (percentage >= 60) return 'D';
        return 'F';
    },

    // Validate quiz data
    validateQuiz(quiz) {
        const errors = [];

        if (!quiz.name || quiz.name.trim() === '') {
            errors.push('Quiz name is required');
        }

        if (!quiz.questions || quiz.questions.length === 0) {
            errors.push('At least one question is required');
        }

        if (quiz.time_limit_minutes && quiz.time_limit_minutes < 1) {
            errors.push('Time limit must be at least 1 minute');
        }

        return {
            isValid: errors.length === 0,
            errors
        };
    }
};

export default {
    quizApi,
    quizUtils
};
