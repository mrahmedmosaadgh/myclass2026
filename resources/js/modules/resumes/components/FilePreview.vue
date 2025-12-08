<template>
  <div class="q-mb-sm">
    <q-img v-if="isImage" :src="fileUrl" style="max-width:120px;max-height:120px;" />
    <audio v-else-if="isAudio" :src="fileUrl" controls style="width:120px;" />
    <video v-else-if="isVideo" :src="fileUrl" controls style="max-width:120px;max-height:120px;" />
    <q-btn v-else flat :label="fileName" :href="fileUrl" target="_blank" icon="attach_file" />
    <q-btn dense flat icon="close" color="negative" @click="$emit('remove')" />
  </div>
</template>

<script setup>
import { computed, defineProps } from 'vue';
const props = defineProps({ file: [Object, String] });
const fileUrl = computed(() => typeof props.file === 'string' ? props.file : (props.file.url || ''));
const fileName = computed(() => typeof props.file === 'string' ? props.file.split('/').pop() : (props.file.name || 'File'));
const isImage = computed(() => /\.(jpg|jpeg|png|gif)$/i.test(fileUrl.value));
const isAudio = computed(() => /\.(mp3|wav)$/i.test(fileUrl.value));
const isVideo = computed(() => /\.(mp4|webm)$/i.test(fileUrl.value));
</script>
