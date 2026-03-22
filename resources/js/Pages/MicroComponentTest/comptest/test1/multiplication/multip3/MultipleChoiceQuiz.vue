<template>
  <div class="quiz-wrapper">
    <!-- Header -->
    <div class="quiz-header">
      <div class="header-icons">🥇🥈🏆</div>
      <h2 class="header-title">Step 4: Multiple choice</h2>
    </div>

    <p class="quiz-instructions">Try to answer all the {{ activeQuestions.length }} questions right!</p>

    <!-- Completion Screen -->
    <div v-if="isComplete" class="completion-screen">
      <div class="medal-icon">🏅</div>
      <h3 class="completion-title">Quiz Complete!</h3>
      <p class="completion-score">You got <strong>{{ correctCount }}</strong> out of <strong>{{ activeQuestions.length }}</strong> correct!</p>
      <p class="completion-percent">{{ finalPercentage }}%</p>
      <div class="completion-actions">
        <button class="btn-retry" @click="restartQuiz(false)">Try Again 🔄</button>
        <button v-if="incorrectCount > 0" class="btn-mistakes" @click="restartQuiz(true)">Practice Mistakes Only 🎯</button>
      </div>
    </div>

    <!-- Game Area -->
    <div v-else class="game-area">
      <!-- Scoreboard Sidebar -->
      <QuizScoreboard
        :correct-count="correctCount"
        :incorrect-count="incorrectCount"
        :answered-count="answeredCount"
        :total-questions="activeQuestions.length"
      />

      <!-- Question Card -->
      <QuizQuestionCard
        :question="currentQuestion"
        :disabled="isAnswered"
        @answer-selected="handleAnswer"
      />
    </div>

    <!-- Progress Bar -->
    <div v-if="!isComplete" class="progress-track">
      <div
        class="progress-fill"
        :style="{ width: progressPercent + '%' }"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import QuizQuestionCard from './QuizQuestionCard.vue';
import QuizScoreboard from './QuizScoreboard.vue';
import quizData from './quizData.js';

// ─── Props & Emits ────────────────────────────────────────────────────────────
const props = defineProps({
  questions: { type: Array, default: () => quizData },
});

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
        audio.addEventListener('error', resolve); // Resolve on error so quiz still starts
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
const activeQuestions = ref([...props.questions]);
const mistakeQuestions = ref([]);
const currentIndex  = ref(0);
const correctCount  = ref(0);
const incorrectCount = ref(0);
const answeredCount = ref(0);
const isAnswered    = ref(false);
const isComplete    = ref(false);

/**
 * studentProgress — saved here for parent to read via template ref or emit.
 * Updated after every question and on quiz completion.
 */
const studentProgress = ref({
  correctCount:   0,
  total:          activeQuestions.value.length,
  percentage:     0,
  completedAt:    null,
});

// ─── Computed ─────────────────────────────────────────────────────────────────
const currentQuestion = computed(() => activeQuestions.value[currentIndex.value]);

const progressPercent = computed(() =>
  Math.round((answeredCount.value / activeQuestions.value.length) * 100)
);

const finalPercentage = computed(() =>
  Math.round((correctCount.value / activeQuestions.value.length) * 100)
);

// ─── Handlers ────────────────────────────────────────────────────────────────
const handleAnswer = (selected) => {
  if (isAnswered.value) return;
  isAnswered.value = true;
  answeredCount.value++;

  const correct = selected === currentQuestion.value.correctAnswer;
  if (correct) {
    correctCount.value++;
    playSound('correct');
  } else {
    incorrectCount.value++;
    mistakeQuestions.value.push(currentQuestion.value);
    playSound('incorrect');
  }

  // Update progress snapshot
  studentProgress.value = {
    correctCount:  correctCount.value,
    total:         activeQuestions.value.length,
    percentage:    Math.round((correctCount.value / activeQuestions.value.length) * 100),
    completedAt:   null,
  };

  // Advance after a short delay so the student sees the feedback
  setTimeout(() => {
    if (currentIndex.value + 1 >= activeQuestions.value.length) {
      finishQuiz();
    } else {
      currentIndex.value++;
      isAnswered.value = false;
    }
  }, 900);
};

const finishQuiz = () => {
  isComplete.value = true;
  playSound('success');

  studentProgress.value = {
    correctCount:  correctCount.value,
    total:         activeQuestions.value.length,
    percentage:    finalPercentage.value,
    completedAt:   new Date().toISOString(),
  };

  emit('quiz-complete', {
    correctCount: correctCount.value,
    total:        activeQuestions.value.length,
    percentage:   finalPercentage.value,
  });
};

const restartQuiz = (mistakesOnly = false) => {
  if (mistakesOnly && mistakeQuestions.value.length > 0) {
    activeQuestions.value = [...mistakeQuestions.value];
  } else {
    activeQuestions.value = [...props.questions];
  }
  
  mistakeQuestions.value = [];
  currentIndex.value   = 0;
  correctCount.value   = 0;
  incorrectCount.value = 0;
  answeredCount.value  = 0;
  isAnswered.value     = false;
  isComplete.value     = false;
  
  studentProgress.value = {
    correctCount: 0,
    total:        activeQuestions.value.length,
    percentage:   0,
    completedAt:  null,
  };
};

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(async () => {
  await preloadAudio(); // Zero-delay audio on first answer click
});

// Expose studentProgress so a parent using template ref can read it
defineExpose({ studentProgress });
</script>

<style scoped>
.quiz-wrapper {
  font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
  max-width: 760px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

/* Header */
.quiz-header {
  display: flex;
  align-items: center;
  gap: 10px;
}

.header-icons { font-size: 1.6rem; }

.header-title {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 700;
  color: #1e293b;
}

.quiz-instructions {
  margin: 0;
  text-align: center;
  color: #6b7280;
  background: #f3f4f6;
  padding: 10px;
  border-radius: 8px;
  font-size: 0.95rem;
}

/* Game Area (Scoreboard + Card side by side) */
.game-area {
  display: flex;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
  min-height: 340px;
}

/* Progress Bar */
.progress-track {
  height: 8px;
  background: #e5e7eb;
  border-radius: 99px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #22c55e);
  border-radius: 99px;
  transition: width 0.4s ease;
}

/* Completion Screen */
.completion-screen {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 40px 20px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
  text-align: center;
}

.medal-icon {
  font-size: 4rem;
  animation: popIn 0.5s ease-out;
}

.completion-title {
  margin: 0;
  font-size: 1.6rem;
  font-weight: 700;
  color: #1e293b;
}

.completion-score {
  margin: 0;
  font-size: 1rem;
  color: #374151;
}

.completion-percent {
  margin: 0;
  font-size: 2.5rem;
  font-weight: 800;
  color: #22c55e;
}

.completion-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-retry, .btn-mistakes {
  padding: 12px 28px;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.15s ease;
}

.btn-retry { background: #3b82f6; }
.btn-mistakes { background: #f59e0b; }

.btn-retry:hover, .btn-mistakes:hover { transform: translateY(-2px); }

@keyframes popIn {
  0%   { transform: scale(0); }
  80%  { transform: scale(1.15); }
  100% { transform: scale(1); }
}
</style>
