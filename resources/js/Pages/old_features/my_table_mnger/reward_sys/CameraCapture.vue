<template>
  <q-card style="min-width: 400px">
    <q-card-section>
      <div class="text-h6">Camera Capture</div>
      <div class="text-subtitle2">Camera functionality not implemented</div>
    </q-card-section>
    
    <q-card-actions align="right">
      <q-btn flat label="Cancel" color="primary" @click="$emit('cancel')" />
    </q-card-actions>
  </q-card>
</template>

<script setup>
// Placeholder component for camera capture functionality
defineEmits(['captured', 'cancel'])
</script>