// Enhanced Resume Questions Manager - Export Module
// This module provides a complete solution for managing resume questions and answers

// Main Components
export { default as ResumeQuestionsManager } from './ResumeQuestionsManager.vue';
export { default as QuestionForm } from './QuestionForm.vue';
export { default as AnswerForm } from './AnswerForm.vue';

// API Module
export { default as resumeApi } from './resumeApi.js';

// Composables
export { useQuestions } from './composables/useQuestions.js';
export { useAnswers } from './composables/useAnswers.js';

// Usage Example:
/*
import { ResumeQuestionsManager } from '@/Pages/modules/resumes/qbank2';

// In your Vue component:
<template>
  <ResumeQuestionsManager />
</template>

// Or use individual components and composables:
import { 
  QuestionForm, 
  AnswerForm, 
  useQuestions, 
  useAnswers,
  resumeApi 
} from '@/Pages/modules/resumes/qbank2';
*/
