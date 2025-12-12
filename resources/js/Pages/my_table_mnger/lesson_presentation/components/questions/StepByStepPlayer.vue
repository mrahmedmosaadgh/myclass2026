<template>
  <div class="step-by-step-player">
    <!-- Main Problem Statement -->
    <div class="text-h6 q-mb-md" v-html="question.text"></div>

    <!-- Steps Container -->
    <div class="q-gutter-y-md">
      <div 
        v-for="(step, index) in question.steps" 
        :key="index"
        class="step-card transition-generic"
        :class="{ 'disabled-step': index > currentStepIndex }"
      >
        <!-- Step Header/Status -->
        <div class="row items-center gap-2 q-mb-sm">
          <q-badge 
            :color="getStepColor(index)" 
            rounded 
            class="q-px-sm"
          >
            Step {{ index + 1 }}
          </q-badge>
          <q-icon 
            v-if="isStepCompleted(index)" 
            name="check_circle" 
            color="positive" 
            size="sm" 
          />
        </div>

        <!-- Step Content (Visible if current or completed) -->
        <div v-if="index <= currentStepIndex">
          <div class="q-mb-md" v-html="step.text"></div>

          <!-- Interaction Area -->
          <div v-if="!isStepCompleted(index)" class="q-pa-sm bg-blue-grey-1 rounded-borders">
            <!-- Short Answer Input -->
            <div v-if="step.type === 'short_answer'">
              <q-input
                v-model="currentAnswer"
                outlined
                dense
                bg-color="white"
                placeholder="Your answer..."
                @keyup.enter="submitStep(index)"
              >
                <template v-slot:append>
                  <q-btn round dense flat icon="send" color="primary" @click="submitStep(index)" />
                </template>
              </q-input>
            </div>

            <!-- Multiple Choice Input -->
            <div v-else-if="step.type === 'single_choice'">
              <div class="column q-gutter-y-xs">
                <q-btn
                  v-for="(option, oIdx) in step.options"
                  :key="oIdx"
                  :label="option.text"
                  :color="currentAnswer === option.text ? 'primary' : 'white'"
                  :text-color="currentAnswer === option.text ? 'white' : 'black'"
                  class="text-left"
                  no-caps
                  align="left"
                  @click="currentAnswer = option.text"
                />
                <q-btn 
                  v-if="currentAnswer"
                  label="Submit" 
                  color="primary" 
                  class="q-mt-sm self-end" 
                  size="sm"
                  @click="submitStep(index)"
                />
              </div>
            </div>

            <!-- Feedback Message -->
            <div v-if="feedbackMessage" class="q-mt-sm text-caption text-negative bg-red-1 q-pa-xs rounded-borders">
              {{ feedbackMessage }}
            </div>
          </div>

          <!-- Completed View (Static Answer) -->
          <div v-else class="text-body2 text-grey-8 bg-green-1 q-pa-sm rounded-borders border-green">
            <strong>Answer:</strong> {{ getStepAnswer(index) }}
            <div v-if="step.feedback" class="text-caption text-grey-7 q-mt-xs border-top-green q-pt-xs">
              <q-icon name="info" size="xs" /> {{ step.feedback }}
            </div>
          </div>
        </div>
        
        <!-- Locked State -->
        <div v-else class="text-grey-5 italic q-pa-sm">
          <q-icon name="lock" size="xs" /> Complete previous step to unlock.
        </div>
      </div>
    </div>

    <!-- Final Completion Message -->
    <div v-if="currentStepIndex >= question.steps.length" class="q-mt-lg text-center q-pa-md bg-blue-1 rounded-borders">
      <q-icon name="emoji_events" size="lg" color="warning" />
      <div class="text-h6 text-primary q-mt-sm">Problem Solved!</div>
      <div class="text-body2">You have successfully completed all steps.</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  question: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['complete']);

const currentStepIndex = ref(0);
const currentAnswer = ref('');
const feedbackMessage = ref('');
const stepAnswers = ref({}); // Store answers for completed steps

const isStepCompleted = (index) => {
  return index < currentStepIndex.value;
};

const getStepColor = (index) => {
  if (isStepCompleted(index)) return 'positive';
  if (index === currentStepIndex.value) return 'primary';
  return 'grey';
};

const getStepAnswer = (index) => {
  return stepAnswers.value[index];
};

const submitStep = (index) => {
  const step = props.question.steps[index];
  const userAnswer = currentAnswer.value.trim();
  const correctAnswer = step.correct_answer.trim();

  if (!userAnswer) return;

  // Simple case-insensitive comparison
  if (userAnswer.toLowerCase() === correctAnswer.toLowerCase()) {
    // Correct
    stepAnswers.value[index] = userAnswer;
    currentStepIndex.value++;
    currentAnswer.value = '';
    feedbackMessage.value = '';
    
    // Check if all steps done
    if (currentStepIndex.value >= props.question.steps.length) {
      emit('complete');
    }
  } else {
    // Incorrect
    feedbackMessage.value = 'Incorrect, please try again.';
  }
};
</script>

<style scoped>
.step-card {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 16px;
  background: white;
}
.disabled-step {
  opacity: 0.6;
  background: #f9f9f9;
}
.border-green {
  border: 1px solid #c8e6c9;
}
.border-top-green {
  border-top: 1px solid #c8e6c9;
}
</style>
