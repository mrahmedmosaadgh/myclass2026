# Resume Questions Manager (QBank2) - Complete Reference Guide

## Overview
The Resume Questions Manager (QBank2) is a comprehensive Vue 3 + Quasar application for managing resume-related questions and answers with advanced interactive features including comments, ratings, likes, voice notes, AI integration, and expandable nested tables.

## Architecture

### Frontend Structure
```
resources/js/Pages/modules/resumes/qbank2/
├── ResumeQuestionsManager.vue     # Main component (21KB, 771 lines)
├── QuestionDetailsDialog.vue      # Full-screen question details (8.2KB, 316 lines)
├── AnswerCard.vue                 # Individual answer display (17KB, 610 lines)
├── CommentsSection.vue            # Comments and replies (14KB, 529 lines)
├── AnswerForm.vue                 # Add/edit answers (9KB, 356 lines)
├── AnswerFormDialog.vue           # Dialog-based answer form (11KB, 454 lines)
├── VoiceRecorderDialog.vue        # Voice note recording (14KB, 571 lines)
├── VoicePlayer.vue                # Voice playback component (7.7KB, 372 lines)
├── QuestionForm.vue               # Question creation/editing (8.2KB, 333 lines)
├── Demo.vue                       # Demo/example component (6.5KB, 243 lines)
├── resumeApi.js                   # API integration module (13KB, 474 lines)
├── index.js                       # Module exports (921B, 34 lines)
├── README.md                      # Comprehensive documentation (6.5KB, 225 lines)
├── components/
│   ├── GeminiPrompt.vue           # AI-powered prompt generation (15KB, 567 lines)
│   └── TextToSpeechButton.vue     # Text-to-speech functionality (5KB, 228 lines)
└── composables/
    ├── useQuestions.js            # Question state management (6.4KB, 244 lines)
    └── useAnswers.js              # Answer state management (5.2KB, 221 lines)
```

### Backend Structure
```
app/
├── Http/Controllers/Api/
│   └── ResumeAnswerController.php    # Main API controller
├── Models/
│   ├── ResumeAnswer.php             # Answer model
│   ├── ResumeQuestion.php           # Question model
│   ├── ResumeAnswerRating.php       # Rating system
│   ├── ResumeAnswerLike.php         # Like system
│   ├── ResumeCommentLike.php        # Comment likes
│   ├── ResumeQuestionComment.php    # Comments system
│   ├── ResumeAnswerBookmark.php     # Bookmarking
│   └── ResumeAnswerReport.php       # Content reporting
└── database/migrations/
    ├── 2024_12_XX_create_resume_answer_ratings_table.php
    ├── 2024_12_XX_create_resume_answer_likes_table.php
    ├── 2024_12_XX_create_resume_comment_likes_table.php
    ├── 2024_12_XX_create_resume_answer_bookmarks_table.php
    ├── 2024_12_XX_create_resume_answer_reports_table.php
    └── 2024_12_XX_add_interaction_fields_to_resume_answers.php
```

## Key Features

### 1. Question Management
- **Browse Questions**: Paginated list with search and filtering
- **Question Details**: Full-screen dialog with comprehensive answer view
- **Question Types**: Support for multiple question categories
- **Expandable Tables**: Nested answers display within question rows
- **Form Dialogs**: Dedicated forms for question creation/editing

### 2. Answer Management
- **Create Answers**: Rich text editor with media support
- **Edit/Delete**: Full CRUD operations with proper permissions
- **Status Management**: Draft, published, private states
- **File Attachments**: Support for multiple file types
- **Dialog Forms**: Enhanced form dialogs for better UX

### 3. Interactive Features
- **Rating System**: 5-star rating with average calculations
- **Like System**: Toggle likes with real-time counts
- **Comments**: Threaded comments with replies
- **Voice Notes**: Record and attach voice responses
- **Voice Playback**: Advanced voice player component
- **Bookmarking**: Save answers for later reference

