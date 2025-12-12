<template>
  <div class="question-renderer">
    <!-- Error Boundary -->
    <div v-if="error" class="error-container p-6 bg-red-50 border-2 border-red-200 rounded-lg">
      <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h3 class="text-lg font-semibold text-red-900">Failed to Load Question</h3>
          <p class="text-sm text-red-700 mt-1">{{ error }}</p>
          <button
            @click="retryLoad"
            class="mt-3 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium"
          >
            Retry
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="loading-container p-8 flex items-center justify-center">
      <div class="text-center">
        <div class="inline-block w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
        <p class="mt-4 text-gray-600">Loading question...</p>
      </div>
    </div>

    <!-- Question Component -->
    <component
      v-else-if="questionComponent"
      :is="questionComponent"
      :question="question"
      @answer="handleAnswer"
      @complete="handleComplete"
    />

    <!-- Fallback for Unknown Type -->
    <div v-else class="unknown-type-container p-6 bg-yellow-50 border-2 border-yellow-200 rounded-lg">
      <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h3 class="text-lg font-semibold text-yellow-900">Unknown Question Type</h3>
          <p class="text-sm text-yellow-700 mt-1">
            Question type <code class="px-2 py-1 bg-yellow-100 rounded">{{ question?.type }}</code> is not supported.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onErrorCaptured, type Component } from 'vue';
import type { Question, Answer } from './types';

// ============================================================================
// Props & Emits
// ============================================================================

interface Props {
  question: Question;
}

interface Emits {
  (e: 'answer', answer: Answer): void;
  (e: 'complete', data: { questionId: string; answer: Answer; isCorrect: boolean }): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// ============================================================================
// State
// ============================================================================

const loading = ref(false);
const error = ref<string | null>(null);
const questionComponent = ref<Component | null>(null);

// ============================================================================
// Dynamic Component Loading
// ============================================================================

const componentMap: Record<string, () => Promise<Component>> = {
  'labelled-diagram': () => import('./types/labelled-diagram/Main.vue'),
  'image-quiz': () => import('./types/image-quiz/Main.vue'),
  'match-up': () => import('./types/match-up/Main.vue'),
  'group-sort': () => import('./types/group-sort/Main.vue'),
  'missing-word': () => import('./types/missing-word/Main.vue'),
  'sequence': () => import('./types/sequence/Main.vue'),
  'anagram': () => import('./types/anagram/Main.vue'),
  'speaking-cards': () => import('./types/speaking-cards/Main.vue'),
  'quiz': () => import('./types/quiz/Main.vue'),
  'multiple-choice': () => import('./types/multiple-choice/Main.vue'),
  'true-false': () => import('./types/true-false/Main.vue'),
};

/**
 * Load the appropriate question component based on type
 */
async function loadQuestionComponent() {
  if (!props.question?.type) {
    error.value = 'Question type is missing';
    return;
  }

  const loader = componentMap[props.question.type];
  
  if (!loader) {
    error.value = `No component found for question type: ${props.question.type}`;
    return;
  }

  try {
    loading.value = true;
    error.value = null;
    
    const module = await loader();
    questionComponent.value = module.default || module;
  } catch (err) {
    console.error(`Failed to load component for ${props.question.type}:`, err);
    error.value = `Failed to load question component: ${err instanceof Error ? err.message : 'Unknown error'}`;
    questionComponent.value = null;
  } finally {
    loading.value = false;
  }
}

/**
 * Retry loading the component
 */
function retryLoad() {
  loadQuestionComponent();
}

// ============================================================================
// Event Handlers
// ============================================================================

function handleAnswer(answer: Answer) {
  emit('answer', answer);
}

function handleComplete(data: { questionId: string; answer: Answer; isCorrect: boolean }) {
  emit('complete', data);
}

// ============================================================================
// Error Handling
// ============================================================================

onErrorCaptured((err, instance, info) => {
  console.error('Question Renderer Error:', err, info);
  error.value = `Component error: ${err instanceof Error ? err.message : 'Unknown error'}`;
  return false; // Prevent error propagation
});

// ============================================================================
// Watchers
// ============================================================================

watch(
  () => props.question?.type,
  () => {
    loadQuestionComponent();
  },
  { immediate: true }
);
</script>

<style scoped>
.question-renderer {
  @apply w-full;
}

code {
  @apply font-mono text-sm;
}

/* Smooth transitions */
.error-container,
.loading-container,
.unknown-type-container {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
