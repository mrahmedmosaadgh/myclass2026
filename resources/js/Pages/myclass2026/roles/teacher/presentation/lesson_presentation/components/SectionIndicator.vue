<template>
  <div 
    class="flex items-center gap-2 px-3 py-1.5 rounded-md border"
    :class="colorClasses"
  >
    <span class="text-lg">{{ icon }}</span>
    <div class="flex-1">
      <div class="text-xs font-semibold" :class="titleColorClass">
        Section {{ number }}: {{ title }}
      </div>
      <div class="text-xs" :class="subtitleColorClass">
        {{ subtitle }}
      </div>
    </div>
    <slot name="input"></slot>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  number: {
    type: Number,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    required: true
  },
  icon: {
    type: String,
    required: true
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['amber', 'orange', 'blue', 'purple', 'indigo', 'green'].includes(value)
  }
});

const colorClasses = computed(() => {
  const colors = {
    amber: 'bg-amber-50 border-amber-200',
    orange: 'bg-orange-50 border-orange-200',
    blue: 'bg-blue-50 border-blue-200',
    purple: 'bg-purple-50 border-purple-200',
    indigo: 'bg-indigo-50 border-indigo-200',
    green: 'bg-green-50 border-green-200'
  };
  return colors[props.color] || colors.blue;
});

const titleColorClass = computed(() => {
  const colors = {
    amber: 'text-amber-700',
    orange: 'text-orange-700',
    blue: 'text-blue-700',
    purple: 'text-purple-700',
    indigo: 'text-indigo-700',
    green: 'text-green-700'
  };
  return colors[props.color] || colors.blue;
});

const subtitleColorClass = computed(() => {
  const colors = {
    amber: 'text-amber-600',
    orange: 'text-orange-600',
    blue: 'text-blue-600',
    purple: 'text-purple-600',
    indigo: 'text-indigo-600',
    green: 'text-green-600'
  };
  return colors[props.color] || colors.blue;
});
</script>
