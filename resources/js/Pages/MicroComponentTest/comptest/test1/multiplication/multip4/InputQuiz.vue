<template>
  <div class="quiz-wrapper">
    <!-- Header -->
    <div class="quiz-header">
      <div class="header-icons">⭐</div>
      <h2 class="header-title">Step 5: Tables Diploma</h2>
    </div>

    <p class="quiz-instructions">Answer all the {{ totalQuestions }} questions right to get the diploma!</p>

    <!-- Completion Screen -->
    <div v-if="isComplete" class="completion-screen">
      <div class="medal-icon">🏆</div>
      <h3 class="completion-title">Diploma Earned!</h3>
      <p class="completion-score">You got all <strong>{{ totalQuestions }}</strong> questions right!</p>
      <p class="completion-time">Your time: <strong>{{ formattedTime }}</strong></p>
      <div class="completion-actions">
        <button class="btn-retry" @click="restartQuiz">Play Again 🔄</button>
      </div>
    </div>

    <!-- Game Area -->
    <div v-else class="game-area">
      <!-- Top Bar -->
      <div class="game-top-bar">
         <div class="timer">Time: {{ formattedTime }}</div>
         <div class="question-counter">Question {{ currentIndex + 1 }} / {{ totalQuestions }}</div>
      </div>
      
      <!-- Center Content -->
      <div class="game-center">
         <div class="equation-text">{{ currentQuestion.question }}</div>
         <input
            ref="answerInput"
            type="number"
            v-model="inputValue"
            class="answer-input"
            autofocus
            @keydown.enter="submitAnswer"
            :class="{ 'shake-error': hasError }"
          />
      </div>

      <!-- Bottom Right Action -->
      <div class="game-bottom-action">
         <button 
             class="btn-answer" 
             @click="submitAnswer"
             :disabled="isSubmitting || inputValue === '' || inputValue === null">
             Answer
         </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { generateQuestions } from './quizData.js';

const emit = defineEmits(['quiz-complete']);

// ─── Audio ────────────────────────────────────────────────────────────────────
const audioMap = ref({});

const AUDIO_FILES = {
  correct:   '/audio/Audio/correct-answer.mp3',
  incorrect: '/audio/Audio/incorrect-answer.mp3',
  success:   '/audio/Audio/success-jingle.mp3',
};

const preloadAudio = async () => {
  for (const [key, src] of Object.entries(AUDIO_FILES)) {
    try {
      const audio = new Audio();
      audio.src = src;
      audio.preload = 'auto';
      await new Promise((resolve) => {
        audio.addEventListener('loadeddata', resolve);
        audio.addEventListener('error', resolve); 
        audio.load();
      });
      audioMap.value[key] = audio;
    } catch {
      audioMap.value[key] = null;
    }
  }
};

const playSound = (type) => {
  const audio = audioMap.value[type];
  if (audio) {
    audio.currentTime = 0;
    audio.play().catch(() => playSynth(type));
  } else {
    playSynth(type);
  }
};

const playSynth = (type) => {
  const Ctx = window.AudioContext || window.webkitAudioContext;
  if (!Ctx) return;
  const ctx = new Ctx();
  const osc = ctx.createOscillator();
  const gain = ctx.createGain();
  osc.connect(gain);
  gain.connect(ctx.destination);

  if (type === 'correct') {
    osc.type = 'sine'; osc.frequency.value = 523.25; gain.gain.value = 0.3;
    osc.frequency.exponentialRampToValueAtTime(659.25, ctx.currentTime + 0.2);
  } else if (type === 'incorrect') {
    osc.type = 'sawtooth'; osc.frequency.value = 220; gain.gain.value = 0.3;
    osc.frequency.exponentialRampToValueAtTime(110, ctx.currentTime + 0.3);
  } else {
    osc.type = 'sine'; gain.gain.value = 0.3;
    osc.frequency.setValueAtTime(523.25, ctx.currentTime);
    osc.frequency.setValueAtTime(659.25, ctx.currentTime + 0.15);
    osc.frequency.setValueAtTime(783.99, ctx.currentTime + 0.30);
    osc.frequency.setValueAtTime(1046.50, ctx.currentTime + 0.45);
  }
  osc.start();
  osc.stop(ctx.currentTime + 0.6);
};

// ─── State ────────────────────────────────────────────────────────────────────
const activeQuestions = ref([...generateQuestions(24)]);
const currentIndex  = ref(0);
const isComplete    = ref(false);

const inputValue    = ref('');
const isSubmitting  = ref(false);
const hasError      = ref(false);
const answerInput   = ref(null);

// ─── Timer ────────────────────────────────────────────────────────────────
const timerSeconds = ref(0);
const timerInterval = ref(null);

const formattedTime = computed(() => {
    const m = Math.floor(timerSeconds.value / 60).toString().padStart(2, '0');
    const s = (timerSeconds.value % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
});

const startTimer = () => {
    if (timerInterval.value) clearInterval(timerInterval.value);
    timerSeconds.value = 0;
    timerInterval.value = setInterval(() => {
        timerSeconds.value++;
    }, 1000);
};

const stopTimer = () => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
        timerInterval.value = null;
    }
};

// ─── Computed ─────────────────────────────────────────────────────────────────
const totalQuestions = computed(() => activeQuestions.value.length);
const currentQuestion = computed(() => activeQuestions.value[currentIndex.value]);

