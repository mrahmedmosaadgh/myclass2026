<template>
  <div :class="['bg-white p-6 rounded-lg border border-gray-200 shadow-sm overflow-hidden', juniorMode ? 'bg-gradient-to-r from-indigo-100 via-purple-100 to-pink-100' : '']">
    <!-- Header -->
    <div class="flex justify-between items-start mb-4">
      <h3 class="text-lg font-medium text-gray-900 flex-1 mr-4" v-html="question.text"></h3>
      <div class="flex flex-col items-end gap-2">
        <span class="text-xs font-medium px-2.5 py-0.5 rounded" :class="juniorMode ? 'bg-yellow-200 text-yellow-800' : 'bg-blue-100 text-blue-800'">
          {{ question.points }} Points
        </span>
        <div v-if="question.timer > 0" class="flex items-center gap-1 text-sm font-mono" :class="timeLeft < 10 ? 'text-red-600 font-bold' : 'text-gray-600'">
          <i class="fas fa-clock"></i> {{ timeLeft }}s
        </div>
        <div v-if="!solved" class="text-xs text-gray-500">
          Attempts: {{ attempts }}/3
        </div>
      </div>
    </div>

    <!-- Question Types -->
    <div class="space-y-3">
      <!-- Single Choice -->
      <div v-if="question.type === 'single_choice'" class="space-y-2">
        <button v-for="option in question.options" :key="option.id"
                @click="!submitted && updateAnswer(option.id)"
                :class="['w-full text-left p-4 rounded-lg border transition-all flex items-center justify-between', getOptionClass(option.id)]">
          <span v-html="option.text"></span>
          <i v-if="isSelected(option.id)" class="fas fa-check-circle text-blue-500"></i>
        </button>
      </div>

      <!-- Multiple Choice -->
      <div v-else-if="question.type === 'multiple_choice'" class="space-y-2">
        <button v-for="option in question.options" :key="option.id"
                @click="!submitted && toggleOption(option.id)"
                :class="['w-full text-left p-4 rounded-lg border transition-all flex items-center gap-3', getOptionClass(option.id)]">
          <div class="w-5 h-5 border rounded flex items-center justify-center"
               :class="isSelected(option.id) ? 'bg-blue-600 border-blue-600' : 'border-gray-300'">
            <i v-if="isSelected(option.id)" class="fas fa-check text-white text-xs"></i>
          </div>
          <span v-html="option.text"></span>
        </button>
      </div>

      <!-- True / False -->
      <div v-else-if="question.type === 'true_false'" class="flex gap-4">
        <button @click="!submitted && updateAnswer(true)"
                :class="['flex-1 p-4 rounded-lg border text-center font-medium transition-all', getTrueFalseClass(true)]">
          True
        </button>
        <button @click="!submitted && updateAnswer(false)"
                :class="['flex-1 p-4 rounded-lg border text-center font-medium transition-all', getTrueFalseClass(false)]">
          False
        </button>
      </div>

      <!-- Short / Number -->
      <div v-else-if="['short_answer', 'number'].includes(question.type)" class="space-y-2">
        <input type="text" :value="answer || ''" @input="!submitted && updateAnswer($event.target.value)"
               class="w-full p-4 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
               :disabled="submitted" placeholder="Type your answer..." />
      </div>

      <!-- Long Answer -->
      <div v-else-if="question.type === 'long_answer'" class="space-y-2">
        <textarea :value="answer || ''" @input="!submitted && updateAnswer($event.target.value)"
                  class="w-full p-4 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all min-h-[120px]"
                  :disabled="submitted" placeholder="Type your answer..."></textarea>
      </div>
    </div>

    <!-- Feedback / Actions -->
    <div class="mt-6 pt-4 border-t border-gray-100">
      <div v-if="!submitted">
        <button @click="submit" :disabled="!hasAnswer"
                class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
          Submit Answer
        </button>
      </div>
      <div v-else>
        <!-- Correct -->
        <div v-if="isAnswerCorrect" class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check text-green-600 text-xl"></i>
          </div>
          <div>
            <h4 class="font-bold text-green-800">Correct!</h4>
            <p class="text-sm text-green-700">Great job!</p>
          </div>
        </div>
        <!-- Incorrect with retries -->
        <div v-else>
          <div v-if="attempts < 3" class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-3">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
              </div>
              <div>
                <h4 class="font-bold text-orange-800">Incorrect</h4>
                <p class="text-sm text-orange-700">You have {{ 3 - attempts }} attempts remaining.</p>
              </div>
            </div>
            <button @click="retry"
                    class="mt-2 inline-flex items-center px-4 py-2 border border-orange-300 shadow-sm text-sm font-medium rounded-md text-orange-700 bg-white hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
              <i class="fas fa-redo mr-2"></i> Try Again
            </button>
          </div>
          <!-- Failed -->
          <div v-else class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-times text-red-600 text-xl"></i>
              </div>
              <div>
                <h4 class="font-bold text-red-800">Incorrect</h4>
                <p class="text-sm text-red-700">No attempts remaining.</p>
              </div>
            </div>
            <div class="text-sm text-gray-700 bg-white p-3 rounded border border-gray-200">
              <span class="font-bold text-gray-900">Correct Answer:</span>
              <span class="font-medium ml-1" v-html="correctAnswerText"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  question: { type: Object, required: true },
  answer: { type: [String, Number, Array, Boolean], default: null },
  attempts: { type: Number, default: 0 },
  solved: { type: Boolean, default: false },
  submitted: { type: Boolean, default: false },
  mode: { type: String, default: 'learn' }, // learn | practice | quiz
  juniorMode: { type: Boolean, default: false }
});

