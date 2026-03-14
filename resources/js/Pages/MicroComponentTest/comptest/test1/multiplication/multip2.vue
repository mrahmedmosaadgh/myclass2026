<template>
  <div class="drag-match-game">
    <div class="game-header">
      <div class="icons">🥇🥈🏆</div>
      <h2>Multiplication Drag & Drop</h2>
      <div class="audio-controls">
        <button @click="toggleMute" class="mute-btn" :class="{ muted: isMuted }">
          {{ isMuted ? '🔇 Unmute' : '🔊 Mute' }}
        </button>
      </div>
      <p class="instructions">
        Drag the correct answers to match each multiplication problem. 
        Once you have matched all problems, click on 'Check' to see your results!
      </p>
    </div>
    
    <div class="game-board">
      
      <div class="questions-column">
        <div v-for="q in currentQuestions" :key="q.id" class="question-row">
          <span class="question-text">{{ q.label }}</span>
          
          <draggable
            v-model="slots[q.id]"
            :group="{ name: 'blocks', put: () => slots[q.id]?.length === 0 }"
            item-key="id"
            class="drop-zone"
            :class="{ 'has-item': slots[q.id]?.length > 0 }"
            :animation="200"
            @add="onBlockAdded"
          >
            <template #item="{ element }">
              <div class="draggable-block">{{ element.value }}</div>
            </template>
          </draggable>
          
          <!-- Feedback icon displayed after checking answers -->
          <span v-if="resultSummary" class="feedback-icon" :class="getAnswerStatus(q.id)">
            {{ getFeedbackIcon(q.id) }}
          </span>
        </div>
      </div>

      <div class="options-column">
        <draggable
          v-model="currentAvailableBlocks"
          group="blocks"
          item-key="id"
          class="options-pool"
          :animation="200"
          @add="onBlockRemoved"
        >
          <template #item="{ element }">
            <div class="draggable-block">{{ element.value }}</div>
          </template>
        </draggable>
      </div>

    </div>

    <div class="actions-row">
      <button @click="randomizeQuestions" class="btn btn-randomize">Randomize Questions</button>
      <button @click="randomizeAnswers" class="btn btn-randomize">Randomize Answers</button>
      <button @click="randomizeBoth" class="btn btn-randomize">Randomize Both</button>
      <button @click="resetGame" class="btn btn-reset">Practice again</button>
      <button v-if="resultSummary && hasMistakes" @click="practiceMistakesOnly" class="btn btn-practice-mistakes">Practice Mistakes Only</button>
      <button @click="submitAnswers" class="btn btn-check">Check!</button>
    </div>
    
    <div v-if="resultSummary" class="results-display">
      <p>You got {{ resultSummary.score }} out of {{ resultSummary.total }} questions right.</p>
      <p v-if="resultSummary.percentage === 100">Congratulations, you got full marks!!!</p>
      
      <div v-if="resultSummary.percentage === 100" class="medal">
        🏅
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import draggable from 'vuedraggable';

// Configuration
const tableNumber = 2;
const totalQuestions = 12;

// Audio state
const isMuted = ref(false);
const audioElements = ref({});

// State
const availableBlocks = ref([]);
const slots = ref({});
const resultSummary = ref(null);
const originalQuestions = ref([]);
const originalOptions = ref([]);

// Preload audio files
const preloadAudio = async () => {
  const audioFiles = {
    correct: '/audio/Audio/correct-answer.mp3',
    incorrect: '/audio/Audio/incorrect-answer.mp3',
    drag: '/audio/Audio/drag-sound.mp3',
    success: '/audio/Audio/success-jingle.mp3',
    default: '/audio/click-234708.mp3'
  };

  // Create audio elements and preload them
  for (const [type, src] of Object.entries(audioFiles)) {
    try {
      // Try to create audio element
      const audio = new Audio();
      audio.src = src;
      audio.preload = 'auto';
      
      // Load the audio
      await new Promise((resolve, reject) => {
        audio.addEventListener('loadeddata', resolve);
        audio.addEventListener('error', () => {
          console.warn(`Audio file not found: ${src}, will use Web Audio API fallback`);
          resolve(); // Resolve anyway so we continue
        });
        audio.load();
      });
      
      audioElements.value[type] = audio;
    } catch (e) {
      console.warn(`Could not preload audio: ${src}`, e);
      // Fallback: we'll use Web Audio API to generate sounds if files aren't available
      audioElements.value[type] = null;
    }
  }
};

