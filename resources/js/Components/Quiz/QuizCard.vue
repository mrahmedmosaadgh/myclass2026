<template>
  <q-card
    class="quiz-card"
    :class="[`quiz-card--${status}`, { 'quiz-card--hover': !disableHover }]"
    @click="$emit('click')"
  >
    <!-- Gradient Background Overlay -->
    <div class="quiz-card__gradient" :style="{ background: gradientBackground }" />
    
    <!-- Card Content -->
    <q-card-section class="quiz-card__content">
      <!-- Header -->
      <div class="quiz-card__header">
        <div class="quiz-card__title-section">
          <h3 class="quiz-card__title">{{ quiz.name }}</h3>
          <p v-if="quiz.description" class="quiz-card__description">
            {{ truncateText(quiz.description, 80) }}
          </p>
        </div>
        
        <q-badge
          :color="statusColor"
          :label="quiz.status"
          class="quiz-card__status-badge q-py-xs q-px-sm"
          rounded
        />
      </div>

      <!-- Stats -->
      <div class="quiz-card__stats">
        <div class="quiz-card__stat">
          <q-icon name="quiz" size="20px" />
          <span>{{ quiz.questions_count || 0 }} Questions</span>
        </div>
        
        <div v-if="quiz.time_limit_minutes" class="quiz-card__stat">
          <q-icon name="schedule" size="20px" />
          <span>{{ quiz.time_limit_minutes }} min</span>
        </div>
        
        <div v-if="quiz.grade" class="quiz-card__stat">
          <q-icon name="school" size="20px" />
          <span>{{ quiz.grade.name }}</span>
        </div>
        
        <div v-if="quiz.subject" class="quiz-card__stat">
          <q-icon name="book" size="20px" />
          <span>{{ quiz.subject.name }}</span>
        </div>
      </div>

      <!-- Footer with Actions -->
      <div class="quiz-card__footer">
        <div class="quiz-card__meta">
          <q-avatar size="24px" color="primary" text-color="white" class="q-mr-sm">
            {{ (quiz.created_by?.name || 'U').charAt(0).toUpperCase() }}
          </q-avatar>
          <span class="text-caption text-weight-medium">{{ quiz.created_by?.name || 'Unknown' }}</span>
        </div>
        
        <div class="quiz-card__actions" @click.stop>
          <q-btn
            flat
            round
            dense
            icon="visibility"
            size="sm"
            color="primary"
            class="q-mr-xs"
            @click="$emit('preview', quiz)"
          >
            <q-tooltip>Preview</q-tooltip>
          </q-btn>
          
          <q-btn
            flat
            round
            dense
            icon="edit"
            size="sm"
            color="secondary"
            class="q-mr-xs"
            @click="$emit('edit', quiz)"
          >
            <q-tooltip>Edit</q-tooltip>
          </q-btn>
          
          <q-btn
            flat
            round
            dense
            icon="bar_chart"
            size="sm"
            color="info"
            class="q-mr-xs"
            @click="$emit('analytics', quiz)"
          >
            <q-tooltip>Analytics</q-tooltip>
          </q-btn>
          
          <q-btn
            flat
            round
            dense
            icon="more_vert"
            size="sm"
            color="grey-7"
          >
            <q-tooltip>More</q-tooltip>
            <q-menu>
              <q-list style="min-width: 150px">
                <q-item clickable v-close-popup @click="$emit('duplicate', quiz)">
                  <q-item-section avatar>
                    <q-icon name="content_copy" />
                  </q-item-section>
                  <q-item-section>Duplicate</q-item-section>
                </q-item>
                
                <q-item clickable v-close-popup @click="$emit('export', quiz)">
                  <q-item-section avatar>
                    <q-icon name="download" />
                  </q-item-section>
                  <q-item-section>Export</q-item-section>
                </q-item>
                
                <q-separator />
                
                <q-item clickable v-close-popup @click="$emit('delete', quiz)">
                  <q-item-section avatar>
                    <q-icon name="delete" color="negative" />
                  </q-item-section>
                  <q-item-section class="text-negative">Delete</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  quiz: {
    type: Object,
    required: true
  },
  disableHover: {
    type: Boolean,
    default: false
  }
});

defineEmits(['click', 'preview', 'edit', 'analytics', 'duplicate', 'export', 'delete']);

const status = computed(() => props.quiz.status || 'draft');

const statusColor = computed(() => {
  const colors = {
    active: 'positive',
    draft: 'warning',
    archived: 'grey'
  };
  return colors[status.value] || 'grey';
});

const gradientBackground = computed(() => {
  // Generate gradient based on subject or use default
  const gradients = [
    'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
    'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
    'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
    'linear-gradient(135deg, #30cfd0 0%, #330867 100%)'
  ];
  
  const index = props.quiz.subject_id ? props.quiz.subject_id % gradients.length : 0;
  return gradients[index];
});

const truncateText = (text, maxLength) => {
  if (!text) return '';
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};
</script>

<style scoped lang="scss">
.quiz-card {
  position: relative;
  border-radius: 24px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: white;
  border: 1px solid rgba(0,0,0,0.05);
  
  &__gradient {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 8px;
    opacity: 0.9;
    transition: height 0.3s ease;
  }
  
  &__content {
    padding: 24px;
    position: relative;
    z-index: 1;
  }
  
  &__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
    gap: 12px;
  }
  
  &__title-section {
    flex: 1;
    min-width: 0;
  }
  
  &__title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0 0 4px 0;
    color: #1a202c;
    line-height: 1.4;
  }
  
  &__description {
    font-size: 0.875rem;
    color: #718096;
    margin: 0;
    line-height: 1.5;
  }
  
  &__status-badge {
    flex-shrink: 0;
    text-transform: capitalize;
    font-weight: 600;
  }
  
  &__stats {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 20px;
    padding: 16px 0;
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
  }
  
  &__stat {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.875rem;
    color: #4a5568;
    font-weight: 500;
    
    .q-icon {
      color: #718096;
    }
  }
  
  &__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  &__meta {
    display: flex;
    align-items: center;
    color: #4a5568;
  }
  
  &__actions {
    display: flex;
    gap: 4px;
    opacity: 0.8;
    transition: opacity 0.2s ease;
  }
  
  // Hover Effects
  &--hover:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 
                0 10px 10px -5px rgba(0, 0, 0, 0.04);
    
    .quiz-card__gradient {
      height: 100%;
      opacity: 0.03;
    }
    
    .quiz-card__actions {
      opacity: 1;
    }
  }
  
  // Status Variants
  &--active {
    border-color: rgba(34, 197, 94, 0.2);
  }
  
  &--draft {
    border-color: rgba(234, 179, 8, 0.2);
  }
  
  &--archived {
    opacity: 0.8;
    background: #f8fafc;
    
    .quiz-card__title,
    .quiz-card__description {
      color: #a0aec0;
    }
  }
}

// Responsive
@media (max-width: 600px) {
  .quiz-card {
    &__stats {
      gap: 12px;
    }
    
    &__stat {
      font-size: 0.8125rem;
    }
    
    &__actions {
      opacity: 1; // Always show on mobile
    }
  }
}
</style>
