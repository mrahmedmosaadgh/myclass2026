<template>
  <div class="question-input-container bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
    <!-- Header -->
    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-teal-50 to-white dark:from-gray-700 dark:to-gray-800">
      <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
        <span class="text-2xl">✍️</span>
        <span>Submit Your Answer</span>
        <span v-if="isSubmitted" class="text-xs font-normal px-2 py-0.5 bg-green-100 text-green-700 rounded-full animate-pulse">
          ✓ Submitted
        </span>
      </h3>
    </div>

    <!-- Question Display -->
    <div class="p-6 bg-teal-50 dark:bg-teal-900/20">
      <p class="text-lg font-semibold text-gray-800 dark:text-white">
        {{ questionText }}
      </p>
    </div>

    <!-- Input Form -->
    <div class="p-6 space-y-4">
      <!-- Name Input -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Your Name
        </label>
        <input
          v-model="userName"
          type="text"
          placeholder="Enter your name..."
          :disabled="isSubmitted && !allowMultipleSubmissions"
          class="w-full px-4 py-3 rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-900 focus:ring-teal-500 focus:border-teal-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          @keyup.enter="focusAnswerInput"
        />
      </div>

      <!-- Rating Input -->
      <div class="flex flex-col items-center justify-center py-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 w-full text-left">
          Your Rating
        </label>
        
        <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-6 w-full flex justify-center border border-gray-200 dark:border-gray-700">
            <q-rating
                v-model="answerValue"
                :max="maxValue || 5"
                size="3.5em"
                color="teal"
                icon="star_border"
                icon-selected="star"
                icon-half="star_half"
                :readonly="isSubmitted && !allowMultipleSubmissions"
                no-dimming
            />
        </div>

        <div class="flex justify-between w-full mt-2 text-xs text-gray-400 px-1">
             <span>{{ minValue || 1 }} (Lowest)</span>
             <span>{{ maxValue || 5 }} (Highest)</span>
        </div>
      </div>

      <!-- Submit Button -->
      <button
        @click="submitAnswer"
        :disabled="!canSubmit"
        class="w-full px-6 py-4 bg-teal-600 text-white rounded-xl shadow-lg hover:bg-teal-700 transition-all active:scale-95 font-bold text-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-teal-600 disabled:active:scale-100"
      >
        <span v-if="!isSubmitted">Submit Answer</span>
        <span v-else-if="allowMultipleSubmissions">Submit Again</span>
        <span v-else>✓ Submitted</span>
      </button>

      <!-- Success Message -->
      <transition name="fade">
        <div v-if="showSuccessMessage" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 text-center">
          <p class="text-green-700 dark:text-green-400 font-semibold">
            ✓ Your answer has been submitted successfully!
          </p>
        </div>
      </transition>

      <!-- Error Message -->
      <transition name="fade">
        <div v-if="errorMessage" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 text-center">
          <p class="text-red-700 dark:text-red-400 font-semibold">
            {{ errorMessage }}
          </p>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  questionText: {
    type: String,
    required: true
  },
  questionId: {
    type: String,
    required: true
  },
  minValue: {
    type: Number,
    default: null
  },
  maxValue: {
    type: Number,
    default: 5
  },
  step: {
    type: Number,
    default: 1
  },
  allowMultipleSubmissions: {
    type: Boolean,
    default: false
  },
  defaultUserName: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['submit']);

// Reactive state
const userName = ref(props.defaultUserName);
const answerValue = ref(null);
const isSubmitted = ref(false);
const showSuccessMessage = ref(false);
const errorMessage = ref('');
const answerInput = ref(null);

// Computed
const canSubmit = computed(() => {
  if (!userName.value || !userName.value.trim()) return false;
  if (answerValue.value === null || answerValue.value === '') return false;
  if (isSubmitted.value && !props.allowMultipleSubmissions) return false;
  
  // Validate range
  if (props.minValue !== null && answerValue.value < props.minValue) return false;
  if (props.maxValue !== null && answerValue.value > props.maxValue) return false;
  
  return true;
});

// Methods
const focusAnswerInput = () => {
  if (answerInput.value) {
    answerInput.value.focus();
  }
};

const submitAnswer = () => {
  if (!canSubmit.value) {
    if (!userName.value || !userName.value.trim()) {
      errorMessage.value = 'Please enter your name';
    } else if (answerValue.value === null || answerValue.value === '') {
      errorMessage.value = 'Please enter an answer';
    } else if (props.minValue !== null && answerValue.value < props.minValue) {
      errorMessage.value = `Answer must be at least ${props.minValue}`;
    } else if (props.maxValue !== null && answerValue.value > props.maxValue) {
      errorMessage.value = `Answer must be at most ${props.maxValue}`;
    }
    
    setTimeout(() => {
      errorMessage.value = '';
    }, 3000);
    return;
  }

  // Clear any previous error
  errorMessage.value = '';

  // Emit the answer
  emit('submit', {
    questionId: props.questionId,
    senderName: userName.value.trim(),
    value: answerValue.value,
    timestamp: Date.now() / 1000
  });

  // Update UI state
  isSubmitted.value = true;
  showSuccessMessage.value = true;

  // Clear success message after 3 seconds
  setTimeout(() => {
    showSuccessMessage.value = false;
  }, 3000);

  // Reset form if multiple submissions allowed
  if (props.allowMultipleSubmissions) {
    setTimeout(() => {
      answerValue.value = null;
      isSubmitted.value = false;
    }, 1500);
  }
};

// Expose methods for parent component
defineExpose({
  reset: () => {
    userName.value = props.defaultUserName;
    answerValue.value = null;
    isSubmitted.value = false;
    showSuccessMessage.value = false;
    errorMessage.value = '';
  }
});
</script>

<style scoped>
/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Remove number input arrows */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
  appearance: textfield;
}
</style>
