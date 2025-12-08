# Question System - Complete Documentation

## 🎯 Overview

A production-ready, scalable question system for educational platforms built with **Laravel + Vue 3 + TypeScript**. Supports 11 question types with multiple versions, providing flexibility for different learning styles and subjects.

## 📁 Folder Structure

```
types_system/
├── types.ts                          # TypeScript type definitions
├── QuestionRenderer.vue              # Main dispatcher component
├── composables/                      # Shared composables
│   ├── useRecording.ts              # Audio recording with Web Audio API
│   └── useTimer.ts                  # Countdown timer functionality
├── types/                            # Question type implementations
│   ├── labelled-diagram/
│   │   ├── Main.vue                 # Version selector + renderer
│   │   └── versions/
│   │       ├── Default.vue          # Click-to-label
│   │       ├── DragDrop.vue         # Drag & drop labels
│   │       ├── Interactive.vue      # Hover + modal input
│   │       └── index.ts             # Version exports
│   ├── speaking-cards/
│   │   ├── Main.vue
│   │   └── versions/
│   │       ├── Default.vue          # Browser recording + playback
│   │       └── index.ts
│   ├── match-up/
│   │   ├── Main.vue
│   │   └── versions/
│   │       ├── Default.vue
│   │       ├── WithAudio.vue
│   │       ├── ImageOnly.vue
│   │       └── index.ts
│   ├── quiz/
│   ├── image-quiz/
│   ├── group-sort/
│   ├── missing-word/
│   ├── sequence/
│   ├── anagram/
│   ├── multiple-choice/
│   └── true-false/
└── presets/                          # JSON question presets
    ├── science/
    │   ├── preset1.json             # Human Body Systems
    │   ├── preset2.json             # Plant Biology
    │   └── preset3.json             # Solar System
    ├── math/
    │   ├── preset1.json             # Geometry Fundamentals
    │   ├── preset2.json             # Algebra Basics
    │   └── preset3.json             # Fractions and Decimals
    ├── language-learning/
    │   ├── preset1.json             # English Pronunciation
    │   ├── preset2.json             # Spanish Vocabulary
    │   └── preset3.json             # French Grammar
    └── general/
        ├── preset1.json             # General Knowledge Quiz
        ├── preset2.json             # Logic and Reasoning
        └── preset3.json             # Mixed Topics Challenge
```

## 🎨 Question Types

### 1. **Labelled Diagram** (3 versions)
- **Default**: Click points to type labels
- **Drag & Drop**: Drag labels from word bank to diagram
- **Interactive**: Hover tooltips + modal input with animations

**Use Cases**: Anatomy, geography, plant parts, solar system

### 2. **Speaking Cards** (2 versions)
- **Default**: Browser-based audio recording with playback
- **With Feedback**: AI pronunciation analysis (future)

**Features**: Microphone recording, reference audio, phonetic transcription

### 3. **Match Up** (3 versions)
- **Default**: Click to connect pairs
- **With Audio**: Listen and match (language learning)
- **Image Only**: Visual matching

**Supports**: Text, images, LaTeX, audio

### 4. **Image Quiz** (2 versions)
- **Default**: Select from image options
- **With Timer**: Timed challenge mode

### 5. **Group Sort** (2 versions)
- **Default**: Drag items into categories
- **Timed**: Race against the clock

### 6. **Missing Word** (2 versions)
- **Default**: Fill in the blanks
- **With Hints**: Progressive hint system

**Features**: Word bank, alternative answers, hints

### 7. **Sequence** (2 versions)
- **Default**: Drag to reorder
- **Animated**: Smooth transitions

### 8. **Anagram** (2 versions)
- **Default**: Unscramble letters
- **Timed**: Speed challenge

### 9. **Quiz** (2 versions)
- **Default**: Standard quiz
- **With Timer**: Timed questions

**Features**: LaTeX support, explanations, multi-select

### 10. **Multiple Choice** (2 versions)
- **Default**: Standard multiple choice
- **With Explanations**: Instant feedback

### 11. **True/False** (2 versions)
- **Default**: Standard true/false
- **Instant**: Immediate feedback

## 🚀 Usage

### Basic Implementation

```vue
<template>
  <QuestionRenderer
    :question="currentQuestion"
    @answer="handleAnswer"
    @complete="handleComplete"
  />
</template>

<script setup lang="ts">
import QuestionRenderer from './types_system/QuestionRenderer.vue';
import type { Question, Answer } from './types_system/types';

const currentQuestion = ref<Question>({
  id: 'q1',
  type: 'labelled-diagram',
  version: 'interactive', // Optional - defaults to 'default'
  title: 'Label the Human Heart',
  imageUrl: '/images/heart.png',
  labels: [
    { id: 'l1', x: 30, y: 25, label: '1', correctAnswer: 'Aorta' },
    // ... more labels
  ]
});

function handleAnswer(answer: Answer) {
  console.log('User answered:', answer);
}

function handleComplete(data) {
  console.log('Question completed:', data);
  // data = { questionId, answer, isCorrect }
}
</script>
```

