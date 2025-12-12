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
        </div>
      </div>
      
      <!-- Actions -->
      <div class="question-card__actions" @click.stop>
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
  }
});

defineEmits(['preview', 'remove']);

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

const truncateHtml = (html, maxLength) => {
  if (!html) return '';
  const text = html.replace(/<[^>]*>/g, '');
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
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
  }
  
  &__number {
    font-size: 0.75rem;
    font-weight: 600;
    color: #718096;
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
