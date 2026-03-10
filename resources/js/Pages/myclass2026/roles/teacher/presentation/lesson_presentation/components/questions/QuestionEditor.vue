<template>
  <div class="border rounded-lg p-4 bg-white shadow-sm">
    <div class="w-48">
      <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
      <select
        :value="modelValue.type || 'single_choice'"
        @change="update('type', $event.target.value)"
        class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
      >
        <option value="single_choice">Single Choice</option>
        <option value="multiple_choice">Multiple Choice</option>
        <option value="true_false">True / False</option>
        <option value="short_answer">Short Answer</option>
        <option value="long_answer">Long Answer</option>
        <option value="number">Number</option>
        <option value="step_by_step">Step-by-Step</option>
      </select>
    </div>

    <div class="flex justify-between items-start mb-4">
      <div class="flex-1 mr-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Question Text</label>
        <RichTextEditor
          :modelValue="modelValue.text || ''"
          @update:modelValue="update('text', $event)"
          class="w-full"
        />
      </div>
    </div>

    <!-- Options for Choice Types -->
    <div v-if="['single_choice', 'multiple_choice'].includes(modelValue.type)" class="mb-4">
      <div class="flex justify-between items-center mb-2">
        <label class="block text-sm font-medium text-gray-700">Options</label>
        
        <!-- Quick Add Buttons -->
        <div class="flex gap-2">
          <q-btn
            @click="quickAddOptions('ABCD')"
            outline
            dense
            size="sm"
            color="grey-7"
            label="[A, B, C, D]"
            no-caps
          />
          <q-btn
            @click="quickAddOptions('1234')"
            outline
            dense
            size="sm"
            color="grey-7"
            label="[1, 2, 3, 4]"
            no-caps
          />
          <q-btn
            @click="quickAddOptions('AR')"
            outline
            dense
            size="sm"
            color="grey-7"
            label="[أ, ب, ج, د]"
            no-caps
          />
        </div>
      </div>

      <div class="space-y-2">
        <div 
          v-for="(option, idx) in (modelValue.options || [])" 
          :key="option.id"
          class="flex items-start gap-2"
        >
          <input 
            :type="modelValue.type === 'single_choice' ? 'radio' : 'checkbox'"
            :name="'correct_' + uniqueId"
            :checked="isCorrect(option.id)"
            @change="toggleCorrect(option.id)"
            class="mt-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <div class="flex-1">
            <RichTextEditor
              :modelValue="option.text || ''"
              @update:modelValue="updateOptionText(idx, $event)"
            />
          </div>
          <q-btn
            @click="removeOption(idx)"
            flat
            dense
            round
            color="negative"
            icon="close"
            :disable="(modelValue.options || []).length <= 2"
          >
            <q-tooltip>Remove Option (Minimum 2 required)</q-tooltip>
          </q-btn>
        </div>
        <q-btn
          @click="addOption"
          flat
          dense
          color="primary"
          icon="add"
          label="Add Option"
          no-caps
          class="q-mt-sm"
        />
      </div>
    </div>

    <!-- True/False -->
    <div v-if="modelValue.type === 'true_false'" class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Correct Answer</label>
      <div class="flex gap-4">
        <label class="flex items-center">
          <input 
            type="radio" 
            :name="'tf_' + uniqueId"
            :checked="modelValue.correct_answer === true"
            @change="update('correct_answer', true)"
            class="mr-2"
          /> True
        </label>
        <label class="flex items-center">
          <input 
            type="radio" 
            :name="'tf_' + uniqueId"
            :checked="modelValue.correct_answer === false"
            @change="update('correct_answer', false)"
            class="mr-2"
          /> False
        </label>
      </div>
    </div>

    <!-- Short Answer / Number -->
    <div v-if="['short_answer', 'number'].includes(modelValue.type)" class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Correct Answer</label>
      <input
        type="text"
        :value="modelValue.correct_answer || ''"
        @input="update('correct_answer', $event.target.value)"
        class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
        placeholder="Enter the correct answer..."
      />
      <p class="text-xs text-gray-500 mt-1">Exact match required (case-insensitive).</p>
    </div>

    <!-- Step-by-Step -->
    <div v-if="modelValue.type === 'step_by_step'" class="mb-4">
      <StepByStepEditor
        :modelValue="modelValue.steps || []"
        @update:modelValue="update('steps', $event)"
      />
    </div>

    <!-- Feedback -->
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Feedback / Explanation (Optional)</label>
      <RichTextEditor
        :modelValue="modelValue.feedback || ''"
        @update:modelValue="update('feedback', $event)"
        class="w-full"
      />
    </div>

    <!-- Footer: Settings -->
    <div class="flex justify-between items-center pt-4 border-t border-gray-100 mt-4">
      <div class="flex items-center gap-4">
        <div class="flex items-center">
          <label class="text-sm text-gray-600 mr-2">Timer (sec):</label>
          <input
            type="number"
            :value="modelValue.timer || 0"
            @input="update('timer', parseInt($event.target.value))"
            class="w-20 p-1 border border-gray-300 rounded-md text-sm"
          />
        </div>
        <label class="flex items-center text-sm text-gray-600">
          <input
            type="checkbox"
            :checked="modelValue.active || false"
            @change="update('active', $event.target.checked)"
            class="mr-2 rounded text-blue-600"
          />
          Active for Students
        </label>
      </div>
      <q-btn
        @click="$emit('delete')"
        flat
        dense
        color="negative"
        icon="delete"
        label="Delete Question"
        no-caps
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import RichTextEditor from '../RichTextEditor.vue';
import StepByStepEditor from './StepByStepEditor.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  },
  uniqueId: {
    type: String,
    default: () => Math.random().toString(36).substr(2, 9)
  }
});

