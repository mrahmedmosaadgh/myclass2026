<template>
  <div class="question-display">
    <!-- Question Header -->
    <div class="row items-center q-mb-md">
      <div class="text-subtitle1 text-weight-bold">
        Question {{ index ? `#${index}` : '' }}
      </div>
      <q-space />
      <q-chip v-if="question.difficulty" :color="difficultyColor(question.difficulty)" text-color="white">
        {{ question.difficulty }}
      </q-chip>
      <q-chip v-if="question.bloom_level" color="purple-3" :icon="getBloomIcon(question.bloom_level)">
        {{ capitalizeFirst(question.bloom_level) }}
      </q-chip>
      <q-chip color="primary" text-color="white">
        {{ question.marks }} {{ question.marks === 1 ? 'Mark' : 'Marks' }}
      </q-chip>
    </div>

    <!-- Question Text -->
    <div class="q-mb-md">
      <div class="text-body1" v-html="question.question_text"></div>
    </div>

    <!-- MCQ Options -->
    <div v-if="question.question_type === 'mcq'" class="q-gutter-sm">
      <q-radio
        v-for="opt in formattedOptions"
        :key="opt.key"
        v-model="answer.selected_options"
        :val="opt.key"
        :label="`${opt.key}. ${opt.value}`"
        :disable="readonly"
        @update:model-value="updateAnswer"
      />
    </div>

    <!-- True/False Options -->
    <div v-else-if="question.question_type === 'true_false'" class="q-gutter-sm">
      <q-radio
        v-model="answer.selected_options[0]"
        val="true"
        label="True"
        :disable="readonly"
        @update:model-value="updateAnswer"
      />
      <q-radio
        v-model="answer.selected_options[0]"
        val="false"
        label="False"
        :disable="readonly"
        @update:model-value="updateAnswer"
      />
    </div>

    <!-- Short Answer -->
    <div v-else-if="question.question_type === 'short'">
      <q-input
        v-model="answer.answer_text"
        type="text"
        outlined
        label="Your Answer"
        :readonly="readonly"
        @update:model-value="updateAnswer"
      />
    </div>

    <!-- Long Answer -->
    <div v-else-if="question.question_type === 'long'">
      <q-input
        v-model="answer.answer_text"
        type="textarea"
        outlined
        rows="5"
        label="Your Answer"
        :readonly="readonly"
        @update:model-value="updateAnswer"
      />
    </div>

    <!-- Show Correct Answer (for review mode) -->
    <div v-if="showCorrectAnswer && question.correct_answer" class="q-mt-md">
      <q-banner class="bg-green-1 text-green-9">
        <template v-slot:avatar>
          <q-icon name="check_circle" color="green" />
        </template>
        <strong>Correct Answer:</strong>
        <span v-if="question.question_type === 'mcq' || question.question_type === 'true_false'">
          {{ Array.isArray(question.correct_answer) ? question.correct_answer.join(', ') : question.correct_answer }}
        </span>
        <span v-else>
          {{ question.correct_answer }}
        </span>
      </q-banner>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  question: {
    type: Object,
    required: true
  },
  index: {
    type: Number,
    default: null
  },
  modelValue: {
    type: Object,
    default: () => ({ selected_options: [], answer_text: '' })
  },
  readonly: {
    type: Boolean,
    default: true
  },
  showCorrectAnswer: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

const answer = ref({
  selected_options: props.modelValue?.selected_options || [],
  answer_text: props.modelValue?.answer_text || ''
});

const formattedOptions = computed(() => {
  const options = props.question.options;
  if (!options) return [];
  
  // If it's already a shuffled array of objects {key, value}
  if (Array.isArray(options)) {
    // Basic check if it has key/value structure
    if (options.length > 0 && typeof options[0] === 'object' && 'key' in options[0]) {
        return options;
    }
    // Fallback if just simple array of strings (indices as keys)
    return options.map((val, idx) => ({ key: idx, value: val }));
  }
  
  // Normal object format {A: 'Text', B: 'Text'}
  return Object.entries(options).map(([key, value]) => ({ key, value }));
});

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    answer.value = {
      selected_options: newVal.selected_options || [],
      answer_text: newVal.answer_text || ''
    };
  }
}, { deep: true });

const updateAnswer = () => {
  emit('update:modelValue', answer.value);
};

const capitalizeFirst = (str) => {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
};

const difficultyColor = (difficulty) => {
  const colors = {
    easy: 'green',
    medium: 'orange',
    hard: 'red'
  };
  return colors[difficulty] || 'grey';
};

const getBloomIcon = (level) => {
  const icons = {
    remember: 'psychology',
    understand: 'lightbulb',
    apply: 'build',
    analyze: 'analytics',
    evaluate: 'fact_check',
    create: 'auto_awesome'
  };
  return icons[level] || 'help';
};
</script>

<style scoped>
.question-display {
  padding: 16px;
}
</style>