### 4. AI & Voice Integration
- **Gemini AI**: AI-powered prompt generation
- **Text-to-Speech**: Convert text to speech
- **Voice Recording**: Advanced voice recording with metadata
- **Voice Playback**: Enhanced audio player with controls

### 5. User Experience
- **Real-time Updates**: Instant UI feedback
- **Responsive Design**: Mobile-friendly interface
- **Accessibility**: Keyboard navigation and screen reader support
- **Performance**: Optimized loading and caching
- **Modular Architecture**: Composables for reusable logic

## Technical Implementation

### Vue 3 Patterns Used
```javascript
// Composition API with script setup
<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'

// Props and emits
const props = defineProps({
  question: Object,
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue', 'refresh'])

// Reactive state
const answers = ref([])
const loading = ref(false)

// Computed properties
const hasAnswers = computed(() => answers.value.length > 0)

// Methods using .then() style (user preference)
const loadData = () => {
  resumeApi.getAnswers(props.question.id)
    .then(data => {
      answers.value = data
    })
    .catch(error => {
      console.error('Error:', error)
    })
}
</script>
```

### Composables Pattern
```javascript
// useQuestions.js - Centralized question state management
export function useQuestions() {
  const questions = ref([])
  const loading = ref(false)
  const categories = ref([])
  const questionTypes = ref([])
  const filters = reactive({})

  const loadQuestions = () => {
    return resumeApi.getQuestions(filters)
      .then(data => {
        questions.value = data
      })
  }

  const createQuestion = (data) => {
    return resumeApi.createQuestion(data)
      .then(response => {
        questions.value.push(response)
      })
  }

  return {
    questions,
    loading,
    categories,
    questionTypes,
    filters,
    loadQuestions,
    createQuestion,
    // ... other methods
  }
}

// useAnswers.js - Centralized answer state management
export function useAnswers() {
  const answersByQuestion = reactive({})
  const loadingStates = reactive({})
  const error = ref(null)

  const loadAnswers = (questionId) => {
    return resumeApi.getAnswers(questionId)
      .then(data => {
        answersByQuestion[questionId] = data
      })
  }

  return {
    answersByQuestion,
    loadingStates,
    error,
    loadAnswers,
    // ... other methods
  }
}
```

### API Integration Pattern
```javascript
// resumeApi.js - Enhanced API module with voice and AI features
const resumeApi = {
  // Questions
  getQuestions(params = {}) {
    return axios.get('/api/resume-questions', { params })
      .then(response => response.data)
  },

  // Answers
  getAnswers(questionId) {
    return axios.get(`/api/resume-questions/${questionId}/answers`)
      .then(response => response.data)
  },

  createAnswer(questionId, answerData) {
    return axios.post(`/api/resume-questions/${questionId}/answers`, answerData)
      .then(response => response.data)
  },

  // Voice Features
  uploadVoiceNote(file) {
    const formData = new FormData()
    formData.append('voice_note', file)
    return axios.post('/api/voice-notes/upload', formData)
      .then(response => response.data)
  },

  // AI Integration
  generatePrompt(context) {
    return axios.post('/api/ai/generate-prompt', { context })
      .then(response => response.data)
  },

  // Interactions
  rateAnswer(answerId, rating) {
    return axios.post(`/api/answers/${answerId}/rate`, { rating })
      .then(response => response.data)
  },

  toggleAnswerLike(answerId) {
    return axios.post(`/api/answers/${answerId}/like`)
      .then(response => response.data)
  },

  // Comments
  createComment(answerId, commentData) {
    return axios.post(`/api/answers/${answerId}/comments`, commentData)
      .then(response => response.data)
  }
}
```

### Module Exports
```javascript
// index.js - Clean module exports
export { default as ResumeQuestionsManager } from './ResumeQuestionsManager.vue';
export { default as QuestionForm } from './QuestionForm.vue';
export { default as AnswerForm } from './AnswerForm.vue';
export { default as resumeApi } from './resumeApi.js';
export { useQuestions } from './composables/useQuestions.js';
export { useAnswers } from './composables/useAnswers.js';
```

### Database Schema

