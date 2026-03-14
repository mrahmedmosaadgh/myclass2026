<template>
  <div class="element-toolbar flex flex-col items-center space-y-4">
    <button
      v-for="tool in tools"
      :key="tool.type"
      @click="$emit('add-element', tool.type)"
      class="w-12 h-12 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors border border-gray-300"
      :title="tool.label"
    >
      <div class="text-2xl">{{ tool.icon }}</div>
    </button>
  </div>
</template>

<script setup>
defineEmits(['add-element']);

const tools = [
  { type: 'text', label: 'Add Text', icon: 'T' },
  { type: 'image', label: 'Add Image', icon: '🖼️' },
  { type: 'rectangle', label: 'Add Rectangle', icon: '⬜' },
  { type: 'circle', label: 'Add Circle', icon: '⭕' },
  { type: 'arrow', label: 'Add Arrow', icon: '➡️' }
];
</script>