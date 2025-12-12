// Composable for quiz state management
import { ref, computed } from 'vue';
import { quizApi, quizUtils } from '@/services/quizService';
import { useQuasar } from 'quasar';

export function useQuiz() {
    const $q = useQuasar();

    // State
    const quizzes = ref([]);
    const currentQuiz = ref(null);
    const loading = ref(false);
    const saving = ref(false);

    // Computed
    const activeQuizzes = computed(() =>
        quizzes.value.filter(q => q.status === 'active')
    );

    const draftQuizzes = computed(() =>
        quizzes.value.filter(q => q.status === 'draft')
    );

    const archivedQuizzes = computed(() =>
        quizzes.value.filter(q => q.status === 'archived')
    );

    // Methods
    const fetchQuizzes = async (params = {}) => {
        loading.value = true;
        try {
            quizzes.value = await quizApi.getQuizzes(params);
            return quizzes.value;
        } catch (error) {
            console.error('Failed to fetch quizzes:', error);
            $q.notify({
                type: 'negative',
                message: 'Failed to load quizzes',
                icon: 'error'
            });
            throw error;
        } finally {
            loading.value = false;
        }
    };

    const fetchQuiz = async (id) => {
        loading.value = true;
        try {
            currentQuiz.value = await quizApi.getQuiz(id);
            return currentQuiz.value;
        } catch (error) {
            console.error('Failed to fetch quiz:', error);
            $q.notify({
                type: 'negative',
                message: 'Failed to load quiz',
                icon: 'error'
            });
            throw error;
        } finally {
            loading.value = false;
        }
    };

    const createQuiz = async (data) => {
        // Validate
        const validation = quizUtils.validateQuiz(data);
        if (!validation.isValid) {
            $q.notify({
                type: 'warning',
                message: validation.errors[0],
                icon: 'warning'
            });
            return null;
        }

        saving.value = true;
        try {
            const quiz = await quizApi.createQuiz(data);
            quizzes.value.push(quiz);

            $q.notify({
                type: 'positive',
                message: 'Quiz created successfully',
                icon: 'check_circle'
            });

            return quiz;
        } catch (error) {
            console.error('Failed to create quiz:', error);
            $q.notify({
                type: 'negative',
                message: 'Failed to create quiz',
                icon: 'error'
            });
            throw error;
        } finally {
            saving.value = false;
        }
    };

    const updateQuiz = async (id, data) => {
        // Validate
        const validation = quizUtils.validateQuiz(data);
        if (!validation.isValid) {
            $q.notify({
                type: 'warning',
                message: validation.errors[0],
                icon: 'warning'
            });
            return null;
        }

        saving.value = true;
        try {
            const quiz = await quizApi.updateQuiz(id, data);

            // Update in list
            const index = quizzes.value.findIndex(q => q.id === id);
            if (index !== -1) {
                quizzes.value[index] = quiz;
            }

            // Update current
            if (currentQuiz.value?.id === id) {
                currentQuiz.value = quiz;
            }

            $q.notify({
                type: 'positive',
                message: 'Quiz updated successfully',
                icon: 'check_circle'
            });

            return quiz;
        } catch (error) {
            console.error('Failed to update quiz:', error);
            $q.notify({
                type: 'negative',
                message: 'Failed to update quiz',
                icon: 'error'
            });
            throw error;
        } finally {
            saving.value = false;
        }
    };

    const deleteQuiz = async (id) => {
        try {
            await quizApi.deleteQuiz(id);

            // Remove from list
            quizzes.value = quizzes.value.filter(q => q.id !== id);

            // Clear current if it's the deleted one
            if (currentQuiz.value?.id === id) {
                currentQuiz.value = null;
            }

            $q.notify({
                type: 'positive',
                message: 'Quiz deleted successfully',
                icon: 'delete'
            });

            return true;
        } catch (error) {
            console.error('Failed to delete quiz:', error);
            $q.notify({
                type: 'negative',
                message: 'Failed to delete quiz',
                icon: 'error'
            });
            throw error;
        }
    };

    const duplicateQuiz = async (id) => {
        try {
            const quiz = await quizApi.duplicateQuiz(id);
            quizzes.value.push(quiz);

            $q.notify({
                type: 'positive',
                message: 'Quiz duplicated successfully',
                icon: 'content_copy'
            });

            return quiz;
        } catch (error) {
            console.error('Failed to duplicate quiz:', error);
            $q.notify({
                type: 'negative',
                message: 'Failed to duplicate quiz',
                icon: 'error'
            });
            throw error;
        }
    };

    return {
        // State
        quizzes,
        currentQuiz,
        loading,
        saving,

        // Computed
        activeQuizzes,
        draftQuizzes,
        archivedQuizzes,

        // Methods
        fetchQuizzes,
        fetchQuiz,
        createQuiz,
        updateQuiz,
        deleteQuiz,
        duplicateQuiz
    };
}
