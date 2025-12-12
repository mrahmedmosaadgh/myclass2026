<template>
  <div class="resume-questions-manager q-pa-md">
    <!-- Header Card -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center q-gutter-md">
        <q-icon name="quiz" size="32px" color="primary" />
        <div>
          <div class="text-h5">Resume Questions Manager</div>
          <div class="text-caption">
            Manage resume questions and their answers. Expandable rows show nested answer tables.
          </div>
        </div>
        <q-space />
        <q-btn
          color="secondary"
          icon="auto_awesome"
          label="AI Assistant"
          @click="showAIAssistant = true"
          :disable="questionsLoading"
          class="q-mr-sm"
        />
        <q-btn
          color="primary"
          icon="add"
          label="Add Question"
          @click="openQuestionForm()"
          :disable="questionsLoading"
        />
      </q-card-section>
    </q-card>

    <!-- Filters Card -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section>
        <div class="row q-gutter-md items-end">
          <q-input
            v-model="questionFilters.search"
            label="Search questions"
            outlined
            dense
            style="min-width: 200px"
            clearable
          >
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>
          
          <q-select
            v-model="questionFilters.category"
            :options="categories"
            label="Category"
            outlined
            dense
            clearable
            style="min-width: 150px"
          />
          
          <q-select
            v-model="questionFilters.type"
            :options="questionTypes"
            label="Type"
            outlined
            dense
            clearable
            style="min-width: 150px"
          />
          
          <q-btn 
            color="primary" 
            icon="filter_list" 
            label="Apply Filters" 
            @click="applyQuestionFilters"
            :loading="questionsLoading"
          />
          
          <q-btn 
            flat 
            icon="clear" 
            label="Clear" 
            @click="clearQuestionFilters"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- Questions Table Card -->
    <q-card flat bordered>
      <q-card-section class="q-pa-none">
        <q-table
          :rows="questions"
          :columns="questionColumns"
          row-key="id"
          :loading="questionsLoading"
          :pagination="{ rowsPerPage: 10 }"
          flat
          bordered
        >
          <!-- Loading slot -->
          <template v-slot:loading>
            <q-inner-loading showing color="primary" />
          </template>

          <!-- No data slot -->
          <template v-slot:no-data="{ message }">
            <div class="full-width row flex-center q-gutter-sm">
              <q-icon size="2em" name="sentiment_dissatisfied" />
              <span>{{ message }}</span>
            </div>
          </template>

          <!-- Title column with Text-to-Speech -->
          <template v-slot:body-cell-title="props">
            <q-td :props="props">
              <div class="row items-center q-gutter-sm">
                <div class="col text-weight-medium">
                  {{ props.value }}
                </div>
                <div class="col-auto">
                  <TextToSpeechButton
                    :text="props.value + (props.row.description ? '. ' + props.row.description : '')"
                    size="xs"
                    round
                    flat
                  />
                </div>
              </div>
            </q-td>
          </template>

          <!-- Category column -->
          <template v-slot:body-cell-category="props">
            <q-td :props="props">
              <q-chip
                v-for="cat in props.value"
                :key="cat"
                size="sm"
                color="blue-grey-2"
                text-color="blue-grey-8"
                class="q-ma-xs"
              >
                {{ cat }}
              </q-chip>
            </q-td>
          </template>

          <!-- Type column -->
          <template v-slot:body-cell-type="props">
            <q-td :props="props">
              <q-badge :color="getTypeColor(props.value)" :label="props.value" />
            </q-td>
          </template>

          <!-- Required column -->
          <template v-slot:body-cell-is_required="props">
            <q-td :props="props">
              <q-icon 
                :name="props.value ? 'check_circle' : 'radio_button_unchecked'" 
                :color="props.value ? 'positive' : 'grey-5'"
                size="sm"
              />
            </q-td>
          </template>

          <!-- Actions column -->
          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <div class="q-gutter-xs">
                <q-btn
                  icon="visibility"
                  size="sm"
                  flat
                  round
                  color="info"
                  @click="showQuestionDetails(props.row)"
                >
                  <q-tooltip>View Answers & Details</q-tooltip>
                </q-btn>
                <q-btn
                  icon="edit"
                  size="sm"
                  flat
                  round
                  color="primary"
                  @click="openQuestionForm(props.row)"
                >
                  <q-tooltip>Edit Question</q-tooltip>
                </q-btn>
                <q-btn
                  icon="add_comment"
                  size="sm"
                  flat
                  round
                  color="positive"
                  @click="openAnswerForm(props.row.id)"
                >
                  <q-tooltip>Add Answer</q-tooltip>
                </q-btn>
                <q-btn
                  icon="delete"
                  size="sm"
                  flat
                  round
                  color="negative"
                  @click="handleDeleteQuestion(props.row.id)"
                >
                  <q-tooltip>Delete Question</q-tooltip>
                </q-btn>
                <q-btn
                  :icon="expandedRows.includes(props.row.id) ? 'expand_less' : 'expand_more'"
                  size="sm"
                  flat
                  round
                  color="secondary"
                  @click="toggleAnswers(props.row)"
                >
                  <q-tooltip>{{ expandedRows.includes(props.row.id) ? 'Hide' : 'Show' }} Answers</q-tooltip>
                </q-btn>
              </div>
            </q-td>
          </template>

          <!-- Expanded row for answers -->
          <template v-slot:expand="props">
            <q-td colspan="100%">
              <div class="q-pa-md bg-grey-1">
                <div class="row items-center q-mb-md">
                  <div class="col">
                    <div class="row items-center q-gutter-sm">
                      <div class="text-h6">Answers for: {{ props.row.title }}</div>
                      <TextToSpeechButton
                        :text="'Answers for: ' + props.row.title + (props.row.description ? '. ' + props.row.description : '')"
                        size="sm"
                        round
                        flat
                      />
                    </div>
                  </div>
                  <q-space />
                  <q-btn
                    color="secondary"
                    icon="add"
                    label="Add Answer"
                    size="sm"
                    @click="openAnswerForm(props.row.id)"
                  />
                </div>

                <!-- Answers Table -->
                <q-table
                  :rows="getAnswers(props.row.id)"
                  :columns="answerColumns"
                  row-key="id"
                  :loading="isAnswersLoading(props.row.id)"
                  flat
                  bordered
                  dense
                  :pagination="{ rowsPerPage: 5 }"
                >
                  <!-- Loading slot -->
                  <template v-slot:loading>
                    <q-inner-loading showing color="secondary" />
                  </template>

                  <!-- No data slot -->
                  <template v-slot:no-data>
                    <div class="full-width row flex-center q-gutter-sm">
                      <q-icon size="1.5em" name="chat_bubble_outline" />
                      <span>No answers yet</span>
                    </div>
                  </template>

                  <!-- Answer text column -->
                  <template v-slot:body-cell-answer_text="props">
                    <q-td :props="props">
                      <div class="text-body2" style="max-width: 300px;">
                        {{ truncateText(props.value, 100) }}
                      </div>
                    </q-td>
                  </template>

                  <!-- Media links column -->
                  <template v-slot:body-cell-media_links="props">
                    <q-td :props="props">
                      <div v-if="props.value && props.value.length > 0">
                        <q-chip
                          v-for="(link, index) in props.value.slice(0, 2)"
                          :key="index"
                          size="sm"
                          color="blue-2"
                          text-color="blue-8"
                          icon="link"
                          clickable
                          @click="openLink(link)"
                        >
                          Link {{ index + 1 }}
                        </q-chip>
                        <q-chip
                          v-if="props.value.length > 2"
                          size="sm"
                          color="grey-3"
                          text-color="grey-7"
                        >
                          +{{ props.value.length - 2 }} more
                        </q-chip>
                      </div>
                      <span v-else class="text-grey-5">No media</span>
                    </q-td>
                  </template>

                  <!-- Status column -->
                  <template v-slot:body-cell-status="props">
                    <q-td :props="props">
                      <q-badge :color="getStatusColor(props.value)" :label="props.value" />
                    </q-td>
                  </template>

                  <!-- Answer actions column -->
                  <template v-slot:body-cell-actions="props">
                    <q-td :props="props">
                      <div class="q-gutter-xs">
                        <q-btn
                          icon="edit"
                          size="sm"
                          flat
                          round
                          color="primary"
                          @click="openAnswerForm(props.row.question_id, props.row)"
                        >
                          <q-tooltip>Edit Answer</q-tooltip>
                        </q-btn>
                        <q-btn
                          icon="delete"
                          size="sm"
                          flat
                          round
                          color="negative"
                          @click="handleDeleteAnswer(props.row.id, props.row.question_id)"
                        >
                          <q-tooltip>Delete Answer</q-tooltip>
                        </q-btn>
                      </div>
                    </q-td>
                  </template>
                </q-table>
              </div>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <!-- Question Form Dialog -->
    <QuestionForm
      v-model="questionFormVisible"
      :question="selectedQuestion"
      :categories="categories"
      :question-types="questionTypes"
      @save="handleSaveQuestion"
      ref="questionFormRef"
    />

    <!-- Answer Form Dialog -->
    <AnswerForm
      v-model="answerFormVisible"
      :answer="selectedAnswer"
      :question-id="selectedQuestionId"
      @save="handleSaveAnswer"
      ref="answerFormRef"
    />

    <!-- Question Details Dialog -->
    <QuestionDetailsDialog
      v-model="questionDetailsVisible"
      :question="selectedQuestion"
      @questionUpdated="handleQuestionUpdated"
    />

    <!-- AI Assistant Dialog -->
    <q-dialog
      v-model="showAIAssistant"
      maximized
      transition-show="slide-up"
      transition-hide="slide-down"
    >
      <q-card class="full-height">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">AI Assistant</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="q-pt-none full-height">
          <div class="full-height overflow-auto">
            <GeminiPrompt
              v-model="aiPrompt"
              :extra="aiExtra"
              @update:extra="val => aiExtra = val"
              @response="handleAIResponse"
              @use-as-question="handleUseAsQuestion"
              @use-as-answer="handleUseAsAnswer"
            />
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useQuestions } from './composables/useQuestions.js';
import { useAnswers } from './composables/useAnswers.js';
import QuestionForm from './QuestionForm.vue';
import AnswerForm from './AnswerForm.vue';
import QuestionDetailsDialog from './QuestionDetailsDialog.vue';
import TextToSpeechButton from './components/TextToSpeechButton.vue';
import GeminiPrompt from './components/GeminiPrompt.vue';