const emit = defineEmits(['update:modelValue', 'delete']);

const questionTypes = [
  { label: 'Multiple Choice', value: 'single_choice' },
  { label: 'Multiple Select', value: 'multiple_choice' },
  { label: 'Short Answer', value: 'short_answer' },
  { label: 'True/False', value: 'true_false' },
  { label: 'Number', value: 'number' },
  { label: 'Step-by-Step', value: 'step_by_step' }
];

const update = (field, value) => {
  emit('update:modelValue', { ...props.modelValue, [field]: value });
};

const updateOptionText = (index, text) => {
  const options = [...(props.modelValue.options || [])];
  options[index] = { ...options[index], text };
  update('options', options);
};

const isCorrect = (optionId) => {
  if (props.modelValue.type === 'single_choice') {
    return props.modelValue.correct_answer === optionId;
  }
  return (props.modelValue.correct_answer || []).includes(optionId);
};

const toggleCorrect = (optionId) => {
  if (props.modelValue.type === 'single_choice') {
    update('correct_answer', optionId);
  } else {
    const current = props.modelValue.correct_answer || [];
    const index = current.indexOf(optionId);
    if (index === -1) {
      update('correct_answer', [...current, optionId]);
    } else {
      update('correct_answer', current.filter(id => id !== optionId));
    }
  }
};

const addOption = () => {
  const options = props.modelValue.options || [];
  const newId = `opt_${Date.now()}`;
  update('options', [...options, { id: newId, text: '' }]);
};

const removeOption = (index) => {
  const options = [...(props.modelValue.options || [])];
  if (options.length <= 2) return;
  options.splice(index, 1);
  update('options', options);
};

// Quick Add Helpers
const quickAddOptions = (type) => {
  let labels = [];
  if (type === 'ABCD') labels = ['A', 'B', 'C', 'D'];
  else if (type === '1234') labels = ['1', '2', '3', '4'];
  else if (type === 'AR') labels = ['أ', 'ب', 'ج', 'د'];

  const newOptions = labels.map((label, idx) => ({
    id: `opt_${Date.now()}_${idx}`,
    text: label
  }));
  
  // Reset correct answer as options changed completely
  emit('update:modelValue', { 
    ...props.modelValue, 
    options: newOptions,
    correct_answer: props.modelValue.type === 'single_choice' ? null : []
  });
};
</script>