#### Core Tables
```sql
-- resume_answers (enhanced)
ALTER TABLE resume_answers ADD COLUMN voice_note_path VARCHAR(255);
ALTER TABLE resume_answers ADD COLUMN voice_note_duration INTEGER;
ALTER TABLE resume_answers ADD COLUMN voice_note_metadata JSON;
ALTER TABLE resume_answers ADD COLUMN views_count INTEGER DEFAULT 0;
ALTER TABLE resume_answers ADD COLUMN average_rating DECIMAL(3,2) DEFAULT 0;
ALTER TABLE resume_answers ADD COLUMN ratings_count INTEGER DEFAULT 0;
ALTER TABLE resume_answers ADD COLUMN likes_count INTEGER DEFAULT 0;
ALTER TABLE resume_answers ADD COLUMN comments_count INTEGER DEFAULT 0;
ALTER TABLE resume_answers ADD COLUMN is_featured BOOLEAN DEFAULT FALSE;
ALTER TABLE resume_answers ADD COLUMN featured_at TIMESTAMP NULL;
```

#### Interaction Tables
```sql
-- resume_answer_ratings
CREATE TABLE resume_answer_ratings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    answer_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_comment TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_user_answer_rating (user_id, answer_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (answer_id) REFERENCES resume_answers(id) ON DELETE CASCADE
);

-- resume_answer_likes
CREATE TABLE resume_answer_likes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    answer_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_user_answer_like (user_id, answer_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (answer_id) REFERENCES resume_answers(id) ON DELETE CASCADE
);

-- resume_comment_likes
CREATE TABLE resume_comment_likes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    comment_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_user_comment_like (user_id, comment_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES resume_question_comments(id) ON DELETE CASCADE
);
```

## API Endpoints

### Questions
- `GET /api/resume-questions` - List questions with pagination
- `GET /api/resume-questions/{id}` - Get single question
- `POST /api/resume-questions` - Create question
- `PUT /api/resume-questions/{id}` - Update question
- `DELETE /api/resume-questions/{id}` - Delete question

### Answers
- `GET /api/resume-questions/{questionId}/answers` - List answers for question
- `POST /api/resume-questions/{questionId}/answers` - Create answer
- `GET /api/answers/{id}` - Get single answer
- `PUT /api/answers/{id}` - Update answer
- `DELETE /api/answers/{id}` - Delete answer

### Voice Features
- `POST /api/voice-notes/upload` - Upload voice note
- `GET /api/voice-notes/{id}` - Get voice note
- `DELETE /api/voice-notes/{id}` - Delete voice note

### AI Integration
- `POST /api/ai/generate-prompt` - Generate AI prompt
- `POST /api/ai/text-to-speech` - Convert text to speech

### Interactions
- `POST /api/answers/{answerId}/rate` - Rate answer (1-5 stars)
- `GET /api/answers/{answerId}/ratings` - Get rating statistics
- `POST /api/answers/{answerId}/like` - Toggle like
- `POST /api/answers/{answerId}/bookmark` - Toggle bookmark

### Comments
- `GET /api/answers/{answerId}/comments` - Get comments with replies
- `POST /api/answers/{answerId}/comments` - Create comment
- `PUT /api/comments/{id}` - Update comment
- `DELETE /api/comments/{id}` - Delete comment
- `POST /api/comments/{id}/like` - Toggle comment like
- `POST /api/comments/{id}/reply` - Reply to comment

## Authentication & Authorization

### Session-Based Authentication
- Uses Laravel Sanctum with session-based auth
- Users must be logged in through web interface
- CSRF protection enabled for all API calls

### Permission System
```php
// Answer permissions
public function canEdit(ResumeAnswer $answer, User $user): bool
{
    return $answer->user_id === $user->id;
}

public function canDelete(ResumeAnswer $answer, User $user): bool
{
    return $answer->user_id === $user->id || $user->hasRole('admin');
}

// Comment permissions
public function canEditComment(ResumeQuestionComment $comment, User $user): bool
{
    return $comment->user_id === $user->id;
}
```

## Data Flow

