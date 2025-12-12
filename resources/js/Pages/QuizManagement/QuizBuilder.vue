<template>
  <div class="quiz-builder">
    <Head :title="quiz.id ? 'Edit Quiz' : 'Create Quiz'" />
    
    <div class="quiz-builder__container">
      <!-- Navigation -->
      <QuizNavigation role="teacher" />

      <!-- Header -->
      <div class="quiz-builder__header q-mb-md">
        <div class="row items-center justify-between">
          <div class="row items-center q-gutter-x-md">
            <q-btn
              flat
              round
              color="primary"
              icon="arrow_back"
              class="bg-white shadow-1"
              @click="router.visit('/quizzes')"
            />
            <div>
              <h1 class="text-h4 text-weight-bold text-primary q-my-none">
                {{ quiz.id ? 'Edit Quiz' : 'Create New Quiz' }}
              </h1>
              <p class="text-subtitle1 text-grey-7 q-my-none">
                {{ selectedQuestions.length }} questions selected
              </p>
            </div>
          </div>

          <div class="row q-gutter-x-sm">
            <q-btn
              flat
              rounded
              color="primary"
              label="Preview"
              icon="visibility"
              class="bg-white text-weight-bold shadow-1"
              @click="showPreview = true"
            />
            <q-btn
              push
              rounded
              color="secondary"
              label="Save Quiz"
              icon="save"
              class="text-weight-bold"
              @click="saveQuiz"
              :loading="saving"
            />
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="row q-col-gutter-lg">
        <!-- Left Panel: Question Pool -->
        <div class="col-12 col-md-3">
          <q-card class="rounded-xl shadow-2 full-height bg-white">
            <q-card-section class="bg-blue-1 text-primary">
              <div class="row items-center justify-between">
                <div class="text-h6 text-weight-bold">Question Pool</div>
                <q-btn flat round dense icon="refresh" @click="fetchQuestions">
                  <q-tooltip>Refresh</q-tooltip>
                </q-btn>
              </div>
            </q-card-section>

            <q-card-section class="q-pa-md q-gutter-y-md">
              <q-input
                v-model="poolSearch"
                outlined
                dense
                rounded
                placeholder="Search questions..."
                bg-color="grey-1"
              >
                <template v-slot:prepend>
                  <q-icon name="search" color="primary" />
                </template>
              </q-input>

              <div class="row q-col-gutter-sm">
                <div class="col-6">
                  <q-select
                    v-model="poolTypeFilter"
                    outlined
                    dense
                    rounded
                    :options="questionTypes"
                    label="Type"
                    option-label="name"
                    option-value="id"
                    bg-color="grey-1"
                    behavior="menu"
                  />
                </div>
                <div class="col-6">
                  <q-select
                    v-model="poolDifficultyFilter"
                    outlined
                    dense
                    rounded
                    :options="['Easy', 'Medium', 'Hard']"
                    label="Difficulty"
                    bg-color="grey-1"
                    behavior="menu"
                  />
                </div>
              </div>
            </q-card-section>

            <q-separator />

            <q-card-section class="quiz-builder__pool-list scroll" style="height: calc(100vh - 350px)">
              <div v-if="loadingQuestions" class="row justify-center q-pa-lg">
                <q-spinner-dots color="primary" size="40px" />
              </div>

              <div v-else-if="filteredPoolQuestions.length === 0" class="column items-center justify-center text-grey-5 q-pa-xl">
                <q-icon name="sentiment_dissatisfied" size="48px" />
                <p class="q-mt-sm">No questions found</p>
              </div>

              <div v-else class="q-gutter-y-sm">
                <question-card
                  v-for="question in filteredPoolQuestions"
                  :key="`pool-${question.id}`"
                  :question="question"
                  :show-remove="false"
                  compact
                  class="cursor-pointer hover-scale"
                  @click="addQuestion(question)"
                  draggable="true"
                  @dragstart="handleDragStart($event, question)"
                />
              </div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Center Panel: Quiz Canvas -->
        <div class="col-12 col-md-6">
          <q-card class="rounded-xl shadow-3 full-height bg-grey-1">
            <q-card-section class="bg-white text-primary rounded-borders-top">
              <div class="row items-center justify-between">
                <div class="text-h6 text-weight-bold">
                  <q-icon name="edit_document" class="q-mr-sm" />
                  Quiz Questions
                </div>
                <div class="row q-gutter-x-sm">
                  <q-btn
                    v-if="selectedQuestions.length > 0"
                    flat
                    round
                    dense
                    color="negative"
                    icon="delete_sweep"
                    @click="clearAllQuestions"
                  >
                    <q-tooltip>Clear All</q-tooltip>
                  </q-btn>
                  <q-btn
                    v-if="selectedQuestions.length > 1"
                    flat
                    round
                    dense
                    color="secondary"
                    icon="shuffle"
                    @click="shuffleQuestions"
                  >
                    <q-tooltip>Shuffle</q-tooltip>
                  </q-btn>
                </div>
              </div>
            </q-card-section>

            <q-card-section
              class="quiz-builder__canvas-area scroll q-pa-md"
              style="height: calc(100vh - 280px)"
              :class="{ 'bg-blue-1': isDragOver }"
              @drop="handleDrop"
              @dragover.prevent="isDragOver = true"
              @dragleave="isDragOver = false"
            >
              <div v-if="selectedQuestions.length === 0" class="column items-center justify-center full-height text-grey-5">
                <q-icon name="add_circle" size="64px" class="q-mb-md opacity-50" />
                <h5 class="q-my-none text-weight-bold">Your quiz is empty!</h5>
                <p>Drag questions here or click them from the pool</p>
              </div>

              <draggable
                v-else
                v-model="selectedQuestions"
                item-key="id"
                class="q-gutter-y-md"
                handle=".drag-handle"
                @start="isDragging = true"
                @end="isDragging = false"
              >
                <template #item="{ element, index }">
                  <div class="row items-start no-wrap q-gutter-x-sm animate-pop">
                    <div class="column items-center q-pt-sm">
                      <q-badge color="primary" rounded class="text-weight-bold shadow-1">
                        {{ index + 1 }}
                      </q-badge>
                      <q-icon name="drag_indicator" class="drag-handle cursor-move text-grey-5 q-mt-xs" size="20px" />
                    </div>
                    
                    <question-card
                      :question="element"
                      class="col"
                      :show-remove="true"
                      @preview="previewQuestion"
                      @remove="removeQuestion(index)"
                    />
                  </div>
                </template>
              </draggable>
            </q-card-section>
          </q-card>
        </div>

        <!-- Right Panel: Settings -->
        <div class="col-12 col-md-3">
          <q-card class="rounded-xl shadow-2 bg-white">
            <q-card-section class="bg-purple-1 text-purple-9">
              <div class="text-h6 text-weight-bold">Quiz Settings</div>
            </q-card-section>

            <q-card-section class="q-gutter-y-md">
              <q-input
                v-model="quiz.name"
                outlined
                rounded
                label="Quiz Name"
                :rules="[val => !!val || 'Required']"
                bg-color="grey-1"
              >
                <template v-slot:prepend>
                  <q-icon name="title" color="purple" />
                </template>
              </q-input>

              <q-input
                v-model="quiz.description"
                outlined
                rounded
                type="textarea"
                label="Description"
                rows="3"
                bg-color="grey-1"
              />

              <div class="row q-col-gutter-sm">
                <div class="col-6">
                  <q-input
                    v-model.number="quiz.time_limit_minutes"
                    outlined
                    rounded
                    type="number"
                    label="Time (min)"
                    dense
                    bg-color="grey-1"
                  />
                </div>
                <div class="col-6">
                  <q-select
                    v-model="quiz.status"
                    outlined
                    rounded
                    dense
                    :options="['draft', 'active', 'archived']"
                    label="Status"
                    bg-color="grey-1"
                    behavior="menu"
                  />
                </div>
              </div>

              <q-separator />

              <!-- Options Menu -->
              <q-expansion-item
                icon="settings"
                label="Advanced Options"
                header-class="text-weight-bold text-grey-8"
                expand-icon-class="text-grey-6"
              >
                <q-card>
                  <q-card-section class="q-gutter-y-sm">
                    <q-toggle
                      v-model="quiz.shuffle_questions"
                      label="Shuffle Questions"
                      color="purple"
                    />
                    <q-toggle
                      v-model="quiz.shuffle_options"
                      label="Shuffle Answers"
                      color="purple"
                    />
                    <q-toggle
                      v-model="quiz.allow_review"
                      label="Allow Review"
                      color="purple"
                    />
                  </q-card-section>
                </q-card>
              </q-expansion-item>

              <q-separator />

              <!-- Stats -->
              <div class="row q-col-gutter-sm">
                <div class="col-4 text-center">
                  <div class="text-h6 text-weight-bold text-primary">{{ selectedQuestions.length }}</div>
                  <div class="text-caption text-grey-6">Questions</div>
                </div>
                <div class="col-4 text-center">
                  <div class="text-h6 text-weight-bold text-orange">{{ estimatedTime }}</div>
                  <div class="text-caption text-grey-6">Est. Time</div>
                </div>
                <div class="col-4 text-center">
                  <div class="text-h6 text-weight-bold text-secondary">{{ averageDifficulty }}</div>
                  <div class="text-caption text-grey-6">Difficulty</div>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Preview Dialog -->
    <q-dialog v-model="showPreview" maximized transition-show="slide-up" transition-hide="slide-down">
      <q-card class="bg-grey-1">
        <q-toolbar class="bg-white text-primary shadow-1">
          <q-btn flat round dense icon="close" v-close-popup />
          <q-toolbar-title class="text-weight-bold">Quiz Preview</q-toolbar-title>
        </q-toolbar>

        <q-card-section class="row justify-center q-pa-lg">
          <div class="col-12 col-md-8">
            <q-card class="rounded-xl shadow-2 q-mb-lg">
              <q-card-section class="text-center q-pa-xl">
                <h2 class="text-h3 text-weight-bold text-primary q-my-none">{{ quiz.name }}</h2>
                <p class="text-h6 text-grey-7 q-mt-md">{{ quiz.description }}</p>
                <div class="row justify-center q-gutter-x-lg q-mt-lg">
                  <q-chip icon="quiz" color="blue-1" text-color="blue-9" size="lg">
                    {{ selectedQuestions.length }} Questions
                  </q-chip>
                  <q-chip icon="schedule" color="orange-1" text-color="orange-9" size="lg" v-if="quiz.time_limit_minutes">
                    {{ quiz.time_limit_minutes }} Minutes
                  </q-chip>
                </div>
              </q-card-section>
            </q-card>

            <div v-for="(question, index) in selectedQuestions" :key="index" class="q-mb-md">
              <q-card class="rounded-xl shadow-1">
                <q-card-section>
                  <div class="row items-center q-mb-md">
                    <q-badge color="primary" rounded class="q-mr-sm text-subtitle2">Q{{ index + 1 }}</q-badge>
                    <div class="text-h6" v-html="question.question_text"></div>
                  </div>
                  
                  <div class="q-gutter-y-sm">
                    <div
                      v-for="option in question.options"
                      :key="option.id"
                      class="rounded-borders q-pa-md"
                      :class="option.is_correct ? 'bg-green-1 text-green-9 border-green' : 'bg-grey-1'"
                      style="border: 1px solid transparent"
                    >
                      <div class="row items-center">
                        <div class="col">{{ option.option_text }}</div>
                        <q-icon v-if="option.is_correct" name="check_circle" color="positive" size="sm" />
                      </div>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Question Preview Dialog -->
    <q-dialog v-model="showQuestionPreview">
      <q-card style="min-width: 600px" class="rounded-xl">
        <q-card-section class="row items-center bg-primary text-white">
          <div class="text-h6">Question Preview</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section v-if="previewingQuestion" class="q-pa-lg">
          <div class="text-h6 q-mb-md" v-html="previewingQuestion.question_text"></div>
          
          <div class="q-gutter-y-sm">
            <div
              v-for="option in previewingQuestion.options"
              :key="option.id"
              class="rounded-borders q-pa-md"
              :class="option.is_correct ? 'bg-green-1 text-green-9' : 'bg-grey-1'"
            >
              <div class="row items-center">
                <div class="col">{{ option.option_text }}</div>
                <q-icon v-if="option.is_correct" name="check_circle" color="positive" />
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';
import draggable from 'vuedraggable';
import QuestionCard from '@/Components/Quiz/QuestionCard.vue';
import QuizNavigation from '@/Components/Quiz/QuizNavigation.vue';

