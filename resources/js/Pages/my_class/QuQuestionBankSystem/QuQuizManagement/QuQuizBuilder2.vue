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
              @click="router.visit('/qu-quiz-builder')"
            />
            <div>
              <h1 class="text-h4 text-weight-bold text-primary q-my-none">
                {{ quiz.id ? 'Edit Exam' : 'Create New Exam' }}
              </h1>
              <p class="text-subtitle1 text-grey-7 q-my-none">
                {{ selectedQuestions.length }} questions • {{ liveStats.totalPoints }} points
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
          <div class="column q-gutter-y-md">
            <!-- Advanced Filters -->
            <q-card class="rounded-xl shadow-2 bg-white">
              <q-card-section class="bg-purple-1 text-purple-9">
                <div class="text-h6 text-weight-bold">Advanced Filters</div>
              </q-card-section>
              <q-card-section>
                <AdvancedFilters
                  :available-grades="grades"
                  :available-subjects="subjects"
                  :available-topics="topics"
                  :authors="authors"
                  :question-types="questionTypes"
                  :model-value="filterState"
                  @update:model-value="handleFilterChanged"
                  @filter-changed="handleFilterChanged"
                  @filters-cleared="handleFiltersClear"
                />
              </q-card-section>
            </q-card>

            <!-- Question Pool -->
            <QuestionPool
              :filtered-questions="filteredPoolQuestions"
              :selected-questions="selectedQuestions"
              :question-types="questionTypes"
              :bulk-state="bulkState"
              :loading="loadingQuestions"
              :search-term="poolSearch"
              :type-filter="poolTypeFilter"
              :difficulty-filter="poolDifficultyFilter"
              @refresh="fetchQuestions"
              @update:search-term="poolSearch = $event"
              @update:type-filter="poolTypeFilter = $event"
              @update:difficulty-filter="poolDifficultyFilter = $event"
              @question-clicked="addQuestion"
              @question-drag-start="handleDragStart"
              @toggle-question-selection="handleToggleQuestionSelection"
              @add-all-filtered="addAllFilteredQuestions"
              @add-selected="addSelectedQuestions"
              @toggle-multi-select="handleToggleMultiSelectMode"
              @clear-selection="handleClearSelection"
              @select-all-filtered="handleSelectAllFiltered"
              @remove-all="clearAllQuestions"
            />

            <!-- Smart Selection -->
            <SmartSelection
              :available-questions="filteredPoolQuestions"
              :current-filters="filterState"
              @questions-selected="handleSmartSelection"
              @selection-feedback="handleSelectionFeedback"
            />

            <!-- Question Pool Statistics -->
            <QuestionPoolStats
              :stats="questionPoolStats"
              :selection-feedback="smartSelectionFeedback"
              :original-pool-size="originalPoolSize"
              :topic-names="topics.reduce((acc, t) => ({ ...acc, [t.id]: t.name }), {})"
              :author-names="authors.reduce((acc, a) => ({ ...acc, [a.id]: a.name }), {})"
              @refresh="fetchQuestions"
            />
          </div>
        </div>

        <!-- Center Panel: Quiz Canvas -->
        <div class="col-12 col-md-6">
          <QuizCanvas
            :questions="selectedQuestions"
            :sections="sections"
            :is-drag-over="isDragOver"
            @clear-all="clearAllQuestions"
            @shuffle="shuffleQuestions"
            @preview-question="previewQuestion"
            @points-updated="handlePointsUpdated"
            @question-removed="removeQuestionByObject"
            @questions-reordered="handleQuestionsReordered"
            @section-added="handleSectionAdded"
            @section-updated="handleSectionUpdated"
            @section-deleted="handleSectionDeleted"
            @sections-reordered="handleSectionsReordered"
            @question-assigned="handleQuestionAssigned"
            @question-added="addQuestion"
          />
        </div>

        <!-- Right Panel: Settings -->
        <div class="col-12 col-md-3">
          <div class="column q-gutter-y-md">
            <!-- Quiz Settings -->
            <q-card class="rounded-xl shadow-2 bg-white">
              <q-card-section class="bg-purple-1 text-purple-9">
                <div class="text-h6 text-weight-bold">Exam Settings</div>
              </q-card-section>

              <q-card-section class="q-gutter-y-md">
                <q-input
                  v-model="quiz.title"
                  outlined
                  rounded
                  label="Exam Title"
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
                      v-model.number="quiz.duration_minutes"
                      outlined
                      rounded
                      type="number"
                      label="Duration (min)"
                      dense
                      bg-color="grey-1"
                    />
                  </div>
                  <div class="col-6">
                    <q-select
                      v-model="quiz.exam_type"
                      outlined
                      rounded
                      dense
                      :options="['practice', 'quiz', 'midterm', 'final', 'survey']"
                      label="Exam Type"
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
                        v-model="quiz.settings.shuffle_questions"
                        label="Shuffle Questions"
                        color="purple"
                      />
                      <q-toggle
                        v-model="quiz.settings.shuffle_options"
                        label="Shuffle Answers"
                        color="purple"
                      />
                      <q-toggle
                        v-model="quiz.settings.allow_print"
                        label="Allow Print"
                        color="purple"
                      />
                    </q-card-section>
                  </q-card>
                </q-expansion-item>
              </q-card-section>
            </q-card>

            <!-- Scoring Settings -->
            <ScoringSettings
              :questions="selectedQuestions"
              :scoring-config="scoringConfig"
              @points-updated="handlePointsUpdated"
              @passing-score-changed="handlePassingScoreChanged"
              @scoring-config-updated="handleScoringConfigUpdated"
            />

            <!-- Live Statistics -->
            <LiveStats
              :stats="liveStats"
              :passing-score="scoringConfig.passingScoreThreshold"
              :passing-score-is-percentage="scoringConfig.thresholdIsPercentage"
              :scoring-config="scoringConfig"
            />
          </div>
        </div>
      </div>
    </div>

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
                <h2 class="text-h3 text-weight-bold text-primary q-my-none">{{ quiz.title }}</h2>
                <p class="text-h6 text-grey-7 q-mt-md">{{ quiz.description }}</p>
                <div class="row justify-center q-gutter-x-lg q-mt-lg">
                  <q-chip icon="quiz" color="blue-1" text-color="blue-9" size="lg">
                    {{ selectedQuestions.length }} Questions
                  </q-chip>
                  <q-chip icon="schedule" color="orange-1" text-color="orange-9" size="lg" v-if="quiz.duration_minutes">
                    {{ quiz.duration_minutes }} Minutes
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
import QuestionCard from '@/Components/Quiz/QuestionCard.vue';
import QuizNavigation from '@/Components/Quiz/QuizNavigation.vue';
import QuestionPool from '@/Components/Quiz/QuestionPool.vue';
import QuizCanvas from '@/Components/Quiz/QuizCanvas.vue';
import ScoringSettings from '@/Components/Quiz/ScoringSettings.vue';
import LiveStats from '@/Components/Quiz/LiveStats.vue';
import AdvancedFilters from '@/Components/Quiz/AdvancedFilters.vue';
import SmartSelection from '@/Components/Quiz/SmartSelection.vue';
import QuestionPoolStats from '@/Components/Quiz/QuestionPoolStats.vue';
import { useBulkOperations } from '@/composables/useBulkOperations';
import { useScoringStore } from '@/composables/useScoringStore';
import { useSectionStore } from '@/composables/useSectionStore';
import { useFilterStore } from '@/composables/useFilterStore';
import { useSmartSelection } from '@/composables/useSmartSelection';

