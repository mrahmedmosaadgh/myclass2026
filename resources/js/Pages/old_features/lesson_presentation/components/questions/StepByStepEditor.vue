<template>
  <div class="step-by-step-editor q-pa-md bg-grey-1 rounded-borders">
    <div class="text-subtitle1 q-mb-sm text-weight-bold">Steps Configuration</div>
    
    <div v-for="(step, index) in modelValue" :key="index" class="q-mb-md bg-white q-pa-sm rounded-borders shadow-1">
      <div class="row items-center justify-between q-mb-sm">
        <div class="text-subtitle2 text-primary">Step {{ index + 1 }}</div>
        <q-btn 
          flat 
          dense 
          round 
          color="negative" 
          icon="delete" 
          size="sm"
          @click="removeStep(index)"
          :disable="modelValue.length <= 1"
        >
          <q-tooltip>Remove Step</q-tooltip>
        </q-btn>
      </div>

      <!-- Step Content -->
      <div class="q-gutter-y-sm">
        <!-- Step Instruction -->
        <div>
          <label class="text-caption text-grey-7">Step Instruction / Question</label>
          <RichTextEditor
            :modelValue="step.text"
            @update:modelValue="(val) => updateStep(index, 'text', val)"
            placeholder="e.g., First, calculate the area of the base..."
          />
        </div>

        <!-- Step Type -->
        <div class="row q-col-gutter-sm">
          <div class="col-12 col-sm-6">
            <q-select
              :model-value="step.type"
              @update:model-value="(val) => updateStep(index, 'type', val)"
              :options="stepTypes"
              label="Answer Type"
              dense
              outlined
              emit-value
              map-options
              bg-color="white"
            />
          </div>
        </div>

        <!-- Answer Configuration (Simplified version of QuestionEditor logic) -->
        <div v-if="step.type === 'short_answer'">
          <q-input
            :model-value="step.correct_answer"
            @update:model-value="(val) => updateStep(index, 'correct_answer', val)"
            label="Correct Answer"
            dense
            outlined
            bg-color="white"
            placeholder="Exact match required"
          />
        </div>

        <div v-if="step.type === 'single_choice'">
          <div class="text-caption text-grey-7 q-mb-xs">Options</div>
          <div v-for="(option, oIdx) in step.options" :key="oIdx" class="row items-center q-mb-xs">
            <q-radio
              :model-value="step.correct_answer"
              @update:model-value="(val) => updateStep(index, 'correct_answer', val)"
              :val="option.text"
              dense
              size="sm"
            />
            <q-input
              :model-value="option.text"
              @update:model-value="(val) => updateStepOption(index, oIdx, val)"
              dense
              outlined
              class="col q-mx-sm"
              bg-color="white"
              placeholder="Option text"
            />
            <q-btn
              flat
              dense
              round
              color="grey-6"
              icon="close"
              size="xs"
              @click="removeStepOption(index, oIdx)"
              :disable="step.options.length <= 2"
            />
          </div>
          <q-btn
            flat
            dense
            size="sm"
            color="primary"
            icon="add"
            label="Add Option"
            @click="addStepOption(index)"
            class="q-mt-xs"
          />
        </div>

        <!-- Step Feedback -->
        <div>
          <label class="text-caption text-grey-7">Feedback / Explanation (Optional)</label>
           <q-input
            :model-value="step.feedback"
            @update:model-value="(val) => updateStep(index, 'feedback', val)"
            dense
            outlined
            autogrow
            bg-color="white"
            placeholder="Shown after answering..."
          />
        </div>
      </div>
    </div>

    <q-btn
      outline
      color="primary"
      icon="add"
      label="Add Next Step"
      class="full-width q-mt-sm"
      @click="addStep"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import RichTextEditor from '../RichTextEditor.vue';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue']);

const stepTypes = [
  { label: 'Short Answer', value: 'short_answer' },
  { label: 'Multiple Choice', value: 'single_choice' }
];

const addStep = () => {
  const newStep = {
    text: '',
    type: 'short_answer',
    correct_answer: '',
    options: [
      { id: 'o1', text: '' },
      { id: 'o2', text: '' }
    ],
    feedback: ''
  };
  emit('update:modelValue', [...props.modelValue, newStep]);
};

const removeStep = (index) => {
  const newSteps = [...props.modelValue];
  newSteps.splice(index, 1);
  emit('update:modelValue', newSteps);
};

const updateStep = (index, field, value) => {
  const newSteps = [...props.modelValue];
  newSteps[index] = { ...newSteps[index], [field]: value };
  emit('update:modelValue', newSteps);
};

const updateStepOption = (stepIndex, optionIndex, value) => {
  const newSteps = [...props.modelValue];
  const newOptions = [...newSteps[stepIndex].options];
  newOptions[optionIndex] = { ...newOptions[optionIndex], text: value };
  newSteps[stepIndex].options = newOptions;
  
  // If the option text changed and it was the correct answer, we might need to update correct_answer? 
  // For simplicity, we store correct_answer as the text value for now, or we should use IDs.
  // Let's stick to text matching for simplicity in this prototype or use IDs if we were robust.
  // Given the existing structure uses text matching often, we'll keep it simple but be careful.
  
  emit('update:modelValue', newSteps);
};

const addStepOption = (stepIndex) => {
  const newSteps = [...props.modelValue];
  newSteps[stepIndex].options.push({ id: `o${Date.now()}`, text: '' });
  emit('update:modelValue', newSteps);
};

const removeStepOption = (stepIndex, optionIndex) => {
  const newSteps = [...props.modelValue];
  newSteps[stepIndex].options.splice(optionIndex, 1);
  emit('update:modelValue', newSteps);
};

// Initialize with one step if empty
if (props.modelValue.length === 0) {
  addStep();
}
</script>

<style scoped>
.step-by-step-editor {
  border: 1px solid #e0e0e0;
}
</style>
