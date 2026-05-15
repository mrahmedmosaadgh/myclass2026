<script setup>
const props = defineProps({
  options: {
    type: Array,
    required: true
  },
  answersByGroup: {
    type: Object,
    required: true
  },
  selectedGroupId: {
    type: String,
    default: null
  },
  isGraded: {
    type: Boolean,
    default: false
  },
  correctOptionId: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['answer'])

function getGroupCount(optionId) {
  return Object.values(props.answersByGroup).filter((answer) => answer === optionId).length
}
</script>

<template>
  <div class="option-grid">
    <q-card
      v-for="option in options"
      :key="option.id"
      flat
      bordered
      class="option-card cursor-pointer"
      :class="{
        selected: selectedGroupId && answersByGroup[selectedGroupId] === option.id,
        correct: isGraded && option.id === correctOptionId,
        wrong: isGraded && selectedGroupId && answersByGroup[selectedGroupId] === option.id && option.id !== correctOptionId
      }"
      @click="emit('answer', option.id)"
    >
      <q-card-section>
        <div class="option-top">
          <q-badge color="primary" :label="option.id" />
          <q-badge v-if="getGroupCount(option.id)" color="secondary" :label="getGroupCount(option.id) + ' group(s)'" />
        </div>
        <div class="option-text">{{ option.text }}</div>
      </q-card-section>
    </q-card>
  </div>
</template>

<style scoped>
.option-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.option-card {
  border-radius: 16px;
  transition: border-color 0.15s ease, transform 0.15s ease;
}

.option-card:hover {
  transform: translateY(-1px);
}

.option-card.selected {
  border-color: #2563eb;
}

.option-card.correct {
  border-color: #16a34a;
  background: #f0fdf4;
}

.option-card.wrong {
  border-color: #dc2626;
  background: #fef2f2;
}

.option-top {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 10px;
}

.option-text {
  font-size: 16px;
  font-weight: 600;
}

@media (max-width: 640px) {
  .option-grid {
    grid-template-columns: 1fr;
  }
}
</style>
