/**
 * User Context API Client
 * 
 * Provides a clean interface for calling user context API endpoints.
 * Handles authentication, error handling, and response formatting.
 * 
 * @author Education Management System
 * @version 1.0.0
 */

import axios from 'axios';

/**
 * Base API configuration
 */
const api = axios.create({
    baseURL: '/api/user-context',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    withCredentials: true
});

/**
 * Add CSRF token to requests
 */
api.interceptors.request.use((config) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token;
    }
    return config;
});

/**
 * Handle API responses and errors
 */
api.interceptors.response.use(
    (response) => response,
    (error) => {
        console.error('User Context API Error:', error);
        
        // Handle authentication errors
        if (error.response?.status === 401) {
            console.warn('User not authenticated, redirecting to login...');
            window.location.href = '/login';
        }
        
        return Promise.reject(error);
    }
);

/**
 * User Context API Client
 */
export const userContextApi = {
    /**
     * Get all user context segments
     * @returns {Promise<Object>} Complete user context
     */
    async getAllContext() {
        try {
            const response = await api.get('/');
            return response.data;
        } catch (error) {
            throw new Error(`Failed to get all context: ${error.response?.data?.message || error.message}`);
        }
    },

    /**
     * Get specific context segments
     * @param {Array<string>} segments - Array of segment names
     * @returns {Promise<Object>} Requested segments
     */
    async getSegments(segments = ['profile', 'permissions', 'school', 'classroom', 'schedule']) {
        try {
            const response = await api.post('/segments', { segments });
            return response.data;
        } catch (error) {
            throw new Error(`Failed to get segments: ${error.response?.data?.message || error.message}`);
        }
    },

    /**
     * Get user profile segment
     * @returns {Promise<Object>} User profile data
     */
    async getProfile() {
        try {
            const response = await api.get('/profile');
            return response.data;
        } catch (error) {
            throw new Error(`Failed to get profile: ${error.response?.data?.message || error.message}`);
        }
    },

    /**
     * Get user permissions segment
     * @returns {Promise<Object>} User permissions data
     */
    async getPermissions() {
        try {
            const response = await api.get('/permissions');
            return response.data;
        } catch (error) {
            throw new Error(`Failed to get permissions: ${error.response?.data?.message || error.message}`);
        }
    },

    /**
     * Get user school segment
     * @returns {Promise<Object>} User school data
     */
    async getSchool() {
        try {
            const response = await api.get('/school');
            return response.data;
        } catch (error) {
            throw new Error(`Failed to get school: ${error.response?.data?.message || error.message}`);
        }
    },

    /**
     * Get user classroom segment
     * @returns {Promise<Object>} User classroom data
     */
    async getClassroom() {
        try {
            const response = await api.get('/classroom');
            return response.data;
        } catch (error) {
            throw new Error(`Failed to get classroom: ${error.response?.data?.message || error.message}`);
        }
    },

    /**
     * Get user schedule segment
     * @returns {Promise<Object>} User schedule data
     */
    async getSchedule() {
        try {
            const response = await api.get('/schedule');
            return response.data;
        } catch (error) {
            throw new Error(`Failed to get schedule: ${error.response?.data?.message || error.message}`);
        }
    },

    /**
     * Update user context (clears cache and gets fresh data)
     * @returns {Promise<Object>} Updated context data
     */
    async updateContext() {
        try {
            const response = await api.post('/update');
            return response.data;
        } catch (error) {
            throw new Error(`Failed to update context: ${error.response?.data?.message || error.message}`);
        }
    },

    /**
     * Clear user context cache
     * @returns {Promise<Object>} Cache clear result
     */
    async clearCache() {
        try {
            const response = await api.delete('/cache');
            return response.data;
        } catch (error) {
            throw new Error(`Failed to clear cache: ${error.response?.data?.message || error.message}`);
        }
    },

    /**
     * Get cache status
     * @returns {Promise<Object>} Cache status information
     */
    async getCacheStatus() {
        try {
            const response = await api.get('/cache-status');
            return response.data;
        } catch (error) {
            throw new Error(`Failed to get cache status: ${error.response?.data?.message || error.message}`);
        }
    },

    /**
     * Health check for user context system
     * @returns {Promise<Object>} Health check results
     */
    async healthCheck() {
        try {
            const response = await api.get('/health');
            return response.data;
        } catch (error) {
            throw new Error(`Failed to perform health check: ${error.response?.data?.message || error.message}`);
        }
    }
};

/**
 * Convenience functions for common operations
 */
export const userContextHelpers = {
    /**
     * Refresh a specific segment
     * @param {string} segment - Segment name
     * @returns {Promise<Object>} Segment data
     */
    async refreshSegment(segment) {
        const validSegments = ['profile', 'permissions', 'school', 'classroom', 'schedule'];
        
        if (!validSegments.includes(segment)) {
            throw new Error(`Invalid segment: ${segment}. Valid segments: ${validSegments.join(', ')}`);
        }

        switch (segment) {
            case 'profile':
                return await userContextApi.getProfile();
            case 'permissions':
                return await userContextApi.getPermissions();
            case 'school':
                return await userContextApi.getSchool();
            case 'classroom':
                return await userContextApi.getClassroom();
            case 'schedule':
                return await userContextApi.getSchedule();
        }
    },

    /**
     * Refresh multiple segments
     * @param {Array<string>} segments - Array of segment names
     * @returns {Promise<Object>} Segments data
     */
    async refreshSegments(segments) {
        return await userContextApi.getSegments(segments);
    },

    /**
     * Check if user context system is healthy
     * @returns {Promise<boolean>} True if healthy
     */
    async isHealthy() {
        try {
            const health = await userContextApi.healthCheck();
            return health.data?.overall_status === 'healthy';
        } catch (error) {
            console.error('Health check failed:', error);
            return false;
        }
    },

    /**
     * Get cache health percentage
     * @returns {Promise<number>} Cache health percentage (0-100)
     */
    async getCacheHealth() {
        try {
            const status = await userContextApi.getCacheStatus();
            return status.data?.summary?.cache_health || 0;
        } catch (error) {
            console.error('Failed to get cache health:', error);
            return 0;
        }
    },

    /**
     * Force refresh all segments
     * @returns {Promise<Object>} All context data
     */
    async forceRefresh() {
        try {
            // Clear cache first
            await userContextApi.clearCache();
            
            // Get fresh data
            return await userContextApi.getAllContext();
        } catch (error) {
            console.error('Force refresh failed:', error);
            throw error;
        }
    }
};

/**
 * Export default API client
 */
export default userContextApi;
