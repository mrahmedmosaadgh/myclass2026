<template>
  <q-dialog
    v-model="dialogModel"
    maximized
    transition-show="slide-up"
    transition-hide="slide-down"
  >
    <q-card class="question-details-dialog">
      <!-- Header -->
      <q-card-section class="row items-center q-pb-none bg-primary text-white">
        <div class="text-h6 flex-1">
          <q-icon name="quiz" class="q-mr-sm" />
          Question Details & Answers
        </div>
        <q-btn
          icon="close"
          flat
          round
          dense
          v-close-popup
          color="white"
        />
      </q-card-section>

      <!-- Question Info -->
      <q-card-section class="bg-grey-1">
        <div class="row q-gutter-md">
          <div class="col-12 col-md-8">
            <div class="row items-start q-gutter-sm q-mb-sm">
              <div class="col">
                <div class="text-h5">{{ question?.title }}</div>
              </div>
              <div class="col-auto">
                <TextToSpeechButton
                  :text="question?.title + (question?.description ? '. ' + question?.description : '')"
                  size="sm"
                  round
                  color="primary"
                />
              </div>
            </div>
            <div class="text-body2 text-grey-7 q-mb-md">{{ question?.description }}</div>
            
            <div class="row q-gutter-sm q-mb-md">
              <q-chip
                v-for="cat in question?.category"
                :key="cat"
                size="sm"
                color="blue-grey-2"
                text-color="blue-grey-8"
                icon="category"
              >
                {{ cat }}
              </q-chip>
            </div>
          </div>
          
          <div class="col-12 col-md-4">
            <q-card flat bordered class="q-pa-md">
              <div class="text-subtitle2 q-mb-sm">Question Info</div>
              <div class="q-mb-xs"><strong>Type:</strong> 
                <q-badge :color="getTypeColor(question?.type)" :label="question?.type" />
              </div>
              <div class="q-mb-xs"><strong>Language:</strong> {{ question?.language }}</div>
              <div class="q-mb-xs"><strong>Required:</strong> 
                <q-icon 
                  :name="question?.is_required ? 'check_circle' : 'cancel'" 
                  :color="question?.is_required ? 'positive' : 'negative'"
                />
              </div>
              <div><strong>Created:</strong> {{ formatDate(question?.created_at) }}</div>
            </q-card>
          </div>
        </div>
      </q-card-section>

      <!-- Action Buttons -->
      <q-card-section class="q-pt-none">
        <div class="row q-gutter-sm">
          <q-btn
            color="primary"
            icon="add"
            label="Add Answer"
            @click="showAddAnswerForm"
          />
          <q-btn
            color="secondary"
            icon="edit"
            label="Edit Question"
            @click="editQuestion"
          />
          <q-btn
            color="info"
            icon="refresh"
            label="Refresh"
            @click="loadAnswers"
          />
        </div>
      </q-card-section>

      <!-- Answers Section -->
      <q-card-section class="flex-1">
        <div class="text-h6 q-mb-md">
          <q-icon name="forum" class="q-mr-sm" />
          Answers ({{ answers.length }})
        </div>

        <!-- Loading State -->
        <div v-if="answersLoading" class="text-center q-pa-xl">
          <q-spinner-dots size="50px" color="primary" />
          <div class="q-mt-md">Loading answers...</div>
        </div>

        <!-- No Answers State -->
        <div v-else-if="answers.length === 0" class="text-center q-pa-xl">
          <q-icon name="sentiment_neutral" size="4em" color="grey-5" />
          <div class="text-h6 q-mt-md text-grey-6">No answers yet</div>
          <div class="text-body2 text-grey-5 q-mb-md">Be the first to answer this question!</div>
          <q-btn
            color="primary"
            icon="add"
            label="Add First Answer"
            @click="showAddAnswerForm"
          />
        </div>

        <!-- Answers List -->
        <div v-else class="answers-container">
          <AnswerCard
            v-for="answer in answers"
            :key="answer.id"
            :answer="answer"
            :question="question"
            @edit="editAnswer"
            @delete="deleteAnswer"
            @rate="rateAnswer"
            @comment="addComment"
            @refresh="loadAnswers"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- Add/Edit Answer Dialog -->
    <AnswerFormDialog
      v-model="answerFormVisible"
      :answer="selectedAnswer"
      :question-id="question?.id"
      @save="handleAnswerSave"
    />
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import AnswerCard from './AnswerCard.vue';
import AnswerFormDialog from './AnswerFormDialog.vue';
import TextToSpeechButton from './components/TextToSpeechButton.vue';
import resumeApi from './resumeApi.js';

// Props and Emits
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  question: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['update:modelValue', 'questionUpdated']);

// Composables
const $q = useQuasar();

// State
const dialogModel = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const answers = ref([]);
const answersLoading = ref(false);
const answerFormVisible = ref(false);
const selectedAnswer = ref(null);

// Methods
const loadAnswers = () => {
  if (!props.question?.id) return;
  
  answersLoading.value = true;
  resumeApi.getAnswers(props.question.id)
    .then(data => {
      answers.value = data;
    })
    .catch(error => {
      console.error('Error loading answers:', error);
      $q.notify({
        type: 'negative',
        message: 'Failed to load answers',
        position: 'top'
      });
    })
    .finally(() => {
      answersLoading.value = false;
    });
};

const showAddAnswerForm = () => {
  selectedAnswer.value = null;
  answerFormVisible.value = true;
};

const editAnswer = (answer) => {
  selectedAnswer.value = answer;
  answerFormVisible.value = true;
};

const editQuestion = () => {
  emit('questionUpdated', props.question);
  dialogModel.value = false;
};

const deleteAnswer = (answerId) => {
  $q.dialog({
    title: 'Confirm Delete',
    message: 'Are you sure you want to delete this answer?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    resumeApi.deleteAnswer(answerId)
      .then(() => {
        $q.notify({
          type: 'positive',
          message: 'Answer deleted successfully',
          position: 'top'
        });
        loadAnswers();
      })
      .catch(error => {
        console.error('Error deleting answer:', error);
        $q.notify({
          type: 'negative',
          message: 'Failed to delete answer',
          position: 'top'
        });
      });
  });
};

const rateAnswer = (answerId, rating) => {
  // TODO: Implement rating functionality
  console.log('Rate answer:', answerId, rating);
};

const addComment = (answerId) => {
  // TODO: Implement comment functionality
  console.log('Add comment to answer:', answerId);
};

const handleAnswerSave = () => {
  answerFormVisible.value = false;
  loadAnswers();
};

const getTypeColor = (type) => {
  const colors = {
    'text': 'blue',
    'textarea': 'green',
    'select': 'orange',
    'multi-select': 'purple',
    'media': 'pink',
    'file': 'brown'
  };
  return colors[type] || 'grey';
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString();
};

// Watchers
watch(() => props.question, (newQuestion) => {
  if (newQuestion && props.modelValue) {
    loadAnswers();
  }
}, { immediate: true });

watch(() => props.modelValue, (isVisible) => {
  if (isVisible && props.question) {
    loadAnswers();
  }
});
</script>

<style scoped>
.question-details-dialog {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.answers-container {
  max-height: calc(100vh - 400px);
  overflow-y: auto;
}

.q-card {
  border-radius: 8px;
}
</style>
