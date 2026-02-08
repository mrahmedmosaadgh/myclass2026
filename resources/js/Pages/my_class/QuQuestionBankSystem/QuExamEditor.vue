<template>
  <div class="exam-editor">
    <Head :title="exam.id ? 'Edit Exam' : 'Create Exam'" />
    
    <div class="exam-editor__container q-pa-md">
      <!-- Navigation -->
      <!-- <QuizNavigation role="teacher" /> -->

      <!-- Header -->
      <div class="exam-editor__header q-mb-md">
        <div class="row items-center justify-between">
          <div class="row items-center q-gutter-x-md">
            <q-btn
              flat
              round
              color="primary"
              icon="arrow_back"
              class="bg-white shadow-1"
              @click="goBack"
            />
            <div>
              <h1 class="text-h4 text-weight-bold text-primary q-my-none">
                {{ exam.id ? 'Edit Exam Questions' : 'Create New Exam' }}
              </h1>
              <p class="text-subtitle1 text-grey-7 q-my-none">
                {{ selectedQuestions.length }} questions • {{ totalPoints }} points
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
              label="Save Exam"
              icon="save"
              class="text-weight-bold"
              @click="saveExam"
              :loading="saving"
            />
          </div>
        </div>
      </div>

      <!-- Main Content Tabs -->
      <q-card class="rounded-xl shadow-2 bg-white" style="min-height: 600px">
        <q-tabs
          v-model="activeTab"
          class="text-grey"
          active-color="primary"
          indicator-color="primary"
          align="left"
          narrow-indicator
        >
          <q-tab name="settings" label="Exam Settings" icon="settings" />
          <q-tab name="questions" label="Edit Questions" icon="quiz" />
        </q-tabs>

        <q-separator />

        <q-tab-panels v-model="activeTab" animated class="bg-transparent">
          
          <!-- SETTINGS TAB -->
          <q-tab-panel name="settings" class="q-pa-lg">
            <div class="row q-col-gutter-lg justify-center">
              <div class="col-12 col-md-8">
                <div class="text-h6 text-primary q-mb-md">General Information</div>
                
                <q-input
                  v-model="exam.title"
                  outlined
                  rounded
                  label="Exam Title"
                  :rules="[val => !!val || 'Required']"
                  bg-color="grey-1"
                  class="q-mb-md"
                >
                  <template v-slot:prepend>
                    <q-icon name="title" color="purple" />
                  </template>
                </q-input>

                <q-input
                  v-model="exam.description"
                  outlined
                  rounded
                  type="textarea"
                  label="Description"
                  rows="4"
                  bg-color="grey-1"
                  class="q-mb-md"
                />

                <div class="row q-col-gutter-md q-mb-md">
                  <div class="col-12 col-md-6">
                    <q-input
                      v-model.number="exam.duration_minutes"
                      outlined
                      rounded
                      type="number"
                      label="Duration (minutes)"
                      hint="Leave empty for no time limit"
                      bg-color="grey-1"
                    />
                  </div>
                  <div class="col-12 col-md-6">
                    <q-select
                      v-model="exam.exam_type"
                      outlined
                      rounded
                      :options="['practice', 'quiz', 'midterm', 'final', 'survey']"
                      label="Exam Type"
                      bg-color="grey-1"
                      behavior="menu"
                    />
                  </div>
                </div>

                <q-separator class="q-my-lg" />

                <div class="text-h6 text-primary q-mb-md">Advanced Settings</div>
                
                <div class="q-gutter-y-md">
                  <q-item tag="label" v-ripple class="bg-grey-1 rounded-borders">
                    <q-item-section>
                      <q-item-label>Shuffle Questions</q-item-label>
                      <q-item-label caption>Randomize the order of questions for each student</q-item-label>
                    </q-item-section>
                    <q-item-section side >
                      <q-toggle color="purple" v-model="exam.settings.shuffle_questions" />
                    </q-item-section>
                  </q-item>

                  <q-item tag="label" v-ripple class="bg-grey-1 rounded-borders">
                    <q-item-section>
                      <q-item-label>Shuffle Options</q-item-label>
                      <q-item-label caption>Randomize the order of answer choices</q-item-label>
                    </q-item-section>
                    <q-item-section side >
                      <q-toggle color="purple" v-model="exam.settings.shuffle_options" />
                    </q-item-section>
                  </q-item>

                  <q-item tag="label" v-ripple class="bg-grey-1 rounded-borders">
                    <q-item-section>
                      <q-item-label>Allow Print</q-item-label>
                      <q-item-label caption>Allow students to print the exam</q-item-label>
                    </q-item-section>
                    <q-item-section side >
                      <q-toggle color="purple" v-model="exam.settings.allow_print" />
                    </q-item-section>
                  </q-item>
                </div>
              </div>
            </div>
          </q-tab-panel>

          <!-- QUESTIONS TAB -->
          <q-tab-panel name="questions" class="q-pa-none">
            <div class="row">
              <!-- Questions List (Left) -->
              <div class="col-12 col-md-9 q-pa-md border-right">
                <div class="row items-center justify-between q-mb-md">
                  <div class="text-h6 text-weight-bold display-flex items-center">
                    <q-icon name="list" class="q-mr-sm" />
                    Exam Questions ({{ selectedQuestions.length }})
                  </div>
                  <q-btn
                    unelevated
                    rounded
                    color="primary"
                    label="Add Questions"
                    icon="add"
                    @click="showQuestionSelector = true"
                  />
                </div>

                <div v-if="selectedQuestions.length === 0" class="text-center q-pa-xl bg-grey-1 rounded-borders dashed-border">
                  <q-icon name="quiz" size="64px" color="grey-4" />
                  <p class="text-h6 text-grey-6 q-mt-md">No questions added yet</p>
                  <p class="text-grey-7">Switch to the "Add Questions" button to select from the bank</p>
                </div>

                <draggable
                  v-else
                  v-model="selectedQuestions"
                  item-key="id"
                  handle=".drag-handle"
                  @end="onQuestionsReordered"
                  class="q-gutter-y-md"
                >
                  <template #item="{ element: question, index }">
                    <q-card class="rounded-lg shadow-1" bordered>
                      <q-card-section>
                        <div class="row items-start q-gutter-x-md">
                          <!-- Drag Handle -->
                          <div class="column justify-center self-stretch cursor-move drag-handle q-pr-sm">
                            <q-icon name="drag_indicator" size="sm" color="grey-5" />
                          </div>

                          <!-- Question Number -->
                          <q-badge color="grey-3" text-color="grey-9" class="text-subtitle2 q-pa-xs">
                            Q{{ index + 1 }}
                          </q-badge>

                          <!-- Question Content -->
                          <div class="col">
                            <div class="text-body1 text-weight-medium" v-html="question.question_text"></div>
                            
                            <!-- Options -->
                            <div class="q-mt-sm q-gutter-y-xs">
                              <div
                                v-for="option in question.options"
                                :key="option.id"
                                class="rounded-borders q-pa-sm"
                                :class="option.is_correct ? 'bg-green-1 text-green-9 border-green' : 'bg-grey-1'"
                                style="border: 1px solid transparent"
                              >
                                <div class="row items-center">
                                  <div class="col text-caption">{{ option.option_text }}</div>
                                  <q-icon v-if="option.is_correct" name="check_circle" color="positive" size="xs" />
                                </div>
                              </div>
                            </div>
                          </div>

                          <!-- Meta & Actions -->
                          <div class="column items-end q-gutter-y-sm" style="min-width: 120px">
                             <div class="row q-gutter-x-sm">
                                <q-badge :color="getDifficultyColor(question.difficulty)">
                                  {{ question.difficulty || 'Medium' }}
                                </q-badge>
                             </div>
                             
                             <div class="row items-center q-gutter-x-sm">
                                <span class="text-caption text-grey-7">Points:</span>
                                <q-input
                                  v-model.number="question.points"
                                  type="number"
                                  dense
                                  outlined
                                  class="points-input"
                                  @update:model-value="updateTotalPoints"
                                />
                             </div>

                             <q-btn
                                flat
                                round
                                dense
                                color="grey-6"
                                icon="delete"
                                @click="removeQuestion(index)"
                              >
                                <q-tooltip>Remove Question</q-tooltip>
                              </q-btn>
                          </div>
                        </div>
                      </q-card-section>
                    </q-card>
                  </template>
                </draggable>
              </div>

              <!-- Sidebar Stats (Right) -->
              <div class="col-12 col-md-3 q-pa-md bg-grey-1">
                 <div class="text-subtitle2 text-grey-8 q-mb-md text-uppercase">Summary</div>
                 
                 <q-card flat bordered class="bg-white q-mb-md">
                   <q-card-section>
                     <div class="text-center">
                        <div class="text-h4 text-weight-bold text-primary">{{ selectedQuestions.length }}</div>
                        <div class="text-caption text-grey-7">Questions</div>
                     </div>
                   </q-card-section>
                   <q-separator />
                   <q-card-section>
                     <div class="text-center">
                        <div class="text-h4 text-weight-bold text-secondary">{{ totalPoints }}</div>
                        <div class="text-caption text-grey-7">Total Points</div>
                     </div>
                   </q-card-section>
                 </q-card>

                 <q-card flat bordered class="bg-white">
                    <q-card-section>
                      <div class="text-subtitle2 q-mb-sm">Difficulty Distribution</div>
                      <div v-for="(count, level) in difficultyDistribution" :key="level" class="row justify-between items-center q-mb-xs">
                          <span class="text-caption">{{ level }}</span>
                          <span class="text-caption text-weight-bold">{{ count }}</span>
                      </div>
                    </q-card-section>
                 </q-card>
              </div>
            </div>
          </q-tab-panel>
        </q-tab-panels>
      </q-card>
    </div>

    <!-- Question Selector Dialog -->
    <q-dialog v-model="showQuestionSelector" maximized>
      <q-card>
        <q-toolbar class="bg-primary text-white">
          <q-btn flat round dense icon="close" v-close-popup />
          <q-toolbar-title>Select Questions</q-toolbar-title>
        </q-toolbar>

        <q-card-section>
          <QuExamQuestionSelector
            :selected-questions="selectedQuestions"
            :subject-id="exam.subject_id"
            @questions-selected="handleQuestionsSelected"
          />
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Preview Dialog -->
    <q-dialog v-model="showPreview" maximized transition-show="slide-up" transition-hide="slide-down">
      <q-card class="bg-grey-1">
        <q-toolbar class="bg-white text-primary shadow-1">
          <q-btn flat round dense icon="close" v-close-popup />
          <q-toolbar-title class="text-weight-bold">Exam Preview</q-toolbar-title>
        </q-toolbar>

        <q-card-section class="row justify-center q-pa-lg">
          <div class="col-12 col-md-8">
            <q-card class="rounded-xl shadow-2 q-mb-lg">
              <q-card-section class="text-center q-pa-xl">
                <h2 class="text-h3 text-weight-bold text-primary q-my-none">{{ exam.title }}</h2>
                <p class="text-h6 text-grey-7 q-mt-md">{{ exam.description }}</p>
                <div class="row justify-center q-gutter-x-lg q-mt-lg">
                  <q-chip icon="quiz" color="blue-1" text-color="blue-9" size="lg">
                    {{ selectedQuestions.length }} Questions
                  </q-chip>
                  <q-chip icon="schedule" color="orange-1" text-color="orange-9" size="lg" v-if="exam.duration_minutes">
                    {{ exam.duration_minutes }} Minutes
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';
import draggable from 'vuedraggable';
import QuizNavigation from '@/Components/Quiz/QuizNavigation.vue';
import QuExamQuestionSelector from './QuComponents/QuExamQuestionSelector.vue';

