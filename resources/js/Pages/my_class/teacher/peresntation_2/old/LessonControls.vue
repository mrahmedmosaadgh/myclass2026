<template>
  <div class="lesson-controls q-pa-sm q-mt-sm" style="border:1px solid #eee; padding:8px">
    <button @click="$emit('save')">Save</button>
    <button @click="$emit('export')">Export .zip</button>
    <input ref="fileInput" type="file" @change="onFile" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
const emit = defineEmits(['save','export','imported']);
const fileInput = ref(null);

async function onFile(e) {
  const f = e.target.files[0];
  if (!f) return;
  // lazy-load importer to avoid bundling JSZip twice
  const { importLessonFromZip } = await import('./utils/fileManager');
  const result = await importLessonFromZip(f);
  emit('imported', result);
}
</script>

<style scoped>
</style>
