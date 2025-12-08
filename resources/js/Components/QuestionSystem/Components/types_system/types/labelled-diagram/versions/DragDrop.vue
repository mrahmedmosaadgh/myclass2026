<template>
  <div class="drag-drop-labelled-diagram">
    <!-- Instructions -->
    <div class="mb-4 p-4 bg-purple-50 border-l-4 border-purple-500 rounded">
      <p class="text-sm text-purple-900">
        🎯 Drag the labels from the word bank and drop them onto the correct points on the diagram.
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Word Bank -->
      <div class="lg:col-span-1">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Label Bank</h3>
        <div class="space-y-2 p-4 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 min-h-[200px]">
          <div
            v-for="label in availableLabels"
            :key="label.id"
            :draggable="!isSubmitted"
            @dragstart="onDragStart($event, label)"
            @dragend="onDragEnd"
            class="draggable-label"
            :class="{ 'opacity-50': usedLabels.has(label.id), 'cursor-move': !isSubmitted, 'cursor-not-allowed': isSubmitted }"
          >
            <span class="font-medium">{{ label.correctAnswer }}</span>
          </div>
        </div>
      </div>

      <!-- Image with Drop Zones -->
      <div class="lg:col-span-2">
        <div class="relative inline-block max-w-full">
          <img
            :src="question.imageUrl"
            :alt="question.title"
            class="w-full h-auto rounded-lg shadow-md"
          />

          <!-- Drop Zones -->
          <div
            v-for="label in question.labels"
            :key="label.id"
            :style="{
              position: 'absolute',
              left: `${label.x}%`,
              top: `${label.y}%`,
              transform: 'translate(-50%, -50%)'
            }"
            class="drop-zone-container"
          >
            <!-- Drop Zone -->
            <div
              @drop="onDrop($event, label.id)"
              @dragover.prevent
              @dragenter="onDragEnter($event, label.id)"
              @dragleave="onDragLeave"
              class="drop-zone"
              :class="{
                'drop-zone-active': dragOverZone === label.id,
                'drop-zone-filled': userAnswers[label.id],
                'drop-zone-correct': isCorrect(label.id),
                'drop-zone-incorrect': isSubmitted && !isCorrect(label.id)
              }"
            >
              <span class="zone-number">{{ label.label }}</span>
            </div>

            <!-- Dropped Label -->
            <div v-if="userAnswers[label.id]" class="dropped-label">
              <span class="label-text">{{ userAnswers[label.id] }}</span>
              <button
                v-if="!isSubmitted"
                @click="removeLabel(label.id)"
                class="remove-btn"
              >
                ✕
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Progress -->
    <div class="mt-6">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-700">
          Placed: {{ answeredCount }} / {{ question.labels.length }}
        </span>
        <span class="text-sm text-gray-600">
          {{ Math.round((answeredCount / question.labels.length) * 100) }}%
        </span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div
          class="bg-purple-600 h-2 rounded-full transition-all duration-300"
          :style="{ width: `${(answeredCount / question.labels.length) * 100}%` }"
        ></div>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="mt-6 flex gap-3">
      <button
        @click="submitAnswer"
        :disabled="answeredCount < question.labels.length"
        class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
      >
        Check Answers
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
import { ref, computed } from 'vue';
import type { LabelledDiagramQuestion, LabelledDiagramAnswer, LabelPoint } from '../../../types';

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
const dragOverZone = ref<string | null>(null);
const draggedLabel = ref<LabelPoint | null>(null);
const showFeedback = ref(false);
const isSubmitted = ref(false);

// ============================================================================
// Computed
// ============================================================================

const availableLabels = computed(() => props.question.labels);

const usedLabels = computed(() => {
  const used = new Set<string>();
  Object.values(userAnswers.value).forEach(answer => {
    const label = props.question.labels.find(l => l.correctAnswer === answer);
    if (label) used.add(label.id);
  });
  return used;
});

const answeredCount = computed(() => {
  return Object.keys(userAnswers.value).filter(key => userAnswers.value[key]).length;
});

const feedbackClass = computed(() => {
  if (!isSubmitted.value) return '';
  const correct = checkAllCorrect();
  return correct ? 'bg-green-50 border-l-4 border-green-500 text-green-900' : 'bg-red-50 border-l-4 border-red-500 text-red-900';
});

const feedbackMessage = computed(() => {
  if (!isSubmitted.value) return '';
  const correct = checkAllCorrect();
  return correct ? '✅ Excellent! All labels are correctly placed!' : '❌ Some labels are in the wrong position. Try again!';
});

// ============================================================================
// Drag & Drop Methods
// ============================================================================

function onDragStart(event: DragEvent, label: LabelPoint) {
  draggedLabel.value = label;
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', label.id);
  }
}

function onDragEnd() {
  draggedLabel.value = null;
}

function onDragEnter(event: DragEvent, zoneId: string) {
  event.preventDefault();
  dragOverZone.value = zoneId;
}

function onDragLeave() {
  dragOverZone.value = null;
}

function onDrop(event: DragEvent, zoneId: string) {
  event.preventDefault();
  dragOverZone.value = null;

  if (draggedLabel.value) {
    userAnswers.value[zoneId] = draggedLabel.value.correctAnswer;
  }
}

function removeLabel(zoneId: string) {
  delete userAnswers.value[zoneId];
}

// ============================================================================
// Validation
// ============================================================================

function isCorrect(labelId: string): boolean {
  if (!isSubmitted.value) return false;
  const label = props.question.labels.find(l => l.id === labelId);
  if (!label) return false;
  return userAnswers.value[labelId] === label.correctAnswer;
}

function checkAllCorrect(): boolean {
  return props.question.labels.every(label => 
    userAnswers.value[label.id] === label.correctAnswer
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
  showFeedback.value = false;
  isSubmitted.value = false;
}
</script>

<style scoped>
.drag-drop-labelled-diagram {
  @apply w-full;
}

.draggable-label {
  @apply px-4 py-2 bg-white border-2 border-purple-300 rounded-lg shadow-sm hover:shadow-md transition-all;
}

.draggable-label:not(.opacity-50) {
  @apply hover:border-purple-500 hover:bg-purple-50;
}

.drop-zone-container {
  @apply z-10;
}

.drop-zone {
  @apply w-10 h-10 bg-purple-100 border-2 border-purple-400 rounded-full flex items-center justify-center cursor-pointer transition-all;
}

.drop-zone-active {
  @apply bg-purple-200 border-purple-600 scale-125 shadow-lg;
}

.drop-zone-filled {
  @apply bg-green-100 border-green-500;
}

.drop-zone-correct {
  @apply bg-green-500 border-green-700;
}

.drop-zone-incorrect {
  @apply bg-red-500 border-red-700;
}

.zone-number {
  @apply text-xs font-bold text-gray-700;
}

.dropped-label {
  @apply absolute top-12 left-1/2 transform -translate-x-1/2 bg-white px-3 py-2 rounded-lg shadow-lg border-2 border-purple-400 flex items-center gap-2 whitespace-nowrap;
}

.label-text {
  @apply text-sm font-medium text-gray-900;
}

.remove-btn {
  @apply text-red-500 hover:text-red-700 font-bold text-sm hover:scale-110 transition-transform;
}
</style>