const $q = useQuasar();
const props = defineProps({
  examId: [Number, String]
});

// State
const activeTab = ref('settings');
const exam = ref({
  id: null,
  title: '',
  description: '',
  subject_id: null,
  duration_minutes: null,
  exam_type: 'quiz',
  settings: {
    shuffle_questions: false,
    shuffle_options: false,
    allow_print: true
  }
});

const subjects = ref([]);
const selectedQuestions = ref([]);
const saving = ref(false);
const showPreview = ref(false);
const showQuestionSelector = ref(false);

// Computed
const totalPoints = computed(() => {
  return selectedQuestions.value.reduce((sum, q) => sum + (q.points || 1), 0);
});

const averageDifficulty = computed(() => {
  if (selectedQuestions.value.length === 0) return 'N/A';
  
  const difficultyMap = { 'Easy': 1, 'Medium': 2, 'Hard': 3 };
  const total = selectedQuestions.value.reduce((sum, q) => {
    return sum + (difficultyMap[q.difficulty] || 2);
  }, 0);
  
  const avg = total / selectedQuestions.value.length;
  if (avg < 1.5) return 'Easy';
  if (avg < 2.5) return 'Medium';
  return 'Hard';
});

const difficultyDistribution = computed(() => {
  const dist = { Easy: 0, Medium: 0, Hard: 0 };
  selectedQuestions.value.forEach(q => {
    const diff = q.difficulty || 'Medium';
    if (dist[diff] !== undefined) {
      dist[diff]++;
    }
  });
  return dist;
});

