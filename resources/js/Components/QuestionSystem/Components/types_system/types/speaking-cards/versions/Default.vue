<template>
  <div class="speaking-cards-default">
    <!-- Instructions -->
    <div v-if="question.instructions" class="mb-4 p-4 bg-rose-50 border-l-4 border-rose-500 rounded">
      <p class="text-sm text-rose-900">🎤 {{ question.instructions }}</p>
    </div>

    <!-- Browser Support Check -->
    <div v-if="!isRecordingSupported" class="p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded mb-4">
      <p class="text-sm text-yellow-900">
        ⚠️ Audio recording is not supported in your browser. Please use a modern browser like Chrome, Firefox, or Edge.
      </p>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
      <div
        v-for="(card, index) in question.cards"
        :key="card.id"
        class="speaking-card"
        :class="{
          'card-active': activeCardId === card.id,
          'card-recorded': recordings[card.id]
        }"
      >
        <!-- Card Number -->
        <div class="card-number">{{ index + 1 }}</div>

        <!-- Image (if available) -->
        <div v-if="card.imageUrl" class="card-image-container">
          <img :src="card.imageUrl" :alt="card.text" class="card-image" />
        </div>

        <!-- Text -->
        <div class="card-text">
          <p class="text-lg font-semibold text-gray-900">{{ card.text }}</p>
          <p v-if="card.phonetic" class="text-sm text-gray-500 mt-1">{{ card.phonetic }}</p>
        </div>

        <!-- Reference Audio (if available) -->
        <div v-if="card.audioUrl" class="mt-3">
          <button
            @click="playReference(card.audioUrl)"
            class="w-full px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium flex items-center justify-center gap-2"
          >
            <span>🔊</span>
            <span>Listen to Example</span>
          </button>
        </div>

        <!-- Recording Controls -->
        <div class="mt-3 space-y-2">
          <!-- Record Button -->
          <button
            v-if="!recordings[card.id]"
            @click="startRecording(card.id)"
            :disabled="isRecording || !isRecordingSupported"
            class="w-full px-3 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors text-sm font-semibold flex items-center justify-center gap-2"
          >
            <span v-if="activeCardId === card.id && isRecording" class="recording-dot"></span>
            <span>{{ activeCardId === card.id && isRecording ? 'Recording...' : '🎤 Record' }}</span>
          </button>

          <!-- Stop Recording Button -->
          <button
            v-if="activeCardId === card.id && isRecording"
            @click="stopRecording"
            class="w-full px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-semibold"
          >
            ⏹️ Stop Recording
          </button>

          <!-- Playback Controls (when recorded) -->
          <div v-if="recordings[card.id]" class="space-y-2">
            <div class="flex gap-2">
              <button
                @click="playRecording(card.id)"
                class="flex-1 px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm font-medium"
              >
                ▶️ Play
              </button>
              <button
                @click="deleteRecording(card.id)"
                class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm font-medium"
              >
                🗑️
              </button>
            </div>
            <div class="text-xs text-green-600 text-center">✓ Recorded</div>
          </div>
        </div>

        <!-- Recording Duration -->
        <div v-if="activeCardId === card.id && isRecording" class="mt-2 text-center">
          <span class="text-sm font-mono text-rose-600">{{ formatDuration(recordingDuration) }}</span>
        </div>
      </div>
    </div>

    <!-- Progress -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-700">
          Recorded: {{ recordedCount }} / {{ question.cards.length }}
        </span>
        <span class="text-sm text-gray-600">
          {{ Math.round((recordedCount / question.cards.length) * 100) }}%
        </span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div
          class="bg-rose-600 h-2 rounded-full transition-all duration-300"
          :style="{ width: `${(recordedCount / question.cards.length) * 100}%` }"
        ></div>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="flex gap-3">
      <button
        @click="submitAnswer"
        :disabled="!canSubmit"
        class="flex-1 px-6 py-3 bg-rose-600 text-white rounded-lg font-semibold hover:bg-rose-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
      >
        Submit Recordings
      </button>
      <button
        @click="resetAll"
        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors"
      >
        Reset All
      </button>
    </div>

    <!-- Feedback -->
    <div v-if="showFeedback" class="mt-4 p-4 rounded-lg bg-green-50 border-l-4 border-green-500">
      <p class="font-semibold text-green-900">✅ Great job! All recordings submitted successfully.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import type { SpeakingCardsQuestion, SpeakingCardsAnswer } from '../../../types';

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
// State
// ============================================================================

const recordings = ref<Record<string, Blob>>({});
const recordingUrls = ref<Record<string, string>>({});
const activeCardId = ref<string | null>(null);
const isRecording = ref(false);
const recordingDuration = ref(0);
const showFeedback = ref(false);

let mediaRecorder: MediaRecorder | null = null;
let audioChunks: Blob[] = [];
let stream: MediaStream | null = null;
let durationInterval: number | null = null;
let currentAudio: HTMLAudioElement | null = null;

// ============================================================================
// Computed
// ============================================================================

const isRecordingSupported = computed(() => {
  return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
});

const recordedCount = computed(() => {
  return Object.keys(recordings.value).length;
});

