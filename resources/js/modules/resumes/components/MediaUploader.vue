<template>
  <div>
    <q-uploader
      :accept="acceptedTypes.join(',')"
      :max-files="maxFiles"
      @added="onAdded"
      @removed="onRemoved"
      :auto-upload="false"
      :factory="factory"
      label="Upload Media"
      style="width:100%"
    />
    <div class="q-mt-sm row q-col-gutter-sm">
      <FilePreview v-for="(file, idx) in files" :key="idx" :file="file" @remove="remove(idx)" />
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import FilePreview from './FilePreview.vue';

const props = defineProps({
  acceptedTypes: { type: Array, default: () => ['*/*'] },
  maxFiles: { type: Number, default: 3 }
});
const emit = defineEmits(['uploaded']);
const files = ref([]);

function onAdded({ files: newFiles }) {
  files.value = [...files.value, ...newFiles];
  emit('uploaded', files.value);
}
function onRemoved({ files: removedFiles }) {
  files.value = files.value.filter(f => !removedFiles.includes(f));
  emit('uploaded', files.value);
}
function remove(idx) {
  files.value.splice(idx, 1);
  emit('uploaded', files.value);
}
function factory(files) {
  // Placeholder: just return files, real upload logic goes here
  return Promise.resolve(files);
}
</script>
