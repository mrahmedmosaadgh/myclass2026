<template>
  <div class="simple-quiz-selector">
    <q-select
      :model-value="modelValue"
      @update:model-value="$emit('update:modelValue', $event)"
      :options="quizOptions"
      :loading="loading"
      outlined
      :label="label"
      :hint="hint"
      clearable
      option-value="id"
      option-label="name"
      emit-value
      map-options
      :rules="rules"
    >
      <template v-slot:prepend>
        <q-icon name="quiz" />
      </template>
      
      <template v-slot:option="scope">
        <q-item v-bind="scope.itemProps">
          <q-item-section avatar>
            <q-icon name="quiz" :color="getStatusColor(scope.opt.status)" />
          </q-item-section>
          
          <q-item-section>
            <q-item-label>{{ scope.opt.name }}</q-item-label>
            <q-item-label caption>
              {{ scope.opt.questions_count || 0 }} questions
              <span v-if="scope.opt.time_limit_minutes">
                • {{ scope.opt.time_limit_minutes }} min
              </span>
            </q-item-label>
          </q-item-section>
          
          <q-item-section side>
            <q-badge :color="getStatusColor(scope.opt.status)">
              {{ scope.opt.status }}
            </q-badge>
          </q-item-section>
        </q-item>
      </template>
      
      <template v-slot:append>
        <q-btn
          v-if="showCreateButton"
          flat
          dense
          round
          icon="add"
          size="sm"
          @click.stop="showCreateDialog = true"
        >
          <q-tooltip>Create New Quiz</q-tooltip>
        </q-btn>
      </template>
      
      <template v-slot:no-option>
        <q-item>
          <q-item-section class="text-grey">
            No quizzes available
          </q-item-section>
        </q-item>
      </template>
    </q-select>

    <!-- Quick Create Dialog -->
    <q-dialog v-model="showCreateDialog" persistent>
      <q-card style="min-width: 500px">
        <q-card-section class="row items-center bg-primary text-white">
          <div class="text-h6">Quick Create Quiz</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="q-pt-md">
          <q-input
            v-model="newQuizName"
            outlined
            label="Quiz Name"
            autofocus
            @keyup.enter="createQuickQuiz"
          />
          
          <q-input
            v-model="newQuizDescription"
            outlined
            type="textarea"
            label="Description (optional)"
            rows="2"
            class="q-mt-md"
          />
        </q-card-section>

        <q-card-actions align="right" class="q-px-md q-pb-md">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn
            unelevated
            color="primary"
            label="Create & Select"
            :loading="creating"
            :disable="!newQuizName"
            @click="createQuickQuiz"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useQuasar } from 'quasar';
import { quizApi } from '@/services/quizService';

const props = defineProps({
  modelValue: {
    type: Number,
    default: null
  },
  label: {
    type: String,
    default: 'Select Quiz'
  },
  hint: {
    type: String,
    default: ''
  },
  gradeId: {
    type: Number,
    default: null
  },
  subjectId: {
    type: Number,
    default: null
  },
  showCreateButton: {
    type: Boolean,
    default: true
  },
  rules: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue', 'quiz-created']);

const $q = useQuasar();

// State
const quizOptions = ref([]);
const loading = ref(false);
const showCreateDialog = ref(false);
const creating = ref(false);
const newQuizName = ref('');
const newQuizDescription = ref('');

// Methods
const fetchQuizzes = async () => {
  loading.value = true;
  try {
    const params = {
      status: 'active'
    };
    
    if (props.gradeId) params.grade_id = props.gradeId;
    if (props.subjectId) params.subject_id = props.subjectId;
    
    quizOptions.value = await quizApi.getQuizzes(params);
  } catch (error) {
    console.error('Failed to fetch quizzes:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load quizzes',
      icon: 'error'
    });
  } finally {
    loading.value = false;
  }
};

const createQuickQuiz = async () => {
  if (!newQuizName.value) return;
  
  creating.value = true;
  try {
    const quiz = await quizApi.createQuiz({
      name: newQuizName.value,
      description: newQuizDescription.value,
      grade_id: props.gradeId,
      subject_id: props.subjectId,
      status: 'draft'
    });
    
    // Add to options
    quizOptions.value.push(quiz);
    
    // Select the new quiz
    emit('update:modelValue', quiz.id);
    emit('quiz-created', quiz);
    
    // Close dialog
    showCreateDialog.value = false;
    newQuizName.value = '';
    newQuizDescription.value = '';
    
    $q.notify({
      type: 'positive',
      message: 'Quiz created successfully',
      icon: 'check_circle'
    });
  } catch (error) {
    console.error('Failed to create quiz:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to create quiz',
      icon: 'error'
    });
  } finally {
    creating.value = false;
  }
};

const getStatusColor = (status) => {
  const colors = {
    active: 'positive',
    draft: 'warning',
    archived: 'grey'
  };
  return colors[status] || 'grey';
};

// Watch for filter changes
watch([() => props.gradeId, () => props.subjectId], () => {
  fetchQuizzes();
});

// Lifecycle
onMounted(() => {
  fetchQuizzes();
});
</script>

<style scoped>
.simple-quiz-selector {
  width: 100%;
}
</style>