const $q = useQuasar();
const props = defineProps({
  quizId: [Number, String]
});

// State
const quiz = ref({
  name: '',
  description: '',
  time_limit_minutes: null,
  status: 'draft',
  shuffle_questions: false,
  shuffle_options: false,
  allow_review: true
});

const poolQuestions = ref([]);
const selectedQuestions = ref([]);
const questionTypes = ref([]);
const topics = ref([]);

const loadingQuestions = ref(false);
const saving = ref(false);
const isDragging = ref(false);
const isDragOver = ref(false);

// Filters
const poolSearch = ref('');
const poolTypeFilter = ref(null);
const poolDifficultyFilter = ref(null);
const poolTopicFilter = ref(null);

// Dialogs
const showPreview = ref(false);
const showQuestionPreview = ref(false);
const previewingQuestion = ref(null);

// Computed
const filteredPoolQuestions = computed(() => {
  let result = [...poolQuestions.value];
  
  // Exclude already selected questions
  const selectedIds = selectedQuestions.value.map(q => q.id);
  result = result.filter(q => !selectedIds.includes(q.id));
  
  // Search
  if (poolSearch.value) {
    const query = poolSearch.value.toLowerCase();
    result = result.filter(q =>
      q.question_text.toLowerCase().includes(query)
    );
  }
  
  // Type filter
  if (poolTypeFilter.value) {
    result = result.filter(q => q.question_type_id === poolTypeFilter.value.id);
  }
  
  // Difficulty filter
  if (poolDifficultyFilter.value) {
    result = result.filter(q => q.difficulty === poolDifficultyFilter.value);
  }
  
  // Topic filter
  if (poolTopicFilter.value) {
    result = result.filter(q => q.topic_id === poolTopicFilter.value.id);
  }
  
  return result;
});