const emit = defineEmits(['update:answer', 'update:attempts', 'update:solved', 'update:submitted']);

// Timer
const timeLeft = ref(props.question.timer || 0);
let timerInterval = null;
onMounted(() => {
  if (props.question.timer > 0 && !props.solved) {
    timerInterval = setInterval(() => {
      if (timeLeft.value > 0) timeLeft.value--;
      else clearInterval(timerInterval);
    }, 1000);
  }
});
onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
});

// Answer handling
const updateAnswer = (val) => emit('update:answer', val);
const toggleOption = (id) => {
  const cur = Array.isArray(props.answer) ? [...props.answer] : [];
  const idx = cur.indexOf(id);
  if (idx === -1) cur.push(id);
  else cur.splice(idx, 1);
  emit('update:answer', cur);
};
const isSelected = (val) => {
  if (Array.isArray(props.answer)) return props.answer.includes(val);
  return props.answer === val;
};

const hasAnswer = computed(() => {
  return Array.isArray(props.answer) ? props.answer.length > 0 : props.answer !== null && props.answer !== undefined && props.answer !== '';
});

// Correctness check
const checkCorrectness = () => {
  const q = props.question;
  const a = props.answer;
  if (a === undefined || a === null) return false;
  if (q.type === 'single_choice') {
    const correct = q.options.find(o => o.is_correct);
    return correct && a === correct.id;
  }
  if (q.type === 'multiple_choice') {
    const correct = q.options.filter(o => o.is_correct).map(o => o.id);
    if (!Array.isArray(a) || a.length !== correct.length) return false;
    return a.every(v => correct.includes(v));
  }
  if (q.type === 'true_false') {
    return a === (q.correct_answer === true || q.correct_answer === 'true' || q.correct_answer === 1);
  }
  if (['short_answer', 'number'].includes(q.type)) {
    return String(a).trim().toLowerCase() === String(q.correct_answer).trim().toLowerCase();
  }
  return false;
};

const isAnswerCorrect = computed(() => checkCorrectness());
const correctAnswerText = computed(() => {
  const q = props.question;
  if (q.type === 'single_choice') return q.options.find(o => o.is_correct)?.text || '';
  if (q.type === 'multiple_choice') return q.options.filter(o => o.is_correct).map(o => o.text).join(', ');
  if (q.type === 'true_false') return (q.correct_answer === true || q.correct_answer === 'true' || q.correct_answer === 1) ? 'True' : 'False';
  if (['short_answer', 'number'].includes(q.type)) return q.correct_answer;
  return '';
});

// Actions
const submit = () => {
  const newAttempts = props.attempts + 1;
  emit('update:attempts', newAttempts);
  emit('update:submitted', true);
  if (checkCorrectness()) {
    emit('update:solved', true);
    if (timerInterval) clearInterval(timerInterval);
  } else if (newAttempts >= 3) {
    emit('update:solved', true);
    if (timerInterval) clearInterval(timerInterval);
  }
};
const retry = () => {
  emit('update:submitted', false);
};

// Styling helpers
const getOptionClass = (id) => {
  const selected = isSelected(id);
  if (!props.submitted) {
    return selected ? 'bg-blue-50 border-blue-500 ring-1 ring-blue-500' : 'bg-white border-gray-200 hover:border-blue-300';
  }
  const correct = props.question.options.find(o => o.id === id)?.is_correct;
  if (correct) return 'bg-green-50 border-green-500 ring-1 ring-green-500';
  if (selected && !correct) return 'bg-red-50 border-red-500 ring-1 ring-red-500';
  return 'bg-white border-gray-200 opacity-50';
};
const getTrueFalseClass = (val) => {
  const selected = isSelected(val);
  if (!props.submitted) {
    return selected ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:border-blue-300';
  }
  const correct = (props.question.correct_answer === true || props.question.correct_answer === 'true' || props.question.correct_answer === 1) === val;
  if (correct) return 'bg-green-600 text-white border-green-600';
  if (selected && !correct) return 'bg-red-600 text-white border-red-600';
  return 'bg-white text-gray-400 border-gray-200';
};
</script>

<style scoped>
/* Junior mode extra styling */
</style>