const $q = useQuasar();
const props = defineProps({
  quizId: [Number, String]
});

// Bulk Operations
const {
  bulkState,
  toggleMultiSelectMode,
  toggleQuestionSelection,
  selectAllFiltered,
  clearSelection,
  isQuestionSelected,
  getSelectedQuestions,
  validateBulkAdd,
  prepareBulkQuestions,
  getBulkOperationSummary
} = useBulkOperations();

// Scoring Store
const {
  scoringConfig,
  calculateLiveStats,
  applyDefaultPoints,
  updateQuestionPoints,
  updateScoringConfig
} = useScoringStore();

// Section Store
const {
  sections,
  createSection,
  updateSection,
  deleteSection,
  assignQuestionsToSection,
  removeQuestionsFromSection,
  reorderSections,
  toggleSectionCollapse,
  ensureDefaultSection,
  getAllQuestionsOrganized
} = useSectionStore();

// Filter Store
const {
  filterState,
  availableGrades,
  availableSubjects,
  availableTopics,
  availableAuthors,
  filteredSubjects,
  filteredTopics,
  hasActiveFilters,
  getFilterSummary,
  applyFilters,
  clearFilters,
  setAvailableOptions
} = useFilterStore();

// Smart Selection
const {
  calculatePoolStats,
  smartSelection,
  validateSelectionCriteria,
  getRecommendedCount,
  getAlgorithmRecommendations,
  previewSelection
} = useSmartSelection();

