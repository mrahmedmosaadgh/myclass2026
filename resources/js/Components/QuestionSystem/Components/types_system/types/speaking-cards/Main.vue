<template>
  <div class="speaking-cards-container p-6 bg-white rounded-xl shadow-lg">
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ question.title }}</h2>
      <p v-if="question.description" class="text-gray-600">{{ question.description }}</p>
    </div>

    <!-- Version Selector -->
    <div class="version-selector mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">Practice Mode</label>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <button
          v-for="version in availableVersions"
          :key="version.name"
          @click="selectedVersion = version.name"
          :class="[
            'p-4 rounded-lg border-2 transition-all duration-200',
            selectedVersion === version.name
              ? 'border-rose-500 bg-rose-50 shadow-md'
              : 'border-gray-200 hover:border-rose-300 hover:bg-gray-50'
          ]"
        >
          <div class="flex items-center gap-3">
            <span class="text-2xl">{{ version.icon }}</span>
            <div class="text-left">
              <div class="font-semibold text-gray-900">{{ version.displayName }}</div>
              <div class="text-xs text-gray-600">{{ version.description }}</div>
            </div>
          </div>
        </button>
      </div>
    </div>

    <!-- Dynamic Version Renderer -->
    <component
      :is="currentVersionComponent"
      v-if="currentVersionComponent"
      :question="question"
      @answer="handleAnswer"
      @complete="handleComplete"
    />

    <!-- Loading State -->
    <div v-else class="flex items-center justify-center py-12">
      <div class="text-center">
        <div class="inline-block w-8 h-8 border-4 border-rose-200 border-t-rose-600 rounded-full animate-spin"></div>
        <p class="mt-3 text-gray-600">Loading...</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, type Component } from 'vue';
import type { SpeakingCardsQuestion, SpeakingCardsAnswer } from '../../types';
import * as versions from './versions';

// ============================================================================
// Props & Emits
// ============================================================================

interface Props {
  question: SpeakingCardsQuestion;
}

interface Emits {
  (e: 'answer', answer: SpeakingCardsAnswer): void;
  (e: 'complete', data: { questionId: string; answer: SpeakingCardsAnswer; isCorrect: boolean }): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// ============================================================================
// Version Management
// ============================================================================

const availableVersions = [
  {
    name: 'default',
    displayName: 'Standard',
    description: 'Record & playback',
    icon: '🎤',
  },
  {
    name: 'with-feedback',
    displayName: 'With Feedback',
    description: 'AI pronunciation tips',
    icon: '🎯',
  },
];

const initialVersion = props.question.version || 'default';
const selectedVersion = ref(initialVersion);

const versionComponents: Record<string, Component> = {
  'default': versions.Default,
  // 'with-feedback': versions.WithFeedback, // Future implementation
};

const currentVersionComponent = computed(() => {
  return versionComponents[selectedVersion.value] || versionComponents['default'];
});

// ============================================================================
// Event Handlers
// ============================================================================

function handleAnswer(answer: SpeakingCardsAnswer) {
  emit('answer', answer);
}

function handleComplete(data: { questionId: string; answer: SpeakingCardsAnswer; isCorrect: boolean }) {
  emit('complete', data);
}

// ============================================================================
// Watchers
// ============================================================================

watch(() => props.question.version, (newVersion) => {
  if (newVersion && availableVersions.some(v => v.name === newVersion)) {
    selectedVersion.value = newVersion;
  }
});
</script>

<style scoped>
.speaking-cards-container {
  @apply max-w-5xl mx-auto;
}

button {
  @apply transform;
}

button:active {
  @apply scale-95;
}
</style>
