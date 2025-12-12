import axios from 'axios';

/**
 * Enhanced Resume API module with comprehensive CRUD operations
 * Uses .then() style promises as requested
 */
export default {
  // ==================== QUESTIONS API ====================
  
  /**
   * Fetch all questions with optional filters
   * @param {Object} filters - Optional filters for questions
   * @returns {Promise} Promise resolving to questions array
   */
  getQuestions(filters = {}) {
    const params = new URLSearchParams(filters);
    return axios.get(`/api/resume-questions?${params}`)
      .then(response => response.data)
      .catch(error => {
        console.error('Error fetching questions:', error);
        throw error;
      });
  },

  /**
   * Get a single question by ID
   * @param {number} id - Question ID
   * @returns {Promise} Promise resolving to question object
   */
  getQuestion(id) {
    return axios.get(`/api/resume-questions/${id}`)
      .then(response => response.data)
      .catch(error => {
        console.error('Error fetching question:', error);
        throw error;
      });
  },

  /**
   * Create a new question
   * @param {Object} payload - Question data
   * @returns {Promise} Promise resolving to created question
   */
  createQuestion(payload) {
    return axios.post('/api/resume-questions', payload)
      .then(response => response.data)
      .catch(error => {
        console.error('Error creating question:', error);
        throw error;
      });
  },

  /**
   * Update an existing question
   * @param {number} id - Question ID
   * @param {Object} payload - Updated question data
   * @returns {Promise} Promise resolving to updated question
   */
  updateQuestion(id, payload) {
    return axios.put(`/api/resume-questions/${id}`, payload)
      .then(response => response.data)
      .catch(error => {
        console.error('Error updating question:', error);
        throw error;
      });
  },

  /**
   * Delete a question
   * @param {number} id - Question ID
   * @returns {Promise} Promise resolving to deletion confirmation
   */
  deleteQuestion(id) {
    return axios.delete(`/api/resume-questions/${id}`)
      .then(response => response.data)
      .catch(error => {
        console.error('Error deleting question:', error);
        throw error;
      });
  },

  // ==================== ANSWERS API ====================

  /**
   * Fetch all answers for a specific question
   * @param {number} questionId - Question ID
   * @returns {Promise} Promise resolving to answers array
   */
  getAnswers(questionId) {
    return axios.get(`/api/resume-questions/${questionId}/answers`)
      .then(response => response.data)
      .catch(error => {
        console.error('Error fetching answers:', error);
        throw error;
      });
  },

  /**
   * Get a single answer by ID
   * @param {number} id - Answer ID
   * @returns {Promise} Promise resolving to answer object
   */
  getAnswer(id) {
    return axios.get(`/api/resume-answers/${id}`)
      .then(response => response.data)
      .catch(error => {
        console.error('Error fetching answer:', error);
        throw error;
      });
  },

  /**
   * Create a new answer for a question
   * @param {number} questionId - Question ID
   * @param {Object} payload - Answer data
   * @returns {Promise} Promise resolving to created answer
   */
  createAnswer(questionId, payload) {
    return axios.post(`/api/resume-questions/${questionId}/answers`, payload)
      .then(response => response.data)
      .catch(error => {
        console.error('Error creating answer:', error);
        throw error;
      });
  },

  /**
   * Update an existing answer
   * @param {number} id - Answer ID
   * @param {Object} payload - Updated answer data
   * @returns {Promise} Promise resolving to updated answer
   */
  updateAnswer(id, payload) {
    return axios.put(`/api/resume-answers/${id}`, payload)
      .then(response => response.data)
      .catch(error => {
        console.error('Error updating answer:', error);
        throw error;
      });
  },

  /**
   * Delete an answer
   * @param {number} id - Answer ID
   * @returns {Promise} Promise resolving to deletion confirmation
   */
  deleteAnswer(id) {
    return axios.delete(`/api/resume-answers/${id}`)
      .then(response => response.data)
      .catch(error => {
        console.error('Error deleting answer:', error);
        throw error;
      });
  },

  // ==================== UTILITY METHODS ====================

  /**
   * Upload media file
   * @param {File} file - File to upload
   * @returns {Promise} Promise resolving to upload result
   */
  uploadMedia(file) {
    const formData = new FormData();
    formData.append('file', file);
    
    return axios.post('/api/media/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
      .then(response => response.data)
      .catch(error => {
        console.error('Error uploading media:', error);
        throw error;
      });
  },

  /**
   * Get question categories
   * @returns {Promise} Promise resolving to categories array
   */
  getCategories() {
    return axios.get('/api/resume-question-categories')
      .then(response => response.data)
      .catch(error => {
        console.error('Error fetching categories:', error);
        // Return default categories if API fails
        return ['General', 'Technical', 'Behavioral', 'Experience', 'Education'];
      });
  },

  /**
   * Get question types
   * @returns {Promise} Promise resolving to types array
   */
  getQuestionTypes() {
    return axios.get('/api/resume-question-types')
      .then(response => response.data)
      .catch(error => {
        console.error('Error fetching question types:', error);
        // Return default types if API fails
        return ['text', 'textarea', 'select', 'multi-select', 'media', 'file'];
      });
  },

  // ===== COMMENTS API =====

  /**
   * Get comments for an answer
   * @param {number} answerId - Answer ID
   * @returns {Promise} Promise resolving to comments array
   */
  getComments(answerId) {
    return axios.get(`/api/answers/${answerId}/comments`)
      .then(response => response.data)
      .catch(error => {
        console.error('Error fetching comments:', error);
        throw error;
      });
  },

  /**
   * Create a new comment
   * @param {number} answerId - Answer ID
   * @param {Object} commentData - Comment data
   * @returns {Promise} Promise resolving to created comment
   */
  createComment(answerId, commentData) {
    return axios.post(`/api/answers/${answerId}/comments`, commentData)
      .then(response => response.data)
      .catch(error => {
        console.error('Error creating comment:', error);
        throw error;
      });
  },

  /**
   * Update a comment
   * @param {number} commentId - Comment ID
   * @param {Object} commentData - Updated comment data
   * @returns {Promise} Promise resolving to updated comment
   */
  updateComment(commentId, commentData) {
    return axios.put(`/api/comments/${commentId}`, commentData)
      .then(response => response.data)
      .catch(error => {
        console.error('Error updating comment:', error);
        throw error;
      });
  },

  /**
   * Delete a comment
   * @param {number} commentId - Comment ID
   * @returns {Promise} Promise resolving to success message
   */
  deleteComment(commentId) {
    return axios.delete(`/api/comments/${commentId}`)
      .then(response => response.data)
      .catch(error => {
        console.error('Error deleting comment:', error);
        throw error;
      });
  },

  // ===== RATINGS API =====

  /**
   * Rate an answer
   * @param {number} answerId - Answer ID
   * @param {number} rating - Rating value (1-5)
   * @returns {Promise} Promise resolving to rating data
   */
  rateAnswer(answerId, rating) {
    return axios.post(`/api/answers/${answerId}/rate`, { rating })
      .then(response => response.data)
      .catch(error => {
        console.error('Error rating answer:', error);
        throw error;
      });
  },

  /**
   * Get answer ratings
   * @param {number} answerId - Answer ID
   * @returns {Promise} Promise resolving to ratings data
   */
  getAnswerRatings(answerId) {
    return axios.get(`/api/answers/${answerId}/ratings`)
      .then(response => response.data)
      .catch(error => {
        console.error('Error fetching ratings:', error);
        throw error;
      });
  },

  // ===== LIKES API =====

  /**
   * Toggle like on answer
   * @param {number} answerId - Answer ID
   * @returns {Promise} Promise resolving to like status
   */
  toggleAnswerLike(answerId) {
    return axios.post(`/api/answers/${answerId}/like`)
      .then(response => response.data)
      .catch(error => {
        console.error('Error toggling like:', error);
        throw error;
      });
  },

  /**
   * Toggle like on comment
   * @param {number} commentId - Comment ID
   * @returns {Promise} Promise resolving to like status
   */
  toggleCommentLike(commentId) {
    return axios.post(`/api/comments/${commentId}/like`)
      .then(response => response.data)
      .catch(error => {
        console.error('Error toggling comment like:', error);
        throw error;
      });
  },

  // ===== MEDIA UPLOAD API =====

  /**
   * Upload voice note
   * @param {Blob} audioBlob - Audio blob data
   * @param {number} answerId - Answer ID (optional)
   * @returns {Promise} Promise resolving to upload response
   */
  uploadVoiceNote(audioBlob, answerId = null) {
    const formData = new FormData();
    // Use the correct file extension based on the blob type
    const fileExtension = audioBlob.type.includes('webm') ? 'webm' :
                         audioBlob.type.includes('mp3') ? 'mp3' :
                         audioBlob.type.includes('wav') ? 'wav' : 'webm';
    formData.append('voice_note', audioBlob, `voice-note.${fileExtension}`);
    if (answerId) {
      formData.append('answer_id', answerId);
    }

    return axios.post('/api/media/upload-voice', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
      .then(response => response.data)
      .catch(error => {
        console.error('Error uploading voice note:', error);
        throw error;
      });
  },

  /**
   * Upload file attachment
   * @param {File} file - File to upload
   * @param {number} answerId - Answer ID (optional)
   * @returns {Promise} Promise resolving to upload response
   */
  uploadAttachment(file, answerId = null) {
    const formData = new FormData();
    formData.append('attachment', file);
    if (answerId) {
      formData.append('answer_id', answerId);
    }

    return axios.post('/api/media/upload-attachment', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
      .then(response => response.data)
      .catch(error => {
        console.error('Error uploading attachment:', error);
        throw error;
      });
  },

  // ===== BOOKMARKS API =====

  /**
   * Toggle bookmark on answer
   * @param {number} answerId - Answer ID
   * @param {string} bookmarkType - Type of bookmark (favorite, important, reference)
   * @param {string} notes - Optional notes
   * @returns {Promise} Promise resolving to bookmark status
   */
  toggleAnswerBookmark(answerId, bookmarkType = 'favorite', notes = null) {
    return axios.post(`/api/answers/${answerId}/bookmark`, {
      bookmark_type: bookmarkType,
      notes: notes
    })
      .then(response => response.data)
      .catch(error => {
        console.error('Error toggling bookmark:', error);
        throw error;
      });
  },

  /**
   * Get user's bookmarks
   * @param {string} type - Optional bookmark type filter
   * @returns {Promise} Promise resolving to bookmarks array
   */
  getUserBookmarks(type = null) {
    const params = type ? { type } : {};
    return axios.get('/api/user/bookmarks', { params })
      .then(response => response.data)
      .catch(error => {
        console.error('Error fetching bookmarks:', error);
        throw error;
      });
  },

  // ===== REPORTS API =====

  /**
   * Report an answer
   * @param {number} answerId - Answer ID
   * @param {string} reportType - Type of report
   * @param {string} reason - Reason for report
   * @returns {Promise} Promise resolving to report response
   */
  reportAnswer(answerId, reportType, reason) {
    return axios.post(`/api/answers/${answerId}/report`, {
      report_type: reportType,
      reason: reason
    })
      .then(response => response.data)
      .catch(error => {
        console.error('Error reporting answer:', error);
        throw error;
      });
  },

  /**
   * Get reports (admin only)
   * @param {string} status - Report status filter
   * @returns {Promise} Promise resolving to reports array
   */
  getReports(status = 'pending') {
    return axios.get('/api/admin/reports', { params: { status } })
      .then(response => response.data)
      .catch(error => {
        console.error('Error fetching reports:', error);
        throw error;
      });
  },

  /**
   * Update report status (admin only)
   * @param {number} reportId - Report ID
   * @param {string} status - New status
   * @param {string} adminNotes - Admin notes
   * @returns {Promise} Promise resolving to update response
   */
  updateReportStatus(reportId, status, adminNotes = null) {
    return axios.put(`/api/admin/reports/${reportId}/status`, {
      status: status,
      admin_notes: adminNotes
    })
      .then(response => response.data)
      .catch(error => {
        console.error('Error updating report status:', error);
        throw error;
      });
  }
};
