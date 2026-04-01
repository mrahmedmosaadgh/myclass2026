<template>
  <div class="question-type-selector">
    <div class="selector-header">
      <h3>Question Type</h3>
      <p>Choose the type of question you want to create</p>
    </div>
    
    <div class="type-grid">
      <div 
        v-for="questionType in questionTypes" 
        :key="questionType.id"
        class="type-card"
        :class="{ selected: selectedType === questionType.id }"
        @click="selectType(questionType.id)"
      >
        <div class="type-icon" :style="{ color: questionType.color }">
          {{ questionType.icon }}
        </div>
        <div class="type-info">
          <h4>{{ questionType.name }}</h4>
          <p>{{ questionType.description }}</p>
        </div>
        <div class="type-features">
          <span 
            v-for="feature in questionType.features" 
            :key="feature"
            class="feature-badge"
          >
            {{ feature }}
          </span>
        </div>
      </div>
    </div>
    
    <div v-if="selectedType" class="type-settings">
      <div class="settings-header">
        <h4>{{ getSelectedTypeConfig().name }} Settings</h4>
      </div>
      
      <div class="settings-grid">
        <div class="form-group">
          <label>Time Limit (seconds)</label>
          <input 
            v-model.number="settings.timeLimit" 
            type="number" 
            min="0"
            class="form-input"
            placeholder="No limit"
          />
        </div>
        
        <div class="form-group">
          <label>Minimum Score</label>
          <input 
            v-model.number="settings.minScore" 
            type="number" 
            min="0"
            class="form-input"
            placeholder="0"
          />
        </div>
        
        <div class="form-group">
          <label>Maximum Score</label>
          <input 
            v-model.number="settings.maxScore" 
            type="number" 
            min="1"
            class="form-input"
            placeholder="100"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  initialType: String,
  initialSettings: Object
})

const emit = defineEmits(['type-change', 'settings-change'])

const selectedType = ref(props.initialType || 'text')

const settings = ref({
  timeLimit: props.initialSettings?.timeLimit || null,
  minScore: props.initialSettings?.minScore || 0,
  maxScore: props.initialSettings?.maxScore || 100
})

const questionTypes = ref([
  {
    id: 'text',
    name: 'Text Answer',
    description: 'Students type their answer',
    icon: '📝',
    color: '#3b82f6',
    features: ['Open-ended', 'Character limits', 'Sample answers']
  },
  {
    id: 'multiple_choice',
    name: 'Multiple Choice',
    description: 'Choose one correct answer',
    icon: '🔘',
    color: '#10b981',
    features: ['Single answer', 'Auto-gradable', '2-6 options']
  },
  {
    id: 'multi_select',
    name: 'Multiple Selection',
    description: 'Select multiple correct answers',
    icon: '☑️',
    color: '#f59e0b',
    features: ['Multiple answers', 'Partial credit', '2-6 options']
  },
  {
    id: 'number',
    name: 'Number Answer',
    description: 'Numeric input only',
    icon: '🔢',
    color: '#8b5cf6',
    features: ['Numbers only', 'Range validation', 'Decimal support']
  },
  {
    id: 'rating',
    name: 'Rating Scale',
    description: 'Rate on a scale',
    icon: '⭐',
    color: '#ef4444',
    features: ['1-10 scale', 'Visual rating', 'Custom labels']
  },
  {
    id: 'true_false',
    name: 'True/False',
    description: 'Binary choice question',
    icon: '✅',
    color: '#6b7280',
    features: ['Binary choice', 'Quick answer', 'Auto-gradable']
  }
])

const getSelectedTypeConfig = computed(() => {
  return questionTypes.value.find(type => type.id === selectedType.value) || questionTypes.value[0]
})

const selectType = (typeId) => {
  selectedType.value = typeId
  emit('type-change', typeId)
}

// Watch for settings changes
watch(settings, (newSettings) => {
  emit('settings-change', newSettings)
}, { deep: true })

// Watch for type changes to reset settings if needed
watch(selectedType, (newType) => {
  // You can add type-specific default settings here
  if (newType === 'rating') {
    settings.value.maxScore = 10
  } else if (newType === 'true_false') {
    settings.value.maxScore = 1
    settings.value.minScore = 0
  }
})
</script>

<style scoped>
.question-type-selector {
  background: white;
  border-radius: 12px;
  padding: 24px;
  border: 1px solid #e5e7eb;
}

.selector-header {
  margin-bottom: 24px;
}

.selector-header h3 {
  margin: 0 0 8px;
  font-size: 20px;
  font-weight: 600;
  color: #111827;
}

.selector-header p {
  margin: 0;
  color: #6b7280;
  font-size: 14px;
}

.type-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.type-card {
  display: flex;
  flex-direction: column;
  padding: 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  background: white;
}

.type-card:hover {
  border-color: #d1d5db;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.type-card.selected {
  border-color: #3b82f6;
  background: #eff6ff;
}

.type-icon {
  font-size: 32px;
  margin-bottom: 12px;
}

.type-info h4 {
  margin: 0 0 4px;
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.type-info p {
  margin: 0 0 12px;
  color: #6b7280;
  font-size: 14px;
  line-height: 1.4;
}

.type-features {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.feature-badge {
  padding: 2px 6px;
  background: #f3f4f6;
  border-radius: 12px;
  font-size: 11px;
  color: #374151;
  font-weight: 500;
}

.type-settings {
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.settings-header h4 {
  margin: 0 0 16px;
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-group label {
  font-weight: 500;
  color: #374151;
  font-size: 14px;
}

.form-input {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>
