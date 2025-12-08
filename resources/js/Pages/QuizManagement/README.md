# Quiz System - Quick Start Guide

## Installation

1. **Install Dependencies**:
```bash
npm install vuedraggable
```

2. **Import Components** (in your main app or router):
```javascript
// In your routes file
import QuizDashboard from '@/Pages/QuizManagement/QuizDashboard.vue';
import QuizBuilder from '@/Pages/QuizManagement/QuizBuilder.vue';
import QuizPreview from '@/Pages/QuizManagement/QuizPreview.vue';
import QuizAnalytics from '@/Pages/QuizManagement/QuizAnalytics.vue';
```

3. **Add Routes**:
```javascript
{
  path: '/quizzes',
  name: 'quiz.index',
  component: QuizDashboard
},
{
  path: '/quizzes/create',
  name: 'quiz.create',
  component: QuizBuilder
},
{
  path: '/quizzes/:id/edit',
  name: 'quiz.edit',
  component: QuizBuilder
},
{
  path: '/quizzes/:id/preview',
  name: 'quiz.preview',
  component: QuizPreview
},
{
  path: '/quizzes/:id/analytics',
  name: 'quiz.analytics',
  component: QuizAnalytics
}
```

## File Structure

```
resources/js/
├── Pages/
│   └── QuizManagement/
│       ├── QuizDashboard.vue      # Main quiz list
│       ├── QuizBuilder.vue        # Create/edit quizzes
│       ├── QuizPreview.vue        # Preview mode
│       └── QuizAnalytics.vue      # Analytics dashboard
├── Components/
│   └── Quiz/
│       ├── QuizCard.vue           # Quiz display card
│       ├── QuizStats.vue          # Statistics card
│       └── QuestionCard.vue       # Question display card
├── composables/
│   └── useQuiz.js                 # Quiz state management
└── services/
    └── quizService.js             # API calls & utilities
```

## Usage Examples

### Using the Quiz Composable

```vue
<script setup>
import { useQuiz } from '@/composables/useQuiz';

const { 
  quizzes, 
  loading, 
  fetchQuizzes, 
  createQuiz 
} = useQuiz();

// Fetch quizzes
await fetchQuizzes({ status: 'active' });

// Create a quiz
const newQuiz = await createQuiz({
  name: 'Math Quiz',
  description: 'Basic algebra',
  grade_id: 8,
  subject_id: 2,
  time_limit_minutes: 30,
  shuffle_questions: true,
  question_ids: [1, 2, 3, 4, 5]
});
</script>
```

### Using Quiz Components

```vue
<template>
  <quiz-card
    :quiz="quiz"
    @preview="handlePreview"
    @edit="handleEdit"
    @delete="handleDelete"
  />
</template>

<script setup>
import QuizCard from '@/Components/Quiz/QuizCard.vue';

const handlePreview = (quiz) => {
  router.push(`/quizzes/${quiz.id}/preview`);
};
</script>
```

### Using Quiz Service

```javascript
import { quizApi, quizUtils } from '@/services/quizService';

// Fetch quizzes
const quizzes = await quizApi.getQuizzes({ grade_id: 8 });

// Calculate statistics
const stats = quizUtils.calculateStats(quiz);
console.log(stats.estimatedTime); // "15 min"

// Format time
const formatted = quizUtils.formatTime(890); // "14:50"
```

## Component Props

### QuizCard
```typescript
{
  quiz: Object,        // Quiz object
  disableHover: Boolean // Disable hover effects
}

// Events: click, preview, edit, analytics, duplicate, export, delete
```

### QuizStats
```typescript
{
  icon: String,        // Material icon name
  label: String,       // Label text
  value: Number|String,// Display value
  trend: Number,       // Trend percentage (optional)
  variant: String,     // 'primary'|'success'|'warning'|'info'
  animate: Boolean     // Enable counter animation
}
```

### QuestionCard
```typescript
{
  question: Object,    // Question object
  draggable: Boolean,  // Enable drag handle
  showPreview: Boolean,// Show preview button
  showRemove: Boolean  // Show remove button
}

// Events: preview, remove
```

## API Integration

The system expects these endpoints:

```
GET    /api/quizzes
POST   /api/quizzes
GET    /api/quizzes/{id}
PUT    /api/quizzes/{id}
DELETE /api/quizzes/{id}
POST   /api/quizzes/{id}/duplicate
GET    /api/quizzes/{id}/export
GET    /api/quizzes/{id}/analytics

GET    /api/questions
GET    /api/question-types
GET    /api/topics
GET    /api/grades
GET    /api/subjects
```

## Customization

### Colors

Edit the CSS variables in `QuizEngineEnhanced.css`:

```css
:root {
  --quiz-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --quiz-success: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
  /* ... */
}
```

### Animations

Adjust animation durations in component styles:

```scss
.quiz-card {
  transition: all 0.3s ease; // Change duration here
}
```

## Tips

1. **Performance**: Use virtual scrolling for large question lists
2. **Accessibility**: All components include ARIA labels
3. **Mobile**: Fully responsive, test on actual devices
4. **Dark Mode**: Automatic detection via `prefers-color-scheme`
5. **Validation**: Use `quizUtils.validateQuiz()` before saving

## Troubleshooting

**Issue**: Drag-and-drop not working  
**Solution**: Ensure `vuedraggable` is installed and imported

**Issue**: Charts not displaying  
**Solution**: Install Chart.js: `npm install chart.js vue-chartjs`

**Issue**: Styles not applying  
**Solution**: Import enhanced CSS in QuizEngine component

## Support

For issues or questions, refer to:
- Architecture doc: `.kiro/specs/enterprise-quiz-system/QUIZ_SYSTEM_ARCHITECTURE.md`
- Walkthrough: `walkthrough.md`
- Implementation plan: `implementation_plan.md`
