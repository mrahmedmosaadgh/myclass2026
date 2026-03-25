<template>
  <q-card class="hover-shadow transition-generic">
    <q-card-section>
      <div class="text-h6">{{ lesson.name }}</div>
      <div class="text-caption text-grey-6">{{ lesson.description }}</div>
    </q-card-section>
    <q-separator />
    <q-card-actions align="right" class="q-gutter-sm">
      <q-btn dense flat icon="link" label="Copy Link" @click="$emit('copy-link', lesson.id)" />
      <q-btn dense flat icon="lock_open" label="Open to All" @click="$emit('open-to-all', lesson)" />
      <q-btn dense flat icon="delete" color="negative" label="Delete" @click="$emit('delete', lesson)" />
    </q-card-actions>
  </q-card>
  </template>

<script setup>
defineProps({
  lesson: { type: Object, required: true }
});
</script>

<style scoped>
</style>
