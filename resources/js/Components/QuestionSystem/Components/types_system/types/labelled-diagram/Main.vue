<template>
  <div class="labelled-diagram-container p-6 bg-white rounded-xl shadow-lg">
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ question.title }}</h2>
      <p v-if="question.description" class="text-gray-600">{{ question.description }}</p>
    </div>

    <!-- Version Selector -->
    <div class="version-selector mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">Display Mode</label>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <button
          v-for="version in availableVersions"
          :key="version.name"
          @click="selectedVersion = version.name"
          :class="[
            'p-4 rounded-lg border-2 transition-all duration-200',
            selectedVersion === version.name
              ? 'border-blue-500 bg-blue-50 shadow-md'
              : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'
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
        <div class="inline-block w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
        <p class="mt-3 text-gray-600">Loading...</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, type Component } from 'vue';
import type { LabelledDiagramQuestion, LabelledDiagramAnswer } from '../../types';
import * as versions from './versions';

// ============================================================================
// Props & Emits
// ============================================================================

interface Props {
  question: LabelledDiagramQuestion;
}

interface Emits {
  (e: 'answer', answer: LabelledDiagramAnswer): void;
  (e: 'complete', data: { questionId: string; answer: LabelledDiagramAnswer; isCorrect: boolean }): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// ============================================================================
// Version Management
// ============================================================================

const availableVersions = [
  {
    name: 'default',
    displayName: 'Default',
    description: 'Click to label',
    icon: '📝',
  },
  {
    name: 'drag-drop',
    displayName: 'Drag & Drop',
    description: 'Drag labels to points',
    icon: '🎯',
  },
  {
    name: 'interactive',
    displayName: 'Interactive',
    description: 'Hover & click',
    icon: '✨',
  },
];

// Determine initial version from question or default
const initialVersion = props.question.version || 'default';
const selectedVersion = ref(initialVersion);

// Map version names to components
const versionComponents: Record<string, Component> = {
  'default': versions.Default,
  'drag-drop': versions.DragDrop,
  'interactive': versions.Interactive,
};

const currentVersionComponent = computed(() => {
  return versionComponents[selectedVersion.value] || versionComponents['default'];
});

// ============================================================================
// Event Handlers
// ============================================================================

function handleAnswer(answer: LabelledDiagramAnswer) {
  emit('answer', answer);
}

function handleComplete(data: { questionId: string; answer: LabelledDiagramAnswer; isCorrect: boolean }) {
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
.labelled-diagram-container {
  @apply max-w-5xl mx-auto;
}

/* Smooth transitions */
button {
  @apply transform;
}

button:active {
  @apply scale-95;
}
</style>
