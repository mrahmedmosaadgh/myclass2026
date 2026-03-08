<template>
  <div class="answer-input-container">
    <div v-if="type === 'text'" class="text-input">
      <input
        v-model="localValue"
        type="text"
        :placeholder="placeholder"
        :disabled="disabled"
        class="text-input-field"
      />
    </div>
    
    <div v-else-if="type === 'number'" class="number-input">
      <input
        v-model.number="localValue"
        type="number"
        :placeholder="placeholder"
        :disabled="disabled"
        class="number-input-field"
      />
    </div>
    
    <div v-else-if="type === 'multiple-choice'" class="multiple-choice-input">
      <div 
        v-for="(option, index) in options" 
        :key="index"
        class="choice-option"
        :class="{ 'selected': localValue === option.value }"
        @click="selectOption(option.value)"
      >
        <span class="choice-radio"></span>
        <span class="choice-label">{{ option.label }}</span>
      </div>
    </div>
    
    <!-- Default input for unknown types -->
    <div v-else class="default-input">
      <input
        v-model="localValue"
        :type="type"
        :placeholder="placeholder"
        :disabled="disabled"
        class="default-input-field"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'text',
    validator: (value) => ['text', 'number', 'multiple-choice'].includes(value)
  },
  modelValue: {
    type: [String, Number],
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  options: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue', 'submit']);

// Local value for v-model binding
const localValue = ref(props.modelValue);

// Sync local value with prop changes
watch(() => props.modelValue, (newVal) => {
  localValue.value = newVal;
});

// Emit changes when local value updates
watch(localValue, (newVal) => {
  emit('update:modelValue', newVal);
  
  // Emit submit event for auto-submit behavior
  if (newVal && typeof newVal === 'string' && newVal.trim() !== '') {
    emit('submit', newVal);
  } else if (typeof newVal === 'number') {
    emit('submit', newVal);
  }
});

const selectOption = (value) => {
  localValue.value = value;
  emit('submit', value);
};
</script>

<style scoped>
.answer-input-container {
  width: 100%;
}

.text-input-field,
.number-input-field,
.default-input-field {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 16px;
  outline: none;
  transition: border-color 0.2s;
}

.text-input-field:focus,
.number-input-field:focus,
.default-input-field:focus {
  border-color: #3b82f6;
}

.choice-option {
  display: flex;
  align-items: center;
  padding: 12px;
  margin-bottom: 8px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.choice-option:hover {
  background-color: #f9fafb;
  border-color: #d1d5db;
}

.choice-option.selected {
  background-color: #eff6ff;
  border-color: #3b82f6;
  color: #1d4ed8;
}

.choice-radio {
  width: 20px;
  height: 20px;
  border: 2px solid #d1d5db;
  border-radius: 50%;
  margin-right: 12px;
  display: inline-block;
}

.choice-option.selected .choice-radio {
  background-color: #3b82f6;
  border-color: #3b82f6;
}

.choice-label {
  font-size: 16px;
}
</style>