// State
const quiz = ref({
  title: '',
  description: '',
  duration_minutes: null,
  exam_type: 'quiz',
  mark_calculation_method: 'best',
  publish_results_timing: 'immediate',
  settings: {
    shuffle_questions: false,
    shuffle_options: false,
    allow_print: true
  }
});

const poolQuestions = ref([]);
const selectedQuestions = ref([]);
const questionTypes = ref([]);
const topics = ref([]);
const grades = ref([]);
const subjects = ref([]);
const authors = ref([]);

const loadingQuestions = ref(false);
const saving = ref(false);
const isDragging = ref(false);
const isDragOver = ref(false);

// Legacy filters (kept for backward compatibility)
const poolSearch = ref('');
const poolTypeFilter = ref(null);
const poolDifficultyFilter = ref(null);
const poolTopicFilter = ref(null);

// Smart selection state
const smartSelectionFeedback = ref(null);
const originalPoolSize = ref(0);

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
  
  // Apply advanced filters from filter store
  if (filterState.value.searchTerm) {
    const query = filterState.value.searchTerm.toLowerCase();
    result = result.filter(q =>
      q.question_text.toLowerCase().includes(query)
    );
  }
  
  if (filterState.value.grade) {
    result = result.filter(q => q.grade_id?.toString() === filterState.value.grade);
  }
  
  if (filterState.value.subject) {
    result = result.filter(q => q.subject_id?.toString() === filterState.value.subject);
  }
  
  if (filterState.value.topic) {
    result = result.filter(q => q.topic_id?.toString() === filterState.value.topic);
  }
  
  if (filterState.value.questionType) {
    result = result.filter(q => q.question_type_id?.toString() === filterState.value.questionType);
  }
  
  if (filterState.value.difficulty) {
    result = result.filter(q => q.difficulty === filterState.value.difficulty);
  }
  
  if (filterState.value.author) {
    result = result.filter(q => q.author_id?.toString() === filterState.value.author);
  }
  
  if (filterState.value.bloomsLevel) {
    result = result.filter(q => q.blooms_level?.toString() === filterState.value.bloomsLevel);
  }
  
  if (filterState.value.usedInQuiz && filterState.value.usedInQuiz !== 'all') {
    if (filterState.value.usedInQuiz === 'used') {
      result = result.filter(q => q.usage_count && q.usage_count > 0);
    } else if (filterState.value.usedInQuiz === 'unused') {
      result = result.filter(q => !q.usage_count || q.usage_count === 0);
    }
  }
  
  // Legacy filters (for backward compatibility)
  if (poolSearch.value) {
    const query = poolSearch.value.toLowerCase();
    result = result.filter(q =>
      q.question_text.toLowerCase().includes(query)
    );
  }
  
  if (poolTypeFilter.value) {
    result = result.filter(q => q.question_type_id === poolTypeFilter.value.id);
  }
  
  if (poolDifficultyFilter.value) {
    result = result.filter(q => q.difficulty === poolDifficultyFilter.value);
  }
  
  if (poolTopicFilter.value) {
    result = result.filter(q => q.topic_id === poolTopicFilter.value.id);
  }
  
  return result;
});

