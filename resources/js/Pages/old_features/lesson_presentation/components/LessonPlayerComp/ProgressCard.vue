<template>
  <q-card flat bordered class="progress-card">
    <q-card-section class="text-center">
      <q-circular-progress
        show-value
        :value="progress"
        size="80px"
        :thickness="0.15"
        color="primary"
        track-color="grey-3"
        class="q-mb-sm"
      >
        <div class="text-h6 text-weight-bold">{{ Math.round(progress) }}%</div>
      </q-circular-progress>
      <div class="text-caption text-grey-7 q-mt-sm">Overall Progress</div>
    </q-card-section>
  </q-card>
</template>

<script setup>
defineProps({
  progress: {
    type: Number,
    default: 0
  }
});
</script>

<style scoped lang="scss">
.progress-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none !important;
}
</style>
