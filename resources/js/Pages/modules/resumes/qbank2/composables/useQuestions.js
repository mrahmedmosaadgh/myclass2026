import { ref, reactive } from 'vue';
import { useQuasar } from 'quasar';
import resumeApi from '../resumeApi.js';

/**
 * Composable for managing questions state and operations
 * @returns {Object} Questions state and methods
 */
export function useQuestions() {
  const $q = useQuasar();
  
  // State
  const questions = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const categories = ref([]);
  const questionTypes = ref([]);
  
  // Reactive filters
  const filters = reactive({
    search: '',
    category: '',
    type: '',
    language: ''
  });

  /**
   * Load all questions with current filters
   */
  const loadQuestions = () => {
    loading.value = true;
    error.value = null;
    
    return resumeApi.getQuestions(filters)
      .then(data => {
        questions.value = data;
        return data;
      })
      .catch(err => {
        error.value = err.message || 'Failed to load questions';
        $q.notify({
          type: 'negative',
          message: 'Failed to load questions',
          position: 'top'
        });
        throw err;
      })
      .finally(() => {
        loading.value = false;
      });
  };

  /**
   * Create a new question
   * @param {Object} questionData - Question data
   */
  const createQuestion = (questionData) => {
    return resumeApi.createQuestion(questionData)
      .then(newQuestion => {
        questions.value.unshift(newQuestion);
        $q.notify({
          type: 'positive',
          message: 'Question created successfully',
          position: 'top'
        });
        return newQuestion;
      })
      .catch(err => {
        $q.notify({
          type: 'negative',
          message: err.response?.data?.message || 'Failed to create question',
          position: 'top'
        });
        throw err;
      });
  };

  /**
   * Update an existing question
   * @param {number} id - Question ID
   * @param {Object} questionData - Updated question data
   */
  const updateQuestion = (id, questionData) => {
    return resumeApi.updateQuestion(id, questionData)
      .then(updatedQuestion => {
        const index = questions.value.findIndex(q => q.id === id);
        if (index !== -1) {
          questions.value[index] = updatedQuestion;
        }
        $q.notify({
          type: 'positive',
          message: 'Question updated successfully',
          position: 'top'
        });
        return updatedQuestion;
      })
      .catch(err => {
        $q.notify({
          type: 'negative',
          message: err.response?.data?.message || 'Failed to update question',
          position: 'top'
        });
        throw err;
      });
  };

  /**
   * Delete a question with confirmation
   * @param {number} id - Question ID
   */
  const deleteQuestion = (id) => {
    return new Promise((resolve, reject) => {
      $q.dialog({
        title: 'Confirm Delete',
        message: 'Are you sure you want to delete this question? This action cannot be undone.',
        cancel: true,
        persistent: true,
        color: 'negative'
      }).onOk(() => {
        resumeApi.deleteQuestion(id)
          .then(() => {
            questions.value = questions.value.filter(q => q.id !== id);
            $q.notify({
              type: 'positive',
              message: 'Question deleted successfully',
              position: 'top'
            });
            resolve();
          })
          .catch(err => {
            $q.notify({
              type: 'negative',
              message: err.response?.data?.message || 'Failed to delete question',
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
   * Load question categories
   */
  const loadCategories = () => {
    return resumeApi.getCategories()
      .then(data => {
        categories.value = data;
        return data;
      })
      .catch(err => {
        console.warn('Failed to load categories, using defaults');
        categories.value = ['General', 'Technical', 'Behavioral', 'Experience', 'Education'];
        return categories.value;
      });
  };

  /**
   * Load question types
   */
  const loadQuestionTypes = () => {
    return resumeApi.getQuestionTypes()
      .then(data => {
        questionTypes.value = data;
        return data;
      })
      .catch(err => {
        console.warn('Failed to load question types, using defaults');
        questionTypes.value = ['text', 'textarea', 'select', 'multi-select', 'media', 'file'];
        return questionTypes.value;
      });
  };

  /**
   * Get a question by ID
   * @param {number} id - Question ID
   */
  const getQuestion = (id) => {
    return questions.value.find(q => q.id === id) || null;
  };

  /**
   * Clear filters
   */
  const clearFilters = () => {
    Object.keys(filters).forEach(key => {
      filters[key] = '';
    });
  };

  /**
   * Apply filters and reload questions
   */
  const applyFilters = () => {
    return loadQuestions();
  };

  return {
    // State
    questions,
    loading,
    error,
    categories,
    questionTypes,
    filters,
    
    // Methods
    loadQuestions,
    createQuestion,
    updateQuestion,
    deleteQuestion,
    loadCategories,
    loadQuestionTypes,
    getQuestion,
    clearFilters,
    applyFilters
  };
}
