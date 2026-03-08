# Real-time Question Components

Two reusable Vue components for real-time question and answer functionality.

## Components

### 1. QuestionDisplay.vue
Displays a live question and receives answers from multiple users in real-time.

**Features:**
- Shows question text prominently
- Displays all received answers with sender names
- Shows user avatars with initials
- Real-time statistics (average, min, max)
- Smooth animations for new answers
- Responsive design with dark mode support

**Props:**
```javascript
{
  questionTitle: String,      // Optional, default: 'Live Question'
  questionText: String,       // Required - The question to display
  answers: Array             // Array of answer objects
}
```

**Answer Object Format:**
```javascript
{
  id: String,           // Unique identifier
  senderName: String,   // Name of the person who answered
  value: Number,        // The numeric answer
  timestamp: Number     // Unix timestamp (seconds)
}
```

**Usage Example:**
```vue
<QuestionDisplay
  question-title="Math Quiz"
  question-text="What is 5 + 3?"
  :answers="liveAnswers"
/>
```

---

### 2. QuestionInput.vue
Allows users to submit their name and numeric answer to a question.

**Features:**
- Displays the question text
- Input field for user name
- Numeric input with validation
- Min/max value constraints
- Step control for decimals
- Submit button with loading states
- Success/error feedback messages
- Optional multiple submissions
- Keyboard shortcuts (Enter to submit)

**Props:**
```javascript
{
  questionText: String,              // Required - The question
  questionId: String,                // Required - Unique question ID
  minValue: Number,                  // Optional - Minimum allowed value
  maxValue: Number,                  // Optional - Maximum allowed value
  step: Number,                      // Optional - Step increment (default: 1)
  allowMultipleSubmissions: Boolean, // Optional - Allow resubmission (default: false)
  defaultUserName: String            // Optional - Pre-fill user name
}
```

**Events:**
```javascript
@submit - Emitted when answer is submitted
  Payload: {
    questionId: String,
    senderName: String,
    value: Number,
    timestamp: Number
  }
```

**Usage Example:**
```vue
<QuestionInput
  question-text="How many students are in your class?"
  question-id="q1"
  :min-value="1"
  :max-value="100"
  :allow-multiple-submissions="true"
  @submit="handleAnswerSubmit"
/>
```

---

## Complete Integration Example

```vue
<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Display Component (Teacher/Admin View) -->
    <QuestionDisplay
      question-title="Class Poll"
      question-text="How confident are you with today's lesson? (1-10)"
      :answers="receivedAnswers"
    />

    <!-- Input Component (Student View) -->
    <QuestionInput
      question-text="How confident are you with today's lesson? (1-10)"
      question-id="lesson-confidence-q1"
      :min-value="1"
      :max-value="10"
      :default-user-name="currentUser.name"
      @submit="submitToFirebase"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import QuestionDisplay from './QuestionDisplay.vue';
import QuestionInput from './QuestionInput.vue';
import { useRealtimeChannel } from '@/composables/useRealtimeChannel';
import axios from 'axios';

const receivedAnswers = ref([]);
const currentUser = { name: 'Ahmed' };

// Listen for new answers
useRealtimeChannel('question.lesson-confidence-q1', (signal) => {
  if (signal.event === 'NEW_RESPONSE') {
    receivedAnswers.value.push({
      id: signal.context.id || Date.now(),
      senderName: signal.context.senderName,
      value: signal.context.value,
      timestamp: signal.timestamp
    });
  }
});

// Submit answer to backend
const submitToFirebase = async (answerData) => {
  try {
    await axios.post('/api/realtime/test/question', answerData);
    console.log('Answer submitted:', answerData);
  } catch (error) {
    console.error('Failed to submit answer:', error);
  }
};
</script>
```

---

## Styling

Both components use:
- **Tailwind CSS** for styling
- **Dark mode** support
- **Smooth animations** for better UX
- **Responsive design** for mobile/desktop

---

## Methods (QuestionInput)

The `QuestionInput` component exposes a `reset()` method:

```vue
<template>
  <QuestionInput ref="inputRef" ... />
  <button @click="resetForm">Clear Form</button>
</template>

<script setup>
import { ref } from 'vue';

const inputRef = ref(null);

const resetForm = () => {
  inputRef.value.reset();
};
</script>
```

---

## Notes

- Both components are fully reactive and update in real-time
- Use with Firebase Realtime Database or similar real-time backend
- Compatible with the existing `useRealtimeChannel` composable
- Follows the project's design system (indigo/teal color schemes)
