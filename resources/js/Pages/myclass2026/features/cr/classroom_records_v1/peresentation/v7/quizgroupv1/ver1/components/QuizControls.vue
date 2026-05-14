<script setup>
defineProps({
  canGrade: {
    type: Boolean,
    default: false
  },
  isGraded: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['grade', 'reset'])
</script>

<template>
  <q-card flat bordered class="controls-card">
    <q-card-section class="controls-row">
      <div>
        <div class="text-subtitle2 text-weight-bold">Quiz Controls</div>
        <div class="text-caption text-grey-7">Grade answers or reset the sample state.</div>
      </div>

      <div class="actions">
        <q-btn color="primary" label="Grade" no-caps :disable="!canGrade || isGraded" @click="emit('grade')" />
        <q-btn color="grey-7" label="Reset" no-caps outline @click="emit('reset')" />
      </div>
    </q-card-section>
  </q-card>
</template>

<style scoped>
.controls-card {
  border-radius: 16px;
}

.controls-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.actions {
  display: flex;
  gap: 8px;
}
</style>