const estimatedTime = computed(() => {
  // Estimate 1.5 minutes per question
  const minutes = Math.ceil(selectedQuestions.value.length * 1.5);
  return `${minutes} min`;
});

const averageDifficulty = computed(() => {
  if (selectedQuestions.value.length === 0) return 'N/A';
  
  const difficultyMap = { 'Easy': 1, 'Medium': 2, 'Hard': 3 };
  const sum = selectedQuestions.value.reduce((acc, q) => {
    return acc + (difficultyMap[q.difficulty] || 2);
  }, 0);
  
  const avg = sum / selectedQuestions.value.length;
  
  if (avg < 1.5) return 'Easy';
  if (avg < 2.5) return 'Medium';
  return 'Hard';
});

// Methods
const fetchQuestions = async () => {
  loadingQuestions.value = true;
  try {
    const response = await axios.get('/api/questions', {
      params: {
        grade_level_id: quiz.value.grade_id,
        subject_id: quiz.value.subject_id,
        status: 'active',
        per_page: 100 // Get more questions for the pool
      }
    });
    
    // Handle the response structure: { success: true, data: { data: [...], ...pagination } }
    if (response.data.success && response.data.data) {
      poolQuestions.value = response.data.data.data || [];
    } else {
      poolQuestions.value = [];
    }
  } catch (error) {
    console.error('Failed to fetch questions:', error);
    poolQuestions.value = [];
    $q.notify({
      type: 'negative',
      message: 'Failed to load questions',
      icon: 'error'
    });
  } finally {
    loadingQuestions.value = false;
  }
};