// Question pool statistics
const questionPoolStats = computed(() => {
  return calculatePoolStats(filteredPoolQuestions.value);
});

// Live statistics computed from scoring store
const liveStats = computed(() => {
  return calculateLiveStats(selectedQuestions.value, sections.value);
});

const estimatedTime = computed(() => {
  return `${liveStats.value.estimatedTimeMinutes} min`;
});

const averageDifficulty = computed(() => {
  return liveStats.value.averageDifficulty;
});

// Methods
const fetchQuestions = async () => {
  loadingQuestions.value = true;
  try {
    // Build params object for QuQuestion API
    const params = {
      per_page: 100 // Get more questions for the pool
    };
    
    // Add filters if they exist
    if (quiz.value.subject_id) {
      params.subject_id = quiz.value.subject_id;
    }
    
    const response = await axios.get('/api/qu-questions', { params });
    
    // Handle the response structure: { success: true, data: { data: [...], ...pagination } }
    if (response.data.success && response.data.data) {
      // Transform QuQuestion data to match expected format
      const questions = response.data.data.data || [];
      poolQuestions.value = questions.map(transformQuQuestionToQuestion);
      originalPoolSize.value = poolQuestions.value.length;
    } else {
      poolQuestions.value = [];
      originalPoolSize.value = 0;
    }
  } catch (error) {
    console.error('Failed to fetch questions:', error);
    poolQuestions.value = [];
    originalPoolSize.value = 0;
    $q.notify({
      type: 'negative',
      message: 'Failed to load questions',
      icon: 'error'
    });
  } finally {
    loadingQuestions.value = false;
  }
};

// Transform QuQuestion data to match the expected Question format
const transformQuQuestionToQuestion = (quQuestion) => {
  return {
    id: quQuestion.id,
    question_text: quQuestion.question_text,
    question_type_id: getQuestionTypeId(quQuestion.question_type),
    difficulty: mapDifficulty(quQuestion.difficulty),
    bloom_level: mapBloomLevel(quQuestion.bloom_level),
    subject_id: quQuestion.subject_id,
    topic_id: quQuestion.topic_id,
    grade_id: quQuestion.subject?.grade_id || null,
    author_id: quQuestion.created_by,
    status: 'active',
    usage_count: quQuestion.usage_count || 0,
    avg_success_rate: null,
    points: quQuestion.marks || 1,
    options: transformQuQuestionOptions(quQuestion.options, quQuestion.correct_answer),
    subject: quQuestion.subject,
    topic: quQuestion.topic,
    author: quQuestion.creator,
    created_at: quQuestion.created_at,
    updated_at: quQuestion.updated_at,
  };
};

// Helper function to map question types
const getQuestionTypeId = (questionType) => {
  const typeMap = {
    'mcq': 1,
    'true_false': 2,
    'short': 3,
    'long': 4
  };
  return typeMap[questionType] || 1;
};

// Helper function to map difficulty levels
const mapDifficulty = (difficulty) => {
  if (!difficulty) return 'Medium';
  return difficulty.charAt(0).toUpperCase() + difficulty.slice(1).toLowerCase();
};

// Helper function to map Bloom's taxonomy levels
const mapBloomLevel = (bloomLevel) => {
  if (!bloomLevel) return null;
  const bloomMap = {
    'remember': '1',
    'understand': '2',
    'apply': '3',
    'analyze': '4',
    'evaluate': '5',
    'create': '6'
  };
  return bloomMap[bloomLevel.toLowerCase()] || null;
};

// Helper function to transform QuQuestion options to expected format
const transformQuQuestionOptions = (options, correctAnswers) => {
  if (!options || !Array.isArray(options)) return [];
  
  const correctAnswerArray = Array.isArray(correctAnswers) ? correctAnswers : [correctAnswers];
  
  return options.map((option, index) => ({
    id: index + 1,
    option_key: String.fromCharCode(65 + index), // A, B, C, D
    option_text: option,
    is_correct: correctAnswerArray.includes(index),
    order_index: index
  }));
};