const canSubmit = computed(() => {
  if (props.question.recordingRequired) {
    return recordedCount.value === props.question.cards.length;
  }
  return recordedCount.value > 0;
});

// ============================================================================
// Recording Methods
// ============================================================================

async function startRecording(cardId: string) {
  try {
    // Request microphone access
    stream = await navigator.mediaDevices.getUserMedia({ audio: true });

    // Reset state
    audioChunks = [];
    recordingDuration.value = 0;
    activeCardId.value = cardId;

    // Create MediaRecorder
    const options: MediaRecorderOptions = {};
    if (MediaRecorder.isTypeSupported('audio/webm')) {
      options.mimeType = 'audio/webm';
    } else if (MediaRecorder.isTypeSupported('audio/mp4')) {
      options.mimeType = 'audio/mp4';
    }

    mediaRecorder = new MediaRecorder(stream, options);

    // Handle data
    mediaRecorder.ondataavailable = (event) => {
      if (event.data.size > 0) {
        audioChunks.push(event.data);
      }
    };

    // Handle stop
    mediaRecorder.onstop = () => {
      const blob = new Blob(audioChunks, { type: mediaRecorder?.mimeType || 'audio/webm' });
      recordings.value[cardId] = blob;
      
      // Create URL for playback
      if (recordingUrls.value[cardId]) {
        URL.revokeObjectURL(recordingUrls.value[cardId]);
      }
      recordingUrls.value[cardId] = URL.createObjectURL(blob);
      
      // Stop tracks
      stream?.getTracks().forEach(track => track.stop());
      stream = null;
    };

    // Start recording
    mediaRecorder.start();
    isRecording.value = true;

    // Start duration counter
    durationInterval = window.setInterval(() => {
      recordingDuration.value++;
    }, 1000);

  } catch (error) {
    console.error('Failed to start recording:', error);
    alert('Failed to access microphone. Please grant permission and try again.');
  }
}

async function stopRecording() {
  if (mediaRecorder && isRecording.value) {
    mediaRecorder.stop();
    isRecording.value = false;
    
    if (durationInterval) {
      clearInterval(durationInterval);
      durationInterval = null;
    }
  }
}

function playRecording(cardId: string) {
  const url = recordingUrls.value[cardId];
  if (url) {
    // Stop any currently playing audio
    if (currentAudio) {
      currentAudio.pause();
      currentAudio = null;
    }

    currentAudio = new Audio(url);
    currentAudio.play();
  }
}

function deleteRecording(cardId: string) {
  if (recordingUrls.value[cardId]) {
    URL.revokeObjectURL(recordingUrls.value[cardId]);
    delete recordingUrls.value[cardId];
  }
  delete recordings.value[cardId];
}

function playReference(audioUrl: string) {
  // Stop any currently playing audio
  if (currentAudio) {
    currentAudio.pause();
    currentAudio = null;
  }

  currentAudio = new Audio(audioUrl);
  currentAudio.play();
}

// ============================================================================
// Utility Methods
// ============================================================================

function formatDuration(seconds: number): string {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

function submitAnswer() {
  const answer: SpeakingCardsAnswer = {
    questionId: props.question.id,
    recordings: { ...recordings.value },
    timestamp: new Date(),
  };

  showFeedback.value = true;

  emit('answer', answer);
  emit('complete', {
    questionId: props.question.id,
    answer,
    isCorrect: true, // Speaking cards don't have right/wrong answers
  });
}

function resetAll() {
  // Revoke all URLs
  Object.values(recordingUrls.value).forEach(url => URL.revokeObjectURL(url));
  
  recordings.value = {};
  recordingUrls.value = {};
  activeCardId.value = null;
  showFeedback.value = false;
  
  if (isRecording.value) {
    stopRecording();
  }
}

// ============================================================================
// Cleanup
// ============================================================================

onUnmounted(() => {
  // Stop recording if active
  if (isRecording.value) {
    stopRecording();
  }

  // Revoke all URLs
  Object.values(recordingUrls.value).forEach(url => URL.revokeObjectURL(url));

  // Stop any playing audio
  if (currentAudio) {
    currentAudio.pause();
  }

  // Clear interval
  if (durationInterval) {
    clearInterval(durationInterval);
  }
});
</script>

<style scoped>
.speaking-cards-default {
  @apply w-full;
}

.speaking-card {
  @apply p-4 bg-white border-2 border-gray-200 rounded-lg transition-all hover:shadow-md;
}

.card-active {
  @apply border-rose-500 bg-rose-50 shadow-lg;
}

.card-recorded {
  @apply border-green-500 bg-green-50;
}

.card-number {
  @apply absolute top-2 right-2 w-8 h-8 bg-gray-200 text-gray-700 rounded-full flex items-center justify-center text-sm font-bold;
}

.card-image-container {
  @apply mb-3;
}

.card-image {
  @apply w-full h-32 object-cover rounded-lg;
}

.card-text {
  @apply mb-3;
}

.recording-dot {
  @apply inline-block w-2 h-2 bg-white rounded-full animate-pulse;
}
</style>
