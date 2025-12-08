<template>
  <div>
    <q-input v-if="question.type === 'text'" v-model="answer.text" label="Your Answer" dense filled />
    <q-editor v-if="question.type === 'textarea'" v-model="answer.text" label="Your Answer" min-height="5rem" />
    <q-select v-if="question.type === 'select'" v-model="answer.text" :options="question.options || []" label="Select" dense filled />
    <q-option-group v-if="question.type === 'multi'" v-model="answer.text" :options="question.options || []" type="checkbox" />
    <MediaUploader v-if="question.type === 'media'" :accepted-types="['audio/*','video/*','image/*']" :max-files="3" @uploaded="onMediaUploaded" />
    <div v-if="question.type === 'media' && answer.media.length">
      <FilePreview v-for="(file, idx) in answer.media" :key="idx" :file="file" @remove="removeMedia(idx)" />
    </div>
    <q-input v-if="question.type === 'media'" v-model="answer.externalLinks" label="External Media Links (comma separated)" dense filled />
  </div>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue';
import MediaUploader from './MediaUploader.vue';
import FilePreview from './FilePreview.vue';

const props = defineProps({
  question: Object,
  modelValue: Object
});
const emit = defineEmits(['update:modelValue']);

const answer = ref({ text: '', media: [], externalLinks: '' });

watch(() => props.modelValue, (val) => {
  if (val) answer.value = { ...val };
});
watch(answer, (val) => {
  emit('update:modelValue', val);
}, { deep: true });

function onMediaUploaded(files) {
  answer.value.media = files;
}
function removeMedia(idx) {
  answer.value.media.splice(idx, 1);
}
</script>