const fetchMetadata = async () => {
  try {
    const [typesRes, topicsRes] = await Promise.all([
      axios.get('/api/question-types'),
      axios.get('/api/topics')
    ]);
    questionTypes.value = typesRes.data;
    topics.value = topicsRes.data;
  } catch (error) {
    console.error('Failed to fetch metadata:', error);
  }
};

const loadQuiz = async () => {
  if (!props.quizId) return;
  
  try {
    const response = await axios.get(`/api/quizzes/${props.quizId}`);
    quiz.value = response.data;
    selectedQuestions.value = response.data.questions || [];
  } catch (error) {
    console.error('Failed to load quiz:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load quiz',
      icon: 'error'
    });
  }
};

const addQuestion = (question) => {
  if (!selectedQuestions.value.find(q => q.id === question.id)) {
    selectedQuestions.value.push({ ...question });
  }
};

const removeQuestion = (index) => {
  selectedQuestions.value.splice(index, 1);
};

const clearAllQuestions = () => {
  $q.dialog({
    title: 'Clear All Questions',
    message: 'Are you sure you want to remove all questions from this quiz?',
    cancel: true,
    persistent: true,
    class: 'rounded-xl'
  }).onOk(() => {
    selectedQuestions.value = [];
  });
};

const shuffleQuestions = () => {
  selectedQuestions.value = selectedQuestions.value
    .map(value => ({ value, sort: Math.random() }))
    .sort((a, b) => a.sort - b.sort)
    .map(({ value }) => value);
};