### Loading Presets

```typescript
import sciencePreset1 from './presets/science/preset1.json';

const questions = sciencePreset1.questions;
```

### Creating Custom Questions

```typescript
import type { LabelledDiagramQuestion } from './types';

const customQuestion: LabelledDiagramQuestion = {
  id: 'custom-1',
  type: 'labelled-diagram',
  version: 'drag-drop', // Choose version
  title: 'My Custom Diagram',
  imageUrl: '/my-image.png',
  points: 10,
  timeLimit: 300,
  labels: [
    { id: 'l1', x: 50, y: 50, label: 'A', correctAnswer: 'Label A' }
  ]
};
```

## 🎯 Version System

Each question type has a `Main.vue` that:
1. Displays a beautiful version selector
2. Dynamically loads the selected version component
3. Passes props and emits events

**Adding New Versions:**

1. Create new version file: `types/[type]/versions/NewVersion.vue`
2. Export in `versions/index.ts`:
   ```typescript
   export { default as NewVersion } from './NewVersion.vue';
   ```
3. Update `Main.vue` version list:
   ```typescript
   const availableVersions = [
     // ... existing versions
     { name: 'new-version', displayName: 'New Version', description: '...', icon: '🎨' }
   ];
   
   const versionComponents = {
     // ... existing
     'new-version': versions.NewVersion
   };
   ```

**No changes needed to QuestionRenderer.vue!**

## 🎨 Styling

All components use **Tailwind CSS** with:
- Mobile-first responsive design
- Smooth animations and transitions
- Accessible color schemes
- Dark mode support (future)

## 🔧 Composables

### useRecording

```typescript
import { useRecording } from './composables/useRecording';

const { state, startRecording, stopRecording, isSupported } = useRecording();

await startRecording();
const blob = await stopRecording();
```

**Features:**
- Browser microphone access
- Pause/resume support
- Duration tracking
- Blob URL management
- Auto cleanup

### useTimer

```typescript
import { useTimer } from './composables/useTimer';

const { state, start, pause, formattedTime, progress } = useTimer({
  duration: 60,
  onExpire: () => console.log('Time up!'),
  autoStart: true
});
```

## 📊 Preset Structure

```json
{
  "id": "unique-preset-id",
  "name": "Preset Display Name",
  "subject": "science|math|language-learning|general",
  "metadata": {
    "author": "Author Name",
    "createdAt": "2025-11-23",
    "difficulty": "easy|medium|hard",
    "tags": ["tag1", "tag2"]
  },
  "questions": [
    {
      "id": "q1",
      "type": "labelled-diagram",
      "version": "interactive", // Optional
      "title": "Question Title",
      // ... question-specific fields
    }
  ]
}
```

## 🎓 Subject Coverage

### Science (6 question types)
- Labelled diagrams (anatomy, plants, solar system)
- Image quizzes (cell organelles, planets)
- Match-up (body systems, classifications)
- Group sort (plant types)
- Missing word (photosynthesis)
- Sequence (planet order)

### Language Learning (4 question types)
- Speaking cards (pronunciation practice)
- Match-up with audio (vocabulary)
- Anagrams (word scrambles)
- Missing word (sentence completion)

### Math (4 question types)
- Quiz with LaTeX (equations, theorems)
- Match-up with LaTeX (equations to solutions)
- Labelled diagrams (geometric shapes)
- Group sort (number classification)

### General (2 question types)
- Multiple choice (trivia, facts)
- True/False (logic, reasoning)

## 🔐 TypeScript Support

Full TypeScript coverage with:
- Strict type checking
- Discriminated unions for question types
- Type-safe props and emits
- IntelliSense support

## 🚀 Performance

- **Lazy loading**: Components loaded on-demand
- **Code splitting**: Each question type is a separate chunk
- **Optimized rendering**: Virtual scrolling for large lists
- **Memory management**: Auto cleanup of blob URLs and media streams

## 🎯 Future Enhancements

1. **AI Features**
   - Pronunciation feedback for speaking cards
   - Auto-grading for open-ended questions
   - Adaptive difficulty

2. **Analytics**
   - Time tracking per question
   - Attempt history
   - Performance insights

3. **Accessibility**
   - Screen reader support
   - Keyboard navigation
   - High contrast mode

4. **Collaboration**
   - Real-time multiplayer quizzes
   - Peer review
   - Teacher feedback

## 📝 License

MIT License - Feel free to use in your educational projects!

## 🤝 Contributing

To add a new question type:

1. Create folder: `types/[new-type]/`
2. Create `Main.vue` with version selector
3. Create `versions/Default.vue` (required)
4. Add type to `types.ts`
5. Register in `QuestionRenderer.vue` componentMap
6. Create presets in `presets/[subject]/`

---

**Built with ❤️ for modern education platforms**
