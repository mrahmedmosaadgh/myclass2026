<template>
  <div class="default-labelled-diagram">
    <!-- Instructions -->
    <div v-if="question.instructions" class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
      <p class="text-sm text-blue-900">{{ question.instructions }}</p>
    </div>

    <!-- Image Container with Labels -->
    <div class="relative inline-block max-w-full">
      <img
        :src="question.imageUrl"
        :alt="question.title"
        class="w-full h-auto rounded-lg shadow-md"
        @load="onImageLoad"
      />

      <!-- Label Points -->
      <div
        v-for="label in question.labels"
        :key="label.id"
        :style="{
          position: 'absolute',
          left: `${label.x}%`,
          top: `${label.y}%`,
          transform: 'translate(-50%, -50%)'
        }"
        class="label-point"
      >
        <!-- Point Marker -->
        <div
          class="point-marker"
          :class="{ 'answered': userAnswers[label.id], 'correct': isCorrect(label.id) }"
          @click="selectLabel(label.id)"
        >
          <span class="point-number">{{ label.label }}</span>
        </div>

        <!-- Input Field -->
        <div v-if="selectedLabelId === label.id" class="label-input-container">
          <input
            ref="labelInput"
            v-model="userAnswers[label.id]"
            type="text"
            :placeholder="`Enter label ${label.label}`"
            class="label-input"
            @keyup.enter="confirmLabel"
            @blur="confirmLabel"
          />
        </div>

        <!-- Display Answer -->
        <div v-else-if="userAnswers[label.id]" class="label-display">
          <span class="label-text">{{ userAnswers[label.id] }}</span>
          <button @click="editLabel(label.id)" class="edit-btn">✏️</button>
        </div>
      </div>
    </div>

    <!-- Progress -->
    <div class="mt-6">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-700">
          Progress: {{ answeredCount }} / {{ question.labels.length }}
        </span>
        <span class="text-sm text-gray-600">
          {{ Math.round((answeredCount / question.labels.length) * 100) }}%
        </span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div
          class="bg-blue-600 h-2 rounded-full transition-all duration-300"
          :style="{ width: `${(answeredCount / question.labels.length) * 100}%` }"
        ></div>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="mt-6 flex gap-3">
      <button
        @click="submitAnswer"
        :disabled="answeredCount < question.labels.length"
        class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
      >
        Submit Answer
      </button>
      <button
        @click="resetAnswers"
        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors"
      >
        Reset
      </button>
    </div>

    <!-- Feedback -->
    <div v-if="showFeedback" class="mt-4 p-4 rounded-lg" :class="feedbackClass">
      <p class="font-semibold">{{ feedbackMessage }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import type { LabelledDiagramQuestion, LabelledDiagramAnswer } from '../../../types';

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
// State
// ============================================================================

const userAnswers = ref<Record<string, string>>({});
const selectedLabelId = ref<string | null>(null);
const labelInput = ref<HTMLInputElement | null>(null);
const showFeedback = ref(false);
const isSubmitted = ref(false);

// ============================================================================
// Computed
// ============================================================================

const answeredCount = computed(() => {
  return Object.keys(userAnswers.value).filter(key => userAnswers.value[key]?.trim()).length;
});

const feedbackClass = computed(() => {
  if (!isSubmitted.value) return '';
  const correct = checkAllCorrect();
  return correct ? 'bg-green-50 border-l-4 border-green-500 text-green-900' : 'bg-red-50 border-l-4 border-red-500 text-red-900';
});

const feedbackMessage = computed(() => {
  if (!isSubmitted.value) return '';
  const correct = checkAllCorrect();
  return correct ? '✅ Perfect! All labels are correct!' : '❌ Some labels are incorrect. Please try again.';
});

// ============================================================================
// Methods
// ============================================================================

function onImageLoad() {
  console.log('Image loaded successfully');
}

function selectLabel(labelId: string) {
  if (isSubmitted.value) return;
  selectedLabelId.value = labelId;
  nextTick(() => {
    labelInput.value?.focus();
  });
}

function editLabel(labelId: string) {
  if (isSubmitted.value) return;
  selectedLabelId.value = labelId;
  nextTick(() => {
    labelInput.value?.focus();
  });
}

function confirmLabel() {
  selectedLabelId.value = null;
}

function isCorrect(labelId: string): boolean {
  if (!isSubmitted.value) return false;
  const label = props.question.labels.find(l => l.id === labelId);
  if (!label) return false;
  return userAnswers.value[labelId]?.trim().toLowerCase() === label.correctAnswer.toLowerCase();
}

function checkAllCorrect(): boolean {
  return props.question.labels.every(label => 
    userAnswers.value[label.id]?.trim().toLowerCase() === label.correctAnswer.toLowerCase()
  );
}

function submitAnswer() {
  const answer: LabelledDiagramAnswer = {
    questionId: props.question.id,
    labels: { ...userAnswers.value },
    timestamp: new Date(),
  };

  isSubmitted.value = true;
  showFeedback.value = true;

  emit('answer', answer);
  emit('complete', {
    questionId: props.question.id,
    answer,
    isCorrect: checkAllCorrect(),
  });
}

function resetAnswers() {
  userAnswers.value = {};
  selectedLabelId.value = null;
  showFeedback.value = false;
  isSubmitted.value = false;
}
</script>

<style scoped>
.default-labelled-diagram {
  @apply w-full;
}

.label-point {
  @apply z-10;
}

.point-marker {
  @apply w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center cursor-pointer shadow-lg hover:scale-110 transition-transform;
}

.point-marker.answered {
  @apply bg-green-500;
}

.point-marker.correct {
  @apply bg-green-600;
}

.point-number {
  @apply text-xs font-bold;
}

.label-input-container {
  @apply absolute top-10 left-1/2 transform -translate-x-1/2 z-20;
}

.label-input {
  @apply px-3 py-2 border-2 border-blue-500 rounded-lg shadow-lg bg-white text-sm min-w-[200px] focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.label-display {
  @apply absolute top-10 left-1/2 transform -translate-x-1/2 bg-white px-3 py-2 rounded-lg shadow-lg border border-gray-200 flex items-center gap-2 whitespace-nowrap;
}

.label-text {
  @apply text-sm font-medium text-gray-900;
}

.edit-btn {
  @apply text-xs hover:scale-110 transition-transform;
}
</style>