const handleDragStart = (event, question) => {
  event.dataTransfer.effectAllowed = 'copy';
  event.dataTransfer.setData('question', JSON.stringify(question));
};

const handleDrop = (event) => {
  event.preventDefault();
  isDragOver.value = false;
  
  try {
    const questionData = event.dataTransfer.getData('question');
    if (questionData) {
      const question = JSON.parse(questionData);
      addQuestion(question);
    }
  } catch (error) {
    console.error('Failed to handle drop:', error);
  }
};

const previewQuestion = (question) => {
  previewingQuestion.value = question;
  showQuestionPreview.value = true;
};

const saveQuiz = async () => {
  if (!quiz.value.name) {
    $q.notify({
      type: 'warning',
      message: 'Please enter a quiz name',
      icon: 'warning'
    });
    return;
  }
  
  if (selectedQuestions.value.length === 0) {
    $q.notify({
      type: 'warning',
      message: 'Please add at least one question',
      icon: 'warning'
    });
    return;
  }
  
  saving.value = true;
  try {
    const data = {
      ...quiz.value,
      question_ids: selectedQuestions.value.map(q => q.id)
    };
    
    if (props.quizId) {
      await axios.put(`/api/quizzes/${props.quizId}`, data);
      $q.notify({
        type: 'positive',
        message: 'Quiz updated successfully',
        icon: 'check_circle'
      });
    } else {
      await axios.post('/api/quizzes', data);
      $q.notify({
        type: 'positive',
        message: 'Quiz created successfully',
        icon: 'check_circle'
      });
    }
    
    router.visit('/quizzes');
  } catch (error) {
    console.error('Failed to save quiz:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to save quiz',
      icon: 'error'
    });
  } finally {
    saving.value = false;
  }
};

// Lifecycle
onMounted(() => {
  loadQuiz();
  fetchQuestions();
  fetchMetadata();
});
</script>

<style scoped lang="scss">
.quiz-builder {
  min-height: 100vh;
  background: #f0f4f8;
  padding: 24px;
  
  &__container {
    max-width: 1800px;
    margin: 0 auto;
  }
}

.hover-scale {
  transition: transform 0.2s ease;
  &:hover {
    transform: scale(1.02);
  }
}

.animate-pop {
  animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes popIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.border-green {
  border-color: #4caf50 !important;
}

.rounded-borders-top {
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
}
</style>
