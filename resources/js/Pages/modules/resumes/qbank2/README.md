# Enhanced Resume Questions Manager

A comprehensive Vue 3 + Quasar solution for managing resume-related questions and their answers with expandable nested tables and full CRUD operations.

## Features

✅ **Main Questions Table**
- Display questions with title, type, category, language, and required status
- Expandable rows to show nested answers
- Add, Edit, Delete operations with confirmation dialogs
- Filtering by search, category, and type
- Loading indicators and error handling

✅ **Nested Answers Table**
- Shows answers for each question when expanded
- User ID, answer text, media links, and status columns
- Add, Edit, Delete operations for answers
- File upload support with media links

✅ **Form Components**
- `QuestionForm.vue`: Dialog-based form for questions with validation
- `AnswerForm.vue`: Dialog-based form for answers with media support
- Proper error handling and user feedback

✅ **Composables**
- `useQuestions`: State management for questions
- `useAnswers`: State management for answers
- Reusable logic with `.then()` style promises

✅ **API Integration**
- Comprehensive `resumeApi.js` with all CRUD operations
- Error handling and loading states
- File upload support

## Quick Start

### 1. Import and Use the Main Component

```vue
<template>
  <div>
    <ResumeQuestionsManager />
  </div>
</template>

<script setup>
import { ResumeQuestionsManager } from '@/Pages/modules/resumes/qbank2';
</script>
```

### 2. Use Individual Components

```vue
<template>
  <div>
    <!-- Custom implementation using individual components -->
    <QuestionForm 
      v-model="showForm" 
      :question="selectedQuestion"
      @save="handleSave"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { QuestionForm, useQuestions } from '@/Pages/modules/resumes/qbank2';

const { createQuestion, updateQuestion } = useQuestions();
const showForm = ref(false);
const selectedQuestion = ref(null);

const handleSave = (questionData) => {
  const operation = questionData.id 
    ? updateQuestion(questionData.id, questionData)
    : createQuestion(questionData);
    
  operation.then(() => {
    showForm.value = false;
  });
};
</script>
```

## Components

### ResumeQuestionsManager.vue
The main component that provides the complete interface:
- Questions table with expandable rows
- Nested answers tables
- Filter controls
- Form dialogs for CRUD operations

### QuestionForm.vue
Dialog-based form for creating/editing questions:
- **Props**: `modelValue`, `question`, `categories`, `questionTypes`
- **Events**: `update:modelValue`, `save`
- **Features**: Validation, error handling, dynamic options

### AnswerForm.vue
Dialog-based form for creating/editing answers:
- **Props**: `modelValue`, `answer`, `questionId`
- **Events**: `update:modelValue`, `save`
- **Features**: Media links, file uploads, status management

## Composables

### useQuestions()
Manages questions state and operations:
```js
const {
  questions,           // ref([]) - Array of questions
  loading,            // ref(false) - Loading state
  categories,         // ref([]) - Available categories
  questionTypes,      // ref([]) - Available question types
  filters,            // reactive({}) - Current filters
  loadQuestions,      // () => Promise - Load questions
  createQuestion,     // (data) => Promise - Create question
  updateQuestion,     // (id, data) => Promise - Update question
  deleteQuestion,     // (id) => Promise - Delete with confirmation
  loadCategories,     // () => Promise - Load categories
  loadQuestionTypes,  // () => Promise - Load question types
  clearFilters,       // () => void - Clear all filters
  applyFilters        // () => Promise - Apply current filters
} = useQuestions();
```

### useAnswers()
Manages answers state and operations:
```js
const {
  answersByQuestion,  // reactive({}) - Answers grouped by question ID
  loadingStates,      // reactive({}) - Loading states by question ID
  error,              // ref(null) - Error state
  loadAnswers,        // (questionId) => Promise - Load answers
  createAnswer,       // (questionId, data) => Promise - Create answer
  updateAnswer,       // (id, data, questionId) => Promise - Update answer
  deleteAnswer,       // (id, questionId) => Promise - Delete with confirmation
  uploadMedia,        // (file) => Promise - Upload media file
  getAnswers,         // (questionId) => Array - Get answers for question
  isLoading,          // (questionId) => Boolean - Check loading state
  getAnswerCount      // (questionId) => Number - Get answer count
} = useAnswers();
```

## API Module

### resumeApi.js
Comprehensive API module with all CRUD operations:

**Questions API:**
- `getQuestions(filters)` - Fetch questions with filters
- `getQuestion(id)` - Get single question
- `createQuestion(payload)` - Create new question
- `updateQuestion(id, payload)` - Update question
- `deleteQuestion(id)` - Delete question

**Answers API:**
- `getAnswers(questionId)` - Fetch answers for question
- `getAnswer(id)` - Get single answer
- `createAnswer(questionId, payload)` - Create new answer
- `updateAnswer(id, payload)` - Update answer
- `deleteAnswer(id)` - Delete answer

**Utility Methods:**
- `uploadMedia(file)` - Upload media files
- `getCategories()` - Get question categories
- `getQuestionTypes()` - Get question types

## Data Structure

### Question Object
```js
{
  id: 1,
  title: "Question title",
  type: "text|textarea|select|multi-select|media|file",
  category: ["Category1", "Category2"],
  language: "en",
  tags: ["tag1", "tag2"],
  options: ["option1", "option2"], // for select types
  default_answer: "Default text",
  is_required: true,
  description: "Question description"
}
```

### Answer Object
```js
{
  id: 1,
  question_id: 1,
  user_id: 123,
  answer_text: "Answer content",
  media_links: ["url1", "url2"],
  attachments: [
    { name: "file.pdf", type: "application/pdf", size: 1024 }
  ],
  status: "draft|published|review|archived",
  notes: "Optional notes",
  is_public: false
}
```

## Styling

The components use Quasar's design system with custom styling:
- Responsive design with mobile-friendly layouts
- Consistent spacing and colors
- Loading states and animations
- Accessible form controls

## Error Handling

- API errors are caught and displayed via Quasar notifications
- Form validation with real-time feedback
- Confirmation dialogs for destructive actions
- Loading states prevent multiple submissions

## Browser Support

- Modern browsers with ES6+ support
- Vue 3 and Quasar framework required
- Axios for HTTP requests