### Loading Questions and Answers
1. **Component Mount**: `ResumeQuestionsManager.vue` loads with composables
2. **State Management**: `useQuestions()` and `useAnswers()` manage state
3. **API Call**: `resumeApi.getQuestions()` and `resumeApi.getAnswers()`
4. **Backend Processing**: Controller includes user interaction data
5. **Frontend Update**: Components receive data with user states
6. **UI Rendering**: Expandable tables with nested answers display

### User Interactions
1. **User Action**: Click star rating, like button, or submit comment
2. **API Call**: Immediate API request with optimistic UI update
3. **Backend Processing**: Database update + statistics recalculation
4. **Response Handling**: Update local state with server response
5. **UI Sync**: Ensure UI reflects actual database state

### Voice Features Flow
1. **Recording**: `VoiceRecorderDialog.vue` captures audio
2. **Upload**: `resumeApi.uploadVoiceNote()` sends to server
3. **Storage**: Server stores voice note with metadata
4. **Playback**: `VoicePlayer.vue` plays recorded audio
5. **AI Integration**: `GeminiPrompt.vue` generates AI content

## Common Issues & Solutions

### Field Name Mismatches
**Problem**: Frontend expects `rating_count` but backend returns `ratings_count`
**Solution**: Ensure consistent field naming across frontend/backend

### Authentication Errors
**Problem**: 419 CSRF token mismatch
**Solution**: Ensure user is logged in through web session, not just API tokens

### Import Path Issues
**Problem**: `Failed to fetch dynamically imported module`
**Solution**: Start Vite dev server and fix relative import paths

### Validation Errors
**Problem**: "The text field is required" when sending `comment` field
**Solution**: Match frontend field names with backend validation rules

### Voice Recording Issues
**Problem**: Voice notes not uploading or playing
**Solution**: Check file permissions, audio format support, and server storage

## Performance Optimizations

### Frontend
- **Lazy Loading**: Components loaded on demand
- **Debounced Search**: Prevent excessive API calls
- **Optimistic Updates**: Immediate UI feedback
- **Caching**: Store frequently accessed data
- **Composables**: Reusable state management

### Backend
- **Eager Loading**: Load relationships efficiently
- **Database Indexing**: Optimize query performance
- **Response Transformation**: Include only necessary data
- **Pagination**: Limit data transfer
- **Voice Processing**: Optimize audio file handling

## Development Workflow

### Adding New Features
1. **Database**: Create migration for new tables/fields
2. **Models**: Add relationships and helper methods
3. **API**: Create controller methods with proper validation
4. **Routes**: Define API endpoints with middleware
5. **Frontend**: Create/update Vue components
6. **Composables**: Add state management if needed
7. **Integration**: Update resumeApi.js with new methods
8. **Testing**: Test end-to-end functionality

### Debugging Steps
1. **Check Console**: Look for JavaScript errors
2. **Network Tab**: Verify API calls and responses
3. **Database**: Confirm data is being saved
4. **Authentication**: Ensure user is properly logged in
5. **Field Names**: Verify frontend/backend field consistency
6. **Voice Features**: Check audio file handling and permissions

## Best Practices

### Vue 3 Patterns
- Use Composition API with `<script setup>`
- Prefer `.then()` style over async/await (user preference)
- Implement proper error handling with user feedback
- Use Quasar components for consistent UI
- Leverage composables for reusable logic

### API Design
- RESTful endpoints with proper HTTP methods
- Consistent response formats
- Include user interaction data in responses
- Proper validation and error messages
- Support for voice and AI features

### Database Design
- Use foreign key constraints
- Implement soft deletes where appropriate
- Add indexes for performance
- Use appropriate data types
- Support for voice note metadata

### Voice & AI Integration
- Optimize audio file formats and sizes
- Implement proper error handling for AI calls
- Cache AI responses when appropriate
- Provide fallbacks for voice features
- Ensure accessibility for voice content

This reference guide provides comprehensive documentation for the enhanced Resume Questions Manager (QBank2) system, covering all aspects from architecture to implementation details including the latest voice and AI features.