// Methods
const getDifficultyColor = (difficulty) => {
  switch(difficulty) {
    case 'Easy': return 'green';
    case 'Medium': return 'orange';
    case 'Hard': return 'red';
    default: return 'blue';
  }
};

const loadExam = async () => {
  if (!props.examId) return;
  
  try {
    const response = await axios.get(`/qu-exams/${props.examId}/data`);
    if (response.data.success) {
      exam.value = response.data.data;
      selectedQuestions.value = response.data.data.questions || [];
    }
  } catch (error) {
    console.error('Failed to load exam:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load exam',
      icon: 'error'
    });
  }
};

const loadMetadata = async () => {
  try {
    const response = await axios.get('/api/subjects');
    if (response.data.success) {
      subjects.value = response.data.data;
    } else if (Array.isArray(response.data)) {
        subjects.value = response.data;
    }
  } catch (error) {
    console.error('Failed to load subjects:', error);
  }
};

const handleQuestionsSelected = (questions) => {
  // Add new questions that aren't already selected
  questions.forEach(question => {
    if (!selectedQuestions.value.find(q => q.id === question.id)) {
      selectedQuestions.value.push({
        ...question,
        points: question.points || 1
      });
    }
  });
  showQuestionSelector.value = false;
};

const removeQuestion = (index) => {
  $q.dialog({
    title: 'Remove Question',
    message: 'Are you sure you want to remove this question?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    selectedQuestions.value.splice(index, 1);
  });
};

