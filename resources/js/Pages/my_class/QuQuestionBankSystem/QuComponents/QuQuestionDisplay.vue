<template>
  <div v-if="question" class="question-display">
    <!-- Question Header -->
    <div v-if="!hideHeader" class="row items-center q-mb-md">
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
      <div class="text-body1" v-html="formattedQuestionText"></div>
    </div>

    <!-- MCQ Options -->
    <div v-if="question.question_type === 'mcq'" class="q-gutter-sm">
      <div v-for="opt in formattedOptions" :key="opt.key" class="q-mb-sm">
        <q-radio
          v-model="answer.selected_options"
          :val="opt.key"
          :disable="readonly"
          @update:model-value="updateAnswer"
        >
          <div class="row items-center">
            <span class="q-mr-sm text-weight-bold">{{ opt.key }}.</span>
            <span v-html="opt.value"></span>
          </div>
        </q-radio>
      </div>
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
          <span v-html="renderMath(Array.isArray(question.correct_answer) ? question.correct_answer.join(', ') : question.correct_answer)"></span>
        </span>
        <span v-else>
          <span v-html="renderMath(question.correct_answer)"></span>
        </span>
      </q-banner>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { renderMath } from '@/Utils/katex';

const props = defineProps({
  question: {
    type: Object,
    default: null
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
  },
  hideHeader: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

const answer = ref({
  selected_options: props.modelValue?.selected_options || [],
  answer_text: props.modelValue?.answer_text || ''
});

const formattedQuestionText = computed(() => {
  if (!props.question) return '';
  return renderMath(props.question.question_text);
});

const formattedOptions = computed(() => {
  if (!props.question || !props.question.options) return [];
  
  const options = props.question.options;
  if (!options) return [];
  
  let processedOptions = [];

  // If it's already a shuffled array of objects {key, value}
  if (Array.isArray(options)) {
    // Basic check if it has key/value structure
    if (options.length > 0 && typeof options[0] === 'object' && 'key' in options[0]) {
        processedOptions = options;
    } else {
        // Fallback if just simple array of strings (indices as keys)
        processedOptions = options.map((val, idx) => ({ key: idx, value: val }));
    }
  } else {
    // Normal object format {A: 'Text', B: 'Text'}
    processedOptions = Object.entries(options).map(([key, value]) => ({ key, value }));
  }

  // Render math in option values
  return processedOptions.map(opt => ({
    ...opt,
    value: renderMath(opt.value)
  }));
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
