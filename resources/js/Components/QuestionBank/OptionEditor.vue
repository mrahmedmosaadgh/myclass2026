<template>
  <div class="option-editor">
    <div class="text-subtitle2 q-mb-md">Answer Options</div>
    
    <!-- Options List -->
    <draggable
      v-model="localOptions"
      item-key="id"
      handle=".drag-handle"
      @end="onReorder"
    >
      <template #item="{ element, index }">
        <q-card flat bordered class="option-item q-mb-sm">
          <q-card-section class="q-pa-sm">
            <div class="row items-center q-gutter-sm">
              <!-- Drag Handle -->
              <q-icon
                name="drag_indicator"
                class="drag-handle cursor-pointer text-grey-6"
                size="20px"
              />
              
              <!-- Option Key -->
              <div class="option-key">{{ element.option_key }}</div>
              
              <!-- Option Text Input -->
              <q-input
                v-model="element.option_text"
                outlined
                dense
                placeholder="Enter option text"
                class="col"
                :rules="[val => !!val || 'Option text is required']"
              />
              
              <!-- Correct Answer Checkbox/Radio -->
              <q-checkbox
                v-if="allowMultiple"
                v-model="element.is_correct"
                color="positive"
                @update:model-value="validateCorrectAnswers"
              >
                <q-tooltip>Mark as correct answer</q-tooltip>
              </q-checkbox>
              
              <q-radio
                v-else
                :model-value="getCorrectOptionId()"
                :val="element.id"
                color="positive"
                @update:model-value="setCorrectOption(element.id)"
              >
                <q-tooltip>Mark as correct answer</q-tooltip>
              </q-radio>
              
              <!-- Remove Button -->
              <q-btn
                flat
                round
                dense
                icon="close"
                size="sm"
                color="negative"
                @click="removeOption(index)"
                :disable="localOptions.length <= minOptions"
              >
                <q-tooltip>Remove option</q-tooltip>
              </q-btn>
            </div>
          </q-card-section>
        </q-card>
      </template>
    </draggable>
    
    <!-- Add Option Button -->
    <q-btn
      flat
      icon="add"
      label="Add Option"
      color="primary"
      size="sm"
      @click="addOption"
      :disable="localOptions.length >= maxOptions"
      class="q-mt-sm"
    />
    
    <!-- Validation Messages -->
    <div v-if="validationError" class="text-negative text-caption q-mt-sm">
      <q-icon name="warning" size="16px" class="q-mr-xs" />
      {{ validationError }}
    </div>
    
    <div v-if="!hasCorrectAnswer" class="text-warning text-caption q-mt-sm">
      <q-icon name="info" size="16px" class="q-mr-xs" />
      Please mark at least one option as correct
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  allowMultiple: {
    type: Boolean,
    default: false
  },
  minOptions: {
    type: Number,
    default: 2
  },
  maxOptions: {
    type: Number,
    default: 6
  }
});

const emit = defineEmits(['update:modelValue']);

// Local state
const localOptions = ref([...props.modelValue]);
const validationError = ref('');

// Option keys (A, B, C, D, E, F)
const optionKeys = ['A', 'B', 'C', 'D', 'E', 'F'];

// Computed
const hasCorrectAnswer = computed(() => {
  return localOptions.value.some(opt => opt.is_correct);
});

// Methods
const addOption = () => {
  if (localOptions.value.length >= props.maxOptions) return;
  
  const newOption = {
    id: Date.now(), // Temporary ID for new options
    option_key: optionKeys[localOptions.value.length],
    option_text: '',
    is_correct: false,
    order_index: localOptions.value.length
  };
  
  localOptions.value.push(newOption);
  emitUpdate();
};

const removeOption = (index) => {
  if (localOptions.value.length <= props.minOptions) return;
  
  localOptions.value.splice(index, 1);
  
  // Reassign option keys
  localOptions.value.forEach((opt, idx) => {
    opt.option_key = optionKeys[idx];
    opt.order_index = idx;
  });
  
  emitUpdate();
};

const onReorder = () => {
  // Update order indices after drag
  localOptions.value.forEach((opt, idx) => {
    opt.option_key = optionKeys[idx];
    opt.order_index = idx;
  });
  
  emitUpdate();
};

const getCorrectOptionId = () => {
  const correctOption = localOptions.value.find(opt => opt.is_correct);
  return correctOption ? correctOption.id : null;
};

const setCorrectOption = (optionId) => {
  // For single-select, only one option can be correct
  localOptions.value.forEach(opt => {
    opt.is_correct = opt.id === optionId;
  });
  
  emitUpdate();
};

const validateCorrectAnswers = () => {
  validationError.value = '';
  
  if (!hasCorrectAnswer.value) {
    validationError.value = 'At least one option must be marked as correct';
  }
  
  emitUpdate();
};

const emitUpdate = () => {
  emit('update:modelValue', [...localOptions.value]);
};

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  localOptions.value = [...newValue];
}, { deep: true });

// Initialize with default options if empty
if (localOptions.value.length === 0) {
  for (let i = 0; i < props.minOptions; i++) {
    addOption();
  }
}
</script>

<style scoped lang="scss">
.option-editor {
  .option-item {
    transition: all 0.2s ease;
    
    &:hover {
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
  }
  
  .option-key {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
  }
  
  .drag-handle {
    cursor: grab;
    
    &:active {
      cursor: grabbing;
    }
  }
}
</style>