// Audio functions
const playSound = (soundType) => {
  if (isMuted.value) return;
  
  // If we have the audio element cached, play it
  if (audioElements.value[soundType]) {
    // Clone the audio element to allow overlapping sounds
    const audioClone = audioElements.value[soundType].cloneNode();
    audioClone.currentTime = 0;
    audioClone.play().catch(e => {
      console.warn('Audio playback failed, using Web Audio API fallback:', e);
      playSynthesizedSound(soundType);
    });
  } else {
    // Fallback to synthesized sound if audio file isn't available
    playSynthesizedSound(soundType);
  }
};

// Fallback function to play synthesized sounds using Web Audio API
const playSynthesizedSound = (soundType) => {
  // Check if Web Audio API is available
  if (!window.AudioContext && !window.webkitAudioContext) {
    console.warn('Web Audio API not supported, skipping sound');
    return;
  }

  const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  
  // Create oscillator and gain node
  const oscillator = audioCtx.createOscillator();
  const gainNode = audioCtx.createGain();
  
  oscillator.connect(gainNode);
  gainNode.connect(audioCtx.destination);
  
  // Configure different sounds based on type
  switch(soundType) {
    case 'correct':
      oscillator.type = 'sine';
      oscillator.frequency.value = 523.25; // C5 note
      gainNode.gain.value = 0.3;
      // Quick upward tone
      oscillator.frequency.setValueAtTime(523.25, audioCtx.currentTime);
      oscillator.frequency.exponentialRampToValueAtTime(659.25, audioCtx.currentTime + 0.2);
      break;
      
    case 'incorrect':
      oscillator.type = 'sawtooth';
      oscillator.frequency.value = 220; // A3 note
      gainNode.gain.value = 0.3;
      // Descending tone
      oscillator.frequency.setValueAtTime(220, audioCtx.currentTime);
      oscillator.frequency.exponentialRampToValueAtTime(110, audioCtx.currentTime + 0.3);
      break;
      
    case 'drag':
      oscillator.type = 'square';
      oscillator.frequency.value = 330; // E4 note
      gainNode.gain.value = 0.1;
      // Short neutral beep
      break;
      
    case 'success':
      oscillator.type = 'sine';
      gainNode.gain.value = 0.3;
      // Ascending arpeggio (C-E-G-C)
      oscillator.frequency.setValueAtTime(523.25, audioCtx.currentTime);         // C
      oscillator.frequency.setValueAtTime(659.25, audioCtx.currentTime + 0.15);  // E
      oscillator.frequency.setValueAtTime(783.99, audioCtx.currentTime + 0.3);   // G
      oscillator.frequency.setValueAtTime(1046.50, audioCtx.currentTime + 0.45); // C octave
      break;
      
    default:
      oscillator.type = 'sine';
      oscillator.frequency.value = 440; // A4 note
      gainNode.gain.value = 0.2;
  }
  
  // Start and stop the oscillator
  oscillator.start();
  oscillator.stop(audioCtx.currentTime + 0.5); // Stop after 0.5 seconds (or specific duration for success sound)
};

const toggleMute = () => {
  isMuted.value = !isMuted.value;
};

// Generate the initial questions and options
const questions = ref(
  Array.from({ length: totalQuestions }, (_, i) => ({
    id: `q${i + 1}`,
    label: `${tableNumber} x ${i + 1} =`,
    correctAnswer: tableNumber * (i + 1)
  }))
);