const fetchMetadata = async () => {
  try {
    const [topicsRes, gradesRes, subjectsRes] = await Promise.all([
      axios.get('/api/topics'),
      axios.get('/api/grades'),
      axios.get('/api/subjects')
    ]);
    
    // Question types for QuQuestion system
    questionTypes.value = [
      { id: 1, name: 'Multiple Choice' },
      { id: 2, name: 'True/False' },
      { id: 3, name: 'Short Answer' },
      { id: 4, name: 'Long Answer' }
    ];
    
    // Extract the data array from the API response (not the entire response object)
    topics.value = topicsRes.data.success ? topicsRes.data.data : [];
    grades.value = gradesRes.data.success ? gradesRes.data.data : [];
    subjects.value = subjectsRes.data.success ? subjectsRes.data.data : [];
    
    // Get authors from QuQuestion system
    try {
      const authorsRes = await axios.get('/api/qu-questions', { params: { per_page: 100 } });
      if (authorsRes.data.success && authorsRes.data.data) {
        // Extract unique authors from questions
        const questions = authorsRes.data.data.data || [];
        const uniqueAuthors = questions.reduce((acc, question) => {
          if (question.author && !acc.find(a => a.id === question.author.id)) {
            acc.push({
              id: question.author.id,
              name: question.author.name
            });
          }
          return acc;
        }, []);
        authors.value = uniqueAuthors;
      } else {
        authors.value = [];
      }
    } catch (error) {
      console.warn('Failed to fetch authors:', error);
      authors.value = [];
    }
    
    // Update filter store with available options
    setAvailableOptions({
      grades: grades.value,
      subjects: subjects.value,
      topics: topics.value,
      authors: authors.value
    });
  } catch (error) {
    console.error('Failed to fetch metadata:', error);
  }
};

const loadQuiz = async () => {
  if (!props.quizId) return;
  
  try {
    const response = await axios.get(`/api/qu-exams/${props.quizId}`);
    quiz.value = response.data;
    selectedQuestions.value = response.data.questions || [];
  } catch (error) {
    console.error('Failed to load exam:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load exam',
      icon: 'error'
    });
  }
};

const addQuestion = (question) => {
  if (!selectedQuestions.value.find(q => q.id === question.id)) {
    // Apply default points when adding a question
    const questionWithPoints = applyDefaultPoints({ ...question });
    selectedQuestions.value.push(questionWithPoints);
  }
};

const removeQuestion = (index) => {
  selectedQuestions.value.splice(index, 1);
};

const removeQuestionByObject = (question) => {
  const index = selectedQuestions.value.findIndex(q => q.id === question.id);
  if (index !== -1) {
    selectedQuestions.value.splice(index, 1);
    // Also remove from section if assigned
    if (question.sectionId) {
      removeQuestionsFromSection(question.sectionId, [question.id.toString()]);
    }
  }
};

