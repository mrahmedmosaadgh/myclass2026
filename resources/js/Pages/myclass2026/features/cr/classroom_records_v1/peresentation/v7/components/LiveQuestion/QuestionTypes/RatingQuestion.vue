<template>
  <BaseQuestionType
    type="rating"
    :title="title"
    :instructions="instructions"
    :time-limit="timeLimit"
    :min-score="minScore"
    :max-score="maxScore"
    :show-answer-key="showAnswerKey"
  >
    <template #question-body>
      <div class="rating-question">
        <div v-if="isEditMode" class="edit-mode">
          <div class="form-group">
            <label>Rating Scale</label>
            <select v-model.number="questionData.maxRating" class="form-select">
              <option :value="3">1-3 Stars</option>
              <option :value="5">1-5 Stars</option>
              <option :value="7">1-7 Points</option>
              <option :value="10">1-10 Points</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Min Rating</label>
            <input 
              v-model.number="questionData.minRating" 
              type="number" 
              min="0"
              :max="questionData.maxRating - 1"
              class="form-input"
            />
          </div>
          
          <div class="form-group">
            <label>Rating Type</label>
            <select v-model="questionData.ratingType" class="form-select">
              <option value="stars">Stars</option>
              <option value="numbers">Numbers</option>
              <option value="hearts">Hearts</option>
              <option value="thumbs">Thumbs Up/Down</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Labels (optional)</label>
            <div class="labels-grid">
              <div v-for="i in questionData.maxRating" :key="i" class="label-input">
                <span class="label-number">{{ i }}</span>
                <input 
                  v-model="questionData.labels[i - 1]" 
                  type="text"
                  :placeholder="`Label for ${i}`"
                  class="form-input small"
                />
              </div>
            </div>
          </div>
        </div>
        
        <div v-else class="answer-mode">
          <div class="rating-container">
            <div 
              v-for="i in ratingRange" 
              :key="i"
              class="rating-item"
              :class="{ selected: selectedRating === i }"
              @click="selectRating(i)"
            >
              <component 
                :is="ratingIcon" 
                class="rating-icon"
                :class="getIconClass(i)"
              />
              <span v-if="questionData.labels[i - 1]" class="rating-label">
                {{ questionData.labels[i - 1] }}
              </span>
              <span v-else class="rating-number">{{ i }}</span>
            </div>
          </div>
          
          <div v-if="selectedRating !== null" class="selected-display">
            <span class="selected-text">You selected:</span>
            <div class="selected-value">
              <component :is="ratingIcon" class="selected-icon" />
              <span>{{ selectedRating }}</span>
              <span v-if="questionData.labels[selectedRating - 1]" class="selected-label">
                - {{ questionData.labels[selectedRating - 1] }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </template>
    
    <template #answer-key v-if="showAnswerKey">
      <div class="rating-info">
        <p><strong>Scale:</strong> {{ questionData.minRating }} - {{ questionData.maxRating }}</p>
        <p><strong>Type:</strong> {{ questionData.ratingType }}</p>
      </div>
    </template>
  </BaseQuestionType>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import BaseQuestionType from './BaseQuestionType.vue'

const props = defineProps({
  title: String,
  instructions: String,
  timeLimit: Number,
  minScore: Number,
  maxScore: Number,
  isEditMode: Boolean,
  showAnswerKey: Boolean,
  initialData: Object
})

const emit = defineEmits(['answer-change', 'data-change'])

const selectedRating = ref(null)

const questionData = ref({
  maxRating: 5,
  minRating: 1,
  ratingType: 'stars',
  labels: [],
  ...props.initialData
})

// Ensure labels array has correct length
while (questionData.value.labels.length < questionData.value.maxRating) {
  questionData.value.labels.push('')
}

// Watch for data changes and emit
watch(questionData, (newData) => {
  if (props.isEditMode) {
    emit('data-change', newData)
  }
}, { deep: true })

// Watch for maxRating changes to adjust labels array
watch(() => questionData.value.maxRating, (newMax) => {
  while (questionData.value.labels.length < newMax) {
    questionData.value.labels.push('')
  }
  while (questionData.value.labels.length > newMax) {
    questionData.value.labels.pop()
  }
})

const ratingRange = computed(() => {
  const range = []
  for (let i = questionData.value.minRating; i <= questionData.value.maxRating; i++) {
    range.push(i)
  }
  return range
})

const ratingIcon = computed(() => {
  const icons = {
    'stars': 'StarIcon',
    'numbers': 'NumberIcon',
    'hearts': 'HeartIcon',
    'thumbs': 'ThumbIcon'
  }
  return icons[questionData.value.ratingType] || 'StarIcon'
})

const getIconClass = (rating) => {
  if (selectedRating.value === null) return ''
  return rating <= selectedRating.value ? 'filled' : 'empty'
}

const selectRating = (rating) => {
  selectedRating.value = rating
  emit('answer-change', {
    type: 'rating',
    answer: rating,
    maxRating: questionData.value.maxRating,
    ratingType: questionData.value.ratingType
  })
}

// Icon components (simplified for this example)
const StarIcon = { template: '<span>⭐</span>' }
const NumberIcon = { template: '<span>🔢</span>' }
const HeartIcon = { template: '<span>❤️</span>' }
const ThumbIcon = { template: '<span>👍</span>' }
</script>

<style scoped>
.rating-question {
  width: 100%;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 4px;
  font-weight: 500;
  color: #374151;
  font-size: 14px;
}

.form-input, .form-select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.form-input.small {
  width: 120px;
}

.labels-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 8px;
}

.label-input {
  display: flex;
  align-items: center;
  gap: 8px;
}

.label-number {
  font-weight: 500;
  color: #6b7280;
  font-size: 12px;
  min-width: 20px;
}

.rating-container {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  justify-content: center;
  padding: 20px 0;
}

.rating-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  background: white;
  min-width: 80px;
}

.rating-item:hover {
  border-color: #d1d5db;
  transform: translateY(-2px);
}

.rating-item.selected {
  border-color: #f59e0b;
  background: #fffbeb;
}

.rating-icon {
  font-size: 24px;
  transition: all 0.2s;
}

.rating-icon.filled {
  color: #f59e0b;
  transform: scale(1.1);
}

.rating-icon.empty {
  color: #d1d5db;
}

.rating-number {
  font-weight: 600;
  color: #374151;
}

.rating-label {
  font-size: 12px;
  color: #6b7280;
  text-align: center;
}

.selected-display {
  margin-top: 20px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
  text-align: center;
}

.selected-text {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #374151;
}

.selected-value {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 18px;
  font-weight: 600;
  color: #f59e0b;
}

.selected-icon {
  font-size: 20px;
}

.selected-label {
  font-weight: 400;
  color: #6b7280;
  font-size: 14px;
}

.rating-info {
  padding: 8px;
  background: #fef3c7;
  border: 1px solid #fde68a;
  border-radius: 4px;
  color: #92400e;
}

.rating-info p {
  margin: 4px 0;
  font-size: 14px;
}
</style>
