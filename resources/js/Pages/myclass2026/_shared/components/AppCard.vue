<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: String,
  noPadding: {
    type: Boolean,
    default: false
  }
});
</script>

<template>
  <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div v-if="title || $slots.header" class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
      <h3 v-if="title" class="text-lg leading-6 font-medium text-gray-900">{{ title }}</h3>
      <slot name="header"></slot>
    </div>
    <div :class="{ 'p-6': !noPadding }">
      <slot></slot>
    </div>
    <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
      <slot name="footer"></slot>
    </div>
  </div>
</template>