const clearAllQuestions = () => {
  $q.dialog({
    title: 'Clear All Questions',
    message: 'Are you sure you want to remove all questions from this exam?',
    cancel: true,
    persistent: true,
    class: 'rounded-xl'
  }).onOk(() => {
    selectedQuestions.value = [];
    // Clear bulk selection as well
    clearSelection();
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
  } catch (e) {
    console.error('Failed to parse dropped question', e);
  }
};

const handleFilterChanged = (newFilters) => {
  // Filters are already reactive via filterState, but we can trigger refresh if needed
  // or logic to apply filters is largely computed. 
  // If we need to fetch from server based on filters (like for paginated remote pool)
  // we would call fetchQuestions() here.
  // For client-side filtering (current implementation), the computed property handles it.
};

const handleFiltersClear = () => {
  clearFilters();
};

const addAllFilteredQuestions = () => {
  // Add all questions currently in the filtered view
  filteredPoolQuestions.value.forEach(question => {
    addQuestion(question);
  });
  
  $q.notify({
    type: 'positive',
    message: `Added ${filteredPoolQuestions.value.length} questions`,
    position: 'top',
    timeout: 1000
  });
};

const addSelectedQuestions = () => {
  const selected = getSelectedQuestions(poolQuestions.value);
  let addedCount = 0;
  
  selected.forEach(question => {
    if (!selectedQuestions.value.find(q => q.id === question.id)) {
      addQuestion(question);
      addedCount++;
    }
  });
  
  if (addedCount > 0) {
    clearSelection();
    $q.notify({
      type: 'positive',
      message: `Added ${addedCount} questions`,
      position: 'top',
      timeout: 1000
    });
  }
};

const handleToggleQuestionSelection = (question) => {
  toggleQuestionSelection(question);
};

const handleToggleMultiSelectMode = () => {
  toggleMultiSelectMode();
};

const handleClearSelection = () => {
  clearSelection();
};

const handleSelectAllFiltered = () => {
  selectAllFiltered(filteredPoolQuestions.value);
};

// Smart Selection Handlers
const handleSmartSelection = (criteria) => {
  const result = smartSelection(
    poolQuestions.value, 
    selectedQuestions.value, 
    criteria
  );
  
  if (result && result.length > 0) {
    result.forEach(q => addQuestion(q));
    
    $q.notify({
      type: 'positive',
      message: `Automatically selected ${result.length} questions`,
      icon: 'auto_awesome'
    });
  } else {
    $q.notify({
      type: 'warning',
      message: 'No matching questions found for criteria',
      icon: 'warning'
    });
  }
};

const handleSelectionFeedback = (feedback) => {
  smartSelectionFeedback.value = feedback;
};

// Scoring & Section Handlers
const handlePointsUpdated = (questionId, points) => {
  updateQuestionPoints(selectedQuestions.value, questionId, points);
};

const handlePassingScoreChanged = (score) => {
  // handled via v-model or if specific logic needed
  scoringConfig.value.passingScoreThreshold = score;
};

const handleScoringConfigUpdated = (config) => {
  updateScoringConfig(config);
};

// Section Handlers - Map to store actions
const handleSectionAdded = (section) => createSection(section);
const handleSectionUpdated = (section) => updateSection(section);
const handleSectionDeleted = (sectionId) => deleteSection(sectionId);
const handleSectionsReordered = (newOrder) => reorderSections(newOrder);
const handleQuestionsReordered = (newOrder) => {
  // Ensure we update both our local ref and efficiently handle section updates if needed
  selectedQuestions.value = newOrder;
};

const handleQuestionAssigned = ({ questionId, sectionId }) => {
  assignQuestionsToSection(sectionId, [questionId]);
};

const previewQuestion = (question) => {
  previewingQuestion.value = question;
  showQuestionPreview.value = true;
};

const saveQuiz = async () => {
  if (!quiz.value.title) {
    $q.notify({
      type: 'warning',
      message: 'Please enter an exam title',
      icon: 'warning'
    });
    return;
  }
  
  if (!quiz.value.duration_minutes || quiz.value.duration_minutes < 1) {
    $q.notify({
      type: 'warning',
      message: 'Please enter a valid duration (minimum 1 minute)',
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
      subject_id: quiz.value.subject_id || (selectedQuestions.value.length > 0 ? selectedQuestions.value[0].subject_id : null),
      question_ids: selectedQuestions.value.map(q => q.id),
      // Include full section structure and scoring config
      sections: sections.value,
      scoring_config: scoringConfig.value,
      total_points: liveStats.value.totalPoints,
      total_marks: liveStats.value.totalPoints // Map total_points to total_marks for backend validation
    };
    
    if (props.quizId) {
      await axios.put(`/api/qu-exams/${props.quizId}`, data);
      $q.notify({
        type: 'positive',
        message: 'Exam updated successfully',
        icon: 'check_circle'
      });
    } else {
      await axios.post('/api/qu-exams', data);
      $q.notify({
        type: 'positive',
        message: 'Exam created successfully',
        icon: 'check_circle'
      });
    }
    
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

// Lifecycle
onMounted(() => {
  loadQuiz();
  fetchQuestions();
  fetchMetadata();
  ensureDefaultSection();
});
</script>

<style scoped lang="scss">
.quiz-builder {
  min-height: 100vh;
  background: #f0f4f8;
  padding: 24px;
}

.quiz-builder__container {
  max-width: 1800px;
  margin: 0 auto;
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