// ─── Handlers ────────────────────────────────────────────────────────────────
const submitAnswer = () => {
    if (isSubmitting.value || isComplete.value) return;
    if (inputValue.value === '' || inputValue.value === null) return;
    
    isSubmitting.value = true;
    hasError.value = false;

    const answer = parseInt(inputValue.value, 10);
    const correct = currentQuestion.value.correctAnswer;

    if (answer === correct) {
        playSound('correct');
        inputValue.value = ''; // clear for next question
        
        setTimeout(() => {
            if (currentIndex.value + 1 >= totalQuestions.value) {
                finishQuiz();
            } else {
                currentIndex.value++;
                isSubmitting.value = false;
                nextTick(() => {
                    if (answerInput.value) answerInput.value.focus();
                });
            }
        }, 300); // small delay for feedback flow
    } else {
        playSound('incorrect');
        hasError.value = true;
        
        setTimeout(() => {
            hasError.value = false;
            inputValue.value = '';
            isSubmitting.value = false;
            nextTick(() => {
                if (answerInput.value) answerInput.value.focus();
            });
        }, 600); // clear error state after animation/sound
    }
};

const finishQuiz = () => {
    stopTimer();
    isComplete.value = true;
    isSubmitting.value = false;
    playSound('success');

    emit('quiz-complete', {
        time: timerSeconds.value,
        formattedTime: formattedTime.value,
        total: totalQuestions.value,
    });
};

const restartQuiz = () => {
    activeQuestions.value = [...generateQuestions(24)];
    currentIndex.value   = 0;
    isComplete.value     = false;
    inputValue.value     = '';
    isSubmitting.value   = false;
    hasError.value       = false;
    
    startTimer();
    
    nextTick(() => {
        if (answerInput.value) answerInput.value.focus();
    });
};

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(async () => {
    await preloadAudio();
    startTimer();
    nextTick(() => {
        if (answerInput.value) answerInput.value.focus();
    });
});

onUnmounted(() => {
    stopTimer();
});

defineExpose({ formattedTime });
</script>

<style scoped>
.quiz-wrapper {
  font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
  max-width: 800px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

/* Header */
.quiz-header {
  display: flex;
  align-items: center;
  gap: 10px;
}

.header-icons { 
    font-size: 1.6rem; 
    filter: sepia(100%) saturate(300%) hue-rotate(30deg) brightness(80%);
}

.header-title {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 700;
  color: #4b5563; /* Dark gray */
}

.quiz-instructions {
  margin: 0 0 4px 0;
  text-align: center;
  color: #1e293b;
  font-size: 1rem;
}

/* Game Area */
.game-area {
  display: flex;
  flex-direction: column;
  border: 2px solid #1e3a8a; /* Dark blue border */
  border-radius: 4px;
  background: #5babe1;       /* Light blue matching mockup */
  min-height: 400px;
  position: relative;
  overflow: hidden;
}

/* Top Bar */
.game-top-bar {
  display: flex;
  justify-content: space-between;
  padding: 16px 20px;
  font-weight: 700;
  color: #111827;
  font-size: 1.1rem;
}

/* Center Content */
.game-center {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-top: -40px; /* shift slightly up to center visually */
}

.equation-text {
  font-size: 4.5rem;
  color: #fff;
  font-weight: 400;
  letter-spacing: 2px;
  text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.answer-input {
  width: 140px;
  height: 80px;
  font-size: 3rem;
  text-align: center;
  border: 1px solid #6b7280;
  border-radius: 4px;
  outline: none;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.answer-input:focus {
  border-color: #3b82f6;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.05), 0 0 0 3px rgba(59,130,246,0.3);
}

.answer-input.shake-error {
  animation: shake 0.4s ease-in-out;
  border-color: #ef4444;
  background-color: #fef2f2;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-8px); }
  50% { transform: translateX(8px); }
  75% { transform: translateX(-8px); }
}

/* Bottom Action */
.game-bottom-action {
  position: absolute;
  bottom: 24px;
  right: 24px;
}

.btn-answer {
  background: #8dc63f; /* Apple green */
  border: none;
  border-bottom: 4px solid #73a62f; /* darker green for 3D effect */
  border-radius: 6px;
  color: #fff;
  font-size: 1.3rem;
  font-weight: 700;
  padding: 12px 50px;
  cursor: pointer;
  transition: all 0.1s ease;
  text-shadow: 1px 1px 1px rgba(0,0,0,0.1);
}

.btn-answer:active:not(:disabled) {
  transform: translateY(4px);
  border-bottom-width: 0px;
  margin-top: 4px;
}

.btn-answer:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Completion Screen */
.completion-screen {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 60px 20px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
  text-align: center;
}

.medal-icon {
  font-size: 5rem;
  animation: popIn 0.5s ease-out;
}

.completion-title {
  margin: 0;
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
}

.completion-score, .completion-time {
  margin: 0;
  font-size: 1.2rem;
  color: #374151;
}

.completion-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 20px;
}

.btn-retry {
  background: #3b82f6;
  padding: 14px 32px;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 1.2rem;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.15s ease;
}

.btn-retry:hover { transform: translateY(-2px); }

@keyframes popIn {
  0%   { transform: scale(0); }
  80%  { transform: scale(1.15); }
  100% { transform: scale(1); }
}

/* Chrome, Safari, Edge, Opera: hide number arrows */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox: hide number arrows */
input[type=number] {
  -moz-appearance: textfield;
  appearance: textfield;
}
</style>
