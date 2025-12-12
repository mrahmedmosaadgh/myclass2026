<template>
  <div>
    <template v-if="mediaType === 'audio'">
      <audio :src="mediaUrl" controls />
    </template>
    <template v-else-if="mediaType === 'video'">
      <video :src="mediaUrl" controls width="200" />
    </template>
    <template v-else-if="mediaType === 'image'">
      <img :src="mediaUrl" alt="Comment media" class="max-w-xs" />
    </template>
    <template v-else-if="mediaUrl">
      <a :href="mediaUrl" target="_blank">Download file</a>
    </template>
  </div>
</template>

<script setup>
const props = defineProps({
  mediaType: String,
  mediaPath: String,
});

const mediaUrl = computed(() => {
  if (!props.mediaPath) return null;
  // Adjust if you use a different storage disk
  return `/storage/${props.mediaPath}`;
});
</script>
