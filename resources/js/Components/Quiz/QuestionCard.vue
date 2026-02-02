<template>
  <q-card class="question-card" :class="{ 'question-card--dragging': isDragging }">
    <q-card-section class="question-card__content">
      <!-- Drag Handle -->
      <div v-if="draggable" class="question-card__drag-handle">
        <q-icon name="drag_indicator" size="20px" color="grey-6" />
      </div>
      
      <!-- Question Type Icon -->
      <div class="question-card__type-icon" :style="{ background: typeGradient }">
        <q-icon :name="typeIcon" size="24px" color="white" />
      </div>
      
      <!-- Question Content -->
      <div class="question-card__info">
        <div class="question-card__header">
          <span class="question-card__number">Q{{ question.order_index || question.id }}</span>
          <q-badge :color="difficultyColor" :label="question.difficulty || 'Medium'" />
          
          <!-- Points Display/Edit -->
          <div v-if="showPoints" class="question-card__points">
            <q-input
              :model-value="question.points || getDefaultPoints(question.difficulty)"
              outlined
              dense
              type="number"
              min="1"
              max="20"
              style="width: 60px"
              @update:model-value="updatePoints"
              @click.stop
            >
              <template v-slot:after>
                <span class="text-caption text-grey-6">pts</span>
              </template>
            </q-input>
            
            <!-- Default vs Custom Points Indicator -->
            <q-icon
              v-if="hasCustomPoints"
              name="edit"
              size="12px"
              color="orange"
              class="q-ml-xs"
            >
              <q-tooltip>Custom points (default: {{ getDefaultPoints(question.difficulty) }})</q-tooltip>
            </q-icon>
            
            <q-icon
              v-else
              name="auto_mode"
              size="12px"
              color="grey-5"
              class="q-ml-xs"
            >
              <q-tooltip>Default points</q-tooltip>
            </q-icon>
          </div>
        </div>
        
        <div class="question-card__text" v-html="truncateHtml(question.question_text, 100)" />
        
        <div class="question-card__meta">
          <span v-if="question.topic">
            <q-icon name="label" size="14px" />
            {{ question.topic.name }}
          </span>
          <span v-if="question.bloom_level">
            <q-icon name="psychology" size="14px" />
            {{ question.bloom_level }}
          </span>
          
          <!-- Points display in meta for compact view -->
          <span v-if="showPoints && !showPointsInput" class="question-card__points-meta">
            <q-icon name="star" size="14px" />
            {{ question.points || getDefaultPoints(question.difficulty) }} pts
            <q-icon
              v-if="hasCustomPoints"
              name="edit"
              size="10px"
              color="orange"
              class="q-ml-xs"
            />
          </span>
        </div>
      </div>
      
      <!-- Actions -->
      <div class="question-card__actions" @click.stop>
        <!-- Points Reset Button -->
        <q-btn
          v-if="showPoints && hasCustomPoints"
          flat
          round
          dense
          icon="refresh"
          size="sm"
          color="grey-6"
          @click="resetToDefaultPoints"
        >
          <q-tooltip>Reset to default points</q-tooltip>
        </q-btn>
        
        <q-btn
          v-if="showPreview"
          flat
          round
          dense
          icon="visibility"
          size="sm"
          @click="$emit('preview', question)"
        >
          <q-tooltip>Preview</q-tooltip>
        </q-btn>
        
        <q-btn
          v-if="showRemove"
          flat
          round
          dense
          icon="close"
          size="sm"
          color="negative"
          @click="$emit('remove', question)"
        >
          <q-tooltip>Remove</q-tooltip>
        </q-btn>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useScoringStore } from '@/composables/useScoringStore';

const props = defineProps({
  question: {
    type: Object,
    required: true
  },
  draggable: {
    type: Boolean,
    default: false
  },
  showPreview: {
    type: Boolean,
    default: true
  },
  showRemove: {
    type: Boolean,
    default: false
  },
  showPoints: {
    type: Boolean,
    default: false
  },
  showPointsInput: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['preview', 'remove', 'points-updated']);

const { getDefaultPoints } = useScoringStore();

const isDragging = ref(false);

const typeIcon = computed(() => {
  const icons = {
    'multiple-choice': 'radio_button_checked',
    'true-false': 'check_circle',
    'fill-blank': 'edit',
    'matching': 'compare_arrows',
    'essay': 'description'
  };
  return icons[props.question.question_type?.slug] || 'help_outline';
});

const typeGradient = computed(() => {
  const gradients = {
    'multiple-choice': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    'true-false': 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
    'fill-blank': 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    'matching': 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
    'essay': 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
  };
  return gradients[props.question.question_type?.slug] || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
});

const difficultyColor = computed(() => {
  const colors = {
    'Easy': 'positive',
    'Medium': 'warning',
    'Hard': 'negative'
  };
  return colors[props.question.difficulty] || 'info';
});

const hasCustomPoints = computed(() => {
  const defaultPoints = getDefaultPoints(props.question.difficulty);
  return props.question.points && props.question.points !== defaultPoints;
});

const truncateHtml = (html, maxLength) => {
  if (!html) return '';
  const text = html.replace(/<[^>]*>/g, '');
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

const updatePoints = (value) => {
  const points = Number(value);
  if (points > 0) {
    emit('points-updated', props.question.id, points);
  }
};

const resetToDefaultPoints = () => {
  const defaultPoints = getDefaultPoints(props.question.difficulty);
  emit('points-updated', props.question.id, defaultPoints);
};
</script>

<style scoped lang="scss">
.question-card {
  border-radius: 12px;
  transition: all 0.2s ease;
  
  &:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  
  &--dragging {
    opacity: 0.5;
    transform: scale(0.95);
  }
  
  &__content {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
  }
  
  &__drag-handle {
    cursor: grab;
    
    &:active {
      cursor: grabbing;
    }
  }
  
  &__type-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  
  &__info {
    flex: 1;
    min-width: 0;
  }
  
  &__header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
    flex-wrap: wrap;
  }
  
  &__number {
    font-size: 0.75rem;
    font-weight: 600;
    color: #718096;
  }
  
  &__points {
    display: flex;
    align-items: center;
    margin-left: auto;
  }
  
  &__points-meta {
    display: flex;
    align-items: center;
    gap: 4px;
    font-weight: 500;
    color: #4a5568;
  }
  
  &__text {
    font-size: 0.875rem;
    color: #1a202c;
    margin-bottom: 8px;
    line-height: 1.4;
  }
  
  &__meta {
    display: flex;
    gap: 12px;
    font-size: 0.75rem;
    color: #a0aec0;
    flex-wrap: wrap;
    
    span {
      display: flex;
      align-items: center;
      gap: 4px;
    }
  }
  
  &__actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
  }
}
</style>
