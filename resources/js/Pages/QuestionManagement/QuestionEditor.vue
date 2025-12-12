<template>
  <Head :title="isEditMode ? 'Edit Question' : 'Create Question'" />
  <div class="question-editor-page">
    <div class="q-pa-md">
      <!-- Header -->
      <div class="row items-center q-mb-lg">
        <q-btn
          flat
          round
          dense
          icon="arrow_back"
          @click="goBack"
          class="q-mr-md"
        />
        <div>
          <h4 class="q-ma-none">{{ isEditMode ? 'Edit Question' : 'Create New Question' }}</h4>
          <p class="text-grey-7 q-mb-none">{{ isEditMode ? 'Update question details' : 'Add a new question to your bank' }}</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center q-py-xl">
        <q-spinner color="primary" size="50px" />
        <p class="text-grey-7 q-mt-md">Loading question...</p>
      </div>

      <!-- Form -->
      <q-card v-else flat bordered>
        <q-card-section class="q-pa-lg">
          <question-form
            v-model="questionData"
            :mode="isEditMode ? 'edit' : 'create'"
            @submit="saveQuestion"
            @cancel="goBack"
          />
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, Head, usePage } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';
import QuestionForm from '@/Components/QuestionBank/QuestionForm.vue';

const page = usePage();
const $q = useQuasar();

// Props from Inertia
const props = defineProps({
  question: {
    type: Object,
    default: null
  },
  questionId: {
    type: [String, Number],
    default: null
  }
});

// State
const questionData = ref(props.question || {});
const loading = ref(false);
const saving = ref(false);

// Computed
const isEditMode = computed(() => {
  return !!props.questionId && props.questionId !== 'new';
});

// Methods
const loadQuestion = async () => {
  if (!isEditMode.value || props.question) return;
  
  loading.value = true;
  try {
    const response = await axios.get(`/api/questions/${props.questionId}`);
    
    if (response.data.success) {
      questionData.value = response.data.data;
      
      // Ensure hints is an array
      if (!questionData.value.hints) {
        questionData.value.hints = [];
      }
      
      // Ensure explanation has the correct structure
      if (!questionData.value.explanation) {
        questionData.value.explanation = {
          text: '',
          revealed_after_attempt: true
        };
      }
    }
  } catch (error) {
    console.error('Failed to load question:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load question',
      caption: error.response?.data?.error?.message || error.message
    });
    goBack();
  } finally {
    loading.value = false;
  }
};

const saveQuestion = async (data) => {
  saving.value = true;
  
  try {
    // Prepare data for API
    const payload = {
      question_type_id: data.question_type_id,
      question_text: data.question_text,
      grade_id: data.grade_id || null,
      subject_id: data.subject_id || null,
      topic_id: data.topic_id || null,
      difficulty_level: data.difficulty_level || null,
      bloom_level: data.bloom_level || null,
      estimated_time_sec: data.estimated_time_sec || null,
      status: data.status || 'draft',
      hints: data.hints && data.hints.length > 0 ? data.hints.filter(h => h.trim()) : null,
      explanation: data.explanation && data.explanation.text ? data.explanation : null,
      options: data.options && data.options.length > 0 ? data.options.map((opt, idx) => ({
        id: opt.id > 1000000000000 ? undefined : opt.id, // Remove temp IDs
        option_key: opt.option_key,
        option_text: opt.option_text,
        is_correct: opt.is_correct,
        order_index: idx
      })) : undefined
    };
    
    let response;
    if (isEditMode.value) {
      response = await axios.put(`/api/questions/${props.questionId}`, payload);
    } else {
      response = await axios.post('/api/questions', payload);
    }
    
    if (response.data.success) {
      $q.notify({
        type: 'positive',
        message: isEditMode.value ? 'Question updated successfully' : 'Question created successfully'
      });
      
      router.visit('/questions');
    }
  } catch (error) {
    console.error('Failed to save question:', error);
    
    // Handle validation errors
    if (error.response?.status === 422) {
      const errors = error.response.data.error?.details || error.response.data.errors;
      const errorMessages = Object.values(errors).flat().join(', ');
      
      $q.notify({
        type: 'negative',
        message: 'Validation failed',
        caption: errorMessages,
        timeout: 5000
      });
    } else {
      $q.notify({
        type: 'negative',
        message: 'Failed to save question',
        caption: error.response?.data?.error?.message || error.message
      });
    }
  } finally {
    saving.value = false;
  }
};

const goBack = () => {
  router.visit('/questions');
};

// Load question on mount if editing
onMounted(() => {
  if (isEditMode.value) {
    loadQuestion();
  }
});
</script>

<style scoped lang="scss">
.question-editor-page {
  background: #f7fafc;
  min-height: 100vh;
}
</style>