// Composables
const {
  questions,
  loading: questionsLoading,
  categories,
  questionTypes,
  filters: questionFilters,
  loadQuestions,
  createQuestion,
  updateQuestion,
  deleteQuestion,
  loadCategories,
  loadQuestionTypes,
  clearFilters: clearQuestionFilters,
  applyFilters: applyQuestionFilters
} = useQuestions();

const {
  getAnswers,
  isLoading: isAnswersLoading,
  loadAnswers,
  createAnswer,
  updateAnswer,
  deleteAnswer
} = useAnswers();

// State
const expandedRows = ref([]);
const questionFormVisible = ref(false);
const answerFormVisible = ref(false);
const questionDetailsVisible = ref(false);
const selectedQuestion = ref(null);
const selectedAnswer = ref(null);
const selectedQuestionId = ref(null);
const questionFormRef = ref(null);
const answerFormRef = ref(null);

// AI Assistant State
const showAIAssistant = ref(false);
const aiPrompt = ref('');
const aiExtra = ref('');
const aiResponse = ref('');

// Table columns
const questionColumns = [
  {
    name: 'title',
    required: true,
    label: 'Question Title',
    align: 'left',
    field: 'title',
    sortable: true,
    style: 'width: 300px'
  },
  {
    name: 'type',
    label: 'Type',
    align: 'center',
    field: 'type',
    sortable: true
  },
  {
    name: 'category',
    label: 'Category',
    align: 'left',
    field: 'category',
    sortable: false
  },
  {
    name: 'language',
    label: 'Language',
    align: 'center',
    field: 'language',
    sortable: true
  },
  {
    name: 'is_required',
    label: 'Required',
    align: 'center',
    field: 'is_required',
    sortable: true
  },
  {
    name: 'actions',
    label: 'Actions',
    align: 'center',
    sortable: false,
    style: 'width: 180px'
  }
];

