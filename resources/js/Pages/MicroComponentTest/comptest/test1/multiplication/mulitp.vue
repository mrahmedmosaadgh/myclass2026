<template>
  <div class="times-table-container">
    <div class="header">
      <div class="icons">🥇🥈🏆</div>
      <h2>Step 1b: In sequence</h2>
      <p class="instructions">
        Fill in your answers. Once you have entered all the answers, click on 'Check' to see whether you have got them all right!
      </p>
    </div>

    <div class="questions-grid">
      <div class="column">
        <div v-for="q in leftColumn" :key="q.id" class="question-row">
          <span class="equation">{{ tableNumber }} x {{ q.multiplier }} =</span>
          <div class="input-container">
            <input
              type="number"
              v-model.number="q.userAnswer"
              :class="getInputClass(q)"
              :disabled="isChecked"
              placeholder=""
            />
            <!-- Feedback icon displayed after the input -->
            <span v-if="isChecked && q.isCorrect !== null" class="feedback-icon" :class="q.isCorrect ? 'correct' : 'incorrect'">
              {{ q.isCorrect ? '✅' : '❌' }}
            </span>
          </div>
        </div>
      </div>

      <div class="column">
        <div v-for="q in rightColumn" :key="q.id" class="question-row">
          <span class="equation">{{ tableNumber }} x {{ q.multiplier }} =</span>
          <div class="input-container">
            <input
              type="number"
              v-model.number="q.userAnswer"
              :class="getInputClass(q)"
              :disabled="isChecked"
              placeholder=""
            />
            <!-- Feedback icon displayed after the input -->
            <span v-if="isChecked && q.isCorrect !== null" class="feedback-icon" :class="q.isCorrect ? 'correct' : 'incorrect'">
              {{ q.isCorrect ? '✅' : '❌' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="actions">
      <button v-if="!isChecked" @click="checkAnswers" class="action-btn check-btn">
        Check Answers
      </button>

      <div v-if="isChecked" class="results-display">
        <p>You got {{ score }} out of {{ totalQuestions }} questions right.</p>
        <p v-if="score === totalQuestions">Congratulations, you got full marks!!!</p>
        
        <div v-if="score === totalQuestions" class="medal">
          🏅
        </div>

        <button @click="practiceAgain" class="action-btn practice-btn">
          Practice again
        </button>
      </div>
    </div>

    <div v-if="savedResultsJson" class="json-preview">
      <h3>Saved JSON Data (In Variable):</h3>
      <pre>{{ savedResultsJson }}</pre>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// --- Configuration ---
const tableNumber = 2;
const totalQuestions = 12;

// --- State ---
const isChecked = ref(false);
const score = ref(0);
const savedResultsJson = ref(null); // Variable holding the saved JSON data

// Generate the initial 12 questions
const questions = ref(
  Array.from({ length: totalQuestions }, (_, i) => ({
    id: i + 1,
    multiplier: i + 1,
    userAnswer: null,
    isCorrect: null
  }))
);

// Split into two columns for the UI
const leftColumn = computed(() => questions.value.slice(0, 6));
const rightColumn = computed(() => questions.value.slice(6, 12));

// --- Methods ---

// Dynamically apply green/red text classes based on correctness
const getInputClass = (q) => {
  if (!isChecked.value || q.isCorrect === null) return '';
  return q.isCorrect ? 'text-correct' : 'text-incorrect';
};

// Validate inputs and save to JSON
const checkAnswers = () => {
  let currentScore = 0;
  
  // Grade the answers
  questions.value.forEach(q => {
    const correctAnswer = tableNumber * q.multiplier;
    
    // Check if user answer is strictly equal to the correct answer
    if (q.userAnswer === correctAnswer) {
      q.isCorrect = true;
      currentScore++;
    } else {
      q.isCorrect = false;
    }
  });

  score.value = currentScore;
  isChecked.value = true;

  // Format and save the user session data as a JSON string in a variable
  const sessionData = {
    activity: `${tableNumber} Times Table`,
    timestamp: new Date().toISOString(),
    score: currentScore,
    total: totalQuestions,
    passed: currentScore === totalQuestions,
    userInputs: questions.value.map(q => ({
      equation: `${tableNumber} x ${q.multiplier}`,
      userAnswer: q.userAnswer !== null ? q.userAnswer : "Blank",
      correctAnswer: tableNumber * q.multiplier,
      isCorrect: q.isCorrect
    }))
  };

  savedResultsJson.value = JSON.stringify(sessionData, null, 2);
};

// Reset everything to start over
const practiceAgain = () => {
  questions.value.forEach(q => {
    q.userAnswer = null;
    q.isCorrect = null;
  });
  isChecked.value = false;
  score.value = 0;
  savedResultsJson.value = null;
};
</script>

<style scoped>
/* Styling to match the educational/playful vibe */
.times-table-container {
  font-family: 'Comic Sans MS', 'Chalkboard SE', sans-serif; /* Playful font */
  max-width: 700px;
  margin: 0 auto;
  text-align: center;
  color: #333;
}

.header .icons {
  font-size: 2rem;
  margin-bottom: 5px;
}

.instructions {
  font-size: 1.1rem;
  margin-bottom: 30px;
  padding: 0 20px;
}

.questions-grid {
  display: flex;
  justify-content: space-around;
  margin-bottom: 30px;
}

.column {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.question-row {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  font-size: 1.8rem;
}

.equation {
  margin-right: 15px;
}

.input-container {
  display: flex;
  align-items: center;
  position: relative;
}

input {
  width: 70px;
  height: 45px;
  font-size: 1.5rem;
  font-family: inherit;
  text-align: center;
  border: 1px solid #7cb3f5;
  border-radius: 6px;
  outline: none;
}

input:focus {
  border-color: #4a90e2;
  box-shadow: 0 0 5px rgba(74, 144, 226, 0.5);
}

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

/* Hide increment arrows on number inputs */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type=number] {
  -moz-appearance: textfield;
}

/* Validation colors */
.text-correct {
  color: #32CD32; /* Lime Green */
}
.text-incorrect {
  color: #FF0000; /* Red */
}

.actions {
  margin-top: 20px;
}

.action-btn {
  background-color: #bada55; /* Similar to the green in the image */
  border: none;
  padding: 12px 30px;
  font-size: 1.4rem;
  font-family: inherit;
  font-weight: bold;
  color: white;
  cursor: pointer;
  border-radius: 5px;
  transition: background-color 0.2s;
}

.action-btn:hover {
  background-color: #a4c639;
}

.check-btn {
  background-color: #4a90e2;
}

.check-btn:hover {
  background-color: #357abd;
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

.json-preview {
  margin-top: 50px;
  text-align: left;
  background: #2d2d2d;
  color: #a9b7c6;
  padding: 15px;
  border-radius: 8px;
  font-family: monospace;
  font-size: 0.9rem;
  overflow-x: auto;
}

@keyframes popIn {
  0% { transform: scale(0); }
  80% { transform: scale(1.1); }
  100% { transform: scale(1); }
}
</style>