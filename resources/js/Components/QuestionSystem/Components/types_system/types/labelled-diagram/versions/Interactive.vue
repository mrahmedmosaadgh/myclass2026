<template>
  <div class="interactive-labelled-diagram">
    <!-- Instructions -->
    <div class="mb-4 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-l-4 border-indigo-500 rounded">
      <p class="text-sm text-indigo-900">
        ✨ Hover over the glowing points to reveal information, then click to select your answer.
      </p>
    </div>

    <!-- Image Container with Interactive Points -->
    <div class="relative inline-block max-w-full">
      <img
        :src="question.imageUrl"
        :alt="question.title"
        class="w-full h-auto rounded-lg shadow-md"
      />

      <!-- Interactive Label Points -->
      <div
        v-for="label in question.labels"
        :key="label.id"
        :style="{
          position: 'absolute',
          left: `${label.x}%`,
          top: `${label.y}%`,
          transform: 'translate(-50%, -50%)'
        }"
        class="interactive-point-container"
        @mouseenter="hoveredLabel = label.id"
        @mouseleave="hoveredLabel = null"
      >
        <!-- Pulsing Point -->
        <div
          class="interactive-point"
          :class="{
            'point-answered': userAnswers[label.id],
            'point-correct': isCorrect(label.id),
            'point-incorrect': isSubmitted && !isCorrect(label.id),
            'point-hover': hoveredLabel === label.id
          }"
          @click="selectLabel(label.id)"
        >
          <span class="point-label">{{ label.label }}</span>
          
          <!-- Pulse Animation -->
          <div v-if="!userAnswers[label.id]" class="pulse-ring"></div>
          <div v-if="!userAnswers[label.id]" class="pulse-ring pulse-ring-delay"></div>
        </div>

        <!-- Hover Tooltip -->
        <transition name="fade-slide">
          <div v-if="hoveredLabel === label.id && !selectedLabelId" class="hover-tooltip">
            <div class="tooltip-content">
              <div class="text-xs font-semibold text-indigo-600 mb-1">Point {{ label.label }}</div>
              <div class="text-xs text-gray-600">Click to label this point</div>
            </div>
          </div>
        </transition>

        <!-- Answer Selection Modal -->
        <transition name="scale-fade">
          <div v-if="selectedLabelId === label.id" class="answer-modal">
            <div class="modal-content">
              <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold text-gray-900">Label Point {{ label.label }}</h4>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">✕</button>
              </div>
              
              <input
                ref="answerInput"
                v-model="tempAnswer"
                type="text"
                placeholder="Type your answer..."
                class="w-full px-3 py-2 border-2 border-indigo-300 rounded-lg focus:outline-none focus:border-indigo-500 mb-3"
                @keyup.enter="confirmAnswer"
                @keyup.esc="closeModal"
              />
              
              <div class="flex gap-2">
                <button
                  @click="confirmAnswer"
                  class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium"
                >
                  Confirm
                </button>
                <button
                  @click="closeModal"
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </transition>

        <!-- Display Answer -->
        <div v-if="userAnswers[label.id] && selectedLabelId !== label.id" class="answer-display">
          <span class="answer-text">{{ userAnswers[label.id] }}</span>
          <button v-if="!isSubmitted" @click="editLabel(label.id)" class="edit-btn">✏️</button>
          <span v-if="isSubmitted" class="validation-icon">
            {{ isCorrect(label.id) ? '✅' : '❌' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Progress with Animation -->
    <div class="mt-6">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-700">
          Labeled: {{ answeredCount }} / {{ question.labels.length }}
        </span>
        <span class="text-sm font-semibold text-indigo-600">
          {{ Math.round((answeredCount / question.labels.length) * 100) }}%
        </span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
        <div
          class="h-3 rounded-full transition-all duration-500 bg-gradient-to-r from-indigo-500 to-purple-500"
          :style="{ width: `${(answeredCount / question.labels.length) * 100}%` }"
        >
          <div class="h-full w-full bg-white opacity-20 animate-pulse"></div>
        </div>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="mt-6 flex gap-3">
      <button
        @click="submitAnswer"
        :disabled="answeredCount < question.labels.length"
        class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:from-indigo-700 hover:to-purple-700 disabled:from-gray-300 disabled:to-gray-300 disabled:cursor-not-allowed transition-all shadow-lg hover:shadow-xl"
      >
        Submit Answers
      </button>
      <button
        @click="resetAnswers"
        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors"
      >
        Reset
      </button>
    </div>

    <!-- Feedback -->
    <transition name="fade-slide">
      <div v-if="showFeedback" class="mt-4 p-4 rounded-lg" :class="feedbackClass">
        <p class="font-semibold">{{ feedbackMessage }}</p>
        <p v-if="!checkAllCorrect()" class="text-sm mt-1">
          Score: {{ correctCount }} / {{ question.labels.length }} correct
        </p>
      </div>
    </transition>
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
const hoveredLabel = ref<string | null>(null);
const selectedLabelId = ref<string | null>(null);
const tempAnswer = ref('');
const answerInput = ref<HTMLInputElement | null>(null);
const showFeedback = ref(false);
const isSubmitted = ref(false);

// ============================================================================
// Computed
// ============================================================================

const answeredCount = computed(() => {
  return Object.keys(userAnswers.value).filter(key => userAnswers.value[key]?.trim()).length;
});

const correctCount = computed(() => {
  if (!isSubmitted.value) return 0;
  return props.question.labels.filter(label => 
    userAnswers.value[label.id]?.trim().toLowerCase() === label.correctAnswer.toLowerCase()
  ).length;
});

const feedbackClass = computed(() => {
  if (!isSubmitted.value) return '';
  const correct = checkAllCorrect();
  return correct 
    ? 'bg-green-50 border-l-4 border-green-500 text-green-900' 
    : 'bg-amber-50 border-l-4 border-amber-500 text-amber-900';
});

const feedbackMessage = computed(() => {
  if (!isSubmitted.value) return '';
  const correct = checkAllCorrect();
  if (correct) return '🎉 Perfect! All labels are correct!';
  const percentage = (correctCount.value / props.question.labels.length) * 100;
  if (percentage >= 70) return '👍 Good job! A few labels need correction.';
  return '💪 Keep trying! Review the incorrect labels.';
});

// ============================================================================
// Methods
// ============================================================================

function selectLabel(labelId: string) {
  if (isSubmitted.value) return;
  selectedLabelId.value = labelId;
  tempAnswer.value = userAnswers.value[labelId] || '';
  nextTick(() => {
    answerInput.value?.focus();
  });
}

function editLabel(labelId: string) {
  selectLabel(labelId);
}

function confirmAnswer() {
  if (selectedLabelId.value && tempAnswer.value.trim()) {
    userAnswers.value[selectedLabelId.value] = tempAnswer.value.trim();
  }
  closeModal();
}

function closeModal() {
  selectedLabelId.value = null;
  tempAnswer.value = '';
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
  tempAnswer.value = '';
  showFeedback.value = false;
  isSubmitted.value = false;
}
</script>

<style scoped>
.interactive-labelled-diagram {
  @apply w-full;
}

.interactive-point-container {
  @apply z-10;
}

.interactive-point {
  @apply relative w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-full flex items-center justify-center cursor-pointer shadow-lg transition-all duration-300;
}

.interactive-point:hover {
  @apply scale-110 shadow-xl;
}

.point-answered {
  @apply from-green-500 to-emerald-600;
}

.point-correct {
  @apply from-green-600 to-emerald-700 scale-110;
}

.point-incorrect {
  @apply from-red-500 to-rose-600 scale-110;
}

.point-label {
  @apply text-sm font-bold z-10;
}

.pulse-ring {
  @apply absolute inset-0 rounded-full border-2 border-indigo-400 animate-ping;
}

.pulse-ring-delay {
  animation-delay: 0.5s;
}

.hover-tooltip {
  @apply absolute top-14 left-1/2 transform -translate-x-1/2 z-20;
}

.tooltip-content {
  @apply bg-white px-3 py-2 rounded-lg shadow-xl border border-indigo-200 whitespace-nowrap;
}

.answer-modal {
  @apply absolute top-14 left-1/2 transform -translate-x-1/2 z-30;
}

.modal-content {
  @apply bg-white p-4 rounded-lg shadow-2xl border-2 border-indigo-300 min-w-[250px];
}

.answer-display {
  @apply absolute top-14 left-1/2 transform -translate-x-1/2 bg-white px-3 py-2 rounded-lg shadow-lg border-2 border-indigo-300 flex items-center gap-2 whitespace-nowrap;
}

.answer-text {
  @apply text-sm font-medium text-gray-900;
}

.edit-btn {
  @apply text-xs hover:scale-110 transition-transform;
}

.validation-icon {
  @apply text-lg;
}

/* Transitions */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.scale-fade-enter-active,
.scale-fade-leave-active {
  transition: all 0.2s ease;
}

.scale-fade-enter-from,
.scale-fade-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(-10px);
}
</style>