const answerColumns = [
  {
    name: 'user_id',
    label: 'User ID',
    align: 'center',
    field: 'user_id',
    sortable: true
  },
  {
    name: 'answer_text',
    label: 'Answer',
    align: 'left',
    field: 'answer_text',
    sortable: false
  },
  {
    name: 'media_links',
    label: 'Media',
    align: 'center',
    field: 'media_links',
    sortable: false
  },
  {
    name: 'status',
    label: 'Status',
    align: 'center',
    field: 'status',
    sortable: true
  },
  {
    name: 'actions',
    label: 'Actions',
    align: 'center',
    sortable: false,
    style: 'width: 100px'
  }
];

// Methods
const toggleAnswers = (question) => {
  const questionId = question.id;
  const index = expandedRows.value.indexOf(questionId);

  if (index > -1) {
    // Collapse
    expandedRows.value.splice(index, 1);
  } else {
    // Expand and load answers
    expandedRows.value.push(questionId);
    loadAnswers(questionId).catch(err => {
      console.error('Failed to load answers:', err);
    });
  }
};

const openQuestionForm = (question = null) => {
  selectedQuestion.value = question;
  questionFormVisible.value = true;
};

const openAnswerForm = (questionId, answer = null) => {
  selectedQuestionId.value = questionId;
  selectedAnswer.value = answer;
  answerFormVisible.value = true;
};