const options = ref(
  Array.from({ length: totalQuestions }, (_, i) => ({
    id: `o${i + 1}`,
    value: tableNumber * (i + 1)
  }))
);

// Current state for questions and options (can be modified for mistakes only mode)
const currentQuestions = ref([...questions.value]);
const currentAvailableBlocks = ref([...options.value]);

// Store original data for resetting
originalQuestions.value = [...questions.value];
originalOptions.value = [...options.value];

// Check if there are mistakes
const hasMistakes = computed(() => {
  return resultSummary.value && resultSummary.value.score < resultSummary.value.total;
});

// Setup fresh state
const initializeGame = (useMistakesOnly = false) => {
  if (useMistakesOnly && resultSummary.value) {
    // Filter questions to only include those that were answered incorrectly
    const incorrectQuestions = resultSummary.value.details
      .filter(detail => !detail.isCorrect)
      .map(detail => {
        return originalQuestions.value.find(q => q.id === detail.questionId);
      })
      .filter(Boolean); // Remove any undefined values

    currentQuestions.value = [...incorrectQuestions];
    
    // Get only the correct answers for the incorrect questions
    const requiredAnswers = incorrectQuestions.map(q => 
      originalOptions.value.find(opt => opt.value === q.correctAnswer)
    ).filter(Boolean);
    
    currentAvailableBlocks.value = [...requiredAnswers];
  } else {
    // Use all questions and answers
    currentQuestions.value = [...originalQuestions.value];
    currentAvailableBlocks.value = [...originalOptions.value];
  }

  // Shuffle the answers
  currentAvailableBlocks.value = [...currentAvailableBlocks.value].sort(() => Math.random() - 0.5);

  // Create an empty array for each specific question's drop slot
  const newSlots = {};
  currentQuestions.value.forEach(q => {
    newSlots[q.id] = [];
  });
  slots.value = newSlots;
  
  resultSummary.value = null;
};

onMounted(async () => {
  // Preload audio files when component mounts
  await preloadAudio();
  initializeGame();
});

// Randomize questions
const randomizeQuestions = () => {
  currentQuestions.value = [...currentQuestions.value].sort(() => Math.random() - 0.5);
  initializeSlots();
};

// Randomize answers
const randomizeAnswers = () => {
  currentAvailableBlocks.value = [...currentAvailableBlocks.value].sort(() => Math.random() - 0.5);
  initializeSlots();
};

// Randomize both questions and answers
const randomizeBoth = () => {
  randomizeQuestions();
  randomizeAnswers();
};

// Initialize slots after shuffling
const initializeSlots = () => {
  const newSlots = {};
  currentQuestions.value.forEach(q => {
    newSlots[q.id] = [];
  });
  slots.value = newSlots;
  resultSummary.value = null;
};

// Handle block added to drop zone
const onBlockAdded = () => {
  playSound('drag');
};

// Handle block removed from pool
const onBlockRemoved = () => {
  playSound('drag');
};

// Practice mistakes only
const practiceMistakesOnly = () => {
  initializeGame(true);
};

// Determine if an answer is correct or incorrect
const getAnswerStatus = (questionId) => {
  if (!resultSummary.value) return '';
  
  const detail = resultSummary.value.details.find(d => d.questionId === questionId);
  return detail ? (detail.isCorrect ? 'correct' : 'incorrect') : '';
};

// Return the appropriate feedback icon
const getFeedbackIcon = (questionId) => {
  if (!resultSummary.value) return '';
  
  const detail = resultSummary.value.details.find(d => d.questionId === questionId);
  return detail ? (detail.isCorrect ? '✅' : '❌') : '';
};

