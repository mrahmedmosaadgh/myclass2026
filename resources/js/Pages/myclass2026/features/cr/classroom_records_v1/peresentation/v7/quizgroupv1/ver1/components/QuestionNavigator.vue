<script setup>
defineProps({
  currentIndex: {
    type: Number,
    required: true
  },
  total: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['previous', 'next'])
</script>

<template>
  <q-card flat bordered class="navigator-card">
    <q-card-section class="navigator-row">
      <q-btn
        color="primary"
        label="Previous"
        no-caps
        outline
        :disable="currentIndex <= 0"
        @click="emit('previous')"
      />

      <div class="question-counter">
        Question {{ currentIndex + 1 }} of {{ total }}
      </div>

      <q-btn
        color="primary"
        label="Next"
        no-caps
        :disable="currentIndex >= total - 1"
        @click="emit('next')"
      />
    </q-card-section>
  </q-card>
</template>

<style scoped>
.navigator-card {
  border-radius: 16px;
}

.navigator-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.question-counter {
  color: #334155;
  font-weight: 700;
}
</style>
