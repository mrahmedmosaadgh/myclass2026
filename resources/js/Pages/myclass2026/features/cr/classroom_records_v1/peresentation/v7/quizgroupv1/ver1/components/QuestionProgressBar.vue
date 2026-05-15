<script setup>
defineProps({
  total: {
    type: Number,
    required: true
  },
  answered: {
    type: Number,
    required: true
  },
  graded: {
    type: Number,
    required: true
  }
})
</script>

<template>
  <q-card flat bordered class="progress-card">
    <q-card-section>
      <div class="progress-header">
        <div>
          <div class="text-subtitle2 text-weight-bold">Quiz Progress</div>
          <div class="text-caption text-grey-7">{{ answered }} answered, {{ graded }} graded, {{ total }} total</div>
        </div>
        <q-badge color="primary" :label="`${answered}/${total}`" />
      </div>

      <q-linear-progress
        class="q-mt-md"
        rounded
        size="10px"
        color="primary"
        :value="total ? answered / total : 0"
      />
    </q-card-section>
  </q-card>
</template>

<style scoped>
.progress-card {
  border-radius: 16px;
}

.progress-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}
</style>
