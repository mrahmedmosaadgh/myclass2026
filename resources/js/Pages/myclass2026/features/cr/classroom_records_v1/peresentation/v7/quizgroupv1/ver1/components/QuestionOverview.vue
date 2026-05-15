<script setup>
defineProps({
  questions: {
    type: Array,
    required: true
  },
  currentIndex: {
    type: Number,
    required: true
  },
  getStatus: {
    type: Function,
    required: true
  }
})

const emit = defineEmits(['select'])

function statusColor(status) {
  if (status === 'graded') return 'positive'
  if (status === 'answered') return 'warning'
  return 'grey-6'
}
</script>

<template>
  <q-card flat bordered class="overview-card">
    <q-card-section>
      <div class="text-subtitle2 text-weight-bold q-mb-sm">All Questions</div>
      <div class="question-list">
        <button
          v-for="(question, index) in questions"
          :key="question.id"
          class="question-item"
          :class="{ active: index === currentIndex }"
          type="button"
          @click="emit('select', index)"
        >
          <span class="question-number">{{ index + 1 }}</span>
          <span class="question-title">{{ question.question }}</span>
          <q-badge :color="statusColor(getStatus(question))" :label="getStatus(question)" />
        </button>
      </div>
    </q-card-section>
  </q-card>
</template>

<style scoped>
.overview-card {
  border-radius: 16px;
}

.question-list {
  display: grid;
  gap: 8px;
}

.question-item {
  width: 100%;
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  align-items: center;
  gap: 8px;
  padding: 10px;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  background: #ffffff;
  color: #334155;
  text-align: left;
  cursor: pointer;
}

.question-item.active {
  border-color: #2563eb;
  background: #eff6ff;
}

.question-number {
  width: 24px;
  height: 24px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #e0f2fe;
  color: #0369a1;
  font-size: 12px;
  font-weight: 800;
}

.question-title {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-weight: 600;
}
</style>
