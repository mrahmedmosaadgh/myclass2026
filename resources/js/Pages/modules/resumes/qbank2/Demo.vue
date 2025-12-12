<template>
   
    <q-card flat bordered class="q-mb-md">
      <q-card-section>
        <div class="text-h4 q-mb-md">Resume Questions Manager Demo</div>
        <div class="text-body1 q-mb-md">
          This is a demonstration of the enhanced Resume Questions Manager with all features:
        </div>
        <q-list dense>
          <q-item>
            <q-item-section avatar>
              <q-icon name="check_circle" color="positive" />
            </q-item-section>
            <q-item-section>Expandable questions table with nested answers</q-item-section>
          </q-item>
          <q-item>
            <q-item-section avatar>
              <q-icon name="check_circle" color="positive" />
            </q-item-section>
            <q-item-section>Full CRUD operations with confirmation dialogs</q-item-section>
          </q-item>
          <q-item>
            <q-item-section avatar>
              <q-icon name="check_circle" color="positive" />
            </q-item-section>
            <q-item-section>Form validation and error handling</q-item-section>
          </q-item>
          <q-item>
            <q-item-section avatar>
              <q-icon name="check_circle" color="positive" />
            </q-item-section>
            <q-item-section>Loading states and notifications</q-item-section>
          </q-item>
          <q-item>
            <q-item-section avatar>
              <q-icon name="check_circle" color="positive" />
            </q-item-section>
            <q-item-section>Reusable composables and clean architecture</q-item-section>
          </q-item>
        </q-list>
      </q-card-section>
    </q-card>

    <!-- Demo Controls -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section>
        <div class="text-h6 q-mb-md">Demo Controls</div>
        <div class="row q-gutter-md">
          <q-btn 
            color="primary" 
            icon="add" 
            label="Test Question Form" 
            @click="testQuestionForm"
          />
          <q-btn 
            color="secondary" 
            icon="quiz" 
            label="Test Answer Form" 
            @click="testAnswerForm"
          />
          <q-btn 
            color="info" 
            icon="refresh" 
            label="Load Sample Data" 
            @click="loadSampleData"
            :loading="loadingSample"
          />
          <q-btn 
            color="warning" 
            icon="clear_all" 
            label="Clear Data" 
            @click="clearData"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- Main Component -->
    <ResumeQuestionsManager />

    <!-- Test Forms -->
    <QuestionForm
      v-model="showQuestionForm"
      :question="testQuestion"
      @save="handleTestQuestionSave"
    />

    <AnswerForm
      v-model="showAnswerForm"
      :answer="testAnswer"
      :question-id="1"
      @save="handleTestAnswerSave"
    />
 
</template>

<script setup>
import { ref } from 'vue';
import { useQuasar } from 'quasar';
import { 
  ResumeQuestionsManager, 
  QuestionForm, 
  AnswerForm,
  useQuestions,
  useAnswers
} from './index.js';

// Composables
const $q = useQuasar();
const { createQuestion } = useQuestions();
const { createAnswer } = useAnswers();

// State
const showQuestionForm = ref(false);
const showAnswerForm = ref(false);
const loadingSample = ref(false);
const testQuestion = ref(null);
const testAnswer = ref(null);

// Sample data
const sampleQuestions = [
  {
    title: "Tell me about your professional background",
    type: "textarea",
    category: ["Experience"],
    language: "en",
    tags: ["core", "background"],
    is_required: true,
    description: "Provide a comprehensive overview of your work experience"
  },
  {
    title: "What programming languages are you proficient in?",
    type: "multi-select",
    category: ["Technical"],
    language: "en",
    tags: ["technical", "skills"],
    options: ["JavaScript", "Python", "Java", "PHP", "C++", "Go", "Rust"],
    is_required: true,
    description: "Select all programming languages you're comfortable working with"
  },
  {
    title: "Upload your portfolio or work samples",
    type: "media",
    category: ["Portfolio"],
    language: "en",
    tags: ["portfolio", "samples"],
    is_required: false,
    description: "Share examples of your work (images, documents, or links)"
  }
];

const sampleAnswers = [
  {
    user_id: 1,
    answer_text: "I have 5 years of experience in full-stack web development, specializing in Vue.js and Laravel. I've worked on various projects ranging from e-commerce platforms to enterprise applications.",
    media_links: ["https://github.com/user/portfolio", "https://linkedin.com/in/user"],
    status: "published",
    is_public: true
  },
  {
    user_id: 2,
    answer_text: "I'm proficient in JavaScript, Python, and PHP. I have experience with modern frameworks and have been working with these technologies for over 3 years.",
    status: "published",
    is_public: true
  }
];

// Methods
const testQuestionForm = () => {
  testQuestion.value = null; // New question
  showQuestionForm.value = true;
};

const testAnswerForm = () => {
  testAnswer.value = null; // New answer
  showAnswerForm.value = true;
};

const handleTestQuestionSave = (questionData) => {
  console.log('Test question save:', questionData);
  $q.notify({
    type: 'info',
    message: 'This is a demo - question data logged to console',
    position: 'top'
  });
  showQuestionForm.value = false;
};

const handleTestAnswerSave = (answerData) => {
  console.log('Test answer save:', answerData);
  $q.notify({
    type: 'info',
    message: 'This is a demo - answer data logged to console',
    position: 'top'
  });
  showAnswerForm.value = false;
};

const loadSampleData = () => {
  loadingSample.value = true;
  
  // Simulate API calls
  setTimeout(() => {
    $q.notify({
      type: 'positive',
      message: 'Sample data loaded! (This is a demo - check browser console for data)',
      position: 'top'
    });
    
    console.log('Sample Questions:', sampleQuestions);
    console.log('Sample Answers:', sampleAnswers);
    
    loadingSample.value = false;
  }, 1500);
};

const clearData = () => {
  $q.dialog({
    title: 'Clear Demo Data',
    message: 'This would clear all demo data. Continue?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    $q.notify({
      type: 'info',
      message: 'Demo data cleared (simulated)',
      position: 'top'
    });
  });
};
</script>

<style scoped>
.q-page {
  max-width: 1200px;
  margin: auto;
}

.q-card {
  border-radius: 8px;
}
</style>
