import { ref, reactive } from 'vue';
import { useQuasar } from 'quasar';
import resumeApi from '../resumeApi.js';

/**
 * Composable for managing answers state and operations
 * @returns {Object} Answers state and methods
 */
export function useAnswers() {
  const $q = useQuasar();
  
  // State - using reactive object to store answers by question ID
  const answersByQuestion = reactive({});
  const loadingStates = reactive({});
  const error = ref(null);

  /**
   * Load answers for a specific question
   * @param {number} questionId - Question ID
   */
  const loadAnswers = (questionId) => {
    loadingStates[questionId] = true;
    error.value = null;
    
    return resumeApi.getAnswers(questionId)
      .then(data => {
        answersByQuestion[questionId] = data;
        return data;
      })
      .catch(err => {
        error.value = err.message || 'Failed to load answers';
        $q.notify({
          type: 'negative',
          message: 'Failed to load answers',
          position: 'top'
        });
        throw err;
      })
      .finally(() => {
        loadingStates[questionId] = false;
      });
  };

  /**
   * Create a new answer for a question
   * @param {number} questionId - Question ID
   * @param {Object} answerData - Answer data
   */
  const createAnswer = (questionId, answerData) => {
    return resumeApi.createAnswer(questionId, answerData)
      .then(newAnswer => {
        if (!answersByQuestion[questionId]) {
          answersByQuestion[questionId] = [];
        }
        answersByQuestion[questionId].unshift(newAnswer);
        $q.notify({
          type: 'positive',
          message: 'Answer created successfully',
          position: 'top'
        });
        return newAnswer;
      })
      .catch(err => {
        $q.notify({
          type: 'negative',
          message: err.response?.data?.message || 'Failed to create answer',
          position: 'top'
        });
        throw err;
      });
  };

  /**
   * Update an existing answer
   * @param {number} answerId - Answer ID
   * @param {Object} answerData - Updated answer data
   * @param {number} questionId - Question ID (for updating local state)
   */
  const updateAnswer = (answerId, answerData, questionId) => {
    return resumeApi.updateAnswer(answerId, answerData)
      .then(updatedAnswer => {
        if (answersByQuestion[questionId]) {
          const index = answersByQuestion[questionId].findIndex(a => a.id === answerId);
          if (index !== -1) {
            answersByQuestion[questionId][index] = updatedAnswer;
          }
        }
        $q.notify({
          type: 'positive',
          message: 'Answer updated successfully',
          position: 'top'
        });
        return updatedAnswer;
      })
      .catch(err => {
        $q.notify({
          type: 'negative',
          message: err.response?.data?.message || 'Failed to update answer',
          position: 'top'
        });
        throw err;
      });
  };

  /**
   * Delete an answer with confirmation
   * @param {number} answerId - Answer ID
   * @param {number} questionId - Question ID (for updating local state)
   */
  const deleteAnswer = (answerId, questionId) => {
    return new Promise((resolve, reject) => {
      $q.dialog({
        title: 'Confirm Delete',
        message: 'Are you sure you want to delete this answer? This action cannot be undone.',
        cancel: true,
        persistent: true,
        color: 'negative'
      }).onOk(() => {
        resumeApi.deleteAnswer(answerId)
          .then(() => {
            if (answersByQuestion[questionId]) {
              answersByQuestion[questionId] = answersByQuestion[questionId].filter(a => a.id !== answerId);
            }
            $q.notify({
              type: 'positive',
              message: 'Answer deleted successfully',
              position: 'top'
            });
            resolve();
          })
          .catch(err => {
            $q.notify({
              type: 'negative',
              message: err.response?.data?.message || 'Failed to delete answer',
              position: 'top'
            });
            reject(err);
          });
      }).onCancel(() => {
        reject(new Error('Delete cancelled'));
      });
    });
  };

  /**
   * Upload media file
   * @param {File} file - File to upload
   */
  const uploadMedia = (file) => {
    return resumeApi.uploadMedia(file)
      .then(result => {
        $q.notify({
          type: 'positive',
          message: 'Media uploaded successfully',
          position: 'top'
        });
        return result;
      })
      .catch(err => {
        $q.notify({
          type: 'negative',
          message: err.response?.data?.message || 'Failed to upload media',
          position: 'top'
        });
        throw err;
      });
  };

  /**
   * Get answers for a specific question
   * @param {number} questionId - Question ID
   */
  const getAnswers = (questionId) => {
    return answersByQuestion[questionId] || [];
  };

  /**
   * Get loading state for a specific question
   * @param {number} questionId - Question ID
   */
  const isLoading = (questionId) => {
    return loadingStates[questionId] || false;
  };

  /**
   * Get an answer by ID from a specific question
   * @param {number} questionId - Question ID
   * @param {number} answerId - Answer ID
   */
  const getAnswer = (questionId, answerId) => {
    const answers = answersByQuestion[questionId] || [];
    return answers.find(a => a.id === answerId) || null;
  };

  /**
   * Clear answers for a specific question
   * @param {number} questionId - Question ID
   */
  const clearAnswers = (questionId) => {
    delete answersByQuestion[questionId];
    delete loadingStates[questionId];
  };

  /**
   * Clear all answers
   */
  const clearAllAnswers = () => {
    Object.keys(answersByQuestion).forEach(key => {
      delete answersByQuestion[key];
    });
    Object.keys(loadingStates).forEach(key => {
      delete loadingStates[key];
    });
  };

  /**
   * Get total answer count for a question
   * @param {number} questionId - Question ID
   */
  const getAnswerCount = (questionId) => {
    return (answersByQuestion[questionId] || []).length;
  };

  return {
    // State
    answersByQuestion,
    loadingStates,
    error,
    
    // Methods
    loadAnswers,
    createAnswer,
    updateAnswer,
    deleteAnswer,
    uploadMedia,
    getAnswers,
    isLoading,
    getAnswer,
    clearAnswers,
    clearAllAnswers,
    getAnswerCount
  };
}
