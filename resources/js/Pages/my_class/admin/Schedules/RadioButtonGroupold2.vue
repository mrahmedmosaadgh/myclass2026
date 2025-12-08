<template>
  <div :class="my_class">
    <label v-for="option in filteredOptions" :key="option?.id || option?.value">
      <input
        type="radio"
        :name="name"
        :value="option"
        :checked="isSelected(option)"
        @change="handleChange(option)"
      >
      <div class="radio-button-content w-16 h-16">
        <NameAbbreviator2
          v-if="option?.cst?.subject"
          :name="option.cst.subject.name"
          :color_bg="option.cst.subject.color_bg"
          :color_text="option.cst.subject.color_text"
        />
        <span v-else>{{ option?.label || 'N/A' }}</span>
      </div>
    </label>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import NameAbbreviator2 from './NameAbbreviator2.vue';

const model = defineModel();

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  my_class: {
    type: String,
    default: ''
  },
  options: {
    type: Array,
    default: () => []
  },
  period_day: {
    type: Object,
    default: () => ({})
  }
});

// Filter out null/undefined options
const filteredOptions = computed(() => {
  return props.options.filter(option => option != null);
});

const isSelected = (option) => {
  if (!model.value || !option) return false;
  return model.value.id === option.id || model.value === option;
};

const handleChange = (option) => {
  if (option) {
    model.value = option;
  }
};
</script>

<style scoped>
.radio-button-content {
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s;
}

input[type="radio"] {
  display: none;
}

input[type="radio"]:checked + .radio-button-content {
  border-color: #4f46e5;
  background-color: #f8fafc;
}
</style>