const handleSaveQuestion = (questionData) => {
  const isEditing = !!questionData.id;
  const operation = isEditing
    ? updateQuestion(questionData.id, questionData)
    : createQuestion(questionData);

  operation
    .then(() => {
      questionFormRef.value?.handleSaveComplete();
    })
    .catch(error => {
      questionFormRef.value?.handleSaveError(error);
    });
};

const handleSaveAnswer = (answerData) => {
  const isEditing = !!answerData.id;
  const operation = isEditing
    ? updateAnswer(answerData.id, answerData, answerData.question_id)
    : createAnswer(answerData.question_id, answerData);

  operation
    .then(() => {
      answerFormRef.value?.handleSaveComplete();
    })
    .catch(error => {
      answerFormRef.value?.handleSaveError(error);
    });
};

const handleDeleteQuestion = (questionId) => {
  deleteQuestion(questionId).catch(err => {
    console.error('Failed to delete question:', err);
  });
};

const handleDeleteAnswer = (answerId, questionId) => {
  deleteAnswer(answerId, questionId).catch(err => {
    console.error('Failed to delete answer:', err);
  });
};

// Utility methods
const getTypeColor = (type) => {
  const colors = {
    'text': 'blue',
    'textarea': 'green',
    'select': 'orange',
    'multi-select': 'purple',
    'media': 'pink',
    'file': 'teal'
  };
  return colors[type] || 'grey';
};

const getStatusColor = (status) => {
  const colors = {
    'draft': 'grey',
    'published': 'green',
    'review': 'orange',
    'archived': 'red'
  };
  return colors[status] || 'grey';
};

const truncateText = (text, maxLength) => {
  if (!text) return '';
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

const openLink = (url) => {
  window.open(url, '_blank');
};

const showQuestionDetails = (question) => {
  selectedQuestion.value = question;
  questionDetailsVisible.value = true;
};

const handleQuestionUpdated = (question) => {
  questionFormVisible.value = true;
  selectedQuestion.value = question;
  questionDetailsVisible.value = false;
};

// AI Assistant Methods
const handleAIResponse = (response) => {
  aiResponse.value = response;
};

const handleUseAsQuestion = (content) => {
  // Parse the AI-generated content and pre-fill the question form
  showAIAssistant.value = false;

  // Extract title and description from the content
  const lines = content.split('\n').filter(line => line.trim());
  let title = '';
  let description = '';

  // Try to extract structured content
  for (let line of lines) {
    if (line.match(/^\d+\.\s*\*?\*?(.+)/)) {
      // Found a numbered question
      title = line.replace(/^\d+\.\s*\*?\*?/, '').trim();
      break;
    } else if (line.includes('Question') && line.includes(':')) {
      title = line.split(':')[1].trim();
      break;
    }
  }

  if (!title) {
    title = lines[0] || 'AI Generated Question';
  }

  description = content;

  // Pre-fill the question form
  selectedQuestion.value = {
    title: title,
    description: description,
    type: 'behavioral',
    category: ['AI Generated'],
    language: 'en',
    is_required: false
  };

  questionFormVisible.value = true;
};

const handleUseAsAnswer = (content) => {
  // This would be used when generating answers for existing questions
  showAIAssistant.value = false;

  if (selectedQuestionId.value) {
    selectedAnswer.value = {
      question_id: selectedQuestionId.value,
      answer_text: content,
      is_preferred: false
    };
    answerFormVisible.value = true;
  } else {
    // Show notification to select a question first
    console.log('Please select a question first to add an AI-generated answer');
  }
};

// Lifecycle
onMounted(() => {
  // Load initial data
  Promise.all([
    loadQuestions(),
    loadCategories(),
    loadQuestionTypes()
  ]).catch(err => {
    console.error('Failed to load initial data:', err);
  });
});
</script>

<style scoped>
.resume-questions-manager {
  max-width: 1400px;
  margin: auto;
}

.q-table {
  border-radius: 8px;
}

.q-card {
  border-radius: 8px;
}

/* Custom styling for expanded rows */
.q-table__container .q-table__middle .q-table tbody tr.q-table__tr--expanded {
  background-color: #f8f9fa;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .resume-questions-manager {
    padding: 8px;
  }

  .row.q-gutter-md {
    flex-direction: column;
  }

  .row.q-gutter-md > * {
    margin: 4px 0;
  }
}
</style>