// Actions
const submitAnswers = () => {
  let score = 0;
  
  // Map through questions to evaluate what the user dropped in each slot
  const details = currentQuestions.value.map(q => {
    const slotContent = slots.value[q.id];
    const userAnswer = slotContent?.length > 0 ? slotContent[0].value : null;
    
    // Compare as strings to prevent strict typing issues (e.g., number 2 vs string "2")
    const isCorrect = String(userAnswer) === String(q.correctAnswer);

    if (isCorrect) {
      playSound('correct');
    } else {
      playSound('incorrect');
    }

    if (isCorrect) score++;

    return {
      questionId: q.id,
      questionLabel: q.label,
      userAnswer: userAnswer,
      correctAnswer: q.correctAnswer,
      isCorrect
    };
  });

  // Play success sound if all answers are correct
  if (score === currentQuestions.value.length && currentQuestions.value.length > 0) {
    playSound('success');
  }

  // Store the results
  resultSummary.value = {
    score,
    total: currentQuestions.value.length,
    percentage: Math.round((score / currentQuestions.value.length) * 100),
    details
  };

  console.log('Multiplication Results:', resultSummary.value);
};

const resetGame = () => {
  initializeGame();
  resultSummary.value = null;
};
</script>

<style scoped>
/* Container Layout */
.drag-match-game {
  font-family: 'Comic Sans MS', 'Chalkboard SE', sans-serif; /* Playful font */
  max-width: 700px;
  margin: 0 auto;
  text-align: center;
  color: #333;
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.game-header {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.game-header .icons {
  font-size: 2rem;
  margin-bottom: 5px;
}

.audio-controls {
  margin-bottom: 10px;
}

.mute-btn {
  padding: 5px 10px;
  background-color: #87CEEB; /* Light blue */
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}

.mute-btn.muted {
  background-color: #FF6347; /* Tomato red */
}

.instructions {
  font-size: 1.1rem;
  margin-bottom: 30px;
  padding: 0 20px;
}

.game-board {
  display: flex;
  gap: 40px;
}

/* Left Column: Questions */
.questions-column {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.question-row {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  font-size: 1.8rem;
}

.question-text {
  font-weight: 600;
  color: #333;
  font-size: 16px;
}

/* Drop Zones */
.drop-zone {
  width: 70px;
  height: 45px;
  background-color: #d1d5db; /* Gray representing an empty slot */
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #7cb3f5;
}
.drop-zone.has-item {
  background-color: transparent; /* Hide the gray background when filled */
}

/* Feedback Icon */
.feedback-icon {
  margin-left: 10px;
  font-size: 1.5rem;
  min-width: 24px;
}

.feedback-icon.correct {
  color: #32CD32; /* Lime Green */
}

.feedback-icon.incorrect {
  color: #FF0000; /* Red */
}

/* Right Column: Pool of Options */
.options-pool {
  display: flex;
  flex-direction: column;
  flex-wrap: wrap;
  align-content: flex-start;
  gap: 10px;
  width: 200px;
  min-height: 100%;
}

/* Draggable Blocks */
.draggable-block {
  width: 70px;
  height: 45px;
  background-color: #3b82f6; /* Blue blocks */
  color: white;
  font-weight: bold;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  cursor: grab;
  user-select: none;
  border: 1px solid #2563eb;
}
.draggable-block:active {
  cursor: grabbing;
}

/* Buttons */
.actions-row {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 10px;
  padding: 20px 0;
}

.btn {
  padding: 10px 15px;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  font-weight: bold;
  cursor: pointer;
  font-family: inherit;
  margin: 5px;
}

.btn-reset {
  background-color: #a4c639; /* Green */
  color: white;
}

.btn-check {
  background-color: #4a90e2; /* Blue */
  color: white;
}

.btn-randomize {
  background-color: #87CEEB; /* Light blue */
  color: #333;
}

.btn-practice-mistakes {
  background-color: #FF6347; /* Tomato red */
  color: white;
}

.results-display {
  font-size: 1.3rem;
  margin-top: 20px;
}

.medal {
  font-size: 5rem;
  margin: 15px 0;
  animation: popIn 0.5s ease-out;
}

@keyframes popIn {
  0% { transform: scale(0); }
  80% { transform: scale(1.1); }
  100% { transform: scale(1); }
}
</style>