const onQuestionsReordered = () => {
  // Questions have been reordered via drag and drop
  updateTotalPoints();
};

const updateTotalPoints = () => {
  // Trigger reactivity
  selectedQuestions.value = [...selectedQuestions.value];
};

const saveExam = async () => {
  if (!exam.value.title) {
    $q.notify({
      type: 'warning',
      message: 'Please enter an exam title',
      icon: 'warning'
    });
    // Switch to settings tab if title is missing
    activeTab.value = 'settings';
    return;
  }

  if (selectedQuestions.value.length === 0) {
    $q.notify({
      type: 'warning',
      message: 'Please add at least one question',
      icon: 'warning'
    });
    // Switch to questions tab if no questions
    activeTab.value = 'questions';
    return;
  }

  saving.value = true;
  try {
    const payload = {
      ...exam.value,
      questions: selectedQuestions.value.map((q, index) => ({
        question_id: q.id,
        order_index: index,
        points: q.points || 1
      }))
    };

    let response;
    if (exam.value.id) {
      response = await axios.put(`/qu-exams/${exam.value.id}`, payload);
    } else {
      response = await axios.post('/qu-exams', payload);
    }

    $q.notify({
      type: 'positive',
      message: exam.value.id ? 'Exam updated successfully' : 'Exam created successfully',
      icon: 'check_circle'
    });

    router.visit('/qu-exams');
  } catch (error) {
    console.error('Failed to save exam:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to save exam',
      icon: 'error'
    });
  } finally {
    saving.value = false;
  }
};

const goBack = () => {
  router.visit('/qu-exams');
};

onMounted(() => {
  loadMetadata();
  loadExam();
});
</script>

<style scoped>
.exam-editor {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.exam-editor__container {
  max-width: 1400px;
  margin: 0 auto;
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.drag-handle {
  cursor: move;
}

.border-green {
  border-color: #4caf50 !important;
}

.border-right {
  border-right: 1px solid #e0e0e0;
}

.dashed-border {
  border: 2px dashed #bdbdbd;
}

.points-input {
  width: 70px;
}
</style>
