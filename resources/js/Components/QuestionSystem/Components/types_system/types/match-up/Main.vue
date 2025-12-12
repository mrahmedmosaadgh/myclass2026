<template>
  <div class="match-up-container p-6 bg-white rounded-xl shadow-lg">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ question.title }}</h2>
      <p v-if="question.description" class="text-gray-600">{{ question.description }}</p>
    </div>

    <div class="version-selector mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">Match Mode</label>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <button
          v-for="version in availableVersions"
          :key="version.name"
          @click="selectedVersion = version.name"
          :class="[
            'p-4 rounded-lg border-2 transition-all duration-200',
            selectedVersion === version.name
              ? 'border-emerald-500 bg-emerald-50 shadow-md'
              : 'border-gray-200 hover:border-emerald-300 hover:bg-gray-50'
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

    <component
      :is="currentVersionComponent"
      v-if="currentVersionComponent"
      :question="question"
      @answer="handleAnswer"
      @complete="handleComplete"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, type Component } from 'vue';
import type { MatchUpQuestion, MatchUpAnswer } from '../../types';
import * as versions from './versions';

interface Props {
  question: MatchUpQuestion;
}

interface Emits {
  (e: 'answer', answer: MatchUpAnswer): void;
  (e: 'complete', data: { questionId: string; answer: MatchUpAnswer; isCorrect: boolean }): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const availableVersions = [
  { name: 'default', displayName: 'Default', description: 'Click to match', icon: '🔗' },
  { name: 'with-audio', displayName: 'With Audio', description: 'Listen & match', icon: '🔊' },
  { name: 'image-only', displayName: 'Image Only', description: 'Visual matching', icon: '🖼️' },
];

const initialVersion = props.question.version || 'default';
const selectedVersion = ref(initialVersion);

const versionComponents: Record<string, Component> = {
  'default': versions.Default,
  'with-audio': versions.WithAudio,
  'image-only': versions.ImageOnly,
};

const currentVersionComponent = computed(() => {
  return versionComponents[selectedVersion.value] || versionComponents['default'];
});

function handleAnswer(answer: MatchUpAnswer) {
  emit('answer', answer);
}

function handleComplete(data: { questionId: string; answer: MatchUpAnswer; isCorrect: boolean }) {
  emit('complete', data);
}

watch(() => props.question.version, (newVersion) => {
  if (newVersion && availableVersions.some(v => v.name === newVersion)) {
    selectedVersion.value = newVersion;
  }
});
</script>
