<template>
  <div class="h-full flex flex-col">
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Media URL</label>
      <input
        type="text"
        :value="modelValue.url || ''"
        @input="updateUrl"
        class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
        placeholder="https://example.com/image.jpg"
      />
    </div>

    <div class="flex-1 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden relative">
      <div v-if="!modelValue.url" class="text-center text-gray-500">
        <i class="fas fa-image text-4xl mb-2"></i>
        <p>Enter a URL to preview media</p>
      </div>
      
      <img 
        v-else-if="isImage" 
        :src="modelValue.url" 
        class="max-w-full max-h-full object-contain" 
        alt="Slide Media"
      />
      
      <video 
        v-else-if="isVideo" 
        :src="modelValue.url" 
        controls 
        class="max-w-full max-h-full"
      ></video>
      
      <div v-else class="text-center">
        <i class="fas fa-file-alt text-4xl mb-2 text-blue-500"></i>
        <p class="text-blue-600 underline truncate max-w-xs">{{ modelValue.url }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:modelValue']);

const updateUrl = (e) => {
  emit('update:modelValue', { ...props.modelValue, url: e.target.value });
};

const isImage = computed(() => {
  const url = props.modelValue.url?.toLowerCase();
  return url && (url.endsWith('.jpg') || url.endsWith('.jpeg') || url.endsWith('.png') || url.endsWith('.gif') || url.endsWith('.webp'));
});

const isVideo = computed(() => {
  const url = props.modelValue.url?.toLowerCase();
  return url && (url.endsWith('.mp4') || url.endsWith('.webm') || url.endsWith('.ogg'));
});
</script>
