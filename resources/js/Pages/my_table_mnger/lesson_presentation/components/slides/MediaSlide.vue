<template>
  <div class="q-gutter-md">
    <q-input v-model="content.url" label="Media URL" />
    <div class="text-caption text-grey-7">Supported: image, video, audio, pdf</div>
    <div class="q-mt-md">
      <img v-if="type === 'image' && content.url" :src="content.url" class="max-h-[300px] object-contain" />
      <video v-else-if="type === 'video' && content.url" :src="content.url" controls class="w-full max-h-[300px]"></video>
      <audio v-else-if="type === 'audio' && content.url" :src="content.url" controls class="w-full"></audio>
      <div v-else-if="type === 'pdf' && content.url">
        <a :href="content.url" target="_blank" class="text-blue-600 underline">Open PDF</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, computed } from 'vue';

const props = defineProps({
  modelValue: { type: Object, default: () => ({ url: '' }) }
});
const emit = defineEmits(['update:modelValue']);

const content = reactive({ url: props.modelValue?.url || '' });
const type = computed(() => props.modelValue?.type || 'image');

watch(() => props.modelValue, (v) => {
  content.url = v?.url || '';
});

watch(content, () => {
  emit('update:modelValue', { ...content, type: type.value });
}, { deep: true });
</script>

<style scoped>
</style>
